<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kelas\CopyAbsensiRequest;
use App\Http\Requests\Kelas\StoreAbsensiRequest;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Periode;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function __construct(private readonly AbsensiService $service) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $periode = Periode::getActive();
        $hasSelected = $request->filled('kelompok_id') && $request->filled('kelas_id');
        $isSaya = $request->query('mode') === 'saya' || ($user->hasRole('tutor') && !$user->hasRole('admin'));

        $query = DB::table('mahad_kelompok_pengampu')
            ->join('mahad_kelompok', 'mahad_kelompok_pengampu.kelompok_id', '=', 'mahad_kelompok.id')
            ->join('mahad_kelas', 'mahad_kelompok_pengampu.kelas_id', '=', 'mahad_kelas.id')
            ->where('mahad_kelompok.periode_id', $periode?->id)
            ->when($isSaya, fn ($q) => $q->where('mahad_kelompok_pengampu.tutor_id', $user->id));

        $rawList = $query->select(
            'mahad_kelompok.id as kelompok_id',
            'mahad_kelompok.nama as kelompok_nama',
            'mahad_kelas.id as kelas_id',
            'mahad_kelas.nama as kelas_nama',
            'mahad_kelas.tipe as kelas_tipe'
        )->orderBy('mahad_kelompok.nama')->get();

        $kelompokIds = $rawList->pluck('kelompok_id')->unique();
        $kelasIds = $rawList->pluck('kelas_id')->unique();

        $santriCounts = DB::table('mahad_kelompok_anggota')
            ->whereIn('kelompok_id', $kelompokIds)
            ->groupBy('kelompok_id')
            ->select('kelompok_id', DB::raw('count(*) as total'))
            ->pluck('total', 'kelompok_id');

        $sesiCounts = DB::table('mahad_absensi')
            ->whereIn('kelompok_id', $kelompokIds)
            ->groupBy('kelompok_id', 'kelas_id')
            ->selectRaw('kelompok_id, kelas_id, count(distinct tanggal) as total')
            ->get()->keyBy(fn($r) => $r->kelompok_id . '_' . $r->kelas_id);

        $jadwalMap = DB::table('mahad_kelas_jadwal')->whereIn('kelas_id', $kelasIds)->get()->groupBy('kelas_id');

        $myKajianList = $rawList->map(function ($item) use ($santriCounts, $sesiCounts, $jadwalMap) {
            $j = $jadwalMap->get($item->kelas_id)?->first();
            return (object) [
                'kelompok_id'   => $item->kelompok_id,
                'kelompok_nama' => $item->kelompok_nama,
                'kelas_id'      => $item->kelas_id,
                'kelas_nama'    => $item->kelas_nama,
                'kelas_tipe'    => $item->kelas_tipe,
                'total_santri'  => $santriCounts->get($item->kelompok_id, 0),
                'total_sesi'    => $sesiCounts->get($item->kelompok_id . '_' . $item->kelas_id)?->total ?? 0,
                'hari'          => $j?->hari ?? 'Sesuai Jadwal',
                'jam_mulai'     => $j?->jam_mulai ? substr($j->jam_mulai, 0, 5) : '00:00',
                'jam_selesai'   => $j?->jam_selesai ? substr($j->jam_selesai, 0, 5) : '00:00',
                'nama_sesi'     => $j?->nama_sesi ?? 'Sesi Kajian',
            ];
        });

        $selectedKelompok = null;
        $selectedKelas = null;
        if ($hasSelected) {
            $selectedKelompok = Kelompok::where('periode_id', $periode?->id)->where('id', $request->query('kelompok_id'))->first();
            $selectedKelas = Kelas::where('periode_id', $periode?->id)->where('id', $request->query('kelas_id'))->first();

            if ($user->hasRole('tutor') && !$user->hasRole('admin')) {
                $isAllowed = DB::table('mahad_kelompok_pengampu')
                    ->where('kelompok_id', $selectedKelompok?->id)
                    ->where('kelas_id', $selectedKelas?->id)
                    ->where('tutor_id', $user->id)->exists();
                if (!$isAllowed) abort(403, 'Anda tidak memiliki hak akses mengajar pada kelompok dan kajian ini.');
            }
        }

        $jadwalList = $selectedKelas?->jadwal ?? collect();
        $selectedJadwalId = $request->filled('jadwal_id') ? (int) $request->query('jadwal_id') : $jadwalList->first()?->id;
        $tanggal = $request->query('tanggal', Carbon::today()->format('Y-m-d'));
        $yesterday = Carbon::parse($tanggal)->subDay()->format('Y-m-d');

        $anggotaList = collect();
        $absensiMap = collect();
        $hasYesterdayData = false;
        $stats = (object) ['total_input' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'terlambat' => 0];
        $riwayatSesi = collect();

        if ($selectedKelompok && $selectedKelas) {
            $selectedKelompok->load(['anggota.santri.user', 'anggota.santri.penempatanAktif.kamar.lantai.gedung']);
            $anggotaList = $selectedKelompok->anggota;

            $absensiMap = Absensi::where('kelompok_id', $selectedKelompok->id)
                ->where('kelas_id', $selectedKelas->id)
                ->when($selectedJadwalId, fn ($q) => $q->where('jadwal_id', $selectedJadwalId))
                ->where('tanggal', $tanggal)->get()->keyBy('santri_id');

            $hasYesterdayData = Absensi::where('kelompok_id', $selectedKelompok->id)
                ->where('kelas_id', $selectedKelas->id)
                ->when($selectedJadwalId, fn ($q) => $q->where('jadwal_id', $selectedJadwalId))
                ->where('tanggal', $yesterday)->exists();

            $stats = $this->service->getHariIniStats($selectedKelompok->id, $selectedKelas->id, $selectedJadwalId, $tanggal);

            $riwayatSesi = DB::table('mahad_absensi')
                ->leftJoin('mahad_kelas_jadwal', 'mahad_absensi.jadwal_id', '=', 'mahad_kelas_jadwal.id')
                ->where('mahad_absensi.kelompok_id', $selectedKelompok->id)
                ->where('mahad_absensi.kelas_id', $selectedKelas->id)
                ->selectRaw('
                    mahad_absensi.tanggal,
                    mahad_absensi.jadwal_id,
                    COALESCE(mahad_kelas_jadwal.nama_sesi, "Sesi Kajian") as nama_sesi,
                    COUNT(*) as total_santri,
                    SUM(CASE WHEN mahad_absensi.status = "hadir" THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN mahad_absensi.status = "izin" THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN mahad_absensi.status = "sakit" THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN mahad_absensi.status = "alfa" THEN 1 ELSE 0 END) as alfa
                ')
                ->groupBy('mahad_absensi.tanggal', 'mahad_absensi.jadwal_id', 'mahad_kelas_jadwal.nama_sesi')
                ->orderBy('mahad_absensi.tanggal', 'desc')->limit(20)->get();
        }

        return view('kelas.absensi.index', compact(
            'hasSelected', 'myKajianList', 'selectedKelompok', 'selectedKelas',
            'jadwalList', 'selectedJadwalId', 'tanggal', 'yesterday',
            'anggotaList', 'absensiMap', 'hasYesterdayData', 'stats', 'riwayatSesi', 'periode'
        ));
    }

    public function store(StoreAbsensiRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $user = Auth::user();

        if ($user->hasRole('tutor') && !$user->hasRole('admin')) {
            $isPengampu = DB::table('mahad_kelompok_pengampu')
                ->where('kelompok_id', $v['kelompok_id'])
                ->where('kelas_id', $v['kelas_id'])
                ->where('tutor_id', $user->id)->exists();
            if (!$isPengampu) abort(403, 'Anda bukan tutor pengampu kajian ini di kelompok tersebut.');
        }

        $count = $this->service->savePresensi(
            (int) $v['kelompok_id'],
            (int) $v['kelas_id'],
            isset($v['jadwal_id']) ? (int) $v['jadwal_id'] : null,
            $v['tanggal'],
            $v['items'],
            $user->id,
            $v['catatan_sesi'] ?? null
        );

        return redirect()->route('kelas.absensi.index', [
            'kelompok_id' => $v['kelompok_id'],
            'kelas_id'    => $v['kelas_id'],
            'jadwal_id'   => $v['jadwal_id'] ?? null,
            'tanggal'     => $v['tanggal'],
        ])->with('success', "Presensi {$count} mahasantri berhasil disimpan.");
    }

    public function copyYesterday(CopyAbsensiRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $user = Auth::user();

        $copied = $this->service->copyPresensi(
            (int) $v['kelompok_id'],
            (int) $v['kelas_id'],
            isset($v['jadwal_id']) ? (int) $v['jadwal_id'] : null,
            $v['from_tanggal'],
            $v['to_tanggal'],
            $user->id
        );

        if ($copied === 0) {
            return back()->with('error', "Tidak ditemukan data presensi pada tanggal {$v['from_tanggal']} untuk disalin.");
        }

        return redirect()->route('kelas.absensi.index', [
            'kelompok_id' => $v['kelompok_id'],
            'kelas_id'    => $v['kelas_id'],
            'jadwal_id'   => $v['jadwal_id'] ?? null,
            'tanggal'     => $v['to_tanggal'],
        ])->with('success', "Berhasil menyalin {$copied} data presensi dari tanggal {$v['from_tanggal']}.");
    }

    public function rekap(Request $request): View
    {
        $user = Auth::user();
        $periode = Periode::getActive();
        $bulan = (int) $request->query('bulan', Carbon::now()->month);
        $tahun = (int) $request->query('tahun', Carbon::now()->year);
        $isTutorOnly = ($user->hasRole('tutor') && !$user->hasRole('admin')) || $request->query('mode') === 'saya';

        $kelompokQuery = Kelompok::where('periode_id', $periode?->id)->where('is_active', true);
        if ($isTutorOnly) {
            $kelompokQuery->whereHas('pengampu', fn ($q) => $q->where('tutor_id', $user->id));
        }
        $kelompokList = $kelompokQuery->orderBy('nama')->get();
        $selectedKelompokId = (int) $request->query('kelompok_id', $kelompokList->first()?->id ?? 0);
        $selectedKelompok = $kelompokList->firstWhere('id', $selectedKelompokId);

        if ($selectedKelompok && $isTutorOnly) {
            $isPengampu = DB::table('mahad_kelompok_pengampu')
                ->where('kelompok_id', $selectedKelompok->id)
                ->where('tutor_id', $user->id)
                ->exists();
            if (!$isPengampu) {
                $selectedKelompok = $kelompokList->first();
            }
        }

        $kelasListQuery = Kelas::where('periode_id', $periode?->id)->where('is_active', true);
        if ($isTutorOnly) {
            if ($selectedKelompok) {
                $kelasListQuery->whereHas('pengampu', fn ($q) => $q->where('kelompok_id', $selectedKelompok->id)->where('tutor_id', $user->id));
            } else {
                $kelasListQuery->whereHas('pengampu', fn ($q) => $q->where('tutor_id', $user->id));
            }
        }
        $kelasList = $kelasListQuery->orderBy('tipe')->get();
        $selectedKelasId = (int) $request->query('kelas_id', $kelasList->first()?->id ?? 0);
        $selectedKelas = $kelasList->firstWhere('id', $selectedKelasId);

        $rekapData = collect();
        if ($selectedKelompok && $selectedKelas) {
            $selectedKelompok->load('anggota.santri.user');

            $rekapData = DB::table('mahad_absensi')
                ->where('kelompok_id', $selectedKelompok->id)
                ->where('kelas_id', $selectedKelas->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->selectRaw('santri_id, COUNT(*) as total_pertemuan, SUM(status = "hadir") as hadir, SUM(status = "izin") as izin, SUM(status = "sakit") as sakit, SUM(status = "alfa") as alfa, SUM(status = "terlambat") as terlambat')
                ->groupBy('santri_id')->get()->keyBy('santri_id');
        }

        return view('kelas.absensi.rekap', compact(
            'kelompokList', 'selectedKelompok', 'kelasList', 'selectedKelas',
            'bulan', 'tahun', 'rekapData', 'periode'
        ));
    }
}
