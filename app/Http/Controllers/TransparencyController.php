<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportCluster;
use App\Models\StatusLog;

class TransparencyController extends Controller
{
    public function index()
    {
        $totalReports = Report::count();
        $totalClusters = ReportCluster::count();
        $resolvedClusters = ReportCluster::where('status', 'resolved')->count();

        $completionRate = $totalClusters > 0
            ? round(($resolvedClusters / $totalClusters) * 100)
            : 0;

        $avgResolutionDays = $this->calculateAverageResolutionDays();

        $categoryBreakdown = [
            'pothole' => Report::where('category', 'pothole')->count(),
            'trash' => Report::where('category', 'trash')->count(),
        ];

        $recentlyResolved = StatusLog::where('new_status', 'resolved')
            ->whereNotNull('photo_path')
            ->whereHas('cluster', fn ($q) => $q->where('status', 'resolved'))
            ->with(['cluster.reports' => fn ($q) => $q->oldest(), 'user'])
            ->latest()
            ->take(6)
            ->get();

        $monthlyTrend = $this->monthlyTrend();

        return view('transparency.index', compact(
            'totalReports',
            'totalClusters',
            'resolvedClusters',
            'completionRate',
            'avgResolutionDays',
            'categoryBreakdown',
            'recentlyResolved',
            'monthlyTrend'
        ));
    }
    public function proof()
    {
        $resolvedCases = StatusLog::where('new_status', 'resolved')
            ->whereNotNull('photo_path')
            ->whereHas('cluster', fn ($q) => $q->where('status', 'resolved'))
            ->with(['cluster.reports' => function ($query) {
                $query->oldest();
            }, 'user'])
            ->latest()
            ->paginate(9);

        return view('transparency.proof', compact('resolvedCases'));
    }

    private function calculateAverageResolutionDays(): ?float
    {
        $resolvedClusters = ReportCluster::where('status', 'resolved')
            ->whereNotNull('first_reported_at')
            ->get();

        if ($resolvedClusters->isEmpty()) {
            return null;
        }

        $totalDays = 0;
        $countedClusters = 0;

        foreach ($resolvedClusters as $cluster) {
            $resolvedLog = StatusLog::where('cluster_id', $cluster->id)
                ->where('new_status', 'resolved')
                ->latest()
                ->first();

            if ($resolvedLog) {
                $totalDays += $cluster->first_reported_at->diffInDays($resolvedLog->created_at);
                $countedClusters++;
            }
        }

        return $countedClusters > 0 ? round($totalDays / $countedClusters, 1) : null;
    }
    private function monthlyTrend(): array
    {
        $startDate = now()->subMonths(5)->startOfMonth();

        $reportsRaw = Report::where('created_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $resolvedRaw = StatusLog::where('new_status', 'resolved')
            ->where('created_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $labels = [];
        $reportedData = [];
        $resolvedData = [];

        for ($i = 0; $i < 6; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $monthKey = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y');

            $reportedData[] = $reportsRaw->has($monthKey) ? $reportsRaw[$monthKey]->total : 0;
            $resolvedData[] = $resolvedRaw->has($monthKey) ? $resolvedRaw[$monthKey]->total : 0;
        }

        return [
            'labels' => $labels,
            'reported' => $reportedData,
            'resolved' => $resolvedData,
        ];
    }

}