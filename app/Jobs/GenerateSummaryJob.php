<?php

namespace App\Jobs;

use App\Services\TrendSummaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateSummaryJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;

    public function handle(TrendSummaryService $trendService): void
    {
        Cache::put('dashboard_summary_status', 'processing', now()->addMinutes(2));

        $summary = $trendService->generate();

        if ($summary) {
            Cache::put('dashboard_trend_summary', [
                'text' => $summary,
                'generated_at' => now()->toIso8601String(),
            ], now()->addHours(12));
        }

        Cache::put('dashboard_summary_status', 'done', now()->addMinutes(2));
    }
}