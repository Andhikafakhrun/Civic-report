<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\AiClassificationService;
use App\Services\ClusteringService;
use App\Services\PriorityScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable as BusQueueable;

class ClassifyReportJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Report $report)
    {
    }

    public function handle(
        AiClassificationService $aiService,
        ClusteringService $clusteringService,
        PriorityScoringService $priorityService
    ): void {
        $aiResult = $aiService->classify(
            $this->report->photo_path,
            $this->report->description,
            $this->report->category
        );

    if ($aiResult) {
        $this->report->update([
            'category' => $aiResult['confirmed_category'] ?? $this->report->category,
            'severity' => $aiResult['severity'] ?? $this->report->severity,
        ]);
    }

        $cluster = $clusteringService->assignToCluster($this->report->fresh());

        $priorityService->updateClusterScore($cluster);
    }
}