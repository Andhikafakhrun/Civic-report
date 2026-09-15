<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportCluster;
use Illuminate\Support\Facades\DB;

class ClusteringService
{
    private const RADIUS_METERS = 100;

    public function assignToCluster(Report $report): ReportCluster
    {
        $nearbyCluster = $this->findNearbyCluster($report);

        if ($nearbyCluster) {
            $this->attachToCluster($report, $nearbyCluster);
            return $nearbyCluster;
        }

        return $this->createCluster($report);
    }

    private function findNearbyCluster(Report $report): ?ReportCluster
    {
        $result = DB::selectOne("
            SELECT id, ST_Distance_Sphere(
                POINT(center_longitude, center_latitude),
                POINT(?, ?)
            ) AS distance
            FROM report_clusters
            WHERE category = ?
              AND status != 'resolved'
            HAVING distance <= ?
            ORDER BY distance ASC
            LIMIT 1
        ", [$report->longitude, $report->latitude, $report->category, self::RADIUS_METERS]);

        return $result ? ReportCluster::find($result->id) : null;
    }

    private function attachToCluster(Report $report, ReportCluster $cluster): void
    {
        $report->update(['cluster_id' => $cluster->id]);

        $allReports = $cluster->reports()->get();

        $cluster->update([
            'report_count' => $allReports->count(),
            'center_latitude' => $allReports->avg('latitude'),
            'center_longitude' => $allReports->avg('longitude'),
            'last_reported_at' => now(),
        ]);
    }

    private function createCluster(Report $report): ReportCluster
    {
        $cluster = ReportCluster::create([
            'category' => $report->category,
            'center_latitude' => $report->latitude,
            'center_longitude' => $report->longitude,
            'report_count' => 1,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

        $report->update(['cluster_id' => $cluster->id]);

        return $cluster;
    }
}