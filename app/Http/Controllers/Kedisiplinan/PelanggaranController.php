<?php

namespace App\Http\Controllers\Kedisiplinan;

use App\Enums\PelanggaranStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kedisiplinan\StorePelanggaranRequest;
use App\Http\Requests\Kedisiplinan\UpdatePelanggaranRequest;
use App\Http\Requests\Kedisiplinan\UpdateStatusPelanggaranRequest;
use App\Models\Pelanggaran;
use App\Models\PelanggaranJenis;
use App\Models\Periode;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PelanggaranController extends Controller
{
    /**
     * Tampilan Utama Modul 10: Pelanggaran & Kedisiplinan Mahasantri
     */
    public function index(Request $request): View
    {
        $activePeriode = Periode::getActive();

        $statusFilter = in_array($request->query('status'), ['terdaftar', 'selesai']) ? $request->query('status') : null;
        $jenisFilter = filter_var($request->query('jenis_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $search = mb_substr(trim(strip_tags((string) $request->query('search'))), 0, 50);

        // 1. Single Aggregated KPI Query
        $statsQuery = DB::table('mahad_pelanggaran')->where('periode_id', $activePeriode?->id);
        if ($statusFilter && in_array($statusFilter, ['terdaftar', 'selesai'])) $statsQuery->where('status', $statusFilter);
        if ($jenisFilter) $statsQuery->where('jenis_pelanggaran_id', $jenisFilter);
        if ($search !== '') {
            $statsQuery->where(fn($q) => $q->where('deskripsi', 'like', "%{$search}%")->orWhere('hukuman', 'like', "%{$search}%")
                ->orWhereExists(fn($sub) => $sub->select(DB::raw(1))->from('mahad_santri')
                    ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                    ->whereColumn('mahad_santri.id', 'mahad_pelanggaran.santri_id')
                    ->where(fn($sq) => $sq->where('mahad_santri.nomor_induk', 'like', "%{$search}%")->orWhere('users.name', 'like', "%{$search}%"))));
        }

        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stats = $statsQuery->selectRaw('
            COUNT(*) as total_kasus,
            COALESCE(SUM(CASE WHEN tanggal >= ? AND tanggal <= ? THEN 1 ELSE 0 END), 0) as total_bulan_ini,
            COALESCE(SUM(CASE WHEN status = "terdaftar" THEN 1 ELSE 0 END), 0) as total_terdaftar,
            COALESCE(SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END), 0) as total_selesai
        ', [$startOfMonth, $endOfMonth])->first();

        // 2. Santri Paling Sering Melanggar (Radar Disiplin)
        $topViolatorsQuery = DB::table('mahad_pelanggaran')
            ->join('mahad_santri', 'mahad_pelanggaran.santri_id', '=', 'mahad_santri.id')
            ->join('users', 'mahad_santri.user_id', '=', 'users.id')
            ->where('mahad_pelanggaran.periode_id', $activePeriode?->id);

        if ($jenisFilter) $topViolatorsQuery->where('mahad_pelanggaran.jenis_pelanggaran_id', $jenisFilter);
        if ($search !== '') {
            $topViolatorsQuery->where(fn($q) => $q->where('mahad_pelanggaran.deskripsi', 'like', "%{$search}%")
                ->orWhere('mahad_santri.nomor_induk', 'like', "%{$search}%")->orWhere('users.name', 'like', "%{$search}%"));
        }

        $topViolators = $topViolatorsQuery->groupBy('mahad_santri.id', 'users.name', 'mahad_santri.nomor_induk')
            ->selectRaw('mahad_santri.id as santri_id, users.name, mahad_santri.nomor_induk, COUNT(*) as total_kasus')
            ->orderByDesc('total_kasus')->limit(5)->get();

        // 3. Query Data Pelanggaran (Eager Load Anti N+1)
        $query = Pelanggaran::query()->where('periode_id', $activePeriode?->id)
            ->with(['santri.user', 'jenisPelanggaran', 'inputBy']);

        if ($statusFilter && in_array($statusFilter, ['terdaftar', 'selesai'])) $query->where('status', $statusFilter);
        if ($jenisFilter) $query->where('jenis_pelanggaran_id', $jenisFilter);
        if ($search !== '') {
            $query->where(fn($q) => $q->where('deskripsi', 'like', "%{$search}%")->orWhere('hukuman', 'like', "%{$search}%")
                ->orWhereHas('santri', fn($sub) => $sub->where('nomor_induk', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))));
        }

        $pelanggaranList = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // 4. Cache Master Data & Lightweight Santri Dropdown
        $jenisList = Cache::remember('active_pelanggaran_jenis', 3600, function () {
            return PelanggaranJenis::where('is_active', true)->orderBy('nama', 'asc')->get();
        });

        $santriList = Cache::remember('active_santri_select_list_' . ($activePeriode?->id ?? '0'), 3600, function () use ($activePeriode) {
            return DB::table('mahad_santri')
                ->where('mahad_santri.periode_id', $activePeriode?->id)
                ->where('mahad_santri.status', 'aktif')
                ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                ->leftJoin('mahad_penempatan_kamar', fn($j) => $j->on('mahad_santri.id', '=', 'mahad_penempatan_kamar.santri_id')->where('mahad_penempatan_kamar.status', '=', 'aktif'))
                ->leftJoin('mahad_kamar', 'mahad_penempatan_kamar.kamar_id', '=', 'mahad_kamar.id')
                ->leftJoin('mahad_lantai', 'mahad_kamar.lantai_id', '=', 'mahad_lantai.id')
                ->leftJoin('mahad_gedung', 'mahad_lantai.gedung_id', '=', 'mahad_gedung.id')
                ->select(['mahad_santri.id', 'mahad_santri.nomor_induk as nim', 'users.name as nama', 'mahad_santri.prodi', 'mahad_santri.gender', 'mahad_gedung.nama as gedung_nama', 'mahad_kamar.nomor_kamar'])
                ->orderBy('mahad_santri.nomor_induk', 'asc')
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'nim' => $s->nim,
                    'nama' => $s->nama,
                    'prodi' => $s->prodi ?: 'Umum',
                    'gender' => $s->gender === 'L' ? 'Ikhwan' : 'Akhwat',
                    'kamar' => $s->nomor_kamar ? "{$s->gedung_nama} (Kamar {$s->nomor_kamar})" : 'Belum Ber-Kamar',
                ]);
        });

        return view('kedisiplinan.pelanggaran.index', compact('pelanggaranList', 'activePeriode', 'stats', 'topViolators', 'jenisList', 'santriList'));
    }

    public function store(StorePelanggaranRequest $request): RedirectResponse
    {
        $activePeriode = Periode::where('is_active', true)->first()
            ?? Periode::orderBy('tanggal_mulai', 'desc')->first();

        $validated = $request->validated();
        $jenis = PelanggaranJenis::find($validated['jenis_pelanggaran_id']);
        $poin = !empty($validated['poin']) ? (int) $validated['poin'] : ($jenis?->poin_default ?? 5);

        DB::transaction(function () use ($validated, $activePeriode, $poin) {
            Pelanggaran::create([
                'periode_id' => $activePeriode->id,
                'santri_id' => $validated['santri_id'],
                'jenis_pelanggaran_id' => $validated['jenis_pelanggaran_id'],
                'tanggal' => $validated['tanggal'],
                'poin' => $poin,
                'deskripsi' => $validated['deskripsi'],
                'hukuman' => $validated['hukuman'] ?? null,
                'status' => $validated['status'] ?? PelanggaranStatus::Terdaftar->value,
                'input_by' => Auth::id() ?? 1,
            ]);
        });

        return redirect()->route('kedisiplinan.pelanggaran.index')->with('success', 'Catatan pelanggaran disiplin santri berhasil ditambahkan.');
    }

    public function update(UpdatePelanggaranRequest $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $validated = $request->validated();
        $jenis = PelanggaranJenis::find($validated['jenis_pelanggaran_id']);
        $poin = !empty($validated['poin']) ? (int) $validated['poin'] : ($jenis?->poin_default ?? 5);

        DB::transaction(function () use ($pelanggaran, $validated, $poin) {
            $pelanggaran->update([
                'tanggal' => $validated['tanggal'],
                'jenis_pelanggaran_id' => $validated['jenis_pelanggaran_id'],
                'poin' => $poin,
                'deskripsi' => $validated['deskripsi'],
                'hukuman' => $validated['hukuman'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('kedisiplinan.pelanggaran.index')->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    public function updateStatus(UpdateStatusPelanggaranRequest $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $validated = $request->validated();
        DB::transaction(function () use ($pelanggaran, $validated) {
            $data = ['status' => $validated['status']];
            if (!empty($validated['hukuman'])) $data['hukuman'] = $validated['hukuman'];
            $pelanggaran->update($data);
        });

        return back()->with('success', 'Status tindak lanjut pelanggaran berhasil diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran): RedirectResponse
    {
        if (!Auth::user()?->hasRole('admin')) {
            abort(403, 'Aksi ini hanya diperbolehkan untuk Administrator.');
        }

        DB::transaction(fn() => $pelanggaran->delete());
        return back()->with('success', 'Catatan pelanggaran berhasil dihapus.');
    }

    public function export(Request $request, \App\Services\PelaporanExportService $exportService): Response
    {
        $activePeriode = Periode::getActive();
        $scope = $request->query('scope', 'bulan');
        $bulan = $scope === 'bulan' ? ($request->query('bulan') ?: date('Y-m')) : null;
        $format = $request->query('format', 'pdf');
        $status = $request->query('status');

        return $exportService->exportPelanggaran($activePeriode?->id ?? 0, $status, $format, $bulan);
    }
}
