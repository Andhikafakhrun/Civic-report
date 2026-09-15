<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_reports_list(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_approved_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_pending_user_is_redirected_away_from_dashboard(): void
    {
        $user = User::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_can_still_submit_a_report_and_track_it(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/track')->assertStatus(200);
    }
    public function test_high_priority_kpi_excludes_resolved_clusters(): void
    {
        $user = \App\Models\User::factory()->create(['status' => 'approved']);

        $activeCluster = \App\Models\ReportCluster::create([
            'category' => 'pothole',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 10,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

        for ($i = 0; $i < 10; $i++) {
            \App\Models\Report::create([
                'category' => 'pothole',
                'photo_path' => 'reports/dummy.jpg',
                'description' => 'Test laporan',
                'latitude' => -6.9,
                'longitude' => 107.6,
                'severity' => 'high',
                'status' => 'reported',
                'cluster_id' => $activeCluster->id,
            ]);
        }

        \App\Models\ReportCluster::create([
            'category' => 'trash',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 5,
            'priority_score' => 85,
            'status' => 'resolved',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertViewHas('stats', function ($stats) {
            return $stats['high_priority'] === 1;
        });
    }
}