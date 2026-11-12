<?php

namespace App\Http\Controllers;

use App\Models\Kelompok;
use App\Models\Pelanggaran;
use App\Models\Perizinan;
use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $role = $user?->roles->first()?->name ?? 'mahasantri';
        $periode = Periode::getActive();

        $data = match ($role) {
            'mudabbir'   => $this->mudabbirData($user, $periode),
            'tutor'      => $this->tutorData($user, $periode),
            'mahasantri' => $this->mahasantriData($user, $periode),
            default      => $this->adminData($periode),
        };

        return view('dashboard', array_merge($data, [
            'currentRole' => $role,
            'currentUser' => $user,
            'activePeriode' => $periode,
        ]));
    }

    private function adminData(?Periode $periode): array
    {
        return app(\App\Services\AdminDashboardService::class)->getStats($periode);
    }

    private function mudabbirData(User $user, ?Periode $periode): array
    {
        $asrama = DB::table('mahad_kamar')
            ->selectRaw("
                COUNT(*) as total_kamar,
                COALESCE(SUM(kapasitas_maks), 0) as total_kapasitas,
                COUNT(CASE WHEN EXISTS (SELECT 1 FROM mahad_penempatan_kamar pk WHERE pk.kamar_id = mahad_kamar.id AND pk.status = 'aktif') THEN 1 END) as kamar_terisi
            ")->first();

        $santriAktif = (int) DB::table('mahad_santri')
            ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
            ->where('status', 'aktif')->count();

        $izinAktif = DB::table('mahad_perizinan')
            ->where('periode_id', $periode?->id)->where('status', 'berlaku')->count();

        $pelanggaranPending = DB::table('mahad_pelanggaran')
            ->where('periode_id', $periode?->id)->where('status', 'terdaftar')->count();

        $kelompokReadOnlyList = Kelompok::where('periode_id', $periode?->id)
            ->with(['pengampu.tutor', 'pengampu.kelas', 'anggota.santri.user'])
            ->withCount('anggota')->orderBy('nama')->get();

        return [
            'totalKamar'           => (int) ($asrama->total_kamar ?? 0),
            'kamarTerisi'          => (int) ($asrama->kamar_terisi ?? 0),
            'santriAktif'          => $santriAktif,
            'izinAktif'            => $izinAktif,
            'pelanggaranPending'   => $pelanggaranPending,
            'kelompokReadOnlyList' => $kelompokReadOnlyList,
        ];
    }

    private function tutorData(User $user, ?Periode $periode): array
    {
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        $myKelompokPengampu = DB::table('mahad_kelompok_pengampu')
            ->join('mahad_kelompok', 'mahad_kelompok_pengampu.kelompok_id', '=', 'mahad_kelompok.id')
            ->join('mahad_kelas', 'mahad_kelompok_pengampu.kelas_id', '=', 'mahad_kelas.id')
            ->where('mahad_kelompok.periode_id', $periode?->id)
            ->where('mahad_kelompok_pengampu.tutor_id', $user->id)
            ->select('mahad_kelompok.id as kelompok_id', 'mahad_kelompok.nama as kelompok_nama', 'mahad_kelas.id as kelas_id', 'mahad_kelas.nama as kelas_nama', 'mahad_kelas.tipe as kelas_tipe')->get();

        $myKelompokIds = $myKelompokPengampu->pluck('kelompok_id')->unique()->toArray();
        $myClassIds = $myKelompokPengampu->pluck('kelas_id')->unique()->toArray();

        $totalSantri = DB::table('mahad_kelompok_anggota')->whereIn('kelompok_id', $myKelompokIds)->count();
        $sesiBulanIni = DB::table('mahad_absensi')->whereIn('kelompok_id', $myKelompokIds)->whereMonth('tanggal', $currentMonth)->whereYear('tanggal', $currentYear)->distinct('tanggal')->count('tanggal');
        $laporanBulanIni = DB::table('mahad_laporan_bulanan')->where('tutor_id', $user->id)->where('bulan', $currentMonth)->where('tahun', $currentYear)->first();

        $jadwalRaw = DB::table('mahad_kelas_jadwal')->whereIn('kelas_id', $myClassIds)->get();
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Ahad' => 7, 'Minggu' => 7];
        $jadwalMengajar = collect();
        foreach ($myKelompokPengampu as $kp) {
            $sessions = $jadwalRaw->where('kelas_id', $kp->kelas_id);
            if ($sessions->isEmpty()) {
                $jadwalMengajar->push((object) [
                    'kelompok_id' => $kp->kelompok_id, 'kelompok_nama' => $kp->kelompok_nama,
                    'kelas_id' => $kp->kelas_id, 'kelas_nama' => $kp->kelas_nama, 'kelas_tipe' => $kp->kelas_tipe,
                    'hari' => 'Senin – Kamis', 'jam_mulai' => '05:45', 'jam_selesai' => '07:00', 'nama_sesi' => 'Kajian Ba\'da Subuh',
                ]);
            } else {
                foreach ($sessions as $j) {
                    $jadwalMengajar->push((object) [
                        'kelompok_id' => $kp->kelompok_id, 'kelompok_nama' => $kp->kelompok_nama,
                        'kelas_id' => $kp->kelas_id, 'kelas_nama' => $kp->kelas_nama, 'kelas_tipe' => $kp->kelas_tipe,
                        'hari' => $j->hari ?? 'Sesuai Jadwal', 'jam_mulai' => $j->jam_mulai ? substr($j->jam_mulai, 0, 5) : '00:00',
                        'jam_selesai' => $j->jam_selesai ? substr($j->jam_selesai, 0, 5) : '00:00', 'nama_sesi' => $j->nama_sesi ?? 'Sesi Kajian',
                    ]);
                }
            }
        }
        $jadwalMengajar = $jadwalMengajar->sortBy([
            fn ($a, $b) => ($dayOrder[$a->hari] ?? 99) <=> ($dayOrder[$b->hari] ?? 99),
            fn ($a, $b) => strcmp($a->jam_mulai, $b->jam_mulai),
        ])->values();

        $hariIni = Carbon::today()->format('Y-m-d');
        $sesiHariIni = collect();
        foreach ($myKelompokPengampu as $pengampu) {
            $sudahAbsen = DB::table('mahad_absensi')->where('kelompok_id', $pengampu->kelompok_id)->where('kelas_id', $pengampu->kelas_id)->where('tanggal', $hariIni)->exists();
            if (!$sudahAbsen) $sesiHariIni->push($pengampu);
        }

        return [
            'myKajianList'       => $myKelompokPengampu,
            'totalSantriBinaan'  => $totalSantri,
            'sesiBulanIni'       => $sesiBulanIni,
            'laporanBulanIni'    => $laporanBulanIni,
            'unrecordedSessions' => $sesiHariIni,
            'jadwalMengajar'     => $jadwalMengajar,
        ];
    }

    private function mahasantriData(User $user, ?Periode $periode): array
    {
        $santri = Santri::where('user_id', $user->id)->with(['penempatanAktif.kamar.lantai.gedung'])->first();
        $kamar = $santri?->penempatanAktif?->kamar;

        $roommates = collect();
        if ($kamar) {
            $roommates = Santri::where('id', '!=', $santri->id)
                ->whereHas('penempatanAktif', fn($q) => $q->where('kamar_id', $kamar->id))
                ->with('user:id,name,email')->get();
        }

        $myKelompok = null;
        $todayJadwal = collect();
        $rekapNilaiCount = 0;
        $gradedCount = 0;

        if ($santri && $periode) {
            $myKelompok = Kelompok::where('periode_id', $periode->id)
                ->whereHas('anggota', fn($q) => $q->where('santri_id', $santri->id))
                ->with(['pengampu.tutor', 'pengampu.kelas.jadwal'])->first();

            if ($myKelompok) {
                $todayDay = Carbon::now()->translatedFormat('l');
                foreach ($myKelompok->pengampu as $p) {
                    if ($p->kelas) {
                        foreach ($p->kelas->jadwal as $j) {
                            if ($j->hari === $todayDay) {
                                $todayJadwal->push((object)[
                                    'kelas_nama' => $p->kelas->nama,
                                    'nama_sesi'  => $j->nama_sesi,
                                    'jam'        => substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5),
                                    'tutor_nama' => $p->tutor?->name ?? 'Tutor Pengampu',
                                    'ruangan'    => $j->ruangan ?? 'Ruang Kajian',
                                ]);
                            }
                        }
                    }
                }
                $penilaianList = DB::table('mahad_penilaian')
                    ->where('santri_id', $santri->id)->where('kelompok_id', $myKelompok->id)->get();
                $rekapNilaiCount = $myKelompok->pengampu->count();
                $gradedCount = $penilaianList->whereNotNull('nilai_angka')->count();
            }
        }

        return [
            'santri'          => $santri,
            'kamar'           => $kamar,
            'roommates'       => $roommates,
            'myKelompok'      => $myKelompok,
            'todayJadwal'     => $todayJadwal,
            'rekapNilaiCount' => $rekapNilaiCount,
            'gradedCount'     => $gradedCount,
        ];
    }
}
