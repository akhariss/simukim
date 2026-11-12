<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TutorKajianController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $periode = Periode::getActive();

        // Ambil penugasan kajian kelompok untuk tutor ini pada periode aktif
        $pengampuQuery = DB::table('mahad_kelompok_pengampu')
            ->join('mahad_kelompok', 'mahad_kelompok_pengampu.kelompok_id', '=', 'mahad_kelompok.id')
            ->join('mahad_kelas', 'mahad_kelompok_pengampu.kelas_id', '=', 'mahad_kelas.id')
            ->where('mahad_kelompok.periode_id', $periode?->id);

        $pengampuQuery->where('mahad_kelompok_pengampu.tutor_id', $user->id);

        $pengampuList = $pengampuQuery
            ->select(
                'mahad_kelompok_pengampu.id as pengampu_id',
                'mahad_kelompok.id as kelompok_id',
                'mahad_kelompok.nama as kelompok_nama',
                'mahad_kelas.id as kelas_id',
                'mahad_kelas.nama as kelas_nama',
                'mahad_kelas.tipe as kelas_tipe'
            )
            ->orderBy('mahad_kelompok.nama')
            ->orderBy('mahad_kelas.nama')
            ->get();

        $myClassIds = $pengampuList->pluck('kelas_id')->unique()->toArray();
        $jadwalRaw = DB::table('mahad_kelas_jadwal')->whereIn('kelas_id', $myClassIds)->get();
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Ahad' => 7];

        $jadwalMengajar = collect();
        foreach ($pengampuList as $kp) {
            $sessions = $jadwalRaw->where('kelas_id', $kp->kelas_id);
            if ($sessions->isEmpty()) {
                $jadwalMengajar->push((object) [
                    'kelompok_id'   => $kp->kelompok_id,
                    'kelompok_nama' => $kp->kelompok_nama,
                    'kelas_id'      => $kp->kelas_id,
                    'kelas_nama'    => $kp->kelas_nama,
                    'kelas_tipe'    => $kp->kelas_tipe,
                    'hari'          => 'Sesuai Jadwal',
                    'jam_mulai'     => '00:00',
                    'jam_selesai'   => '00:00',
                    'nama_sesi'     => 'Sesi Kajian',
                ]);
            } else {
                foreach ($sessions as $j) {
                    $jadwalMengajar->push((object) [
                        'kelompok_id'   => $kp->kelompok_id,
                        'kelompok_nama' => $kp->kelompok_nama,
                        'kelas_id'      => $kp->kelas_id,
                        'kelas_nama'    => $kp->kelas_nama,
                        'kelas_tipe'    => $kp->kelas_tipe,
                        'hari'          => $j->hari ?? 'Sesuai Jadwal',
                        'jam_mulai'     => $j->jam_mulai ? substr($j->jam_mulai, 0, 5) : '00:00',
                        'jam_selesai'   => $j->jam_selesai ? substr($j->jam_selesai, 0, 5) : '00:00',
                        'nama_sesi'     => $j->nama_sesi ?? 'Sesi Kajian',
                    ]);
                }
            }
        }
        $jadwalMengajar = $jadwalMengajar->sortBy([
            fn ($a, $b) => ($dayOrder[$a->hari] ?? 99) <=> ($dayOrder[$b->hari] ?? 99),
            fn ($a, $b) => strcmp($a->jam_mulai, $b->jam_mulai),
        ])->values();

        // Eager load anggota santri dan statistik presensi per kelompok & kelas
        $kajianCards = $pengampuList->map(function ($item) {
            $totalSantri = DB::table('mahad_kelompok_anggota')
                ->where('kelompok_id', $item->kelompok_id)
                ->count();

            $totalSesi = DB::table('mahad_absensi')
                ->where('kelompok_id', $item->kelompok_id)
                ->where('kelas_id', $item->kelas_id)
                ->distinct('tanggal')
                ->count('tanggal');

            $sudahDinilai = DB::table('mahad_penilaian')
                ->where('kelompok_id', $item->kelompok_id)
                ->where('kelas_id', $item->kelas_id)
                ->whereNotNull('nilai_angka')
                ->count();

            return (object) [
                'kelompok_id'   => $item->kelompok_id,
                'kelompok_nama' => $item->kelompok_nama,
                'kelas_id'      => $item->kelas_id,
                'kelas_nama'    => $item->kelas_nama,
                'kelas_tipe'    => $item->kelas_tipe,
                'total_santri'  => $totalSantri,
                'total_sesi'    => $totalSesi,
                'sudah_dinilai' => $sudahDinilai,
            ];
        });

        return view('tutor.kajian.index', compact('kajianCards', 'periode', 'jadwalMengajar'));
    }

    public function show(Kelompok $kelompok, Kelas $kelas): View
    {
        $user = Auth::user();

        $isAssigned = DB::table('mahad_kelompok_pengampu')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->when(!$user->hasRole('admin'), fn ($q) => $q->where('tutor_id', $user->id))
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Anda tidak memiliki penugasan untuk kelompok dan kajian ini.');
        }

        $kelompok->load([
            'periode',
            'anggota.santri.user',
            'anggota.santri.penempatanAktif.kamar.lantai.gedung',
        ]);

        $kelas->load(['jadwal', 'periode']);

        $totalSesi = DB::table('mahad_absensi')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->distinct('tanggal')
            ->count('tanggal');

        $now = now();
        $namaBulan = $now->translatedFormat('F Y');

        $sesiBulanIni = DB::table('mahad_absensi')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->distinct('tanggal')
            ->count('tanggal');

        $laporanBulanIni = DB::table('mahad_laporan_bulanan')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->where('bulan', $now->month)
            ->where('tahun', $now->year)
            ->first();

        $statusLaporanLabel = match ($laporanBulanIni?->status) {
            'selesai', 'disetujui' => 'Disetujui',
            'menunggu_review', 'menunggu' => 'Menunggu Review',
            'direview' => 'Sedang Direview',
            'draft' => 'Draft Tersimpan',
            default => 'Belum Terkirim',
        };

        $statusLaporanColor = match ($laporanBulanIni?->status) {
            'selesai', 'disetujui' => 'emerald',
            'menunggu_review', 'menunggu' => 'blue',
            'direview' => 'indigo',
            'draft' => 'amber',
            default => 'slate',
        };

        $sudahDinilai = DB::table('mahad_penilaian')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->whereNotNull('nilai_angka')
            ->count();

        return view('tutor.kajian.show', compact(
            'kelompok',
            'kelas',
            'totalSesi',
            'sesiBulanIni',
            'namaBulan',
            'laporanBulanIni',
            'statusLaporanLabel',
            'statusLaporanColor',
            'sudahDinilai'
        ));
    }
}
