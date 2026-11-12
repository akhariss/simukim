<?php

namespace App\Http\Controllers\Fasilitas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fasilitas\AssignBatchKamarRequest;
use App\Http\Requests\Fasilitas\AssignKamarRequest;
use App\Http\Requests\Fasilitas\AutoAssignKamarRequest;
use App\Http\Requests\Fasilitas\PindahKamarRequest;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use App\Services\Fasilitas\PenempatanService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenempatanController extends Controller
{
    /**
     * Tampilan Utama Operasional Penempatan Mahasantri
     */
    public function index(Request $request, PenempatanService $service): View
    {
        $tab = in_array($request->query('tab'), ['aktif', 'unassigned', 'riwayat']) ? $request->query('tab') : 'aktif';
        $search = mb_substr(trim(strip_tags((string) $request->query('search'))), 0, 50);
        $gedungId = filter_var($request->query('gedung_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        if ($gedungId && !Gedung::where('id', $gedungId)->exists()) {
            $gedungId = null;
        }
        $genderFilter = in_array($request->query('gender'), ['L', 'P']) ? $request->query('gender') : null;
        $roleFilter = in_array($request->query('role'), ['mudabbir', 'mahasantri']) ? $request->query('role') : null;
        $periodeAktif = Periode::getActive();

        // 1. Tab-Scoped Conditional Querying
        if ($tab === 'unassigned') {
            $penempatanList = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $availableRooms = $service->getAvailableRoomsQuery($gedungId, $genderFilter, $search)->paginate(24, ['*'], 'kamar_page')->withQueryString();

            // Tab Santri Belum Ber-kamar
            $unassignedQuery = Santri::query()->where('status', 'aktif')->whereDoesntHave('penempatanAktif')->with('user');
            if ($periodeAktif) $unassignedQuery->where('periode_id', $periodeAktif->id);
            if ($genderFilter) $unassignedQuery->where('gender', $genderFilter);
            if ($roleFilter === 'mudabbir') $unassignedQuery->whereHas('user.roles', fn($q) => $q->where('name', 'mudabbir'));
            elseif ($roleFilter === 'mahasantri') $unassignedQuery->whereHas('user.roles', fn($q) => $q->where('name', 'mahasantri'));

            if ($search !== '') {
                $unassignedQuery->where(fn($q) => $q->where('nomor_induk', 'like', "%{$search}%")->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")));
            }

            $unassignedSantri = $unassignedQuery->orderBy('nomor_induk', 'asc')->paginate(20)->withQueryString();
        } else {
            $unassignedSantri = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $availableRooms = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24);

            // Tab Penempatan Aktif / Riwayat
            $penempatanQuery = PenempatanKamar::query()->with([
                'santri.user', 'kamar.lantai.gedung', 'assignedBy', 'periode',
            ]);

            if ($tab === 'aktif') {
                $penempatanQuery->where('status', 'aktif');
                if ($periodeAktif) $penempatanQuery->where('periode_id', $periodeAktif->id);
            } elseif ($tab === 'riwayat') {
                $penempatanQuery->whereIn('status', ['pindah', 'keluar']);
                if ($periodeAktif) $penempatanQuery->where('periode_id', $periodeAktif->id);
            }

            if ($gedungId) $penempatanQuery->whereHas('kamar.lantai', fn($q) => $q->where('gedung_id', $gedungId));
            if ($genderFilter) $penempatanQuery->whereHas('santri', fn($q) => $q->where('gender', $genderFilter));
            if ($roleFilter === 'mudabbir') $penempatanQuery->whereHas('santri.user.roles', fn($q) => $q->where('name', 'mudabbir'));
            elseif ($roleFilter === 'mahasantri') $penempatanQuery->whereHas('santri.user.roles', fn($q) => $q->where('name', 'mahasantri'));

            if ($search !== '') {
                $penempatanQuery->where(fn($q) => $q->whereHas('santri', fn($sq) => $sq->where('nomor_induk', 'like', "%{$search}%")->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")))
                    ->orWhereHas('kamar', fn($kq) => $kq->where('nomor_kamar', 'like', "%{$search}%")));
            }

            $penempatanList = $penempatanQuery->latest()->paginate(20)->withQueryString();
        }

        // 2. Single Aggregated KPI Query (Scoped by active periode)
        $cacheKey = 'penempatan_kpi_counts_' . ($periodeAktif?->id ?? 'all');
        $counts = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($periodeAktif) {
            $pkActiveSub = DB::table('mahad_penempatan_kamar')
                ->select('santri_id')
                ->where('status', 'aktif')
                ->when($periodeAktif, fn($q) => $q->where('periode_id', $periodeAktif->id))
                ->groupBy('santri_id');

            $unassignedRow = DB::table('mahad_santri')
                ->leftJoinSub($pkActiveSub, 'pka', 'mahad_santri.id', '=', 'pka.santri_id')
                ->when($periodeAktif, fn($q) => $q->where('mahad_santri.periode_id', $periodeAktif->id))
                ->selectRaw('
                    COUNT(CASE WHEN mahad_santri.status = "aktif" AND pka.santri_id IS NULL THEN 1 END) as unassigned
                ')->first();

            $pkStats = DB::table('mahad_penempatan_kamar')
                ->when($periodeAktif, fn($q) => $q->where('periode_id', $periodeAktif->id))
                ->selectRaw('
                    COUNT(CASE WHEN status = "aktif" THEN 1 END) as aktif,
                    COUNT(CASE WHEN status IN ("pindah", "keluar") THEN 1 END) as riwayat
                ')->first();

            return [
                'aktif' => (int) ($pkStats->aktif ?? 0),
                'unassigned' => (int) ($unassignedRow->unassigned ?? 0),
                'riwayat' => (int) ($pkStats->riwayat ?? 0),
            ];
        });

        $gedungList = \Illuminate\Support\Facades\Cache::remember('active_gedung_with_lantai', 3600, function () {
            return Gedung::where('is_active', true)->with(['lantai' => fn($q) => $q->orderBy('nomor_lantai', 'asc')])->get();
        });

        return view('fasilitas.penempatan.index', compact(
            'tab', 'penempatanList', 'unassignedSantri', 'availableRooms', 'counts', 'gedungList', 'periodeAktif'
        ));
    }

    public function assign(AssignKamarRequest $request, PenempatanService $service): RedirectResponse
    {
        try {
            $penempatan = $service->assign($request->validated(), (int) (Auth::id() ?? 1));
            return redirect()->route('fasilitas.penempatan.index', ['tab' => 'aktif'])
                ->with('success', "Mahasantri berhasil ditempatkan ke Kamar {$penempatan->kamar->nomor_kamar}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pindah(PindahKamarRequest $request, PenempatanKamar $penempatan, PenempatanService $service): RedirectResponse
    {
        try {
            $service->pindah($penempatan, $request->validated(), (int) (Auth::id() ?? 1));
            return redirect()->route('fasilitas.penempatan.index')->with('success', "Mahasantri berhasil dipindahkan ke kamar baru.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function keluar(PenempatanKamar $penempatan, PenempatanService $service): RedirectResponse
    {
        try {
            $service->keluar($penempatan);
            return back()->with('success', "Mahasantri telah dikeluarkan dari kamar dan status penempatan diarsipkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cetakKartu(PenempatanKamar $penempatan): View
    {
        $penempatan->load(['santri.user', 'kamar.lantai.gedung', 'assignedBy', 'periode']);
        return view('fasilitas.penempatan.kartu', compact('penempatan'));
    }

    public function getAvailableRooms(Request $request, PenempatanService $service): JsonResponse
    {
        $gedungId = filter_var($request->query('gedung_id'), FILTER_VALIDATE_INT) ?: null;
        $gender = in_array($request->query('gender'), ['L', 'P']) ? $request->query('gender') : null;
        $search = mb_substr(trim(strip_tags((string) $request->query('search'))), 0, 50) ?: null;

        $rooms = $service->getAvailableRoomsQuery($gedungId, $gender, $search)->get()->map(fn($k) => [
            'id' => $k->id,
            'nomor_kamar' => $k->nomor_kamar,
            'tipe_ruang' => $k->tipe_ruang,
            'gender' => $k->gender,
            'gedung' => $k->lantai->gedung->nama ?? '-',
            'lantai' => $k->lantai->nomor_lantai ?? '-',
            'kapasitas' => $k->kapasitas_maks,
            'terisi' => (int) $k->terisi_count,
            'sisa' => max(0, $k->kapasitas_maks - (int) $k->terisi_count),
        ]);

        return response()->json($rooms);
    }

    public function autoAssign(AutoAssignKamarRequest $request, PenempatanService $service): RedirectResponse
    {
        $validated = $request->validated();
        $tanggalMasuk = $validated['tanggal_masuk'] ?? now()->toDateString();
        $result = $service->autoAssign($tanggalMasuk, (int) (Auth::id() ?? 1));

        if ($result['berhasil'] === 0 && $result['gagal'] === 0) {
            return back()->with('info', 'Tidak ada mahasantri yang perlu ditempatkan secara otomatis.');
        }

        $pesan = "{$result['berhasil']} mahasantri berhasil ditempatkan secara otomatis.";
        if ($result['gagal'] > 0) {
            $pesan .= " {$result['gagal']} gagal: " . implode('; ', array_slice($result['pesan_gagal'], 0, 3));
            return redirect()->route('fasilitas.penempatan.index', ['tab' => 'unassigned'])->with('warning', $pesan);
        }

        return redirect()->route('fasilitas.penempatan.index', ['tab' => 'aktif'])->with('success', $pesan);
    }

    public function assignBatch(AssignBatchKamarRequest $request, PenempatanService $service): RedirectResponse
    {
        try {
            $result = $service->assignBatch($request->validated(), (int) (Auth::id() ?? 1));
            return redirect()->route('fasilitas.penempatan.index', ['tab' => 'aktif'])
                ->with('success', "{$result['placed']} mahasantri berhasil ditempatkan ke Kamar {$result['kamar']->nomor_kamar}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function getUnassignedSantri(Request $request): JsonResponse
    {
        $periodeAktif = Periode::getActive();
        $query = Santri::query()->where('status', 'aktif')->whereDoesntHave('penempatanAktif')->with('user');
        if ($periodeAktif) $query->where('periode_id', $periodeAktif->id);
        if ($request->query('gender')) $query->where('gender', $request->query('gender'));

        $search = mb_substr(trim(strip_tags((string) $request->query('search'))), 0, 50);
        if ($search !== '') {
            $query->where(fn($q) => $q->where('nomor_induk', 'like', "%{$search}%")
                ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")));
        }

        $santris = $query->orderBy('nomor_induk', 'asc')->limit(500)->get()->map(fn($s) => [
            'id' => $s->id, 'name' => $s->user->name ?? '-', 'nomor_induk' => $s->nomor_induk,
            'gender' => $s->gender->value, 'prodi' => $s->prodi ?? 'Reguler', 'is_mudabbir' => $s->isMudabbir(),
        ]);

        return response()->json($santris);
    }
}
