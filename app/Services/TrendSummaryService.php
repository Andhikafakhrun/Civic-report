<?php

namespace App\Services;

use App\Models\ReportCluster;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrendSummaryService
{
    private string $model = 'gemini-3.6-flash';

    public function generate(): ?string
    {
        $clusters = ReportCluster::where('status', '!=', 'resolved')->get();

        if ($clusters->isEmpty()) {
            return 'Belum ada data laporan aktif untuk dianalisis.';
        }

        $summaryData = $this->prepareSummaryData($clusters);

        try {
            $apiKey = config('services.gemini.api_key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$apiKey}";

            $response = Http::timeout(30)->retry(2, 2000)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $this->buildPrompt($summaryData)],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Trend summary failed', ['response' => $response->body()]);
                return null;
            }

            return $response->json('candidates.0.content.parts.0.text');

        } catch (\Exception $e) {
            Log::error('Trend summary error: ' . $e->getMessage());
            return null;
        }
    }

    private function prepareSummaryData($clusters): array
    {
        $oneWeekAgo = Carbon::now()->subDays(7);

        return $clusters->map(function ($cluster) use ($oneWeekAgo) {
            $recentCount = $cluster->reports()
                ->where('created_at', '>=', $oneWeekAgo)
                ->count();

            return [
                'category' => $cluster->category,
                'report_count' => $cluster->report_count,
                'recent_reports_7d' => $recentCount,
                'priority_score' => $cluster->priority_score,
                'status' => $cluster->status,
                'location' => round($cluster->center_latitude, 4) . ', ' . round($cluster->center_longitude, 4),
            ];
        })->toArray();
    }

    private function buildPrompt(array $summaryData): string
    {
        $dataJson = json_encode($summaryData, JSON_PRETTY_PRINT);

        return <<<PROMPT
        Kamu adalah analis data untuk sistem pelaporan masalah infrastruktur kota (jalan berlubang dan sampah menumpuk).

        Berikut data cluster laporan yang sedang aktif (belum selesai ditangani):
        {$dataJson}

        Tulis ringkasan singkat (maksimal 4-5 kalimat) dalam Bahasa Indonesia untuk staff dinas terkait, yang mencakup:
        1. Total cluster aktif dan kategori mana yang paling dominan
        2. Cluster mana yang paling mendesak (priority_score tertinggi) dan kenapa
        3. Apakah ada tren peningkatan laporan yang mencolok dalam 7 hari terakhir

        Tulis dengan gaya lugas dan actionable, seperti laporan singkat ke atasan. Jangan gunakan format markdown atau bullet point, tulis dalam bentuk paragraf naratif.
        PROMPT;
    }
}