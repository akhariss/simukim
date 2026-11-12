<?php

namespace Tests\Feature\Auth;

use App\Models\Periode;
use App\Models\User;
use App\Services\CampusApiService;
use App\Services\CampusAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SahajaSyncAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'mahasantri']);
        Periode::firstOrCreate(
            ['kode' => '20251'],
            [
                'academic_period_id' => 1,
                'nama' => 'Semester Ganjil 2025/2026',
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2026-01-31',
                'is_active' => true,
            ]
        );
    }

    public function test_mahasantri_can_auto_sync_and_login_with_nim(): void
    {
        $mockApi = Mockery::mock(CampusApiService::class);
        $mockApi->shouldReceive('verifyCredentials')
            ->once()
            ->with('240100002', 'password')
            ->andReturn([
                'success' => true,
                'user_type' => 'mahasiswa',
                'data' => [
                    'nomor_induk' => '240100002',
                    'nama' => 'Ahmad Test',
                    'email' => '240100002@student.ac.id',
                    'gender' => 'L',
                    'prodi' => 'Ilmu Al-Qur\'an dan Tafsir',
                    'angkatan' => '2024',
                ],
            ]);
        $mockApi->shouldReceive('smartLookup')
            ->once()
            ->with('240100002')
            ->andReturn([
                'success' => true,
                'source_type' => 'mahasiswa',
                'data' => [
                    'nomor_induk' => '240100002',
                    'nama' => 'Ahmad Test',
                ],
                'raw' => [
                    'nim' => '240100002',
                    'nama' => 'Ahmad Test',
                    'jenis_kelamin' => 'L',
                    'prodi' => 'Ilmu Al-Qur\'an dan Tafsir',
                    'angkatan' => '2024',
                    'billing_tagihan' => [
                        'briva_number' => '12789240100002',
                        'bsb_customer_id' => 'BSB-12789240100002',
                        'tagihan_terakhir' => [
                            'status' => 'Lunas',
                            'nominal' => 1500000,
                        ],
                    ],
                ],
            ]);
        $this->app->instance(CampusApiService::class, $mockApi);

        $response = $this->post('/login', [
            'login_id' => '240100002',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', ['nomor_induk' => '240100002']);
        $this->assertDatabaseHas('mahad_santri', ['nomor_induk' => '240100002', 'briva_number' => '12789240100002']);

        $user = User::where('nomor_induk', '240100002')->first();
        $this->assertTrue($user->hasRole('mahasantri'));
    }

    public function test_admin_can_reconcile_and_sync_sahaja_from_cache_control_center(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'nomor_induk' => 'NIP-ADM-TEST',
            'password' => bcrypt('password'),
            'auth_source' => 'local',
        ]);
        $admin->assignRole('admin');

        $mockAuth = Mockery::mock(CampusAuthService::class);
        $mockAuth->shouldReceive('syncAllMuqimMahasiswa')
            ->once()
            ->with('20251', ['status_pembayaran' => 'Lunas', 'gender' => 'L'])
            ->andReturn([
                'synced' => 5,
                'total' => 5,
                'new' => 3,
                'updated' => 2,
                'summary' => ['total_mukim' => 5],
            ]);
        $this->app->instance(CampusAuthService::class, $mockAuth);

        $response = $this->actingAs($admin)->post(route('admin.cache.sahaja.sync'), [
            'kode_periode' => '20251',
            'status_pembayaran' => 'Lunas',
            'gender' => 'L',
        ]);

        $response->assertSessionHas('status');
        $response->assertSessionHas('reconciliation_report');
    }

    public function test_admin_can_switch_active_periode_from_cache_control_center(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin2@test.com',
            'nomor_induk' => 'NIP-ADM-TEST2',
            'password' => bcrypt('password'),
            'auth_source' => 'local',
        ]);
        $admin->assignRole('admin');

        $periodeBaru = Periode::create([
            'kode' => '20252',
            'academic_period_id' => 2,
            'nama' => 'Semester Genap 2025/2026',
            'tanggal_mulai' => '2026-02-01',
            'tanggal_selesai' => '2026-07-31',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.cache.periode.switch'), [
            'periode_id' => $periodeBaru->id,
        ]);

        $response->assertSessionHas('status');
        $this->assertTrue((bool) $periodeBaru->fresh()->is_active);
    }
}
