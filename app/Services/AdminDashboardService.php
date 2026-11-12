<?php

namespace App\Services;

use App\Models\Kelompok;
use App\Models\Pelanggaran;
use App\Models\Periode;
use App\Models\Perizinan;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Ambil seluruh metrik, indikator kinerja, dan antrean aksi operasional Administrator
     */
    public function getStats(?Periode $periode): array
    {
        $periodeId = $periode?->id ?? 0;

        return Cache::remember('dashboard_admin_comprehensive_' . $periodeId, 45, function () use ($periode, $periodeId) {
            // 1. Populasi Pengguna Sistem
            $roleCounts = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', User::class)
                ->selectRaw("
                    COUNT(CASE WHEN roles.name = 'mahasantri' THEN 1 END) as mahasantri,
                    COUNT(CASE WHEN roles.name = 'mudabbir' THEN 1 END) as mudabbir,
                    COUNT(CASE WHEN roles.name = 'tutor' THEN 1 END) as tutor,
                    COUNT(CASE WHEN roles.name = 'admin' THEN 1 END) as admin
                ")->first();

            // 2. Okupansi Fasilitas & Hunian Kamar
            $asrama = DB::table('mahad_kamar')
                ->selectRaw("
                    COUNT(*) as total_kamar,
                    COALESCE(SUM(kapasitas_maks), 0) as total_kapasitas,
                    COUNT(CASE WHEN status = 'perbaikan' THEN 1 END) as kamar_perbaikan,
                    COUNT(CASE WHEN EXISTS (
                        SELECT 1 FROM mahad_penempatan_kamar pk
                        WHERE pk.kamar_id = mahad_kamar.id AND pk.status = 'aktif'
                    ) THEN 1 END) as kamar_terisi
                ")->first();

            $santriAktif = (int) DB::table('mahad_santri')
                ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
                ->where('status', 'aktif')->count();

            $santriMenempati = (int) DB::table('mahad_penempatan_kamar')
                ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
                ->where('status', 'aktif')->count();

            $totalKamar = (int) ($asrama->total_kamar ?? 0);
            $totalKapasitas = (int) ($asrama->total_kapasitas ?? 0);
            $kamarTerisi = (int) ($asrama->kamar_terisi ?? 0);
            $kamarPerbaikan = (int) ($asrama->kamar_perbaikan ?? 0);
            $kamarKosong = max(0, $totalKamar - $kamarTerisi - $kamarPerbaikan);
            $okupansi = $totalKapasitas > 0 ? round(($santriMenempati / $totalKapasitas) * 100) : 0;

            // 3. Antrean Aksi Mendesak (Action Required)
            // A. Santri Belum Dapat Kamar
            $santriUnassignedRoom = (int) DB::table('mahad_santri')
                ->where('status', 'aktif')
                ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('mahad_penempatan_kamar')
                        ->whereColumn('mahad_penempatan_kamar.santri_id', 'mahad_santri.id')
                        ->where('status', 'aktif');
                })->count();

            // B. Santri Belum Masuk Rombel Halaqah
            $santriUnassignedKelompok = (int) DB::table('mahad_santri')
                ->where('status', 'aktif')
                ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('mahad_kelompok_anggota')
                        ->whereColumn('mahad_kelompok_anggota.santri_id', 'mahad_santri.id');
                })->count();

            // C. Perizinan & Pelanggaran
            $perizinanAktif = (int) DB::table('mahad_perizinan')
                ->where('periode_id', $periodeId)->where('status', 'berlaku')->count();

            $pelanggaranPending = (int) DB::table('mahad_pelanggaran')
                ->where('periode_id', $periodeId)->where('status', 'terdaftar')->count();

            // D. Laporan Tutor Menunggu Verifikasi
            $laporanTutorPending = (int) DB::table('mahad_laporan_bulanan')
                ->where('periode_id', $periodeId)->whereIn('status', ['menunggu_review', 'direview'])->count();

            // 4. Metrik Akademik & Halaqah
            $totalKelompok = (int) DB::table('mahad_kelompok')->where('periode_id', $periodeId)->count();
            $totalKelas = (int) DB::table('mahad_kelas')->where('periode_id', $periodeId)->where('is_active', true)->count();

            // 5. Cuplikan Antrean Operasional Terbaru
            $unassignedSantriSample = DB::table('mahad_santri')
                ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                ->where('mahad_santri.status', 'aktif')
                ->when($periode, fn($q) => $q->where('mahad_santri.periode_id', $periode->id))
                ->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('mahad_penempatan_kamar')->whereColumn('mahad_penempatan_kamar.santri_id', 'mahad_santri.id')->where('status', 'aktif'))
                ->select('mahad_santri.id', 'mahad_santri.nomor_induk as nim', 'users.name as nama', 'mahad_santri.prodi', 'mahad_santri.gender')
                ->limit(4)->get();

            $recentPelanggaranSample = DB::table('mahad_pelanggaran')
                ->join('mahad_santri', 'mahad_pelanggaran.santri_id', '=', 'mahad_santri.id')
                ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                ->where('mahad_pelanggaran.periode_id', $periodeId)
                ->where('mahad_pelanggaran.status', 'terdaftar')
                ->select('mahad_pelanggaran.id', 'users.name as santri_nama', 'mahad_santri.nomor_induk as nim', 'mahad_pelanggaran.deskripsi', 'mahad_pelanggaran.tanggal', 'mahad_pelanggaran.hukuman')
                ->orderByDesc('mahad_pelanggaran.tanggal')->limit(4)->get();

            $recentPerizinanSample = DB::table('mahad_perizinan')
                ->join('mahad_santri', 'mahad_perizinan.santri_id', '=', 'mahad_santri.id')
                ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                ->where('mahad_perizinan.periode_id', $periodeId)
                ->where('mahad_perizinan.status', 'berlaku')
                ->select('mahad_perizinan.id', 'users.name as santri_nama', 'mahad_santri.nomor_induk as nim', 'mahad_perizinan.alasan', 'mahad_perizinan.tanggal_selesai')
                ->orderByDesc('mahad_perizinan.tanggal_mulai')->limit(4)->get();

            return [
                'totalMahasantri'          => (int) ($roleCounts->mahasantri ?? 0),
                'totalMudabbir'            => (int) ($roleCounts->mudabbir ?? 0),
                'totalTutor'               => (int) ($roleCounts->tutor ?? 0),
                'totalAdmin'               => (int) ($roleCounts->admin ?? 0),
                'totalKamar'               => $totalKamar,
                'totalKapasitas'           => $totalKapasitas,
                'kamarTerisi'              => $kamarTerisi,
                'kamarKosong'              => $kamarKosong,
                'kamarPerbaikan'           => $kamarPerbaikan,
                'santriAktif'              => $santriAktif,
                'santriMenempati'          => $santriMenempati,
                'okupansi'                 => $okupansi,
                'santriUnassignedRoom'     => $santriUnassignedRoom,
                'santriUnassignedKelompok' => $santriUnassignedKelompok,
                'perizinanAktif'           => $perizinanAktif,
                'pelanggaranPending'       => $pelanggaranPending,
                'laporanTutorPending'      => $laporanTutorPending,
                'totalKelompok'            => $totalKelompok,
                'totalKelas'               => $totalKelas,
                'unassignedSantriSample'   => $unassignedSantriSample,
                'recentPelanggaranSample'  => $recentPelanggaranSample,
                'recentPerizinanSample'    => $recentPerizinanSample,
            ];
        });
    }
}

