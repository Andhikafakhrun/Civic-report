<?php

namespace Tests\Unit;

use App\Models\ReportCluster;
use App\Models\Report;
use App\Services\PriorityScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private PriorityScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PriorityScoringService();
    }

    public function test_high_severity_and_volume_produces_high_score(): void
    {
        $cluster = ReportCluster::create([
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
            Report::create([
                'category' => 'pothole',
                'photo_path' => 'reports/dummy.jpg',
                'description' => 'Test laporan',
                'latitude' => -6.9,
                'longitude' => 107.6,
                'severity' => 'high',
                'status' => 'reported',
                'cluster_id' => $cluster->id,
            ]);
        }

        $score = $this->service->calculate($cluster->fresh());

        $this->assertGreaterThanOrEqual(70, $score);
    }

    public function test_single_low_severity_report_produces_low_score(): void
    {
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

        Report::create([
            'category' => 'trash',
            'photo_path' => 'reports/dummy.jpg',
            'description' => 'Test laporan',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'severity' => 'low',
            'status' => 'reported',
            'cluster_id' => $cluster->id,
        ]);

        $score = $this->service->calculate($cluster->fresh());

        $this->assertLessThan(40, $score);
    }

    public function test_old_cluster_score_decays_over_time(): void
    {
        $cluster = ReportCluster::create([
            'category' => 'pothole',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 3,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now()->subDays(30),
            'last_reported_at' => now(),
        ]);

        for ($i = 0; $i < 3; $i++) {
            Report::create([
                'category' => 'pothole',
                'photo_path' => 'reports/dummy.jpg',
                'description' => 'Test laporan',
                'latitude' => -6.9,
                'longitude' => 107.6,
                'severity' => 'high',
                'status' => 'reported',
                'cluster_id' => $cluster->id,
            ]);
        }

        $freshScore = $this->service->calculate($cluster->fresh());

        $cluster->update(['last_reported_at' => now()->subDays(25)]);
        $decayedScore = $this->service->calculate($cluster->fresh());

        $this->assertLessThan($freshScore, $decayedScore);
    }
}