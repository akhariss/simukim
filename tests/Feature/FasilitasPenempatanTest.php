<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\KamarStatus;
use App\Enums\PenempatanStatus;
use App\Enums\SantriStatus;
use App\Enums\TipeRuang;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FasilitasPenempatanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Periode $periode;
    protected Gedung $gedungIkhwan;
    protected Gedung $gedungAkhwat;
    protected Lantai $lantaiIkhwan;
    protected Lantai $lantaiAkhwat;
    protected Kamar $kamarIkhwan;
    protected Kamar $kamarAkhwat;
    protected Santri $santriIkhwan;
    protected Santri $santriAkhwat;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'mudabbir']);
        Role::firstOrCreate(['name' => 'tutor']);
        Role::firstOrCreate(['name' => 'mahasantri']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->periode = Periode::create([
            'kode' => '20261',
            'nama' => '2026 Ganjil',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->gedungIkhwan = Gedung::create([
            'kode' => 'G-IKH',
            'nama' => 'Gedung Ali bin Abi Thalib',
            'gender_khusus' => 'L',
            'deskripsi' => 'Gedung Khusus Ikhwan',
            'is_active' => true,
        ]);

        $this->gedungAkhwat = Gedung::create([
            'kode' => 'G-AKH',
            'nama' => 'Gedung Khadijah',
            'gender_khusus' => 'P',
            'deskripsi' => 'Gedung Khusus Akhwat',
            'is_active' => true,
        ]);

        $this->lantaiIkhwan = Lantai::create([
            'gedung_id' => $this->gedungIkhwan->id,
            'nomor_lantai' => 1,
            'kapasitas_kamar_standar' => 4,
        ]);

        $this->lantaiAkhwat = Lantai::create([
            'gedung_id' => $this->gedungAkhwat->id,
            'nomor_lantai' => 1,
            'kapasitas_kamar_standar' => 4,
        ]);

        $this->kamarIkhwan = Kamar::create([
            'lantai_id' => $this->lantaiIkhwan->id,
            'nomor_kamar' => 'IKH-101',
            'tipe_ruang' => TipeRuang::Kamar,
            'gender' => Gender::L,
            'kapasitas_maks' => 2,
            'status' => KamarStatus::Tersedia,
        ]);

        $this->kamarAkhwat = Kamar::create([
            'lantai_id' => $this->lantaiAkhwat->id,
            'nomor_kamar' => 'AKH-101',
            'tipe_ruang' => TipeRuang::Kamar,
            'gender' => Gender::P,
            'kapasitas_maks' => 2,
            'status' => KamarStatus::Tersedia,
        ]);

        $userIkhwan = User::factory()->create(['nomor_induk' => '2388010001']);
        $userIkhwan->assignRole('mahasantri');
        $this->santriIkhwan = Santri::create([
            'user_id' => $userIkhwan->id,
            'periode_id' => $this->periode->id,
            'nomor_induk' => '2388010001',
            'gender' => Gender::L,
            'status' => SantriStatus::Aktif,
            'prodi' => 'Informatika',
            'semester' => 1,
            'angkatan' => 2026,
            'status_pembayaran' => 'lunas',
        ]);

        $userAkhwat = User::factory()->create(['nomor_induk' => '2388010002']);
        $userAkhwat->assignRole('mahasantri');
        $this->santriAkhwat = Santri::create([
            'user_id' => $userAkhwat->id,
            'periode_id' => $this->periode->id,
            'nomor_induk' => '2388010002',
            'gender' => Gender::P,
            'status' => SantriStatus::Aktif,
            'prodi' => 'Pendidikan Agama Islam',
            'semester' => 1,
            'angkatan' => 2026,
            'status_pembayaran' => 'lunas',
        ]);
    }

    /**
     * Uji Kasus Berhasil: Santri Ikhwan ditempatkan di Kamar Ikhwan yang tersedia
     */
    public function test_admin_can_assign_santri_to_available_room_with_matching_gender(): void
    {
        $response = $this->actingAs($this->admin)->post(route('fasilitas.penempatan.assign'), [
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $this->kamarIkhwan->id,
            'tanggal_masuk' => '2026-08-15',
            'catatan' => 'Penempatan santri baru',
        ]);

        $response->assertRedirect(route('fasilitas.penempatan.index', ['tab' => 'aktif']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mahad_penempatan_kamar', [
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $this->kamarIkhwan->id,
            'periode_id' => $this->periode->id,
            'status' => PenempatanStatus::Aktif->value,
        ]);
    }

    /**
     * Uji Kasus 1: Penempatan Gagal saat Kamar Penuh
     */
    public function test_assign_fails_when_room_is_full(): void
    {
        // 1. Buat kamar dengan kapasitas maks = 1
        $kamarKecil = Kamar::create([
            'lantai_id' => $this->lantaiIkhwan->id,
            'nomor_kamar' => 'IKH-VIP1',
            'tipe_ruang' => TipeRuang::Kamar,
            'gender' => Gender::L,
            'kapasitas_maks' => 1,
            'status' => KamarStatus::Tersedia,
        ]);

        // 2. Isi kamar dengan santri pertama sampai penuh
        PenempatanKamar::create([
            'periode_id' => $this->periode->id,
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $kamarKecil->id,
            'tanggal_masuk' => '2026-08-15',
            'status' => PenempatanStatus::Aktif->value,
            'assigned_by' => $this->admin->id,
        ]);
        $kamarKecil->update(['status' => KamarStatus::Penuh]);

        // 3. Buat santri ikhwan kedua
        $userIkhwan2 = User::factory()->create(['nomor_induk' => '2388010003']);
        $santri2 = Santri::create([
            'user_id' => $userIkhwan2->id,
            'periode_id' => $this->periode->id,
            'nomor_induk' => '2388010003',
            'gender' => Gender::L,
            'status' => SantriStatus::Aktif,
            'prodi' => 'Informatika',
            'semester' => 1,
            'angkatan' => 2026,
            'status_pembayaran' => 'lunas',
        ]);

        // 4. Coba tempatkan santri kedua ke kamar VIP1 yang sudah penuh
        $response = $this->actingAs($this->admin)->post(route('fasilitas.penempatan.assign'), [
            'santri_id' => $santri2->id,
            'kamar_id' => $kamarKecil->id,
            'tanggal_masuk' => '2026-08-15',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah penuh', session('error'));

        // Pastikan tidak ada penempatan kedua di kamar tersebut
        $this->assertDatabaseMissing('mahad_penempatan_kamar', [
            'santri_id' => $santri2->id,
            'kamar_id' => $kamarKecil->id,
        ]);
    }

    /**
     * Uji Kasus 2: Penempatan Gagal saat Gender Tidak Cocok (Akhwat ke Kamar Ikhwan)
     */
    public function test_assign_fails_when_gender_mismatches(): void
    {
        // Coba tempatkan santri Akhwat ke Kamar Ikhwan
        $response = $this->actingAs($this->admin)->post(route('fasilitas.penempatan.assign'), [
            'santri_id' => $this->santriAkhwat->id,
            'kamar_id' => $this->kamarIkhwan->id,
            'tanggal_masuk' => '2026-08-15',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Ketidakcocokan Gender', session('error'));

        // Pastikan santri Akhwat tidak tersimpan di kamar Ikhwan
        $this->assertDatabaseMissing('mahad_penempatan_kamar', [
            'santri_id' => $this->santriAkhwat->id,
            'kamar_id' => $this->kamarIkhwan->id,
        ]);
    }

    /**
     * Uji Kasus 3: Penempatan Gagal saat Santri Sudah Memiliki Kamar Aktif
     */
    public function test_assign_fails_when_santri_already_has_active_room(): void
    {
        // 1. Santri sudah ditempatkan di kamar Ikhwan 101
        PenempatanKamar::create([
            'periode_id' => $this->periode->id,
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $this->kamarIkhwan->id,
            'tanggal_masuk' => '2026-08-15',
            'status' => PenempatanStatus::Aktif->value,
            'assigned_by' => $this->admin->id,
        ]);

        // 2. Buat kamar Ikhwan kedua
        $kamarIkhwan2 = Kamar::create([
            'lantai_id' => $this->lantaiIkhwan->id,
            'nomor_kamar' => 'IKH-102',
            'tipe_ruang' => TipeRuang::Kamar,
            'gender' => Gender::L,
            'kapasitas_maks' => 2,
            'status' => KamarStatus::Tersedia,
        ]);

        // 3. Coba tempatkan santri yang sama ke kamar 102
        $response = $this->actingAs($this->admin)->post(route('fasilitas.penempatan.assign'), [
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $kamarIkhwan2->id,
            'tanggal_masuk' => '2026-08-16',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah memiliki kamar aktif', session('error'));

        // Pastikan penempatan ganda tidak terbentuk
        $this->assertDatabaseMissing('mahad_penempatan_kamar', [
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $kamarIkhwan2->id,
        ]);
    }

    /**
     * Uji Kasus Pindah Kamar (Mutasi)
     */
    public function test_admin_can_transfer_santri_to_another_room(): void
    {
        $penempatan = PenempatanKamar::create([
            'periode_id' => $this->periode->id,
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $this->kamarIkhwan->id,
            'tanggal_masuk' => '2026-08-15',
            'status' => PenempatanStatus::Aktif->value,
            'assigned_by' => $this->admin->id,
        ]);

        $kamarTujuan = Kamar::create([
            'lantai_id' => $this->lantaiIkhwan->id,
            'nomor_kamar' => 'IKH-103',
            'tipe_ruang' => TipeRuang::Kamar,
            'gender' => Gender::L,
            'kapasitas_maks' => 2,
            'status' => KamarStatus::Tersedia,
        ]);

        $response = $this->actingAs($this->admin)->post(route('fasilitas.penempatan.pindah', $penempatan), [
            'kamar_tujuan_id' => $kamarTujuan->id,
            'tanggal_pindah' => '2026-09-01',
            'alasan_pindah' => 'Penyesuaian kenyamanan belajar',
        ]);

        $response->assertSessionHas('success');

        // Penempatan lama berstatus pindah
        $this->assertDatabaseHas('mahad_penempatan_kamar', [
            'id' => $penempatan->id,
            'status' => PenempatanStatus::Pindah->value,
        ]);

        // Penempatan baru berstatus aktif di kamar tujuan
        $this->assertDatabaseHas('mahad_penempatan_kamar', [
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $kamarTujuan->id,
            'status' => PenempatanStatus::Aktif->value,
        ]);
    }

    /**
     * Uji Kasus Santri Keluar / Checkout dari Kamar
     */
    public function test_admin_can_checkout_santri_from_room(): void
    {
        $penempatan = PenempatanKamar::create([
            'periode_id' => $this->periode->id,
            'santri_id' => $this->santriIkhwan->id,
            'kamar_id' => $this->kamarIkhwan->id,
            'tanggal_masuk' => '2026-08-15',
            'status' => PenempatanStatus::Aktif->value,
            'assigned_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('fasilitas.penempatan.keluar', $penempatan), [
            'tanggal_keluar' => '2026-10-01',
            'alasan_keluar' => 'Lulus mukim semester',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mahad_penempatan_kamar', [
            'id' => $penempatan->id,
            'status' => PenempatanStatus::Keluar->value,
        ]);
    }
}
