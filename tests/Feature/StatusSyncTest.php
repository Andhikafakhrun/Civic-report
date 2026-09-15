<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportCluster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_cluster_status_cascades_to_all_reports_in_cluster(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);

        $cluster = ReportCluster::create([
            'category' => 'pothole',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 2,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

        $report1 = Report::create([
            'category' => 'pothole',
            'photo_path' => 'reports/dummy.jpg',
            'description' => 'Laporan pertama',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'status' => 'reported',
            'cluster_id' => $cluster->id,
        ]);

        $report2 = Report::create([
            'category' => 'pothole',
            'photo_path' => 'reports/dummy.jpg',
            'description' => 'Laporan kedua',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'status' => 'reported',
            'cluster_id' => $cluster->id,
        ]);

        $response = $this->actingAs($admin)
            ->patch("/clusters/{$cluster->id}", ['status' => 'in_progress']);

        $response->assertRedirect('/dashboard');

        $this->assertEquals('in_progress', $report1->fresh()->status);
        $this->assertEquals('in_progress', $report2->fresh()->status);
        $this->assertEquals('in_progress', $cluster->fresh()->status);
    }

    public function test_tracking_page_reflects_cluster_status_after_staff_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);

        $cluster = ReportCluster::create([
            'category' => 'trash',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 1,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

    $report = Report::create([
        'category' => 'trash',
        'photo_path' => 'reports/dummy.jpg',
        'description' => 'Sampah menumpuk',
        'latitude' => -6.9,
        'longitude' => 107.6,
        'status' => 'reported',
        'cluster_id' => $cluster->id,
    ]);

    $this->actingAs($admin)->patch("/clusters/{$cluster->id}", ['status' => 'in_progress']);

    $response = $this->post('/track', ['tracking_code' => $report->tracking_code]);

    $response->assertSee('Sedang Ditangani');
    $response->assertDontSee('bg-secondary">Dilaporkan', false);
    }
}