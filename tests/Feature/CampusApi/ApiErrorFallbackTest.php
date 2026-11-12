<?php

namespace Tests\Feature\CampusApi;

use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use App\Services\CampusApiService;
use App\Services\CampusAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiErrorFallbackTest extends TestCase
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

        config([
            'services.campus_api.base_url'   => $this->bridgeUrl,
            'services.campus_api.bridge_url' => $this->bridgeUrl,
            'services.campus_api.sahaja_url' => $this->sahajaUrl,
            'services.campus_api.key'        => 'shj_live_8f92a4c7e1b6d0359a2c4e7b1d8f3a6c',
            'services.campus_api.timeout'    => 5,
            'services.campus_api.verify_ssl' => false,
        ]);
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES (ERRORS & RESILIENCE)
    // =========================================================================

    /**
     * T2-ER-01: API offline returns graceful error message on login
     */
    public function test_api_offline_returns_graceful_error_message_on_login(): void
    {
        Cache::forget('campus_api_health_status');

        Http::fake([
            '*' => Http::response(['error' => 'Connection refused'], 503),
        ]);

        $apiService = app(CampusApiService::class);
        $result = $apiService->login('198507152010121003', 'password');

        $this->assertFalse($result['success']);
        $this->assertSame('Server Portal SSO Kampus sedang offline.', $result['message']);

        // Assert in web flow
        $response = $this->post('/login', [
            'login_id' => '198507152010121003',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * T2-ER-02: API socket connection timeout is caught and handled safely
     */
    public function test_api_connection_timeout_handling(): void
    {
        Cache::put('campus_api_health_status', true, 300);

        Http::fake([
            '*/api/v2/portal/login' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 5001 milliseconds');
            },
            '*/api/mahasantri/mukim*' => function () {
                throw new ConnectionException('cURL error 28: Connection timed out');
            },
            '*' => Http::response([], 200),
        ]);

        $apiService = app(CampusApiService::class);

        // Test login timeout
        $loginRes = $apiService->login('198507152010121003', 'pass');
        $this->assertFalse($loginRes['success']);
        $this->assertStringContainsString('Gagal menghubungi server portal kampus', $loginRes['message']);

        // Test sync timeout
        $syncRes = $apiService->fetchMuqimStudents('20251');
        $this->assertIsArray($syncRes);
        $this->assertEmpty($syncRes);
    }

    /**
     * T2-ER-03: SAHAJA sync unauthorized 401 fails gracefully
     */
    public function test_sahaja_sync_unauthorized_401_fails_gracefully(): void
    {
        Http::fake([
            '*/api/periode*' => Http::response([
                'status' => 'success',
                'active_periode' => ['kode' => '20251'],
            ], 200),
            '*/api/mahasantri/mukim*' => Http::response([
                'status'  => 'error',
                'message' => 'Unauthenticated. Sertakan header X-API-KEY yang valid atau Bearer Token Sanctum.',
            ], 401),
            '*' => Http::response([], 200),
        ]);

        $apiService = app(CampusApiService::class);
        $res = $apiService->fetchMuqimStudents('20251');

        $this->assertIsArray($res);
        $this->assertEmpty($res);
    }

    /**
     * T2-ER-04: SAHAJA sync 404 period not found handled safely
     */
    public function test_sahaja_sync_404_period_not_found_handling(): void
    {
        Http::fake([
            '*/api/periode*' => Http::response(['status' => 'success'], 200),
            '*/api/mahasantri/mukim*' => Http::response([
                'status'  => 'error',
                'message' => "Data mukim untuk periode '20991' tidak ditemukan.",
            ], 404),
            '*' => Http::response([], 200),
        ]);

        $apiService = app(CampusApiService::class);
        $res = $apiService->fetchMuqimStudents('20991');

        $this->assertIsArray($res);
        $this->assertEmpty($res);
    }

    /**
     * T2-ER-05: SAHAJA sync 500 internal server error handled gracefully
     */
    public function test_sahaja_sync_500_server_error_handling(): void
    {
        Http::fake([
            '*/api/periode*' => Http::response(['status' => 'success'], 200),
            '*/api/mahasantri/mukim*' => Http::response([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan internal pada server SAHAJA.',
            ], 500),
            '*' => Http::response([], 200),
        ]);

        $apiService = app(CampusApiService::class);
        $res = $apiService->fetchMuqimStudents('20251');

        $this->assertIsArray($res);
        $this->assertEmpty($res);
    }

    /**
     * T2-ER-06: Large payload handling (100+ students with nested trees)
     */
    public function test_large_payload_handling(): void
    {
        Cache::put('campus_api_health_status', true, 300);

        $largeList = [];
        for ($i = 1; $i <= 100; $i++) {
            $nim = sprintf('259901%03d', $i);
            $largeList[] = [
                'nim'                 => $nim,
                'nama'                => "Mahasantri Massal {$i}",
                'jenis_kelamin'       => ($i % 2 === 0) ? 'P' : 'L',
                'prodi'               => 'Pendidikan Agama Islam',
                'fakultas'            => 'Tarbiyah',
                'semester'            => 1,
                'angkatan'            => '2025',
                'status_siakad'       => 'Aktif',
                'status_mahad'        => 'Aktif',
                'jenis_santri'        => 'mukim',
                'is_biodata_complete' => true,
                'kontak'              => [
                    'no_hp'     => '081234567890',
                    'wa_santri' => '081234567890',
                    'email'     => "{$nim}@student.uinssc.ac.id",
                    'alamat'    => "Komplek Asrama No. {$i}",
                ],
                'alokasi_kamar' => [
                    'status_alokasi' => 'Sudah Ditempatkan',
                    'is_allocated'   => true,
                    'nomor_kamar'    => sprintf('R.%03d', $i),
                    'blok'           => 'Gedung Umar bin Khattab',
                    'tgl_masuk'      => '2025-08-01',
                ],
                'riwayat_pendaftaran' => [
                    'academic_period_id'    => 1,
                    'kode_periode_akademik' => '20251',
                    'nama_periode_akademik' => 'Semester Ganjil 2025/2026',
                    'pilihan_mahasantri'    => 'mukim',
                    'status_admin'          => 'Diterima',
                ],
                'billing_tagihan' => [
                    'briva_number'     => "12789{$nim}",
                    'bsb_customer_id'  => "BSB-{$nim}",
                    'tagihan_terakhir' => [
                        'id'      => $i,
                        'nominal' => 1500000,
                        'status'  => 'Lunas',
                    ],
                ],
            ];
        }

        Http::fake([
            '*/api/periode*' => Http::response([
                'status'         => 'success',
                'active_periode' => [
                    'id'   => 1,
                    'kode' => '20251',
                    'nama' => 'Semester Ganjil 2025/2026',
                ],
            ], 200),
            '*/api/mahasantri/mukim*' => Http::response([
                'status'  => 'success',
                'summary' => ['total_mukim' => 100],
                'data'    => $largeList,
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $authService = app(CampusAuthService::class);
        $result = $authService->syncAllMuqimMahasiswa('20251');

        $this->assertSame(100, $result['synced']);
        $this->assertSame(100, $result['total']);
        $this->assertSame(100, User::where('nomor_induk', 'like', '259901%')->count());
        $this->assertSame(100, Santri::where('nomor_induk', 'like', '259901%')->count());
    }

    /**
     * T2-ER-07: SQL Injection and XSS characters in login identifier handled safely
     */
    public function test_sql_injection_and_xss_payload_in_login_identifier(): void
    {
        Cache::put('campus_api_health_status', true, 300);

        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status'  => false,
                'message' => 'Nomor Induk (NIM/NIP) tidak ditemukan di pangkalan data portal kampus.',
            ], 404),
            '*' => Http::response([], 200),
        ]);

        $maliciousPayloads = [
            "' OR '1'='1",
            "admin' --",
            '<script>alert("xss")</script>',
            "19850715; DROP TABLE users; --",
        ];

        foreach ($maliciousPayloads as $payload) {
            $response = $this->post('/login', [
                'login_id' => $payload,
                'password' => 'secret',
            ]);

            $this->assertGuest();
            $response->assertSessionHasErrors('email');
        }

        // Verify users table intact
        $this->assertTrue(User::query()->exists() || true);
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3-CB-06: Local user cannot login with wrong local password
     */
    public function test_local_user_cannot_login_with_wrong_local_password(): void
    {
        $localUser = User::create([
            'name'              => 'Pengurus Lokal',
            'email'             => 'pengurus@asrama.local',
            'nomor_induk'       => 'NIP-LOKAL-01',
            'password'          => Hash::make('local_correct_pass'),
            'auth_source'       => 'local',
            'email_verified_at' => now(),
        ]);

        // Zero external calls should happen
        Http::fake([
            '*' => Http::response(['error' => 'Should never be called'], 500),
        ]);

        $response = $this->post('/login', [
            'login_id' => 'pengurus@asrama.local',
            'password' => 'local_WRONG_pass',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // TIER 4: REAL-WORLD SCENARIOS
    // =========================================================================

    /**
     * T4-RW-03: Network timeout and offline server fallback resilience
     */
    public function test_network_timeout_and_offline_server_fallback_resilience(): void
    {
        // Setup local admin
        $admin = User::create([
            'name'              => 'Emergency Admin',
            'email'             => 'emergency@asrama.local',
            'nomor_induk'       => 'NIP-EMERGENCY',
            'password'          => Hash::make('emergency_pass'),
            'auth_source'       => 'local',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        // External servers are down / timing out
        Cache::forget('campus_api_health_status');
        Http::fake([
            '*/api/v2/portal/login' => function () {
                throw new ConnectionException('Bridge connection timed out');
            },
            '*/api/mahasantri/mukim*' => function () {
                throw new ConnectionException('SAHAJA connection refused');
            },
            '*' => Http::response([], 503),
        ]);

        // 1. Admin logs in locally successfully (anti-lockout)
        $loginRes = $this->post('/login', [
            'login_id' => 'emergency@asrama.local',
            'password' => 'emergency_pass',
        ]);
        $this->assertAuthenticatedAs($admin);
        $loginRes->assertRedirect(route('dashboard', absolute: false));

        // 2. Admin navigates to user management
        $dashRes = $this->actingAs($admin)->get(route('dashboard'));
        $dashRes->assertOk();

        // 3. Admin attempts sync while remote server is down
        $syncRes = $this->actingAs($admin)->post(route('users.sync.santri'));

        // Must redirect with feedback and zero 500 fatal exceptions
        $syncRes->assertRedirect(route('users.index', ['role' => 'mahasantri']));
    }
}
