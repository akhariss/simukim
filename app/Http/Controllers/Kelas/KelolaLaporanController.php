<?php

namespace App\Http\Controllers\Kelas;

use App\Enums\LaporanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kelas\StoreLaporanBulananRequest;
use App\Http\Requests\Kelas\UpdateLaporanStatusRequest;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\LaporanBulanan;
use App\Models\LaporanBulananFoto;
use App\Models\Periode;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KelolaLaporanController extends Controller
{
    public function index(Request $request): View
    {
        $periode = Periode::getActive();
        $user = Auth::user();
        $isSaya = ($user->hasRole('tutor') && !$user->hasRole('admin')) || $request->query('mode') === 'saya';

        $myKajianList = collect();
        if ($isSaya) {
            $myKajianList = DB::table('mahad_kelompok_pengampu')
                ->join('mahad_kelompok', 'mahad_kelompok_pengampu.kelompok_id', '=', 'mahad_kelompok.id')
                ->join('mahad_kelas', 'mahad_kelompok_pengampu.kelas_id', '=', 'mahad_kelas.id')
                ->where('mahad_kelompok.periode_id', $periode?->id)
                ->where('mahad_kelompok_pengampu.tutor_id', $user->id)
                ->select('mahad_kelompok.id as kelompok_id', 'mahad_kelompok.nama as kelompok_nama', 'mahad_kelas.id as kelas_id', 'mahad_kelas.nama as kelas_nama', 'mahad_kelas.tipe as kelas_tipe')
                ->orderBy('mahad_kelompok.nama')->get()->map(function ($item) use ($user) {
                    $m = (int) date('m');
                    $y = (int) date('Y');
                    $lap = DB::table('mahad_laporan_bulanan')->where('kelompok_id', $item->kelompok_id)->where('kelas_id', $item->kelas_id)->where('tutor_id', $user->id)->where('bulan', $m)->where('tahun', $y)->first();
                    $tot = DB::table('mahad_laporan_bulanan')->where('kelompok_id', $item->kelompok_id)->where('kelas_id', $item->kelas_id)->where('tutor_id', $user->id)->count();
                    $jdw = DB::table('mahad_kelas_jadwal')->where('kelas_id', $item->kelas_id)->get();
                    $jdwText = $jdw->isNotEmpty() ? $jdw->map(fn($j) => $j->hari . ' (' . substr($j->jam_mulai, 0, 5) . ')')->unique()->join(', ') : 'Sesuai Jadwal';
                    return (object) array_merge((array) $item, ['laporan_bulan_ini' => $lap, 'total_laporan' => $tot, 'jadwal_text' => $jdwText]);
                });
        }

        $query = LaporanBulanan::query()
            ->when($periode, fn ($q) => $q->where('periode_id', $periode->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('bulan'), fn ($q) => $q->where('bulan', $request->bulan))
            ->when($isSaya, fn ($q) => $q->where('tutor_id', $user->id))
            ->with(['tutor', 'kelompok', 'kelas', 'reviewer'])
            ->orderByDesc('tahun')->orderByDesc('bulan')->paginate(15)->withQueryString();

        $stats = $periode ? LaporanBulanan::where('periode_id', $periode->id)
            ->when($isSaya, fn ($q) => $q->where('tutor_id', $user->id))
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as menunggu, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as direview, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as selesai', [LaporanStatus::MenungguReview->value, LaporanStatus::Direview->value, LaporanStatus::Selesai->value])->first() : null;

        return view('kelas.laporan.index', compact('periode', 'query', 'stats', 'myKajianList'));
    }

    public function showKelas(Request $request, Kelompok $kelompok, Kelas $kelas): View
    {
        $this->authorizeTutor($kelompok->id, $kelas->id);
        $periode = Periode::getActive();
        $riwayatLaporan = LaporanBulanan::where('kelompok_id', $kelompok->id)->where('kelas_id', $kelas->id)
            ->when(!Auth::user()->hasRole('admin'), fn ($q) => $q->where('tutor_id', Auth::id()))
            ->with(['foto', 'reviewer', 'tutor'])->orderByDesc('tahun')->orderByDesc('bulan')->get();
        $kelas->load('jadwal');
        return view('kelas.laporan.kelas', compact('kelompok', 'kelas', 'riwayatLaporan', 'periode'));
    }

    public function create(Kelompok $kelompok, Kelas $kelas): View
    {
        $this->authorizeTutor($kelompok->id, $kelas->id);
        $periode = Periode::getActive();
        $analytics = $this->computeMonthlyAnalytics($kelompok->id, $kelas->id, (int) date('m'), (int) date('Y'));
        $kelas->load('jadwal');
        return view('kelas.laporan.create', array_merge(['kelompok' => $kelompok, 'kelas' => $kelas, 'periode' => $periode], $analytics));
    }

    public function edit(LaporanBulanan $laporan): View
    {
        if (!Auth::user()->hasRole('admin') && $laporan->tutor_id !== Auth::id()) abort(403);
        if ($laporan->status !== LaporanStatus::MenungguReview) {
            abort(403, 'Laporan yang telah diverifikasi tidak dapat diubah.');
        }
        $laporan->load(['kelompok', 'kelas.jadwal', 'foto']);
        $analytics = $this->computeMonthlyAnalytics($laporan->kelompok_id, $laporan->kelas_id, $laporan->bulan, $laporan->tahun);
        return view('kelas.laporan.edit', array_merge(['laporan' => $laporan, 'kelompok' => $laporan->kelompok, 'kelas' => $laporan->kelas], $analytics));
    }

    public function store(StoreLaporanBulananRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $this->authorizeTutor((int)$v['kelompok_id'], (int)$v['kelas_id']);
        $periode = Periode::getActive() ?? Periode::firstOrFail();
        $tahun = $v['tahun'] ?? (int) date('Y');

        DB::transaction(function () use ($request, $v, $periode, $tahun) {
            $laporan = LaporanBulanan::where('kelompok_id', $v['kelompok_id'])->where('kelas_id', $v['kelas_id'])
                ->where('tutor_id', Auth::id())->where('bulan', $v['bulan'])->where('tahun', $tahun)->first();

            if ($laporan) {
                $laporan->update(['catatan' => $v['catatan'], 'status' => LaporanStatus::MenungguReview]);
                $this->clearPhotos($laporan);
            } else {
                $laporan = LaporanBulanan::create([
                    'periode_id' => $periode->id, 'kelompok_id' => $v['kelompok_id'], 'kelas_id' => $v['kelas_id'],
                    'tutor_id' => Auth::id(), 'bulan' => $v['bulan'], 'tahun' => $tahun,
                    'catatan' => $v['catatan'], 'status' => LaporanStatus::MenungguReview,
                ]);
            }
            $this->savePhotos($request, $laporan);
        });

        return redirect()->route('kelas.laporan.kelas', ['kelompok' => $v['kelompok_id'], 'kelas' => $v['kelas_id']])
            ->with('success', 'Laporan bulanan kegiatan berhasil disimpan.');
    }

    public function update(StoreLaporanBulananRequest $request, LaporanBulanan $laporan): RedirectResponse
    {
        if (!Auth::user()->hasRole('admin') && $laporan->tutor_id !== Auth::id()) abort(403);
        if ($laporan->status !== LaporanStatus::MenungguReview) abort(403, 'Laporan terkunci.');

        $v = $request->validated();
        DB::transaction(function () use ($request, $v, $laporan) {
            $laporan->update(['catatan' => $v['catatan']]);
            if ($request->hasFile('foto')) {
                $this->clearPhotos($laporan);
                $this->savePhotos($request, $laporan);
            }
        });

        return redirect()->route('kelas.laporan.kelas', ['kelompok' => $laporan->kelompok_id, 'kelas' => $laporan->kelas_id])
            ->with('success', 'Laporan bulanan berhasil diperbarui.');
    }

    public function destroy(LaporanBulanan $laporan): RedirectResponse
    {
        if (!Auth::user()->hasRole('admin') && $laporan->tutor_id !== Auth::id()) abort(403);
        if ($laporan->status !== LaporanStatus::MenungguReview) abort(403, 'Laporan terkunci.');

        $kelId = $laporan->kelompok_id;
        $klsId = $laporan->kelas_id;
        $this->clearPhotos($laporan);
        $laporan->delete();

        return redirect()->route('kelas.laporan.kelas', ['kelompok' => $kelId, 'kelas' => $klsId])
            ->with('success', 'Laporan bulanan dan berkas dokumentasi berhasil dihapus.');
    }

    public function show(LaporanBulanan $laporan): View
    {
        if (!Auth::user()->hasRole('admin') && $laporan->tutor_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang melihat laporan tutor lain.');
        }
        $laporan->load(['tutor', 'kelompok', 'kelas', 'periode', 'foto', 'reviewer']);
        return view('kelas.laporan.show', compact('laporan'));
    }

    public function updateStatus(UpdateLaporanStatusRequest $request, LaporanBulanan $laporan): RedirectResponse
    {
        $v = $request->validated();
        $laporan->update(['status' => $v['status'], 'catatan_admin' => $v['catatan_admin'] ?? null, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        return redirect()->route('kelas.laporan.index')->with('success', "Status laporan diperbarui.");
    }

    private function authorizeTutor(int $kelId, int $klsId): void
    {
        if (!Auth::user()->hasRole('admin')) {
            $ok = DB::table('mahad_kelompok_pengampu')->where('kelompok_id', $kelId)->where('kelas_id', $klsId)->where('tutor_id', Auth::id())->exists();
            if (!$ok) abort(403, 'Anda bukan pengampu kelompok ini.');
        }
    }

    private function clearPhotos(LaporanBulanan $lap): void
    {
        foreach ($lap->foto as $f) {
            Storage::disk('local')->delete($f->file_path);
            $f->delete();
        }
    }

    private function savePhotos(Request $req, LaporanBulanan $lap): void
    {
        if (!$req->hasFile('foto')) return;
        $idx = 1;
        foreach ($req->file('foto') as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;
            $path = $file->storeAs('kelas_laporan', Str::random(40) . '.' . $ext, 'local');
            LaporanBulananFoto::create(['laporan_id' => $lap->id, 'file_path' => $path, 'file_name' => 'Dokumentasi-' . $idx . '.' . $ext, 'file_size' => $file->getSize()]);
            $idx++;
        }
    }

    private function computeMonthlyAnalytics(int $kelId, int $klsId, int $m, int $y): array
    {
        $stats = DB::table('mahad_absensi')->where('kelompok_id', $kelId)->where('kelas_id', $klsId)->whereMonth('tanggal', $m)->whereYear('tanggal', $y)
            ->selectRaw('COUNT(DISTINCT tanggal) as total_sesi, COUNT(*) as total_rec, SUM(CASE WHEN status="hadir" THEN 1 ELSE 0 END) as hadir, SUM(CASE WHEN status="izin" THEN 1 ELSE 0 END) as izin, SUM(CASE WHEN status="sakit" THEN 1 ELSE 0 END) as sakit, SUM(CASE WHEN status="alfa" THEN 1 ELSE 0 END) as alfa')->first();

        $pct = ($stats && $stats->total_rec > 0) ? round(($stats->hadir / $stats->total_rec) * 100) : 0;
        $mName = Carbon::create(null, $m, 1)->translatedFormat('F');
        $narrative = "Pelaksanaan kajian bulan {$mName} {$y} berlangsung sebanyak " . ($stats->total_sesi ?? 0) . " sesi dengan tingkat kehadiran santri {$pct}%. Rincian kehadiran: " . ($stats->hadir ?? 0) . " hadir, " . ($stats->izin ?? 0) . " izin, " . ($stats->sakit ?? 0) . " sakit, " . ($stats->alfa ?? 0) . " alfa. Evaluasi capaian materi berjalan dengan baik.";

        return ['absensiStats' => $stats, 'attendancePct' => $pct, 'autoNarrative' => $narrative];
    }
}
