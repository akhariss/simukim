<?php

namespace App\Http\Controllers\Kedisiplinan;

use App\Enums\PerizinanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kedisiplinan\StorePerizinanRequest;
use App\Http\Requests\Kedisiplinan\UpdateStatusPerizinanRequest;
use App\Models\Pengaturan;
use App\Models\Perizinan;
use App\Models\PerizinanJenis;
use App\Models\Periode;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PerizinanController extends Controller
{
    /**
     * Tampilan Utama Modul 9: Perizinan Mahasantri
     */
    public function index(Request $request): View
    {
        $activePeriode = Periode::getActive();

        $statusFilter = $request->query('status');
        $jenisFilter = $request->query('jenis_id');
        $search = trim((string) $request->query('search'));

        $user = Auth::user();
        $isMahasantri = $user?->hasRole('mahasantri') ?? false;
        $currentSantriId = $user?->santri?->id;

        // 1. Single Aggregated KPI Query
        $statsQuery = DB::table('mahad_perizinan')->where('periode_id', $activePeriode?->id);
        if ($isMahasantri && $currentSantriId) {
            $statsQuery->where('santri_id', $currentSantriId);
        }
        if ($statusFilter && in_array($statusFilter, ['berlaku', 'selesai', 'dibatalkan'])) {
            $statsQuery->where('status', $statusFilter);
        }
        if ($jenisFilter) $statsQuery->where('jenis_perizinan_id', $jenisFilter);
        if ($search !== '') {
            $statsQuery->where(fn($q) => $q->where('alasan', 'like', "%{$search}%")
                ->orWhereExists(fn($sub) => $sub->select(DB::raw(1))->from('mahad_santri')
                    ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                    ->whereColumn('mahad_santri.id', 'mahad_perizinan.santri_id')
                    ->where(fn($sq) => $sq->where('mahad_santri.nomor_induk', 'like', "%{$search}%")->orWhere('users.name', 'like', "%{$search}%"))));
        }

        $stats = $statsQuery->selectRaw('
            COUNT(*) as total_izin,
            COALESCE(SUM(CASE WHEN status = "berlaku" THEN 1 ELSE 0 END), 0) as total_berlaku,
            COALESCE(SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END), 0) as total_selesai,
            COALESCE(SUM(CASE WHEN status = "dibatalkan" THEN 1 ELSE 0 END), 0) as total_dibatalkan
        ')->first();

        // 2. Query Data Perizinan (Eager Load Anti N+1)
        $query = Perizinan::query()->where('periode_id', $activePeriode?->id)
            ->with(['santri.user', 'jenisPerizinan', 'inputBy']);

        if ($isMahasantri && $currentSantriId) {
            $query->where('santri_id', $currentSantriId);
        }
        if ($statusFilter && in_array($statusFilter, ['berlaku', 'selesai', 'dibatalkan'])) $query->where('status', $statusFilter);
        if ($jenisFilter) $query->where('jenis_perizinan_id', $jenisFilter);
        if ($search !== '') {
            $query->where(fn($q) => $q->where('alasan', 'like', "%{$search}%")
                ->orWhereHas('santri', fn($sub) => $sub->where('nomor_induk', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))));
        }

        $perizinanList = $query->orderBy('tanggal_mulai', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // 3. Cache Master Data & Lightweight Santri Dropdown
        $jenisList = Cache::remember('active_perizinan_jenis', 3600, function () {
            return PerizinanJenis::where('is_active', true)->orderBy('nama', 'asc')->get();
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

        $pelanggaranSaya = collect();
        if ($isMahasantri && $currentSantriId) {
            $pelanggaranSaya = \App\Models\Pelanggaran::where('santri_id', $currentSantriId)
                ->where('periode_id', $activePeriode?->id)
                ->with(['jenisPelanggaran', 'penindak'])
                ->orderByDesc('tanggal')
                ->get();
        }

        return view('kedisiplinan.perizinan.index', compact('perizinanList', 'activePeriode', 'stats', 'jenisList', 'santriList', 'isMahasantri', 'pelanggaranSaya'));
    }

    public function store(StorePerizinanRequest $request): RedirectResponse
    {
        $activePeriode = Periode::where('is_active', true)->first()
            ?? Periode::orderBy('tanggal_mulai', 'desc')->first();

        $validated = $request->validated();
        if (Auth::user()->hasRole('mahasantri')) {
            $santri = Auth::user()->santri;
            if (!$santri) {
                return back()->with('error', 'Akun Anda belum terdaftar sebagai mahasantri aktif.');
            }
            $validated['santri_id'] = $santri->id;
        }

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $filePath = $file->storeAs('bukti_perizinan', 'izin_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension(), 'local');
        }

        DB::transaction(function () use ($validated, $filePath, $activePeriode) {
            Perizinan::create([
                'periode_id' => $activePeriode->id,
                'santri_id' => $validated['santri_id'],
                'jenis_perizinan_id' => $validated['jenis_perizinan_id'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'alasan' => $validated['alasan'],
                'file_bukti' => $filePath,
                'status' => PerizinanStatus::Berlaku->value,
                'input_by' => Auth::id() ?? 1,
            ]);
        });

        return redirect()->route('kedisiplinan.perizinan.index')->with('success', 'Permohonan izin mahasantri berhasil dicatat dan berstatus aktif (berlaku).');
    }

    public function updateStatus(UpdateStatusPerizinanRequest $request, Perizinan $perizinan): RedirectResponse
    {
        if (Auth::user()->hasRole('mahasantri')) {
            abort(403, 'Mahasantri tidak memiliki hak mengubah status perizinan.');
        }

        $validated = $request->validated();
        DB::transaction(fn() => $perizinan->update(['status' => $validated['status']]));
        $statusText = $validated['status'] === PerizinanStatus::Selesai->value ? 'diselesaikan (santri telah kembali)' : 'dibatalkan';
        return back()->with('success', "Status izin berhasil diperbarui menjadi {$statusText}.");
    }

    public function destroy(Perizinan $perizinan): RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasRole('mahasantri')) {
            if ($perizinan->santri_id !== $user->santri?->id || $perizinan->status !== PerizinanStatus::Berlaku->value) {
                abort(403, 'Anda tidak berhak menghapus perizinan ini.');
            }
        } elseif (!$user->hasAnyRole(['admin', 'mudabbir'])) {
            abort(403, 'Akses tidak diizinkan untuk menghapus data perizinan.');
        }

        DB::transaction(function () use ($perizinan) {
            if ($perizinan->file_bukti) {
                if (Storage::disk('local')->exists($perizinan->file_bukti)) {
                    Storage::disk('local')->delete($perizinan->file_bukti);
                }
                if (Storage::disk('public')->exists($perizinan->file_bukti)) {
                    Storage::disk('public')->delete($perizinan->file_bukti);
                }
            }
            $perizinan->delete();
        });

        return back()->with('success', 'Data perizinan berhasil dihapus.');
    }

    public function export(Request $request, \App\Services\PelaporanExportService $exportService): Response
    {
        $activePeriode = Periode::getActive();
        $scope = $request->query('scope', 'bulan');
        $bulan = $scope === 'bulan' ? ($request->query('bulan') ?: date('Y-m')) : null;
        $format = $request->query('format', 'pdf');
        $status = $request->query('status');

        return $exportService->exportPerizinan($activePeriode?->id ?? 0, $status, $format, $bulan);
    }

    public function cetak(Perizinan $perizinan): View
    {
        if (Auth::user()->hasRole('mahasantri') && $perizinan->santri_id !== Auth::user()->santri?->id) {
            abort(403, 'Anda hanya berhak mencetak surat izin milik sendiri.');
        }

        $perizinan->load(['santri.user', 'santri.penempatanAktif.kamar.lantai.gedung', 'jenisPerizinan', 'periode', 'inputBy']);

        return view('kedisiplinan.perizinan.cetak', [
            'perizinan' => $perizinan,
            'namaMahad' => Pengaturan::get('nama_mahad', "Ma'had Al-Jami'ah Terpadu"),
            'kepalaMahad' => Pengaturan::get('nama_kepala', "Dr. KH. Abdullah Shiddiq, M.Ag."),
            'nipKepala' => Pengaturan::get('nip_kepala', "197808152003121003"),
            'alamatMahad' => Pengaturan::get('alamat', "Kompleks Asrama Kampus Terpadu"),
        ]);
    }
}
