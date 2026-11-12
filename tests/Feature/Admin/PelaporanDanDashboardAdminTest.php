<?php

namespace Tests\Feature\Admin;

use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PelaporanDanDashboardAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $santri;
    protected Periode $periode;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mahasantri', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->santri = User::factory()->create();
        $this->santri->assignRole('mahasantri');

        $this->periode = Periode::create([
            'kode' => '20261',
            'nama' => '2026 Ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2027-01-31',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_dashboard_with_action_center(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Ahlan wa Sahlan');
        $response->assertSee('Pusat Unduh Laporan');
        $response->assertSee('Fasilitas');
        $response->assertSee('Akademik');
        $response->assertSee('Kedisiplinan');
    }

    public function test_admin_can_access_pelaporan_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pelaporan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Unduh Laporan Eksekutif');
        $response->assertSee('Laporan Kinerja', false);
        $response->assertSee('Ruang Lingkup Waktu');
    }

    public function test_admin_can_export_master_report_print(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pelaporan.export', [
            'tipe' => 'master',
            'format' => 'print',
            'rentang' => 'periode',
            'periode_id' => $this->periode->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN KINERJA');
        $response->assertSee('UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON');
        $response->assertSee('Mudir / Kepala');
    }

    public function test_admin_can_export_master_report_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pelaporan.export', [
            'tipe' => 'master',
            'format' => 'excel',
            'rentang' => 'periode',
            'periode_id' => $this->periode->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));
    }

    public function test_admin_can_export_single_module_with_monthly_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pelaporan.export', [
            'tipe' => 'pelanggaran',
            'format' => 'print',
            'rentang' => 'bulanan',
            'bulan' => '2026-10',
            'periode_id' => $this->periode->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laporan Kedisiplinan & Pelanggaran');
    }

    public function test_admin_can_export_all_single_modules_excel_and_pdf_with_empty_strings(): void
    {
        $modules = ['okupansi', 'nilai', 'kehadiran', 'pelanggaran', 'perizinan'];
        foreach ($modules as $mod) {
            // Excel test with empty parameters
            $resExcel = $this->actingAs($this->admin)->get(route('pelaporan.export', [
                'tipe' => $mod,
                'format' => 'excel',
                'periode_id' => (string) $this->periode->id,
                'rentang' => 'periode',
                'bulan' => '',
                'gedung_id' => '',
                'kelas_id' => '',
                'status' => '',
            ]));
            $resExcel->assertStatus(200);
            $this->assertStringContainsString('spreadsheetml.sheet', (string) $resExcel->headers->get('content-type'));

            // Print / PDF view test
            $resPrint = $this->actingAs($this->admin)->get(route('pelaporan.export', [
                'tipe' => $mod,
                'format' => 'print',
                'periode_id' => (string) $this->periode->id,
                'rentang' => 'periode',
                'bulan' => '',
                'gedung_id' => '',
                'kelas_id' => '',
                'status' => '',
            ]));
            $resPrint->assertStatus(200);
        }
    }

    public function test_non_admin_cannot_access_pelaporan(): void
    {
        $response = $this->actingAs($this->santri)->get(route('pelaporan.index'));

        $response->assertStatus(403);
    }
}
