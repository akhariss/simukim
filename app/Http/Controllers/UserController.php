<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreLocalUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    /**
     * Tampilan Direktori & Kelola Pengguna
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $activePeriode = Periode::getActive();
        $periodeFilter = $request->get('periode_filter', 'active');
        if (!in_array($periodeFilter, ['active', 'all'])) $periodeFilter = 'active';

        $query = User::with([
            'roles',
            'santri.penempatanAktif.kamar.lantai.gedung',
            'kelasTutor',
            'halaqahBinaan',
        ]);

        if ($periodeFilter === 'active' && $activePeriode) {
            $query->where(function ($q) use ($activePeriode) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('name', ['admin', 'tutor']))
                  ->orWhereHas('santri', fn($sq) => $sq->where('periode_id', $activePeriode->id));
            });
        }

        if ($request->filled('search')) {
            $search = mb_substr(trim(strip_tags((string) $request->search)), 0, 50);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        $activeRole = $request->get('role', 'all');
        if ($activeRole !== 'all') {
            if (in_array($activeRole, ['mahasantri', 'mudabbir', 'tutor', 'admin'])) {
                $query->role($activeRole);
            } else {
                $activeRole = 'all';
            }
        }

        if ($request->filled('auth_source')) {
            $authSource = in_array($request->auth_source, ['local', 'campus']) ? $request->auth_source : null;
            if ($authSource) $query->where('auth_source', $authSource);
        }

        if ($request->filled('status')) {
            $status = in_array($request->status, ['aktif', 'nonaktif']) ? $request->status : null;
            if ($status) $query->whereHas('santri', fn($q) => $q->where('status', $status));
        }

        $perPage = (int) $request->get('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100])) $perPage = 25;

        $users = $query->latest()->paginate($perPage)->withQueryString();
        $roles = Cache::remember('all_roles', 300, fn() => Role::all());

        $cacheKey = "users_role_counts_{$periodeFilter}_" . ($activePeriode?->id ?? 0);
        $counts = Cache::remember($cacheKey, 3600, function () use ($periodeFilter, $activePeriode) {
            $base = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', User::class);

            if ($periodeFilter === 'active' && $activePeriode) {
                $base->leftJoin('mahad_santri', 'model_has_roles.model_id', '=', 'mahad_santri.user_id')
                    ->where(function ($q) use ($activePeriode) {
                        $q->whereIn('roles.name', ['admin', 'tutor'])
                          ->orWhere('mahad_santri.periode_id', $activePeriode->id);
                    });
            }

            $roleCounts = $base->select('roles.name', DB::raw('count(distinct model_has_roles.model_id) as total'))
                ->groupBy('roles.name')->pluck('total', 'name');

            $mahasantri = (int) ($roleCounts['mahasantri'] ?? 0);
            $mudabbir   = (int) ($roleCounts['mudabbir'] ?? 0);
            $tutor      = (int) ($roleCounts['tutor'] ?? 0);
            $admin      = (int) ($roleCounts['admin'] ?? 0);
            $all        = $mahasantri + $mudabbir + $tutor + $admin;

            return compact('all', 'mahasantri', 'mudabbir', 'tutor', 'admin');
        });

        return view('users.index', compact('users', 'roles', 'counts', 'activeRole', 'perPage', 'activePeriode', 'periodeFilter'));
    }

    /**
     * Tambah Pengguna Baru (SSO Kampus atau Mandiri)
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $role = $v['role'];
        $authSource = $v['auth_source'] ?? 'campus';
        $nomorInduk = trim((string) $v['nomor_induk']);
        $name = trim((string) $v['name']);

        $email = !empty($v['email']) ? trim((string) $v['email']) : (
            in_array($role, ['mahasantri', 'mudabbir'], true)
                ? "{$nomorInduk}@student.uinssc.ac.id"
                : "{$nomorInduk}@uinssc.ac.id"
        );

        $activePeriode = Periode::getActive();
        $rawPassword = !empty($v['password']) ? trim((string) $v['password']) : 'user';

        $user = DB::transaction(function () use ($v, $role, $authSource, $nomorInduk, $name, $email, $rawPassword, $activePeriode) {
            $user = User::updateOrCreate(['nomor_induk' => $nomorInduk], [
                'name' => $name,
                'email' => $email,
                'auth_source' => $authSource,
                'password' => Hash::make($rawPassword),
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([$role]);

            if (in_array($role, ['mahasantri', 'mudabbir'], true)) {
                $angkatan = !empty($v['angkatan']) ? trim((string) $v['angkatan']) : (
                    strlen($nomorInduk) >= 2 ? ('20' . substr($nomorInduk, 0, 2)) : date('Y')
                );

                Santri::updateOrCreate(['user_id' => $user->id], [
                    'nomor_induk' => $nomorInduk,
                    'gender' => $v['gender'] ?? 'L',
                    'prodi' => !empty($v['prodi']) ? trim((string) $v['prodi']) : 'Umum',
                    'angkatan' => $angkatan,
                    'no_hp' => !empty($v['no_hp']) ? trim((string) $v['no_hp']) : null,
                    'status' => 'aktif',
                    'periode_id' => $activePeriode?->id,
                ]);
            }

            return $user;
        });

        $this->clearRoleCountsCache();
        $roleLabels = [
            'mahasantri' => 'Mahasantri',
            'mudabbir' => 'Mudabbir',
            'tutor' => 'Tutor Pengajar',
            'admin' => 'Administrator',
        ];
        $label = $roleLabels[$role] ?? ucfirst($role);

        return redirect()->route('users.index', ['role' => $role])
            ->with('success', "Akun {$user->name} ({$user->nomor_induk}) berhasil didaftarkan sebagai {$label}!");
    }

    /**
     * Tambah Pengguna Lokal (Backward Compatibility)
     */
    public function storeLocal(StoreLocalUserRequest $request): RedirectResponse
    {
        $v = array_merge($request->validated(), ['auth_source' => 'local']);
        $subRequest = new StoreUserRequest();
        $subRequest->merge($v);
        $subRequest->setValidator(app('validator')->make($v, (new StoreUserRequest())->rules()));
        return $this->store($subRequest);
    }

    protected function clearRoleCountsCache(): void
    {
        $id = Periode::getActive()?->id ?? 0;
        foreach (["users_role_counts_active_{$id}", "users_role_counts_all_{$id}", 'users_role_counts_active_0', 'users_role_counts_all_0', 'users_role_counts', 'dashboard_stats'] as $k) {
            Cache::forget($k);
        }
    }

    public function toggleSantriStatus(User $user): RedirectResponse
    {
        if (!$user->santri) return back()->with('error', 'Pengguna ini bukan santri.');
        $newStatus = $user->santri->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->santri->update(['status' => $newStatus]);
        $this->clearRoleCountsCache();
        return back()->with('success', "Status santri {$user->name} berhasil " . ($newStatus === 'aktif' ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $v = $request->validated();
        DB::transaction(function () use ($user, $v) {
            $data = ['name' => $v['name'], 'email' => $v['email'], 'nomor_induk' => $v['nomor_induk']];
            if (!empty($v['password'])) $data['password'] = Hash::make($v['password']);
            $user->update($data);
            $user->syncRoles([$v['role']]);
            if (in_array($v['role'], ['admin', 'tutor']) && $user->santri) {
                \App\Models\KelompokAnggota::where('santri_id', $user->santri->id)->delete();
            }
            if (in_array($v['role'], ['mahasantri', 'mudabbir'])) {
                $st = [
                    'nomor_induk' => $v['nomor_induk'], 'gender' => $v['gender'] ?? ($user->santri?->gender ?? 'L'),
                    'prodi' => $v['prodi'] ?? ($user->santri?->prodi ?? '-'), 'fakultas' => $v['fakultas'] ?? ($user->santri?->fakultas ?? null),
                    'angkatan' => $v['angkatan'] ?? ($user->santri?->angkatan ?? date('Y')), 'no_hp' => $v['no_hp'] ?? ($user->santri?->no_hp ?? null),
                    'alamat' => $v['alamat'] ?? ($user->santri?->alamat ?? null),
                ];
                if (!empty($v['status'])) $st['status'] = $v['status'];
                Santri::updateOrCreate(['user_id' => $user->id], $st);
            }
        });
        $this->clearRoleCountsCache();
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function create(): \Illuminate\View\View { return view('users.create', ['roles' => Cache::remember('all_roles', 300, fn() => Role::all())]); }
    public function show(User $user): \Illuminate\View\View { $user->load(['roles', 'santri.penempatanAktif.kamar.lantai.gedung', 'kelasTutor', 'halaqahBinaan']); return view('users.show', compact('user')); }
    public function edit(User $user): \Illuminate\View\View { $user->load(['roles', 'santri.penempatanAktif.kamar.lantai.gedung']); return view('users.edit', ['user' => $user, 'roles' => Cache::remember('all_roles', 300, fn() => Role::all())]); }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        DB::transaction(function () use ($user) {
            if ($user->santri) {
                $user->santri->penempatanKamar()->delete();
                $user->santri->delete();
            }
            $user->delete();
        });
        $this->clearRoleCountsCache();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
