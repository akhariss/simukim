<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\CampusApiService;
use App\Services\CampusAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CampusGatekeeperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'mahasantri']);
        Role::firstOrCreate(['name' => 'mudabbir']);
        Role::firstOrCreate(['name' => 'tutor']);
    }

    public function test_unregistered_student_is_auto_rejected_at_login(): void
    {
        // Mahasiswa biasa dari kampus yang belum didaftarkan oleh admin
        $response = $this->post('/login', [
            'login_id' => '99999999',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_registered_campus_student_can_login_with_valid_bridge_credentials(): void
    {
        // 1. Daftarkan santri terlebih dahulu oleh Admin / SK
        $authService = app(CampusAuthService::class);
        $user = $authService->assignSantri('21001');

        // 2. Mock Bridge API verification success
        $mockApi = Mockery::mock(CampusApiService::class);
        $mockApi->shouldReceive('verifyCredentials')
            ->once()
            ->with('21001', 'password123')
            ->andReturn([
                'success' => true,
                'user_type' => 'mahasiswa',
                'data' => [
                    'nomor_induk' => '21001',
                    'nama' => 'Ahmad Fauzi',
                    'email' => 'ahmad.fauzi@uin.ac.id',
                    'gender' => 'L',
                    'prodi' => 'Teknik Informatika',
                    'angkatan' => '2021',
                ],
            ]);
        $this->app->instance(CampusApiService::class, $mockApi);

        // 3. Login dengan kredensial kampus
        $response = $this->post('/login', [
            'login_id' => '21001',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registered_campus_student_fails_with_invalid_password(): void
    {
        $authService = app(CampusAuthService::class);
        $authService->assignSantri('21001');

        $mockApi = Mockery::mock(CampusApiService::class);
        $mockApi->shouldReceive('verifyCredentials')
            ->once()
            ->with('21001', 'salah_password')
            ->andReturn([
                'success' => false,
                'message' => 'NIM/NIP atau kata sandi portal kampus tidak cocok.',
            ]);
        $this->app->instance(CampusApiService::class, $mockApi);

        $response = $this->post('/login', [
            'login_id' => '21001',
            'password' => 'salah_password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_register_new_santri_via_nim(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('users.assign.santri'), [
            'nim' => '21005',
        ]);

        $response->assertRedirect(route('users.index', ['role' => 'mahasantri']));
        $this->assertDatabaseHas('users', ['nomor_induk' => '21005']);
        $this->assertDatabaseHas('mahad_santri', ['nomor_induk' => '21005', 'status' => 'aktif']);

        $registeredUser = User::where('nomor_induk', '21005')->first();
        $this->assertTrue($registeredUser->hasRole('mahasantri'));
    }

    public function test_admin_can_register_new_santri_with_custom_name_and_prodi(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('users.assign.santri'), [
            'nim' => '2388010004',
            'nama' => 'Mahasiswa Test 2388010004',
            'gender' => 'L',
            'prodi' => 'Teknik Informatika',
        ]);

        $response->assertRedirect(route('users.index', ['role' => 'mahasantri']));
        $this->assertDatabaseHas('users', [
            'nomor_induk' => '2388010004',
            'name' => 'Mahasiswa Test 2388010004',
        ]);
        $this->assertDatabaseHas('mahad_santri', [
            'nomor_induk' => '2388010004',
            'prodi' => 'Teknik Informatika',
            'gender' => 'L',
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_lookup_student_by_nim(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $mockApi = Mockery::mock(CampusApiService::class);
        $mockApi->shouldReceive('smartLookup')
            ->once()
            ->with('2388010004')
            ->andReturn([
                'success' => true,
                'source_type' => 'mahasiswa',
                'data' => [
                    'nomor_induk' => '2388010004',
                    'nama' => 'Abdul Kharis',
                    'email' => '2388010004@uin.ac.id',
                    'gender' => 'L',
                    'prodi' => 'Informatika',
                ],
            ]);
        $this->app->instance(CampusApiService::class, $mockApi);

        $response = $this->actingAs($admin)->getJson(route('users.campus.lookup', ['nim' => '2388010004']));

        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'source_type' => 'mahasiswa',
            'data' => [
                'nomor_induk' => '2388010004',
                'nama' => 'Abdul Kharis',
                'prodi' => 'Informatika',
            ],
        ]);
    }

    public function test_admin_can_register_user_via_unified_store_endpoint(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'role' => 'mudabbir',
            'auth_source' => 'campus',
            'nomor_induk' => '240199999',
            'name' => 'Ahmad Mudabbir Baru',
            'gender' => 'L',
            'prodi' => 'Ilmu Hadis',
        ]);

        $response->assertRedirect(route('users.index', ['role' => 'mudabbir']));
        $this->assertDatabaseHas('users', [
            'nomor_induk' => '240199999',
            'name' => 'Ahmad Mudabbir Baru',
            'auth_source' => 'campus',
        ]);
        $this->assertDatabaseHas('mahad_santri', [
            'nomor_induk' => '240199999',
            'gender' => 'L',
            'prodi' => 'Ilmu Hadis',
            'status' => 'aktif',
        ]);
        $u = User::where('nomor_induk', '240199999')->first();
        $this->assertTrue($u->hasRole('mudabbir'));
    }
}
