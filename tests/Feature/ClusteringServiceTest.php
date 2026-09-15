<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Services\ClusteringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClusteringServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClusteringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClusteringService();
    }

    private function makeReport(float $lat, float $lng, string $category = 'pothole'): Report
    {
        return Report::create([
            'category' => $category,
            'photo_path' => 'reports/dummy.jpg',
            'description' => 'Test laporan',
            'latitude' => $lat,
            'longitude' => $lng,
            'status' => 'reported',
        ]);
    }

    public function test_nearby_reports_join_the_same_cluster(): void
    {
        $report1 = $this->makeReport(-6.9147, 107.6098);
        $cluster1 = $this->service->assignToCluster($report1);

        $report2 = $this->makeReport(-6.9148, 107.6099);
        $cluster2 = $this->service->assignToCluster($report2);

        $this->assertEquals($cluster1->id, $cluster2->id);
        $this->assertEquals(2, $cluster2->fresh()->report_count);
    }

    public function test_distant_reports_create_separate_clusters(): void
    {
        $report1 = $this->makeReport(-6.9147, 107.6098);
        $cluster1 = $this->service->assignToCluster($report1);

        $report2 = $this->makeReport(-6.9700, 107.6600);
        $cluster2 = $this->service->assignToCluster($report2);

        $this->assertNotEquals($cluster1->id, $cluster2->id);
    }

    public function test_different_categories_never_share_a_cluster(): void
    {
        $report1 = $this->makeReport(-6.9147, 107.6098, 'pothole');
        $cluster1 = $this->service->assignToCluster($report1);

        $report2 = $this->makeReport(-6.9147, 107.6098, 'trash');
        $cluster2 = $this->service->assignToCluster($report2);

        $this->assertNotEquals($cluster1->id, $cluster2->id);
    }
}