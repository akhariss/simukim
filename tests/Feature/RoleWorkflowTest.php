<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\KelasKomponenNilai;
use App\Models\Kelompok;
use App\Models\Pelanggaran;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $mudabbir;
    private User $tutor;
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

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->mudabbir = User::factory()->create();
        $this->mudabbir->assignRole('mudabbir');

        $this->tutor = User::factory()->create();
        $this->tutor->assignRole('tutor');
    }

    public function test_mudabbir_cannot_access_presensi_harian(): void
    {
        $response = $this->actingAs($this->mudabbir)->get(route('kelas.absensi.index'));
        $response->assertForbidden();
    }

    public function test_mudabbir_can_access_penempatan_mahasantri(): void
    {
        $response = $this->actingAs($this->mudabbir)->get(route('fasilitas.penempatan.index'));
        $response->assertOk();
    }

    public function test_mudabbir_cannot_delete_pelanggaran(): void
    {
        $santriUser = User::factory()->create();
        $santriUser->assignRole('mahasantri');

        $santri = \App\Models\Santri::create([
            'user_id' => $santriUser->id,
            'nomor_induk' => '20269999',
            'periode_id' => $this->periode->id,
            'gender' => 'L',
            'status' => 'aktif',
        ]);

        $jenis = \App\Models\PelanggaranJenis::create([
            'nama' => 'Terlambat Masuk Asrama',
            'poin_default' => 5,
            'is_active' => true,
        ]);

        $pelanggaran = Pelanggaran::create([
            'santri_id' => $santri->id,
            'periode_id' => $this->periode->id,
            'jenis_pelanggaran_id' => $jenis->id,
            'tanggal' => now()->toDateString(),
            'deskripsi' => 'Uji coba pelanggaran',
            'status' => 'terdaftar',
            'input_by' => $this->mudabbir->id,
        ]);

        $response = $this->actingAs($this->mudabbir)->delete(route('kedisiplinan.pelanggaran.destroy', $pelanggaran));
        $response->assertForbidden();

        $adminResponse = $this->actingAs($this->admin)->delete(route('kedisiplinan.pelanggaran.destroy', $pelanggaran));
        $adminResponse->assertRedirect();
        $this->assertDatabaseMissing('mahad_pelanggaran', ['id' => $pelanggaran->id]);
    }

    public function test_mudabbir_dashboard_shows_kelompok_detail_links(): void
    {
        $kelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Halaqah Abu Bakar 01',
            'kapasitas' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->mudabbir)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee(route('kelompok.show', $kelompok->id));
        $response->assertSee('Lihat Detail');
    }

    public function test_mudabbir_can_view_kelompok_show_in_read_only_mode(): void
    {
        $kelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Halaqah Umar 02',
            'kapasitas' => 25,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->mudabbir)->get(route('kelompok.show', $kelompok));
        $response->assertOk();
        $response->assertSee('Halaqah Umar 02');
        $response->assertDontSee('Edit Kelompok');
        $response->assertDontSee('Tambah Santri');
    }

    public function test_mudabbir_can_view_kelompok_kajian_show_in_read_only_mode(): void
    {
        $kelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Halaqah Utsman 03',
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        $kelas = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kajian Fiqih Ibadah',
            'tipe' => 'kitab',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->mudabbir)->get(route('kelompok.kajian.show', [$kelompok, $kelas]));
        $response->assertOk();
        $response->assertSee('Kajian Fiqih Ibadah');
        $response->assertDontSee('Presensi Kajian');
    }

    public function test_tutor_can_access_kajian_saya_and_kedisiplinan(): void
    {
        $kajianResponse = $this->actingAs($this->tutor)->get(route('tutor.kajian.index'));
        $kajianResponse->assertOk();

        $kedisiplinanResponse = $this->actingAs($this->tutor)->get(route('tutor.kedisiplinan.index'));
        $kedisiplinanResponse->assertOk();
    }

    public function test_tutor_cannot_delete_mandatory_absensi_component(): void
    {
        $kelas = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Al-Quran Tahsin',
            'tipe' => 'quran',
            'is_active' => true,
        ]);

        $komponenAbsen = KelasKomponenNilai::create([
            'kelas_id' => $kelas->id,
            'nama' => 'Absensi',
            'bobot' => 25.0,
            'is_absensi' => true,
            'is_fixed' => true,
            'urutan' => 1,
        ]);

        $response = $this->actingAs($this->tutor)->delete(route('kelas.nilai.komponen.destroy', $komponenAbsen));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('mahad_kelas_komponen_nilai', ['id' => $komponenAbsen->id]);
    }

    public function test_tutor_can_add_custom_grading_component(): void
    {
        $kelas = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kitab Fiqih',
            'tipe' => 'kitab',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->tutor)->post(route('kelas.nilai.komponen.store'), [
            'kelas_id' => $kelas->id,
            'nama' => 'Setoran Hafalan',
            'bobot' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mahad_kelas_komponen_nilai', [
            'kelas_id' => $kelas->id,
            'nama' => 'Setoran Hafalan',
            'bobot' => 30.0,
        ]);
    }

    public function test_tutor_or_admin_can_update_grading_component_name_and_weight(): void
    {
        $kelas = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Hadis Tematik',
            'tipe' => 'kitab',
            'is_active' => true,
        ]);

        $komponen = KelasKomponenNilai::create([
            'kelas_id' => $kelas->id,
            'nama' => 'Tugas Awal',
            'bobot' => 20.0,
            'is_absensi' => false,
            'is_fixed' => false,
            'urutan' => 1,
        ]);

        $response = $this->actingAs($this->tutor)->put(route('kelas.nilai.komponen.update', $komponen), [
            'nama' => 'Tugas Mandiri & Resume',
            'bobot' => 35.5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mahad_kelas_komponen_nilai', [
            'id' => $komponen->id,
            'nama' => 'Tugas Mandiri & Resume',
            'bobot' => 35.5,
        ]);
    }

    public function test_nilai_export_supports_excel_and_pdf(): void
    {
        $responseExcel = $this->actingAs($this->admin)->get(route('kelas.nilai.export', ['format' => 'excel']));
        $responseExcel->assertOk();

        $responsePdf = $this->actingAs($this->admin)->get(route('kelas.nilai.export', ['format' => 'pdf']));
        $responsePdf->assertOk();
    }
}
