<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TutorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $tutor;
    private Periode $periode;
    private Kelompok $kelompok;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);

        $this->periode = Periode::create([
            'nama' => 'Semester Gasal 2026/2027',
            'kode' => '2026-1',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->tutor = User::factory()->create();
        $this->tutor->assignRole('tutor');

        $this->kelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Halaqah Ali Bin Abi Thalib',
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        $this->kelas = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Tajwid & Tahsin',
            'tipe' => 'quran',
            'is_active' => true,
        ]);

        DB::table('mahad_kelompok_pengampu')->insert([
            'kelompok_id' => $this->kelompok->id,
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_tutor_dashboard_renders_monthly_cards_and_schedule(): void
    {
        $response = $this->actingAs($this->tutor)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Presensi Terlaksana');
        $response->assertSee('Laporan Bulanan');
        $response->assertSee('Jadwal Mengajar Anda');
    }

    public function test_tutor_can_view_assigned_kajian_detail_and_forbidden_for_unassigned(): void
    {
        // 1. Assigned class detail page works
        $response = $this->actingAs($this->tutor)->get(route('tutor.kajian.show', [
            'kelompok' => $this->kelompok->id,
            'kelas' => $this->kelas->id,
        ]));
        $response->assertOk();
        $response->assertSee('Tajwid & Tahsin');
        $response->assertSee('Halaqah Ali Bin Abi Thalib');

        // 2. Unassigned class returns 403
        $unassignedKelompok = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Halaqah Lain',
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        $unassignedResponse = $this->actingAs($this->tutor)->get(route('tutor.kajian.show', [
            'kelompok' => $unassignedKelompok->id,
            'kelas' => $this->kelas->id,
        ]));
        $unassignedResponse->assertForbidden();
    }

    public function test_tutor_presensi_initial_view_and_dedicated_session_sheet(): void
    {
        // Initial view shows class list
        $response = $this->actingAs($this->tutor)->get(route('kelas.absensi.index'));
        $response->assertOk();
        $response->assertSee('Halaqah Ali Bin Abi Thalib');
        $response->assertSee('Tajwid & Tahsin');

        // Opening assigned class sheet
        $sheetResponse = $this->actingAs($this->tutor)->get(route('kelas.absensi.index', [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id' => $this->kelas->id,
        ]));
        $sheetResponse->assertOk();
        $sheetResponse->assertSee('Presensi Sesi Berjalan');
        $sheetResponse->assertSee('Riwayat Presensi Sesi');
    }

    public function test_tutor_laporan_class_list_and_dedicated_page(): void
    {
        // Class list for reports
        $response = $this->actingAs($this->tutor)->get(route('kelas.laporan.index'));
        $response->assertOk();
        $response->assertSee('Halaqah Ali Bin Abi Thalib');
        $response->assertSee('Kelola Laporan');

        // Dedicated page for this class
        $kelasLaporanResponse = $this->actingAs($this->tutor)->get(route('kelas.laporan.kelas', [
            'kelompok' => $this->kelompok->id,
            'kelas' => $this->kelas->id,
        ]));
        $kelasLaporanResponse->assertOk();
        $kelasLaporanResponse->assertSee('Tambah Laporan Bulanan');
        $kelasLaporanResponse->assertSee('Riwayat Laporan Bulanan Kelas Ini');
    }

    public function test_tutor_laporan_create_page_and_min_3_photos_validation(): void
    {
        // 1. Create page renders successfully
        $createResponse = $this->actingAs($this->tutor)->get(route('kelas.laporan.create', [
            'kelompok' => $this->kelompok->id,
            'kelas' => $this->kelas->id,
        ]));
        $createResponse->assertOk();
        $createResponse->assertSee('Kirim Laporan Bulanan');

        // 2. Submit with only 1 photo fails with validation error
        $file1 = \Illuminate\Http\UploadedFile::fake()->image('foto1.jpg');
        $failResponse = $this->actingAs($this->tutor)->post(route('kelas.laporan.store'), [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id'    => $this->kelas->id,
            'bulan'       => 11,
            'tahun'       => 2026,
            'catatan'     => 'Catatan pengujian singkat',
            'foto'        => [$file1],
        ]);
        $failResponse->assertSessionHasErrors(['foto']);

        // 3. Submit with 3 photos succeeds
        \Illuminate\Support\Facades\Storage::fake('local');
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('foto2.jpg');
        $file3 = \Illuminate\Http\UploadedFile::fake()->image('foto3.jpg');

        $successResponse = $this->actingAs($this->tutor)->post(route('kelas.laporan.store'), [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id'    => $this->kelas->id,
            'bulan'       => 11,
            'tahun'       => 2026,
            'catatan'     => 'Catatan pembelajaran dan evaluasi bulanan materi tajwid berjalan lancar.',
            'foto'        => [$file1, $file2, $file3],
        ]);

        $successResponse->assertRedirect(route('kelas.laporan.kelas', [$this->kelompok->id, $this->kelas->id]));
        $successResponse->assertSessionHas('success');
        $this->assertDatabaseHas('mahad_laporan_bulanan', [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id'    => $this->kelas->id,
            'tutor_id'    => $this->tutor->id,
            'bulan'       => 11,
            'tahun'       => 2026,
        ]);
    }

    public function test_tutor_penilaian_initial_view_and_dedicated_sheet(): void
    {
        // 1. Initial view shows class list
        $response = $this->actingAs($this->tutor)->get(route('kelas.nilai.index'));
        $response->assertOk();
        $response->assertSee('Halaqah Ali Bin Abi Thalib');
        $response->assertSee('Kelola Nilai');

        // 2. Opening class grading sheet
        $sheetResponse = $this->actingAs($this->tutor)->get(route('kelas.nilai.index', [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id'    => $this->kelas->id,
        ]));
        $sheetResponse->assertOk();
        $sheetResponse->assertSee('Komponen Nilai');
        $sheetResponse->assertSee('Simpan Rekapitulasi Nilai');
    }
}

