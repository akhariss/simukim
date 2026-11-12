<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_navigation_returns_full_application_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('<aside', false);
        $response->assertSee('<main', false);
        $response->assertSee('Ahlan wa Sahlan');
    }

    public function test_spa_navigation_header_returns_only_partial_main_content(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'X-SPA-Navigation' => 'true',
        ])->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('<aside', false);
        $response->assertSee('<main', false);
        $response->assertSee('Ahlan wa Sahlan');
    }
}

