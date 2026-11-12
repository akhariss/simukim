<?php

namespace Tests\Feature;

use App\Enums\KelasType;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use App\Services\KelompokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KelompokAkademikTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $tutor1;
    private User $tutor2;
    private Periode $periode;
    private KelompokService $kelompokService;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan role ada
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tutor']);
        Role::firstOrCreate(['name' => 'mahasantri']);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@example.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password')]
        );
        $this->admin->syncRoles(['admin']);

        $this->tutor1 = User::firstOrCreate(
            ['email' => 'tutor1@example.com'],
            ['name' => 'Ustadz Ahmad', 'password' => bcrypt('password')]
        );
        $this->tutor1->syncRoles(['tutor']);

        $this->tutor2 = User::firstOrCreate(
            ['email' => 'tutor2@example.com'],
            ['name' => 'Ustadz Budi', 'password' => bcrypt('password')]
        );
        $this->tutor2->syncRoles(['tutor']);

        $this->periode = Periode::firstOrCreate(
            ['kode' => '2026-TEST'],
            [
                'nama' => '2026 Test',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-06-30',
                'is_active' => true,
            ]
        );

        $this->kelompokService = app(KelompokService::class);
    }

    public function test_admin_dapat_melihat_halaman_kelompok(): void
    {
        $response = $this->actingAs($this->admin)->get(route('kelompok.index'));
        $response->assertStatus(200);
        $response->assertSee('Kelompok Kajian');
    }

    public function test_admin_dapat_membuat_kelompok_baru(): void
    {
        $response = $this->actingAs($this->admin)->post(route('kelompok.store'), [
            'nama' => 'Kelompok Al-Kahfi',
            'kode' => 'KLP-KAHFI',
            'kapasitas' => 30,
            'keterangan' => 'Kelompok bimbingan tahfidz intensif',
        ]);

        $response->assertRedirect(route('kelompok.index'));
        $this->assertDatabaseHas('mahad_kelompok', [
            'nama' => 'Kelompok Al-Kahfi',
            'kode' => 'KLP-KAHFI',
            'periode_id' => $this->periode->id,
        ]);
    }

    public function test_santri_hanya_bisa_terdaftar_di_satu_kelompok_dalam_satu_periode(): void
    {
        $santriUser = User::factory()->create();
        $santri = Santri::firstOrCreate(
            ['user_id' => $santriUser->id],
            ['nomor_induk' => 'TEST9901', 'gender' => 'L', 'status' => 'aktif']
        );

        $kelompokA = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok Alfa',
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        $kelompokB = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok Beta',
            'kapasitas' => 20,
            'is_active' => true,
        ]);

        // Masukkan ke Kelompok A
        $this->kelompokService->addAnggota($kelompokA, $santri->id);
        $this->assertDatabaseHas('mahad_kelompok_anggota', [
            'kelompok_id' => $kelompokA->id,
            'santri_id' => $santri->id,
        ]);

        // Coba masukkan ke Kelompok B (harus throw exception)
        $this->expectException(\DomainException::class);
        $this->kelompokService->addAnggota($kelompokB, $santri->id);
    }

    public function test_aturan_tutor_quran_dan_kitab_hanya_1_kelompok(): void
    {
        $kelasQuran = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => "Kajian Al-Qur'an",
            'tipe' => KelasType::Quran,
            'is_active' => true,
        ]);

        $kelompok1 = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok 1',
            'kapasitas' => 25,
            'is_active' => true,
        ]);

        $kelompok2 = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok 2',
            'kapasitas' => 25,
            'is_active' => true,
        ]);

        // Tugaskan Tutor 1 ke Kelompok 1 untuk Quran -> Berhasil
        $this->kelompokService->assignTutor($kelompok1, $kelasQuran->id, $this->tutor1->id);
        $this->assertDatabaseHas('mahad_kelompok_pengampu', [
            'kelompok_id' => $kelompok1->id,
            'kelas_id' => $kelasQuran->id,
            'tutor_id' => $this->tutor1->id,
        ]);

        // Coba tugaskan Tutor 1 yang sama ke Kelompok 2 untuk Quran -> Harus gagal (DomainException)
        $this->expectException(\DomainException::class);
        $this->kelompokService->assignTutor($kelompok2, $kelasQuran->id, $this->tutor1->id);
    }

    public function test_aturan_tutor_masal_bisa_mengampu_banyak_kelompok(): void
    {
        $kelasMasal = Kelas::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kajian Masal Fiqih',
            'tipe' => KelasType::Masal,
            'is_active' => true,
        ]);

        $kelompok1 = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok Masal 1',
            'kapasitas' => 30,
            'is_active' => true,
        ]);

        $kelompok2 = Kelompok::create([
            'periode_id' => $this->periode->id,
            'nama' => 'Kelompok Masal 2',
            'kapasitas' => 30,
            'is_active' => true,
        ]);

        // Tutor 2 mengampu Kelompok 1
        $this->kelompokService->assignTutor($kelompok1, $kelasMasal->id, $this->tutor2->id);

        // Tutor 2 juga mengampu Kelompok 2 -> Harus BERHASIL tanpa exception
        $this->kelompokService->assignTutor($kelompok2, $kelasMasal->id, $this->tutor2->id);

        $this->assertDatabaseHas('mahad_kelompok_pengampu', [
            'kelompok_id' => $kelompok1->id,
            'kelas_id' => $kelasMasal->id,
            'tutor_id' => $this->tutor2->id,
        ]);
        $this->assertDatabaseHas('mahad_kelompok_pengampu', [
            'kelompok_id' => $kelompok2->id,
            'kelas_id' => $kelasMasal->id,
            'tutor_id' => $this->tutor2->id,
        ]);
    }

    public function test_master_kelas_dan_sesi_jadwal_crud(): void
    {
        // 1. Tambah Master Kelas via Controller
        $response = $this->actingAs($this->admin)->post(route('kelas.master.store'), [
            'nama' => 'Kajian Ushul Fiqih',
            'tipe' => 'kitab',
            'deskripsi' => 'Pengenalan kaidah ushul fiqih',
            'is_active' => 1,
        ]);
        $response->assertRedirect(route('kelas.master.index'));

        $kelas = Kelas::where('nama', 'Kajian Ushul Fiqih')->firstOrFail();

        // 2. Tambah Sesi Jadwal Pertemuan
        $responseJadwal = $this->actingAs($this->admin)->post(route('kelas.master.jadwal.store', $kelas), [
            'nama_sesi' => 'Ushul Fiqih Malam',
            'hari' => 'Rabu',
            'jam_mulai' => '19:30',
            'jam_selesai' => '20:45',
            'ruangan' => 'Aula Gedung 2',
        ]);
        $responseJadwal->assertRedirect(route('kelas.master.index'));

        $this->assertDatabaseHas('mahad_kelas_jadwal', [
            'kelas_id' => $kelas->id,
            'nama_sesi' => 'Ushul Fiqih Malam',
            'hari' => 'Rabu',
        ]);
    }
}
