<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SantriKelasController extends Controller
{
    /**
     * Tampilan Portal Akademik & Kajian Mahasantri
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $periodeAktif = Periode::getActive();

        // 1. Tentukan santri aktif (prioritas user login, fallback untuk pengujian admin)
        $santri = $user->santri;
        if (!$santri && $user->hasRole('admin')) {
            $santri = Santri::whereHas('kelompokAnggota')->with('user')->first()
                ?? Santri::where('status', 'aktif')->with('user')->first();
        }

        $kelompok = null;
        $anggotaList = collect();
        $jadwalList = collect();
        $absensiList = collect();
        $daftarKajian = collect();
        $rekapNilaiList = collect();

        $rekapAbsensi = [
            'total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'persen' => 0,
        ];

        if ($santri) {
            // 2. Ambil Kelompok Kajian Utama & Anggota Kelompok
            $kelompokAnggota = $santri->kelompokAnggota()
                ->with([
                    'kelompok.periode',
                    'kelompok.pengampu.tutor',
                    'kelompok.pengampu.kelas.jadwal',
                    'kelompok.anggota.santri.user',
                    'kelompok.anggota.santri.penempatanAktif.kamar.lantai.gedung',
                ])
                ->latest('id')
                ->first();

            $kelompok = $kelompokAnggota?->kelompok;

            if ($kelompok) {
                // Rekan Mahasantri dalam Kelompok
                $anggotaList = $kelompok->anggota->map(function ($a) use ($santri) {
                    $s = $a->santri;
                    $pk = $s?->penempatanAktif;
                    $kamarText = $pk?->kamar ? ($pk->kamar->lantai->gedung->nama ?? '') . ' - ' . $pk->kamar->nomor_kamar : 'Belum ber-kamar';
                    return (object) [
                        'id' => $s?->id,
                        'nim' => $s?->nomor_induk ?? '-',
                        'name' => $s?->user?->name ?? '-',
                        'prodi' => $s?->prodi ?? 'Reguler',
                        'gender' => $s?->gender?->value ?? ($s?->gender ?? '-'),
                        'kamar' => $kamarText,
                        'is_me' => $s?->id === $santri->id,
                    ];
                })->sortBy('nim')->values();

                // Jadwal Pertemuan & Master Kajian
                foreach ($kelompok->pengampu as $pengampu) {
                    if ($pengampu->kelas) {
                        $daftarKajian->push($pengampu->kelas);
                        foreach ($pengampu->kelas->jadwal as $jdw) {
                            $jadwalList->push((object) [
                                'nama_kelas' => $pengampu->kelas->nama,
                                'tipe_kelas' => $pengampu->kelas->tipe?->label() ?? 'Kajian',
                                'tutor' => $pengampu->tutor?->name ?? 'Tutor Pengampu',
                                'nama_sesi' => $jdw->nama_sesi,
                                'hari' => $jdw->hari,
                                'jam' => substr($jdw->jam_mulai, 0, 5) . ' - ' . substr($jdw->jam_selesai, 0, 5) . ' WIB',
                                'ruangan' => $jdw->ruangan ?? 'Ruang Kajian',
                            ]);
                        }
                    }
                }
                $daftarKajian = $daftarKajian->unique('id')->values();
            }

            // 3. Log Riwayat Presensi Mahasantri
            $absensiList = $santri->absensi()
                ->with(['kelas', 'kelompok'])
                ->latest('tanggal')
                ->get()
                ->map(fn($abs) => (object) [
                    'id' => $abs->id,
                    'kelas_id' => $abs->kelas_id,
                    'tanggal' => $abs->tanggal?->format('d/m/Y') ?? '-',
                    'nama_kelas' => $abs->kelas?->nama ?? 'Kajian Asrama',
                    'kelompok_nama' => $abs->kelompok?->nama ?? ($kelompok?->nama ?? '-'),
                    'status_val' => $abs->status?->value ?? 'hadir',
                    'status_label' => $abs->status?->label() ?? 'Hadir',
                    'status_badge' => $abs->status?->badgeColor() ?? 'bg-emerald-50 text-emerald-700',
                    'catatan' => $abs->catatan ?: '-',
                ]);

            $totalAbsen = $absensiList->count();
            $hadir = $absensiList->where('status_val', 'hadir')->count();
            $izin = $absensiList->where('status_val', 'izin')->count();
            $sakit = $absensiList->whereIn('status_val', ['sakit', 'sakit_tambahan'])->count();
            $alfa = $absensiList->whereIn('status_val', ['alfa', 'terlambat'])->count();

            $rekapAbsensi = [
                'total' => $totalAbsen,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alfa' => $alfa,
                'persen' => $totalAbsen > 0 ? round(($hadir / $totalAbsen) * 100) : 0,
            ];

            // 4. Rekap Penilaian: Semua Kajian Terdaftar (Kosong bila belum dinilai)
            $existingPenilaian = $santri->penilaian()->get()->keyBy('kelas_id');

            if ($kelompok && $kelompok->pengampu->isNotEmpty()) {
                $allKomponen = \App\Models\KelasKomponenNilai::whereIn('kelas_id', $kelompok->pengampu->pluck('kelas_id'))
                    ->where(fn($q) => $q->where('kelompok_id', $kelompok->id)->orWhereNull('kelompok_id'))
                    ->orderBy('urutan')->get()->groupBy('kelas_id');

                foreach ($kelompok->pengampu as $p) {
                    if (!$p->kelas) continue;
                    $pen = $existingPenilaian->get($p->kelas_id);
                    $komps = $allKomponen->get($p->kelas_id, collect());
                    $breakdown = $komps->map(fn($k) => (object) [
                        'nama'       => $k->nama,
                        'bobot'      => (float) $k->bobot,
                        'nilai'      => isset($pen?->nilai_komponen[$k->id]) && $pen->nilai_komponen[$k->id] !== '' ? (float) $pen->nilai_komponen[$k->id] : null,
                        'is_absensi' => (bool) $k->is_absensi,
                    ]);

                    $rekapNilaiList->push((object) [
                        'kelas_id'    => $p->kelas_id,
                        'nama_kelas'  => $p->kelas->nama,
                        'tipe_kelas'  => $p->kelas->tipe?->label() ?? 'Kajian',
                        'tutor_name'  => $p->tutor?->name ?? 'Tutor Pengampu',
                        'nilai_angka' => $pen?->nilai_angka,
                        'nilai_huruf' => $pen?->nilai_huruf,
                        'catatan'     => $pen?->catatan,
                        'is_graded'   => $pen !== null && $pen->nilai_angka !== null,
                        'breakdown'   => $breakdown,
                    ]);
                }
            }
        }

        return view('kelas.santri.index', compact(
            'santri',
            'periodeAktif',
            'kelompok',
            'anggotaList',
            'jadwalList',
            'absensiList',
            'daftarKajian',
            'rekapAbsensi',
            'rekapNilaiList'
        ));
    }
}
