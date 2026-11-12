<?php

namespace Tests\Feature\CampusApi;

use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use App\Services\CampusApiService;
use App\Services\CampusAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SahajaMukimSyncIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $sahajaUrl = 'https://sahaja.uinssc.ac.id';
    protected string $apiKey = 'shj_live_8f92a4c7e1b6d0359a2c4e7b1d8f3a6c';

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
            'services.campus_api.base_url'   => $this->sahajaUrl,
            'services.campus_api.bridge_url' => $this->sahajaUrl,
            'services.campus_api.sahaja_url' => $this->sahajaUrl,
            'services.campus_api.key'        => $this->apiKey,
            'services.campus_api.timeout'    => 5,
            'services.campus_api.verify_ssl' => false,
        ]);

        Cache::put('campus_api_health_status', true, 300);
    }

    /**
     * Helper to generate standard mock student data structure matching SAHAJA specification
     */
    protected function generateMockStudent(string $nim, array $overrides = []): array
    {
        $gender = $overrides['jenis_kelamin'] ?? 'L';
        $base = [
            'nim'                 => $nim,
            'nama'                => $overrides['nama'] ?? "Mahasantri {$nim}",
            'jenis_kelamin'       => $gender,
            'prodi'               => $overrides['prodi'] ?? "Ilmu Al-Qur'an dan Tafsir",
            'fakultas'            => $overrides['fakultas'] ?? "Ushuluddin dan Dakwah",
            'semester'            => $overrides['semester'] ?? 1,
            'angkatan'            => $overrides['angkatan'] ?? "2025",
            'status_siakad'       => 'Aktif',
            'status_mahad'        => 'Aktif',
            'jenis_santri'        => 'mukim',
            'is_biodata_complete' => true,
            'kontak'              => [
                'no_hp'               => $overrides['no_hp'] ?? '081234567890',
                'wa_santri'           => $overrides['wa_santri'] ?? '081234567890',
                'email'               => $overrides['email'] ?? "{$nim}@student.uinssc.ac.id",
                'alamat'              => $overrides['alamat'] ?? 'Jl. Perjuangan No. 1, Cirebon',
                'kontak_darurat_ortu' => [
                    'hp_ayah'    => $overrides['hp_ayah'] ?? '081398765432',
                    'email_ayah' => $overrides['email_ayah'] ?? "ayah.{$nim}@gmail.com",
                    'hp_ibu'     => $overrides['hp_ibu'] ?? '081398765433',
                    'email_ibu'  => $overrides['email_ibu'] ?? "ibu.{$nim}@gmail.com",
                ],
            ],
            'alokasi_kamar' => [
                'status_alokasi' => $overrides['status_alokasi'] ?? 'Sudah Ditempatkan',
                'is_allocated'   => $overrides['is_allocated'] ?? true,
                'hunian_id'      => 101,
                'kamar_id'       => 201,
                'nomor_kamar'    => $overrides['nomor_kamar'] ?? 'A.101',
                'blok'           => $overrides['blok'] ?? ($gender === 'P' ? 'Gedung Khadijah' : 'Gedung Ali bin Abi Thalib'),
                'kapasitas'      => 4,
                'tgl_masuk'      => $overrides['tgl_masuk'] ?? '2025-08-15',
                'tgl_keluar'     => null,
            ],
            'riwayat_pendaftaran' => [
                'academic_period_id'    => 1,
                'kode_periode_akademik' => '20251',
                'nama_periode_akademik' => 'Semester Ganjil 2025/2026',
                'pilihan_mahasantri'    => 'mukim',
                'status_admin'          => 'Diterima',
            ],
            'billing_tagihan' => [
                'briva_number'     => $overrides['briva_number'] ?? "12789{$nim}",
                'bsb_customer_id'  => $overrides['bsb_customer_id'] ?? "BSB-{$nim}",
                'tagihan_terakhir' => [
                    'id'      => 1001,
                    'nominal' => $overrides['nominal'] ?? 1500000,
                    'status'  => $overrides['status_pembayaran'] ?? 'Lunas',
                ],
            ],
        ];

        return array_replace_recursive($base, $overrides);
    }

    protected function fakeSahajaResponse(array $students, ?array $summary = null): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());

        $defaultSummary = [
            'total_mukim'       => count($students),
            'total_laki_laki'   => count(array_filter($students, fn($s) => ($s['jenis_kelamin'] ?? 'L') === 'L')),
            'total_perempuan'   => count(array_filter($students, fn($s) => ($s['jenis_kelamin'] ?? '') === 'P')),
            'sudah_dapat_kamar' => count($students),
            'belum_dapat_kamar' => 0,
            'periode_filter'    => 'Semester Ganjil 2025/2026 (20251)',
        ];

        Http::fake([
            '*/api/periode*' => Http::response([
                'status'         => 'success',
                'data'           => [
                    [
                        'id'                 => 1,
                        'academic_period_id' => 1,
                        'kode'               => '20251',
                        'nama'               => 'Semester Ganjil 2025/2026',
                        'tanggal_mulai'      => '2025-08-01',
                        'tanggal_selesai'    => '2026-01-31',
                        'is_active'          => true,
                    ],
                ],
                'active_periode' => [
                    'id'                 => 1,
                    'academic_period_id' => 1,
                    'kode'               => '20251',
                    'nama'               => 'Semester Ganjil 2025/2026',
                    'tanggal_mulai'      => '2025-08-01',
                    'tanggal_selesai'    => '2026-01-31',
                    'is_active'          => true,
                ],
            ], 200),
            '*/api/mahasantri/mukim*' => Http::response([
                'status'     => 'success',
                'message'    => 'Data mahasantri MUKIM berhasil dimuat.',
                'summary'    => $summary ?? $defaultSummary,
                'data'       => $students,
                'pagination' => [
                    'current_page'   => 1,
                    'per_page'       => count($students),
                    'total'          => count($students),
                    'last_page'      => 1,
                    'has_more_pages' => false,
                ],
            ], 200),
            '*/api/v1/mahasantri/mukim*' => Http::response([
                'status'     => 'success',
                'message'    => 'Data mahasantri MUKIM berhasil dimuat.',
                'summary'    => $summary ?? $defaultSummary,
                'data'       => $students,
                'pagination' => [
                    'current_page'   => 1,
                    'per_page'       => count($students),
                    'total'          => count($students),
                    'last_page'      => 1,
                    'has_more_pages' => false,
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);
    }

    // =========================================================================
    // TIER 1: FEATURE COVERAGE
    // =========================================================================

    /**
     * T1-SH-01: SAHAJA Mukim sync happy path creates User and Santri models
     */
    public function test_sahaja_mukim_sync_happy_path_creates_santri_and_user(): void
    {
        $nim = '240101001';
        $student = $this->generateMockStudent($nim, [
            'nama'     => 'Muhammad Rizqi Pratama',
            'prodi'    => "Ilmu Al-Qur'an dan Tafsir",
            'fakultas' => 'Ushuluddin dan Dakwah',
        ]);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $result = $authService->syncAllMuqimMahasiswa('20251');

        $this->assertSame(1, $result['synced']);
        $this->assertSame(1, $result['total']);

        // Assert User
        $user = User::where('nomor_induk', $nim)->first();
        $this->assertNotNull($user);
        $this->assertSame('Muhammad Rizqi Pratama', $user->name);
        $this->assertSame("{$nim}@student.uinssc.ac.id", $user->email);
        $this->assertSame('campus', $user->auth_source);
        $this->assertTrue($user->hasRole('mahasantri'));

        // Assert Santri
        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertNotNull($santri);
        $this->assertSame($user->id, $santri->user_id);
        $this->assertSame("Ilmu Al-Qur'an dan Tafsir", $santri->prodi);
        $this->assertSame('Ushuluddin dan Dakwah', $santri->fakultas);
    }

    /**
     * T1-SH-02: SAHAJA sync sends X-API-KEY or Bearer token header
     */
    public function test_sahaja_sync_sends_x_api_key_or_bearer_header(): void
    {
        $student = $this->generateMockStudent('240101002');
        $this->fakeSahajaResponse([$student]);

        $apiService = app(CampusApiService::class);
        $apiService->fetchMuqimStudents('20251');

        Http::assertSent(function ($request) {
            $hasMukimUrl = str_contains($request->url(), '/api/mahasantri/mukim') || str_contains($request->url(), '/api/v1/mahasantri/mukim');
            if (!$hasMukimUrl) {
                return true;
            }

            // Must transmit authentication credential (X-API-KEY or Authorization header)
            $hasApiKey = $request->hasHeader('X-API-KEY') || $request->hasHeader('X-Api-Key');
            $hasBearer = $request->hasHeader('Authorization');

            return $hasApiKey || $hasBearer;
        });
    }

    /**
     * T1-SH-03: SAHAJA room allocation creates Gedung, Lantai, Kamar, and PenempatanKamar
     */
    public function test_sahaja_room_allocation_creates_gedung_lantai_kamar_and_placement(): void
    {
        $nim = '240101003';
        $student = $this->generateMockStudent($nim, [
            'jenis_kelamin' => 'L',
            'alokasi_kamar' => [
                'is_allocated'   => true,
                'status_alokasi' => 'Sudah Ditempatkan',
                'nomor_kamar'    => 'A.203',
                'blok'           => 'Gedung Ali bin Abi Thalib',
                'tgl_masuk'      => '2025-08-15',
            ],
        ]);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertNotNull($santri);

        $placement = PenempatanKamar::where('santri_id', $santri->id)->first();
        $this->assertNotNull($placement);
        $this->assertSame('2025-08-15', $placement->tanggal_masuk->toDateString());

        $kamar = Kamar::find($placement->kamar_id);
        $this->assertNotNull($kamar);
        $this->assertSame('A.203', $kamar->nomor_kamar);

        $gedung = Gedung::where('nama', 'Gedung Ali bin Abi Thalib')->first();
        $this->assertNotNull($gedung);
        $this->assertSame($gedung->id, $kamar->lantai->gedung_id);
    }

    /**
     * T1-SH-04: SAHAJA billing attributes mapped accurately (briva, bsb, status, nominal)
     */
    public function test_sahaja_billing_attributes_mapped_accurately(): void
    {
        $nim = '240101004';
        $student = $this->generateMockStudent($nim, [
            'billing_tagihan' => [
                'briva_number'     => '12789240101004',
                'bsb_customer_id'  => 'BSB-240101004',
                'tagihan_terakhir' => [
                    'nominal' => 1500000,
                    'status'  => 'Lunas',
                ],
            ],
        ]);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertSame('12789240101004', $santri->briva_number);
        $this->assertSame('BSB-240101004', $santri->bsb_customer_id);
        $this->assertSame('Lunas', $santri->status_pembayaran);
        $this->assertSame(1500000, $santri->nominal_tagihan);
    }

    /**
     * T1-SH-05: Manual sync button endpoint triggers sync with admin auth
     */
    public function test_manual_sync_button_endpoint_triggers_sync_with_admin_auth(): void
    {
        $admin = User::create([
            'name'              => 'Admin Sync',
            'email'             => 'admin_sync@uinssc.ac.id',
            'nomor_induk'       => 'NIP-ADM-SYNC',
            'password'          => bcrypt('password'),
            'auth_source'       => 'local',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        $student = $this->generateMockStudent('240101005');
        $this->fakeSahajaResponse([$student]);

        $response = $this->actingAs($admin)->post(route('users.sync.santri'));

        $response->assertRedirect(route('users.index', ['role' => 'mahasantri']));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['nomor_induk' => '240101005']);
    }

    /**
     * T1-SH-06: SAHAJA sync preserves elevated roles (mudabbir, admin)
     */
    public function test_sahaja_sync_preserves_elevated_roles(): void
    {
        $nim = '240101006';
        $mudabbirUser = User::create([
            'name'              => 'Santri Senior Mudabbir',
            'email'             => "{$nim}@student.uinssc.ac.id",
            'nomor_induk'       => $nim,
            'password'          => bcrypt('password'),
            'auth_source'       => 'campus',
            'email_verified_at' => now(),
        ]);
        $mudabbirUser->syncRoles(['mudabbir']);

        $student = $this->generateMockStudent($nim, ['nama' => 'Santri Senior Mudabbir']);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $refreshed = User::where('nomor_induk', $nim)->first();
        $this->assertTrue($refreshed->hasRole('mudabbir'), 'Mudabbir role must NOT be downgraded to mahasantri.');
        $this->assertFalse($refreshed->hasRole('mahasantri'));
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES
    // =========================================================================

    /**
     * T2-SH-01: SAHAJA sync handles empty student list gracefully
     */
    public function test_sahaja_sync_handles_empty_student_list_gracefully(): void
    {
        $this->fakeSahajaResponse([]);

        $authService = app(CampusAuthService::class);
        $result = $authService->syncAllMuqimMahasiswa('20251');

        $this->assertSame(0, $result['synced']);
        $this->assertSame(0, $result['total']);
    }

    /**
     * T2-SH-02: SAHAJA sync handles missing optional fields and null contacts
     */
    public function test_sahaja_sync_handles_missing_optional_fields_and_null_contacts(): void
    {
        $nim = '240101007';
        $sparseStudent = [
            'nim'                 => $nim,
            'nama'                => 'Santri Minim Data',
            'jenis_kelamin'       => 'L',
            'prodi'               => 'Hukum Keluarga',
            'fakultas'            => 'Syariah',
            'semester'            => 1,
            'angkatan'            => '2025',
            'kontak'              => null,
            'alokasi_kamar'       => null,
            'riwayat_pendaftaran' => null,
            'billing_tagihan'     => null,
        ];
        $this->fakeSahajaResponse([$sparseStudent]);

        $authService = app(CampusAuthService::class);
        $result = $authService->syncAllMuqimMahasiswa('20251');

        $this->assertSame(1, $result['synced']);
        $user = User::where('nomor_induk', $nim)->first();
        $this->assertNotNull($user);
        $this->assertSame("{$nim}@student.uinssc.ac.id", $user->email);

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertNotNull($santri);
        $this->assertSame('Belum Bayar', $santri->status_pembayaran);
        $this->assertSame(1500000, $santri->nominal_tagihan);
    }

    /**
     * T2-SH-03: SAHAJA sync handles unallocated room status
     */
    public function test_sahaja_sync_handles_unallocated_room_status(): void
    {
        $nim = '240101008';
        $student = $this->generateMockStudent($nim, [
            'alokasi_kamar' => [
                'is_allocated'   => false,
                'status_alokasi' => 'Belum Ditempatkan',
                'nomor_kamar'    => null,
                'blok'           => null,
            ],
        ]);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertNotNull($santri);

        $placement = PenempatanKamar::where('santri_id', $santri->id)->first();
        $this->assertNull($placement, 'Unallocated student must not have a room placement.');
    }

    /**
     * T2-SH-04: SAHAJA sync handles special characters in names and addresses
     */
    public function test_sahaja_sync_handles_special_characters_in_names_and_addresses(): void
    {
        $nim = '240101009';
        $specialName = "M. Dhiya'ul Haq Ma'ruf";
        $specialAddress = "Jl. Syekh Nurjati No. 99, RT 02/RW 04, Gg. Da'wah (Asrama O'Connor)";

        $student = $this->generateMockStudent($nim, [
            'nama'   => $specialName,
            'alamat' => $specialAddress,
        ]);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $user = User::where('nomor_induk', $nim)->first();
        $this->assertSame($specialName, $user->name);

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertSame($specialAddress, $santri->alamat);
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3-CB-01: Tutor login followed by mahasantri sync maintains separate identities
     */
    public function test_tutor_login_followed_by_mahasantri_sync_maintains_separate_identities(): void
    {
        $nip = '198507152010121003';
        $nim = '240101010';

        // Fake Bridge Login
        Http::fake([
            '*/api/v2/portal/login' => Http::response([
                'status' => true,
                'data'   => [
                    'token'          => 'token-tutor-identity',
                    'nip'            => $nip,
                    'nama'           => 'Dr. H. Maimun, M.Ag.',
                    'email'          => 'maimun@uinssc.ac.id',
                    'jenis_pengguna' => 'pegawai',
                ],
            ], 200),
            '*/api/v2/profil' => Http::response([
                'status' => true,
                'data'   => ['nip' => $nip, 'nama' => 'Dr. H. Maimun, M.Ag.', 'jenis_pengguna' => 'pegawai'],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $this->post('/login', ['login_id' => $nip, 'password' => 'secret_pass']);
        $this->assertAuthenticated();

        // Now run SAHAJA Mukim Sync
        $student = $this->generateMockStudent($nim, ['nama' => 'Mahasantri Junior']);
        $this->fakeSahajaResponse([$student]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $tutor = User::where('nomor_induk', $nip)->first();
        $santriUser = User::where('nomor_induk', $nim)->first();

        $this->assertTrue($tutor->hasRole('tutor'));
        $this->assertFalse($tutor->hasRole('mahasantri'));

        $this->assertTrue($santriUser->hasRole('mahasantri'));
        $this->assertFalse($santriUser->hasRole('tutor'));
    }

    /**
     * T3-CB-02: Sync with existing duplicate NIM updates idempotently
     */
    public function test_sync_with_existing_duplicate_nim_updates_idempotently(): void
    {
        $nim = '240101011';

        // 1st Sync: Unpaid status
        $studentV1 = $this->generateMockStudent($nim, [
            'status_pembayaran' => 'Belum Bayar',
            'no_hp'             => '0811111111',
        ]);
        $this->fakeSahajaResponse([$studentV1]);
        $authService = app(CampusAuthService::class);
        $result1 = $authService->syncAllMuqimMahasiswa('20251');
        $this->assertSame(1, $result1['new']);
        $this->assertSame(0, $result1['updated']);

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertSame('Belum Bayar', $santri->status_pembayaran);
        $this->assertSame('0811111111', $santri->no_hp);

        // 2nd Sync: Updated to Lunas with new phone
        $studentV2 = $this->generateMockStudent($nim, [
            'status_pembayaran' => 'Lunas',
            'no_hp'             => '0822222222',
        ]);
        $this->fakeSahajaResponse([$studentV2]);
        $result2 = $authService->syncAllMuqimMahasiswa('20251');
        $this->assertSame(0, $result2['new']);
        $this->assertSame(1, $result2['updated']);

        // Assert record count remains 1 and updated attributes reflect V2
        $this->assertSame(1, User::where('nomor_induk', $nim)->count());
        $this->assertSame(1, Santri::where('nomor_induk', $nim)->count());

        $refreshedSantri = Santri::where('nomor_induk', $nim)->first();
        $this->assertSame('Lunas', $refreshedSantri->status_pembayaran);
        $this->assertSame('0822222222', $refreshedSantri->no_hp);
    }

    /**
     * T3-CB-03: Sync resolves email collision with different NIM
     */
    public function test_sync_resolves_email_collision_with_different_nim(): void
    {
        $collidingEmail = 'conflict@uinssc.ac.id';

        // User A already registered with this email
        User::create([
            'name'              => 'Santri Existing',
            'email'             => $collidingEmail,
            'nomor_induk'       => '240101012',
            'password'          => bcrypt('password'),
            'auth_source'       => 'campus',
            'email_verified_at' => now(),
        ]);

        // Student B incoming from SAHAJA has same email but different NIM
        $nimB = '240101013';
        $studentB = $this->generateMockStudent($nimB, [
            'email' => $collidingEmail,
        ]);
        $this->fakeSahajaResponse([$studentB]);

        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $userB = User::where('nomor_induk', $nimB)->first();
        $this->assertNotNull($userB);

        // Deduplication rule generates deterministic {nim}@student.uinssc.ac.id
        $this->assertSame("{$nimB}@student.uinssc.ac.id", $userB->email);
        $this->assertNotSame($collidingEmail, $userB->email);
    }

    /**
     * T3-CB-04: Sync updates room allocation idempotently without duplicate placements
     */
    public function test_sync_updates_room_allocation_idempotently_without_duplicate_placements(): void
    {
        $nim = '240101014';

        // 1st sync: Room A.101
        $student1 = $this->generateMockStudent($nim, ['nomor_kamar' => 'A.101']);
        $this->fakeSahajaResponse([$student1]);
        $authService = app(CampusAuthService::class);
        $authService->syncAllMuqimMahasiswa('20251');

        $santri = Santri::where('nomor_induk', $nim)->first();
        $this->assertSame(1, PenempatanKamar::where('santri_id', $santri->id)->count());

        // 2nd sync: Room changed to A.102
        $student2 = $this->generateMockStudent($nim, ['nomor_kamar' => 'A.102']);
        $this->fakeSahajaResponse([$student2]);
        $authService->syncAllMuqimMahasiswa('20251');

        // Verify still only 1 active placement for this period
        $placements = PenempatanKamar::where('santri_id', $santri->id)->get();
        $this->assertCount(1, $placements);
        $this->assertSame('A.102', $placements->first()->kamar->nomor_kamar);
    }

    /**
     * T3-CB-05: Guest or non-admin cannot trigger manual sync button endpoint
     */
    public function test_guest_cannot_trigger_sync_button_endpoint(): void
    {
        $response = $this->post(route('users.sync.santri'));
        $response->assertRedirect('/login');

        // Regular student cannot trigger
        $studentUser = User::factory()->create();
        $studentUser->syncRoles(['mahasantri']);

        $responseStudent = $this->actingAs($studentUser)->post(route('users.sync.santri'));
        $responseStudent->assertForbidden();
    }

    // =========================================================================
    // TIER 4: REAL-WORLD SCENARIOS
    // =========================================================================

    /**
     * T4-RW-01: Full semester onboarding batch synchronization (25+ mukim students)
     */
    public function test_semester_onboarding_batch_sync_large_population(): void
    {
        $population = [];
        $totalStudents = 30;

        for ($i = 1; $i <= $totalStudents; $i++) {
            $nim = sprintf('250101%03d', $i);
            $gender = ($i % 2 === 0) ? 'P' : 'L';
            $roomNum = sprintf('%s.%d0%d', ($gender === 'P' ? 'KD' : 'JD'), ceil($i / 4), ($i % 4) + 1);
            $building = ($gender === 'P') ? 'Gedung Khadijah' : 'Gedung Ali bin Abi Thalib';

            $population[] = $this->generateMockStudent($nim, [
                'nama'          => "Mahasantri Onboarding {$i}",
                'jenis_kelamin' => $gender,
                'prodi'         => ($i % 3 === 0) ? 'Ilmu Hadis' : "Ilmu Al-Qur'an dan Tafsir",
                'nomor_kamar'   => $roomNum,
                'blok'          => $building,
                'nominal'       => 1500000,
                'status_pembayaran' => ($i % 5 === 0) ? 'Belum Bayar' : 'Lunas',
            ]);
        }

        $this->fakeSahajaResponse($population);

        $authService = app(CampusAuthService::class);
        $result = $authService->syncAllMuqimMahasiswa('20251');

        $this->assertSame($totalStudents, $result['synced']);
        $this->assertSame($totalStudents, $result['total']);
        $this->assertSame($totalStudents, $result['new']);

        // Assert database counts
        $this->assertSame($totalStudents, User::where('nomor_induk', 'like', '250101%')->count());
        $this->assertSame($totalStudents, Santri::where('nomor_induk', 'like', '250101%')->count());
        $this->assertSame($totalStudents, PenempatanKamar::count());

        // Assert Gender Distribution
        $maleCount = Santri::where('gender', 'L')->count();
        $femaleCount = Santri::where('gender', 'P')->count();
        $this->assertSame(15, $maleCount);
        $this->assertSame(15, $femaleCount);

        // Assert Buildings Created
        $this->assertTrue(Gedung::where('nama', 'Gedung Khadijah')->exists());
        $this->assertTrue(Gedung::where('nama', 'Gedung Ali bin Abi Thalib')->exists());
    }
}
