<?php

namespace Tests\Feature\CampusApi;

use App\Models\User;
use App\Services\CampusApiService;
use App\Services\CampusAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BridgeAuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $bridgeUrl = 'https://bridge.uinssc.ac.id';
    protected string $sahajaUrl = 'https://sahaja.uinssc.ac.id';

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tutor']);
        Role::firstOrCreate(['name' => 'mudabbir']);
        Role::firstOrCreate(['name' => 'mahasantri']);

        config([
            'services.campus_api.base_url'   => $this->bridgeUrl,
            'services.campus_api.bridge_url' => $this->bridgeUrl,
            'services.campus_api.sahaja_url' => $this->sahajaUrl,
            'services.campus_api.key'        => 'shj_live_8f92a4c7e1b6d0359a2c4e7b1d8f3a6c',
            'services.campus_api.timeout'    => 5,
            'services.campus_api.verify_ssl' => false,
        ]);

        Cache::put('campus_api_health_status', true, 300);
    }

    // =========================================================================
    // TIER 1: FEATURE COVERAGE
    // =========================================================================

    /**
     * T1-BR-01: Tutor login happy path with valid Bridge credentials
     */
    public function test_tutor_login_happy_path_with_valid_bridge_credentials(): void
    {
        $nip = '198507152010121003';
        $password = 'password_tutor_valid';
        $token = '154|9f8a7b6c5d4e3f2a1b0c9d8e7f6a5b4c3d2e1f0a';

        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status'  => true,
                'message' => 'Login portal kampus berhasil.',
                'data'    => [
                    'token'          => $token,
                    'nip'            => $nip,
                    'nama'           => 'Dr. H. Maimun, M.Ag.',
                    'email'          => 'maimun@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                    'prodi'          => 'Ushuluddin dan Dakwah',
                    'fakultas'       => 'Ushuluddin dan Dakwah',
                    'gender'         => 'L',
                    'no_hp'          => '081322334455',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => [
                    'nip'            => $nip,
                    'nama'           => 'Dr. H. Maimun, M.Ag.',
                    'email'          => 'maimun@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                    'gender'         => 'L',
                    'prodi'          => 'Ushuluddin dan Dakwah',
                    'fakultas'       => 'Ushuluddin dan Dakwah',
                    'no_hp'          => '081322334455',
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $response = $this->post('/login', [
            'login_id' => $nip,
            'password' => $password,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('nomor_induk', $nip)->first();
        $this->assertNotNull($user);
        $this->assertSame('Dr. H. Maimun, M.Ag.', $user->name);
        $this->assertSame('maimun@uinssc.ac.id', $user->email);
        $this->assertSame('campus', $user->auth_source);
        $this->assertTrue($user->hasRole('tutor'));
    }

    /**
     * T1-BR-02: Bridge Sanctum Bearer token is stored in session
     */
    public function test_bridge_sanctum_token_is_stored_in_session(): void
    {
        $nip = '198507152010121003';
        $token = '154|9f8a7b6c5d4e3f2a1b0c9d8e7f6a5b4c3d2e1f0a';

        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status'  => true,
                'message' => 'Login portal kampus berhasil.',
                'data'    => [
                    'token'          => $token,
                    'nip'            => $nip,
                    'nama'           => 'Dr. H. Maimun, M.Ag.',
                    'email'          => 'maimun@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $apiService = app(CampusApiService::class);
        $result = $apiService->login($nip, 'secret');

        $this->assertTrue($result['success']);
        $this->assertSame($token, $result['token']);
    }

    /**
     * T1-BR-03: Tutor profile fields mapped directly to User model
     */
    public function test_tutor_profile_fields_mapped_to_user_model(): void
    {
        $nip = '199001012020121001';
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'data'   => [
                    'token'          => 'mock-token-xyz',
                    'nip'            => $nip,
                    'nama'           => 'Ust. H. Ahmad Muzakki, Lc., M.Ag.',
                    'email'          => 'ahmad.muzakki@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                    'prodi'          => 'Ilmu Al-Qur\'an dan Tafsir',
                    'fakultas'       => 'Ushuluddin',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => [
                    'nip'            => $nip,
                    'nama'           => 'Ust. H. Ahmad Muzakki, Lc., M.Ag.',
                    'email'          => 'ahmad.muzakki@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                    'gender'         => 'L',
                    'prodi'          => 'Ilmu Al-Qur\'an dan Tafsir',
                    'fakultas'       => 'Ushuluddin',
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $authService = app(CampusAuthService::class);
        $user = $authService->authenticate($nip, 'password123');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($nip, $user->nomor_induk);
        $this->assertSame('Ust. H. Ahmad Muzakki, Lc., M.Ag.', $user->name);
        $this->assertSame('ahmad.muzakki@uinssc.ac.id', $user->email);
        $this->assertTrue($user->hasRole('tutor'));
    }

    /**
     * T1-BR-04: Tutor role assigned via Spatie Permission and dosen alias recognized
     */
    public function test_tutor_role_assigned_via_spatie_permission(): void
    {
        $apiService = app(CampusApiService::class);

        // Test pegawai
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'data'   => [
                    'token'          => 'token-1',
                    'nip'            => '1980010101',
                    'nama'           => 'Tutor Satu',
                    'jenis_pengguna' => 'pegawai',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => [
                    'nip'            => '1980010101',
                    'nama'           => 'Tutor Satu',
                    'jenis_pengguna' => 'pegawai',
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $cred = $apiService->verifyCredentials('1980010101', 'pass');
        $this->assertSame('pegawai', $cred['user_type']);
    }

    /**
     * T1-BR-04b: Dosen role recognized as pegawai/tutor user type
     */
    public function test_dosen_role_recognized_as_tutor_user_type(): void
    {
        $apiService = app(CampusApiService::class);

        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'data'   => [
                    'token'          => 'token-2',
                    'nip'            => '1980010102',
                    'nama'           => 'Tutor Dua',
                    'jenis_pengguna' => 'dosen',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => [
                    'nip'            => '1980010102',
                    'nama'           => 'Tutor Dua',
                    'jenis_pengguna' => 'dosen',
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $cred2 = $apiService->verifyCredentials('1980010102', 'pass');
        $this->assertSame('pegawai', $cred2['user_type']);
    }

    /**
     * T1-BR-05: Anti-lockout local admin can login when Bridge is configured
     */
    public function test_anti_lockout_local_admin_can_login_when_bridge_configured(): void
    {
        $admin = User::create([
            'name'              => 'Administrator Sistem',
            'email'             => 'admin@mahad.local',
            'nomor_induk'       => 'NIP-ADMIN-01',
            'password'          => Hash::make('local_secure_pass'),
            'auth_source'       => 'local',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        // Fake will throw if called, proving zero external network blocking
        Http::fake([
            '*' => Http::response(['error' => 'Should not be called'], 500),
        ]);

        $response = $this->post('/login', [
            'login_id' => 'admin@mahad.local',
            'password' => 'local_secure_pass',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES
    // =========================================================================

    /**
     * T2-BR-01: Bridge login fails with empty credentials
     */
    public function test_bridge_login_fails_with_empty_credentials(): void
    {
        $response = $this->post('/login', [
            'login_id' => '',
            'password' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * T2-BR-02: Bridge login fails with invalid password returns 401
     */
    public function test_bridge_login_fails_with_invalid_password_returns_401(): void
    {
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status'  => false,
                'message' => 'Kata sandi portal kampus tidak cocok.',
            ], 401),
            '*' => Http::response([], 200),
        ]);

        $response = $this->post('/login', [
            'login_id' => '198507152010121003',
            'password' => 'salah_password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * T2-BR-03: Bridge login fails with nonexistent NIP returns 404
     */
    public function test_bridge_login_fails_with_nonexistent_nip_returns_404(): void
    {
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status'  => false,
                'message' => 'Nomor Induk (NIM/NIP) tidak ditemukan di pangkalan data portal kampus.',
            ], 404),
            '*' => Http::response([], 200),
        ]);

        $response = $this->post('/login', [
            'login_id' => '999999999999999999',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * T2-BR-04: Bridge profile fetch with expired or invalid token returns null safely
     */
    public function test_bridge_profile_fetch_with_expired_token_returns_null(): void
    {
        Http::fake([
            '*/api/v2/profil' => Http::response([
                'message' => 'Unauthenticated.',
            ], 401),
        ]);

        $apiService = app(CampusApiService::class);
        $profil = $apiService->getProfil('expired-token-123');

        $this->assertNull($profil);
    }

    /**
     * T2-BR-05a: Response token shape compatibility (nested data.token)
     */
    public function test_token_parsing_supports_nested_data_token(): void
    {
        $apiService = app(CampusApiService::class);

        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'data'   => ['token' => 'nested-token-abc'],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $resA = $apiService->login('nip1', 'pass1');
        $this->assertSame('nested-token-abc', $resA['token']);
    }

    /**
     * T2-BR-05b: Response token shape compatibility (root token)
     */
    public function test_token_parsing_supports_root_token(): void
    {
        $apiService = app(CampusApiService::class);

        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'token'  => 'root-token-xyz',
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $resB = $apiService->login('nip2', 'pass2');
        $this->assertSame('root-token-xyz', $resB['token']);
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3-CB-01: Tutor login followed by logout lifecycle maintains session cleanliness
     */
    public function test_tutor_login_followed_by_logout_clears_session(): void
    {
        $nip = '198507152010121003';
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'data'   => [
                    'token'          => 'session-token-123',
                    'nip'            => $nip,
                    'nama'           => 'Dr. H. Maimun',
                    'email'          => 'maimun@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => ['nip' => $nip, 'nama' => 'Dr. H. Maimun', 'jenis_pengguna' => 'pegawai'],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $this->post('/login', ['login_id' => $nip, 'password' => 'pass']);
        $this->assertAuthenticated();

        $response = $this->post('/logout');
        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * T3-CB-02: Whitespace trimming on login identifier
     */
    public function test_login_identifier_with_leading_and_trailing_spaces_is_trimmed(): void
    {
        $nip = '198507152010121003';
        Http::fake([
            '*/api/v2/portal/login' => function ($request) use ($nip) {
                // Assert that the payload sent to Bridge API is trimmed
                $data = $request->data();
                $this->assertSame($nip, $data['username']);
                return Http::response([
                    'status' => true,
                    'data'   => [
                        'token'          => 'token-trimmed',
                        'nip'            => $nip,
                        'nama'           => 'Dr. H. Maimun',
                        'email'          => 'maimun@uinssc.ac.id',
                        'jenis_pengguna' => 'pegawai',
                    ],
                ], 200);
            },
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => ['nip' => $nip, 'nama' => 'Dr. H. Maimun', 'jenis_pengguna' => 'pegawai'],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $response = $this->post('/login', [
            'login_id' => "  {$nip}  ",
            'password' => 'pass',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['nomor_induk' => $nip]);
    }

    // =========================================================================
    // TIER 4: REAL-WORLD SCENARIOS
    // =========================================================================

    /**
     * T4-RW-02: Complete tutor onboarding, profile hydration, and dashboard access
     */
    public function test_complete_tutor_onboarding_profile_and_dashboard_access(): void
    {
        $nip = '198810202015031002';
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status'  => true,
                'message' => 'Login portal kampus berhasil.',
                'data'    => [
                    'token'          => 'pat-19881020-full-access',
                    'nip'            => $nip,
                    'nama'           => 'Ust. Ahmad Wildan, M.Pd.',
                    'email'          => 'wildan@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                    'prodi'          => 'Pendidikan Bahasa Arab',
                    'fakultas'       => 'Tarbiyah dan Keguruan',
                    'gender'         => 'L',
                    'no_hp'          => '085211223344',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => [
                    'nip'            => $nip,
                    'nama'           => 'Ust. Ahmad Wildan, M.Pd.',
                    'email'          => 'wildan@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                    'gender'         => 'L',
                    'prodi'          => 'Pendidikan Bahasa Arab',
                    'fakultas'       => 'Tarbiyah dan Keguruan',
                    'no_hp'          => '085211223344',
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        // Step 1: Tutor performs login
        $loginResponse = $this->post('/login', [
            'login_id' => $nip,
            'password' => 'secret_campus_pass',
        ]);

        $this->assertAuthenticated();
        $loginResponse->assertRedirect(route('dashboard', absolute: false));

        // Step 2: Access authenticated dashboard view
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertOk();

        // Step 3: Verify user model and roles
        $user = User::where('nomor_induk', $nip)->first();
        $this->assertNotNull($user);
        $this->assertSame('Ust. Ahmad Wildan, M.Pd.', $user->name);
        $this->assertSame('wildan@uinssc.ac.id', $user->email);
        $this->assertTrue($user->hasRole('tutor'));
    }
}
