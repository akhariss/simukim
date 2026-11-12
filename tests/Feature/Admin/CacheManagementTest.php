<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CacheManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'mahasantri']);
    }

    public function test_guest_cannot_access_cache_management(): void
    {
        $response = $this->get('/admin/cache');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_cache_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole('mahasantri');

        $response = $this->actingAs($user)->get('/admin/cache');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_cache_management_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/cache');
        $response->assertStatus(200);
        $response->assertSee('Kelola Cache & Optimasi Sistem');
        $response->assertSee('storage/framework/cache');
    }

    public function test_admin_can_clear_data_cache(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Cache::put('test_key', 'hello_world', 60);
        $this->assertTrue(Cache::has('test_key'));

        $response = $this->actingAs($admin)->post('/admin/cache/clear', [
            'type' => 'data',
        ]);

        $response->assertRedirect('/admin/cache');
        $response->assertSessionHas('success');
        $this->assertFalse(Cache::has('test_key'));
    }

    public function test_admin_can_invalidate_specific_cache_key(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Cache::put('active_periode', 'sample_data', 300);
        $this->assertTrue(Cache::has('active_periode'));

        $response = $this->actingAs($admin)->post('/admin/cache/clear', [
            'type' => 'key',
            'key' => 'active_periode',
        ]);

        $response->assertRedirect('/admin/cache');
        $response->assertSessionHas('success');
        $this->assertFalse(Cache::has('active_periode'));
    }
}
