<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\KamarStatus;
use App\Enums\TipeRuang;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FasilitasMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Gedung $gedung;
    protected Lantai $lantai;
    protected Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'mudabbir']);
        Role::firstOrCreate(['name' => 'tutor']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->gedung = Gedung::create([
            'kode' => 'GD1',
            'nama' => 'Gedung Ali',
            'gender_khusus' => 'L',
            'deskripsi' => 'Gedung Ikhwan',
            'is_active' => true,
        ]);

        $this->lantai = Lantai::create([
            'gedung_id' => $this->gedung->id,
            'nomor_lantai' => 1,
            'kapasitas_kamar_standar' => 4,
        ]);

        $this->kamar = Kamar::create([
            'lantai_id' => $this->lantai->id,
            'nomor_kamar' => 'GA1-01',
            'tipe_ruang' => TipeRuang::Kamar,
            'gender' => Gender::L,
            'kapasitas_maks' => 4,
            'status' => KamarStatus::Tersedia,
        ]);
    }

    public function test_monitoring_page_can_be_rendered_without_url_exception(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/fasilitas/monitoring');

        $response->assertStatus(200);
        $response->assertSee('Monitoring Asrama');
        $response->assertSee('GA1-01');
    }

    public function test_room_detail_endpoint_returns_valid_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson("/dashboard/fasilitas/monitoring/room/{$this->kamar->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->kamar->id,
            'nomor_kamar' => 'GA1-01',
            'gedung' => 'Gedung Ali',
            'lantai' => 1,
            'gender' => 'Ikhwan',
            'kapasitas_maks' => 4,
            'terisi' => 0,
            'sisa' => 4,
        ]);
    }

    public function test_kelola_kamar_views_render_successfully(): void
    {
        $responseTable = $this->actingAs($this->admin)->get('/dashboard/fasilitas/kelola?tab=kamar&view=table');
        $responseTable->assertStatus(200);
        $responseTable->assertSee('GA1-01');

        $responseLantai = $this->actingAs($this->admin)->get('/dashboard/fasilitas/kelola?tab=kamar&view=per_lantai');
        $responseLantai->assertStatus(200);
        $responseLantai->assertSee('GA1-01');
    }
}
