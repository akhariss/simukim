<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_kegiatan_page_can_be_rendered_in_calendar_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/kegiatan');

        $response->assertStatus(200);
        $response->assertViewHas('dayList');
        $response->assertViewHas('scheduleByDay');
        $response->assertViewHas('khususList');
    }

    public function test_kegiatan_page_can_be_rendered_in_list_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/kegiatan?view=list');

        $response->assertStatus(200);
        $response->assertViewHas('dayList');
        $response->assertViewHas('scheduleByDay');
        $response->assertViewHas('khususList');
        $response->assertSee('Semua Hari');
    }
}
