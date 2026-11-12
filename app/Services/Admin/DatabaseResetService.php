<?php

namespace App\Services\Admin;

use App\Models\PelanggaranJenis;
use App\Models\Periode;
use App\Models\PerizinanJenis;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DatabaseResetService
{
    /**
     * Reset seluruh database ke kondisi awal bersih (Fresh Starter).
     */
    public function resetToFreshStarter(User $currentAdmin): array
    {
        $this->disableForeignKeys();

        try {
            $this->truncateTables();
            $this->seedStarterData($currentAdmin);
        } finally {
            $this->enableForeignKeys();
        }

        $this->flushSystemCache();

        return [
            'users' => User::count(),
            'periode' => Periode::count(),
            'kamar' => DB::table('mahad_kamar')->count(),
            'kelas' => DB::table('mahad_kelas')->count(),
            'kegiatan' => DB::table('mahad_kegiatan')->count(),
            'pelanggaran' => DB::table('mahad_pelanggaran')->count(),
            'perizinan' => DB::table('mahad_perizinan')->count(),
        ];
    }

    protected function disableForeignKeys(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }
    }

    protected function enableForeignKeys(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }

    protected function truncateTables(): void
    {
        $tables = [
            'mahad_kelas_komponen_nilai',
            'mahad_penilaian',
            'mahad_laporan_bulanan_foto',
            'mahad_laporan_bulanan',
            'mahad_absensi',
            'mahad_kelas_jadwal',
            'mahad_kelompok_pengampu',
            'mahad_kelompok_anggota',
            'mahad_kelompok',
            'mahad_kelas',
            'mahad_pelanggaran',
            'mahad_pelanggaran_jenis',
            'mahad_perizinan',
            'mahad_perizinan_jenis',
            'mahad_kegiatan',
            'mahad_penempatan_kamar',
            'mahad_kamar',
            'mahad_lantai',
            'mahad_gedung',
            'mahad_santri',
            'mahad_periode',
            'mahad_pengaturan',
            'model_has_roles',
            'model_has_permissions',
            'sessions',
            'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function seedStarterData(User $currentAdmin): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleTutor = Role::firstOrCreate(['name' => 'tutor']);
        $roleMudabbir = Role::firstOrCreate(['name' => 'mudabbir']);
        $roleMahasantri = Role::firstOrCreate(['name' => 'mahasantri']);

        $defaultPassword = Hash::make('password');

        // 1. Akun Admin Utama
        $admin = User::create([
            'nomor_induk' => $currentAdmin->nomor_induk ?: 'ADM-001',
            'name' => $currentAdmin->name ?: 'Administrator Utama',
            'email' => $currentAdmin->email ?: 'admin@example.com',
            'auth_source' => 'local',
            'password' => $currentAdmin->password ?: $defaultPassword,
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles([$roleAdmin]);

        // 2. Periode Aktif Default Sesuai SAHAJA
        Periode::create([
            'kode' => '20251',
            'academic_period_id' => 1,
            'nama' => 'Semester Ganjil 2025/2026',
            'tanggal_mulai' => '2025-08-01',
            'tanggal_selesai' => '2026-01-31',
            'is_active' => true,
        ]);

        // 3. Akun Sampel Tiap Role (Tutor, Mudabbir, Mahasantri Putra & Putri)
        $samples = [
            ['nomor_induk' => '198501012010121001', 'name' => 'Dr. Ahmad Hidayat, M.Ag.', 'email' => 'dosen.ahmad@uin.ac.id', 'role' => $roleTutor],
            ['nomor_induk' => '20001', 'name' => 'Umar Faruq', 'email' => 'umar.faruq@uin.ac.id', 'role' => $roleMudabbir, 'gender' => 'L', 'prodi' => 'Pendidikan Bahasa Arab', 'angkatan' => 2020],
            ['nomor_induk' => '21001', 'name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@uin.ac.id', 'role' => $roleMahasantri, 'gender' => 'L', 'prodi' => 'Teknik Informatika', 'angkatan' => 2021],
            ['nomor_induk' => '21003', 'name' => 'Fatimah Azzahra', 'email' => 'fatimah.azzahra@uin.ac.id', 'role' => $roleMahasantri, 'gender' => 'P', 'prodi' => 'Ilmu Al-Qur\'an dan Tafsir', 'angkatan' => 2021],
        ];

        foreach ($samples as $s) {
            $u = User::create([
                'nomor_induk' => $s['nomor_induk'],
                'name' => $s['name'],
                'email' => $s['email'],
                'auth_source' => 'campus',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]);
            $u->syncRoles([$s['role']]);

            if (isset($s['gender'])) {
                Santri::create([
                    'user_id' => $u->id,
                    'nomor_induk' => $s['nomor_induk'],
                    'gender' => $s['gender'],
                    'prodi' => $s['prodi'],
                    'angkatan' => $s['angkatan'],
                    'status' => 'aktif',
                ]);
            }
        }

        // 4. Master Kategori Default untuk Pelanggaran & Perizinan
        PelanggaranJenis::create(['nama' => 'Terlambat Jam Malam Asrama (>21.30 WIB)', 'poin_default' => 5, 'sanksi_default' => 'Teguran lisan & piket', 'is_active' => true]);
        PelanggaranJenis::create(['nama' => 'Tidak Mengikuti Shalat Berjamaah Subuh / Isya', 'poin_default' => 10, 'sanksi_default' => 'Setoran hafalan 1 juz', 'is_active' => true]);
        PerizinanJenis::create(['nama' => 'Izin Pulang ke Rumah (Keluarga)', 'butuh_bukti' => false, 'batas_hari_maks' => 3, 'is_active' => true]);
        PerizinanJenis::create(['nama' => 'Izin Sakit / Rawat Medis', 'butuh_bukti' => true, 'batas_hari_maks' => 7, 'is_active' => true]);
        PerizinanJenis::create(['nama' => 'Izin Tugas / Kegiatan Akademik Kampus', 'butuh_bukti' => true, 'batas_hari_maks' => 5, 'is_active' => true]);
    }

    protected function flushSystemCache(): void
    {
        Cache::flush();
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
    }
}
