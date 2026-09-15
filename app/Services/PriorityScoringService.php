<?php

namespace App\Services;

use App\Models\ReportCluster;
use Carbon\Carbon;

class PriorityScoringService
{
    private const SEVERITY_WEIGHT = 0.4;
    private const VOLUME_WEIGHT = 0.3;
    private const VELOCITY_WEIGHT = 0.3;

    private const VOLUME_CAP = 10;
    private const VELOCITY_CAP_PER_WEEK = 14;

    private const SEVERITY_VALUES = [
        'low' => 33,
        'medium' => 66,
        'high' => 100,
    ];

    private const DECAY_GRACE_DAYS = 7;
    private const DECAY_RATE_PER_DAY = 0.03;
    private const DECAY_FLOOR = 0.4;

    public function calculate(ReportCluster $cluster): int
    {
        $severityScore = $this->severityScore($cluster);
        $volumeScore = $this->volumeScore($cluster);
        $velocityScore = $this->velocityScore($cluster);

        $finalScore = ($severityScore * self::SEVERITY_WEIGHT)
            + ($volumeScore * self::VOLUME_WEIGHT)
            + ($velocityScore * self::VELOCITY_WEIGHT);

        $finalScore = $this->applyDecay($finalScore, $cluster);

        return (int) round($finalScore);
    }

    private function applyDecay(float $score, ReportCluster $cluster): float
    {
        if (!$cluster->last_reported_at) {
            return $score;
        }

        $daysSinceLastReport = $cluster->last_reported_at->diffInDays(now());

        if ($daysSinceLastReport <= self::DECAY_GRACE_DAYS) {
            return $score;
        }

        $overdueDays = $daysSinceLastReport - self::DECAY_GRACE_DAYS;
        $decayMultiplier = max(self::DECAY_FLOOR, 1 - ($overdueDays * self::DECAY_RATE_PER_DAY));

        return $score * $decayMultiplier;
    }

    public function updateClusterScore(ReportCluster $cluster): void
    {
        $score = $this->calculate($cluster);
        $cluster->update(['priority_score' => $score]);
    }

    private function severityScore(ReportCluster $cluster): float
    {
        $severities = $cluster->reports()
            ->whereNotNull('severity')
            ->pluck('severity');

        if ($severities->isEmpty()) {
            return 0;
        }

        $total = $severities->sum(fn ($s) => self::SEVERITY_VALUES[$s] ?? 0);

        return $total / $severities->count();
    }

    private function volumeScore(ReportCluster $cluster): float
    {
        $count = min($cluster->report_count, self::VOLUME_CAP);

        return ($count / self::VOLUME_CAP) * 100;
    }

    private function velocityScore(ReportCluster $cluster): float
    {
        $recentCount = $cluster->reports()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $capped = min($recentCount, self::VELOCITY_CAP_PER_WEEK);

        return ($capped / self::VELOCITY_CAP_PER_WEEK) * 100;
    }
}