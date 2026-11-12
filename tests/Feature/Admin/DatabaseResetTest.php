<?php

namespace Tests\Feature\Admin;

use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseResetTest extends TestCase
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

    public function test_guest_cannot_access_database_reset(): void
    {
        $response = $this->post(route('admin.cache.reset.database'), [
            'admin_password' => 'password',
            'confirmation' => 'RESET-DATABASE',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_database_reset(): void
    {
        $user = User::factory()->create();
        $user->assignRole('mahasantri');

        $response = $this->actingAs($user)->post(route('admin.cache.reset.database'), [
            'admin_password' => 'password',
            'confirmation' => 'RESET-DATABASE',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_reset_database_with_invalid_password(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('secret-password'),
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.cache.reset.database'), [
            'admin_password' => 'wrong-password',
            'confirmation' => 'RESET-DATABASE',
        ]);

        $response->assertSessionHasErrors(['admin_password']);
    }

    public function test_admin_cannot_reset_database_with_invalid_confirmation_text(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('secret-password'),
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.cache.reset.database'), [
            'admin_password' => 'secret-password',
            'confirmation' => 'wrong-confirmation',
        ]);

        $response->assertSessionHasErrors(['confirmation']);
    }

    public function test_admin_can_reset_database_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret-password'),
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.cache.reset.database'), [
            'admin_password' => 'secret-password',
            'confirmation' => 'RESET-DATABASE',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // Pastikan tabel bisnis kosong
        $this->assertEquals(0, DB::table('mahad_kamar')->count());
        $this->assertEquals(0, DB::table('mahad_kelas')->count());
        $this->assertEquals(0, DB::table('mahad_kegiatan')->count());
        $this->assertEquals(0, DB::table('mahad_pelanggaran')->count());
        $this->assertEquals(0, DB::table('mahad_perizinan')->count());

        // Pastikan 1 periode aktif dibuat
        $this->assertEquals(1, Periode::where('is_active', true)->count());

        // Pastikan akun starter ada
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'dosen.ahmad@uin.ac.id']);
        $this->assertDatabaseHas('users', ['email' => 'umar.faruq@uin.ac.id']);
        $this->assertDatabaseHas('users', ['email' => 'ahmad.fauzi@uin.ac.id']);
        $this->assertDatabaseHas('users', ['email' => 'fatimah.azzahra@uin.ac.id']);
    }
}
