<?php

namespace Tests\Feature;

use App\Enums\KelasType;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokPengampu;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KelompokKajianTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $tutor;
    private Periode $periode;
    private Kelompok $kelompok;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tutor']);

        $this->admin = User::factory()->create();
        $this->admin->syncRoles(['admin']);

        $this->tutor = User::factory()->create(['name' => 'Ustadz Zaid']);
        $this->tutor->syncRoles(['tutor']);

        $this->periode = Periode::create([
            'nama' => '2025/2026 Ganjil',
            'kode' => '20251',
            'is_active' => true,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonths(6),
        ]);

        $this->kelompok = Kelompok::create([
            'nama' => 'Kelompok Test A',
            'kode' => 'KLP-A',
            'periode_id' => $this->periode->id,
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'Tahsin & Tajwid Al-Quran',
            'tipe' => KelasType::Quran,
            'periode_id' => $this->periode->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_dapat_melihat_halaman_detail_kajian_kelompok(): void
    {
        $response = $this->actingAs($this->admin)->get(route('kelompok.kajian.show', [
            'kelompok' => $this->kelompok,
            'kelas' => $this->kelas,
        ]));

        $response->assertOk();
        $response->assertSee('Tahsin &amp; Tajwid Al-Quran', false);
        $response->assertSee('Kelompok Test A');
        $response->assertSee('Tutor Pengampu Kajian');
    }

    public function test_admin_dapat_menugaskan_tutor_pada_kajian_kelompok(): void
    {
        $response = $this->actingAs($this->admin)->post(route('kelompok.kajian.assign.tutor', [
            'kelompok' => $this->kelompok,
            'kelas' => $this->kelas,
        ]), [
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mahad_kelompok_pengampu', [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);
    }

    public function test_admin_dapat_melepas_tutor_pengampu(): void
    {
        KelompokPengampu::create([
            'kelompok_id' => $this->kelompok->id,
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('kelompok.kajian.unassign.tutor', [
            'kelompok' => $this->kelompok,
            'kelas' => $this->kelas,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('mahad_kelompok_pengampu', [
            'kelompok_id' => $this->kelompok->id,
            'kelas_id' => $this->kelas->id,
        ]);
    }

    public function test_halaman_detail_kajian_menampilkan_link_kembali_dan_tutor_terpilih(): void
    {
        KelompokPengampu::create([
            'kelompok_id' => $this->kelompok->id,
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('kelompok.kajian.show', [
            'kelompok' => $this->kelompok,
            'kelas' => $this->kelas,
        ]));

        $response->assertOk();
        $response->assertSee('Ustadz Zaid');
        $response->assertSee('Lepas Tutor');
        $response->assertSee(route('kelompok.show', $this->kelompok));
    }

    public function test_tutor_conflict_rule_di_kajian_quran_dan_masal(): void
    {
        // Kelompok B
        $kelompokB = Kelompok::create([
            'nama' => 'Kelompok Test B',
            'kode' => 'KLP-B',
            'periode_id' => $this->periode->id,
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        // Tutor ditugaskan di Kelompok B untuk kajian Quran
        KelompokPengampu::create([
            'kelompok_id' => $kelompokB->id,
            'kelas_id' => $this->kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        // Buka Kelompok A untuk kajian Quran
        $response = $this->actingAs($this->admin)->get(route('kelompok.kajian.show', [
            'kelompok' => $this->kelompok,
            'kelas' => $this->kelas,
        ]));

        $response->assertOk();
        $tutorCandidates = $response->viewData('tutorCandidates');
        $zaidCandidate = collect($tutorCandidates)->firstWhere('id', $this->tutor->id);
        $this->assertFalse($zaidCandidate['is_eligible']);
        $this->assertEquals('Kelompok Test B', $zaidCandidate['conflict_kelompok']);
        $this->assertEquals(1, $response->viewData('unavailableCount'));

        // Kajian Masal
        $kelasMasal = Kelas::create([
            'nama' => 'Kuliah Umum Asrama',
            'tipe' => KelasType::Masal,
            'periode_id' => $this->periode->id,
            'is_active' => true,
        ]);

        KelompokPengampu::create([
            'kelompok_id' => $kelompokB->id,
            'kelas_id' => $kelasMasal->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $responseMasal = $this->actingAs($this->admin)->get(route('kelompok.kajian.show', [
            'kelompok' => $this->kelompok,
            'kelas' => $kelasMasal,
        ]));

        $responseMasal->assertOk();
        $masalCandidates = $responseMasal->viewData('tutorCandidates');
        $zaidMasal = collect($masalCandidates)->firstWhere('id', $this->tutor->id);
        $this->assertTrue($zaidMasal['is_eligible']);
    }
}
