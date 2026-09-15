<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_form_has_all_required_fields(): void
    {
        $response = $this->get('/lapor');

        $response->assertOk();
        $response->assertSee('name="category"', false);
        $response->assertSee('name="photo"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="phone_number"', false);
        $response->assertSee('id="btn-get-location"', false);
    }

    public function test_dashboard_has_map_container(): void
    {
        $user = User::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('id="map"', false);
        $response->assertSee('id="trendChart"', false);
    }

    public function test_homepage_and_public_pages_load_successfully(): void
    {
        $this->get('/')->assertOk();
        $this->get('/lapor')->assertOk();
        $this->get('/track')->assertOk();
        $this->get('/transparansi')->assertOk();
        $this->get('/bukti-nyata')->assertOk();
        $this->get('/profil')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}