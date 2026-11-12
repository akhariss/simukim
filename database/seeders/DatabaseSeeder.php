<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\Models\Periode;
use App\Models\PerizinanJenis;
use App\Models\PelanggaranJenis;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles Spatie
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tutor']);
        Role::firstOrCreate(['name' => 'mudabbir']);
        Role::firstOrCreate(['name' => 'mahasantri']);

        // 2. Administrator Asrama Ma'had (Tunggal)
        $admin = User::firstOrCreate(
            ['nomor_induk' => 'NIP-ADM01'],
            [
                'name' => "Admin Asrama Ma'had",
                'email' => 'admin@uinssc.ac.id',
                'password' => Hash::make('password'),
                'auth_source' => 'local',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([$roleAdmin]);

        // 3. Periode Akademik Bersih (Default: 2026 Ganjil / 20261 Resmi SAHAJA)
        Periode::firstOrCreate(
            ['kode' => '20261'],
            [
                'academic_period_id' => 3,
                'nama' => '2026 Ganjil',
                'tanggal_mulai' => '2026-08-01',
                'tanggal_selesai' => '2027-01-31',
                'is_active' => true,
            ]
        );
        Periode::firstOrCreate(
            ['kode' => '20251'],
            [
                'academic_period_id' => 1,
                'nama' => 'Semester Ganjil 2025/2026',
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2026-01-31',
                'is_active' => false,
            ]
        );

        // 4. Pengaturan Sistem
        Pengaturan::set('nama_mahad', "Ma'had Al-Jami'ah", 'umum');
        Pengaturan::set('nama_kepala', "Dr. KH. Abdullah Shiddiq, M.Ag.", 'umum');
        Pengaturan::set('nip_kepala', "197808152003121003", 'umum');
        Pengaturan::set('alamat', "Kompleks Asrama Ma'had Al-Jami'ah UIN Siber Syekh Nurjati Cirebon", 'umum');
        Pengaturan::set('telepon', "081122334455", 'umum');
        Pengaturan::set('email', "mahad@uinssc.ac.id", 'umum');
        Pengaturan::set('jam_malam', "21:30", 'umum');
        Pengaturan::set('maks_santri_per_kamar', "4", 'umum');

        // 5. Master Kategori Perizinan Resmi
        PerizinanJenis::firstOrCreate(
            ['nama' => 'Izin Sakit / Rawat Inap'],
            ['butuh_bukti' => true, 'batas_hari_maks' => 7, 'deskripsi' => 'Surat keterangan dokter wajib dilampirkan', 'is_active' => true]
        );
        PerizinanJenis::firstOrCreate(
            ['nama' => 'Izin Pulang Keperluan Keluarga'],
            ['butuh_bukti' => false, 'batas_hari_maks' => 4, 'deskripsi' => 'Acara mendesak keluarga atau orang tua sakit', 'is_active' => true]
        );
        PerizinanJenis::firstOrCreate(
            ['nama' => 'Dispensasi Tugas / Lomba Kampus'],
            ['butuh_bukti' => true, 'batas_hari_maks' => 5, 'deskripsi' => 'Surat tugas delegasi fakultas/universitas', 'is_active' => true]
        );

        // 6. Master Kategori Pelanggaran Kedisiplinan
        PelanggaranJenis::firstOrCreate(
            ['nama' => 'Terlambat Jam Malam Asrama (>21.30 WIB)'],
            ['poin_default' => 5, 'sanksi_default' => 'Teguran lisan & piket kebersihan', 'is_active' => true]
        );
        PelanggaranJenis::firstOrCreate(
            ['nama' => 'Tidak Mengikuti Shalat Berjamaah Subuh / Isya'],
            ['poin_default' => 10, 'sanksi_default' => 'Setoran hafalan 1 juz', 'is_active' => true]
        );
        PelanggaranJenis::firstOrCreate(
            ['nama' => 'Pelanggaran Ketertiban & Norma Asrama'],
            ['poin_default' => 25, 'sanksi_default' => 'Pemanggilan orang tua / wali', 'is_active' => true]
        );
    }
}
