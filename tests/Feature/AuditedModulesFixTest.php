<?php

namespace Tests\Feature;

use App\Enums\AbsensiStatus;
use App\Enums\Gender;
use App\Enums\PelanggaranStatus;
use App\Enums\PerizinanStatus;
use App\Enums\SantriStatus;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\Pelanggaran;
use App\Models\PelanggaranJenis;
use App\Models\Penilaian;
use App\Models\Periode;
use App\Models\Perizinan;
use App\Models\PerizinanJenis;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditedModulesFixTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $mudabbir;
    private User $tutor;
    private User $santriUser;
    private Santri $santri;
    private Periode $periode;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mudabbir', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mahasantri', 'guard_name' => 'web']);

        $this->periode = Periode::create([
            'nama' => 'Semester Gasal 2026/2027',
            'kode' => '2026-1',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['name' => 'Admin Test', 'nomor_induk' => 'ADM-01']);
        $this->admin->assignRole('admin');

        $this->mudabbir = User::factory()->create(['name' => 'Mudabbir Test', 'nomor_induk' => 'MDB-01']);
        $this->mudabbir->assignRole('mudabbir');

        $this->tutor = User::factory()->create(['name' => 'Tutor Test', 'nomor_induk' => 'TTR-01']);
        $this->tutor->assignRole('tutor');

        $this->santriUser = User::factory()->create(['name' => 'Santri Ahmad', 'nomor_induk' => '12345678']);
        $this->santriUser->assignRole('mahasantri');

        $this->santri = Santri::create([
            'user_id' => $this->santriUser->id,
            'nomor_induk' => '12345678',
            'gender' => Gender::L,
            'status' => SantriStatus::Aktif,
            'periode_id' => $this->periode->id,
        ]);
    }

    public function test_f04_perizinan_page_loads_for_mahasantri_with_pelanggaran_without_crash(): void
    {
        $jenisPel = PelanggaranJenis::create([
            'nama' => 'Terlambat Masuk Asrama',
            'poin' => 5,
        ]);

        Pelanggaran::create([
            'periode_id' => $this->periode->id,
            'santri_id' => $this->santri->id,
            'jenis_pelanggaran_id' => $jenisPel->id,
            'tanggal' => now()->toDateString(),
            'poin' => 5,
            'deskripsi' => 'Terlambat 15 menit',
            'status' => PelanggaranStatus::Terdaftar,
            'input_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->santriUser)->get(route('kedisiplinan.perizinan.index'));
        $response->assertOk();
        $response->assertSee('Catatan Disiplin');
    }

    public function test_f03_tutor_cannot_export_or_delete_perizinan(): void
    {
        $jenisIzin = PerizinanJenis::create(['nama' => 'Izin Pulang', 'is_active' => true]);
        $izin = Perizinan::create([
            'periode_id' => $this->periode->id,
            'santri_id' => $this->santri->id,
            'jenis_perizinan_id' => $jenisIzin->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'alasan' => 'Acara Keluarga',
            'status' => PerizinanStatus::Berlaku,
            'input_by' => $this->santriUser->id,
        ]);

        // Tutor is forbidden from perizinan route group
        $resExport = $this->actingAs($this->tutor)->get(route('kedisiplinan.perizinan.export'));
        $resExport->assertForbidden();

        $resDelete = $this->actingAs($this->tutor)->delete(route('kedisiplinan.perizinan.destroy', $izin));
        $resDelete->assertForbidden();

        // But admin or mudabbir can export and manage
        $resAdminExport = $this->actingAs($this->admin)->get(route('kedisiplinan.perizinan.export'));
        $resAdminExport->assertOk();
    }

    public function test_f07_nilai_update_route_works_cleanly(): void
    {
        $kelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok 1',
            'kapasitas' => 30,
        ]);

        $kelas = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Tahfidz Al-Quran',
            'kode' => 'TF-01',
            'tipe' => 'kelompok',
            'is_active' => true,
        ]);

        $penilaian = Penilaian::create([
            'santri_id' => $this->santri->id,
            'kelompok_id' => $kelompok->id,
            'kelas_id' => $kelas->id,
            'input_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put('/dashboard/kelas/nilai/' . $penilaian->id, [
            'nilai_angka' => 88,
            'catatan' => 'Sangat baik',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mahad_penilaian', [
            'id' => $penilaian->id,
            'nilai_angka' => 88,
            'nilai_huruf' => 'A',
            'catatan' => 'Sangat baik',
        ]);
    }

    public function test_f05_kelompok_anggota_store_by_nim_works_without_username_sql_error(): void
    {
        $kelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok Halaqah Alpha',
            'kapasitas' => 20,
        ]);

        $response = $this->actingAs($this->admin)->post('/dashboard/kelompok/' . $kelompok->id . '/anggota', [
            'nim' => '12345678',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mahad_kelompok_anggota', [
            'kelompok_id' => $kelompok->id,
            'santri_id' => $this->santri->id,
        ]);
    }

    public function test_f12_pelanggaran_index_stats_works_in_sqlite_without_date_format_error(): void
    {
        $response = $this->actingAs($this->admin)->get(route('kedisiplinan.pelanggaran.index'));
        $response->assertOk();
    }
}
