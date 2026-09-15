<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportCluster;
use App\Models\StatusLog;

class HomeController extends Controller
{
    public function index()
    {
        $totalReports = Report::count();
        $totalClusters = ReportCluster::count();
        $resolvedClusters = ReportCluster::where('status', 'resolved')->count();

        $completionRate = $totalClusters > 0
            ? round(($resolvedClusters / $totalClusters) * 100)
            : 0;

        $recentlyResolved = StatusLog::where('new_status', 'resolved')
            ->whereNotNull('photo_path')
            ->with(['cluster.reports' => fn ($q) => $q->oldest(), 'user'])
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact(
            'totalReports',
            'resolvedClusters',
            'completionRate',
            'recentlyResolved'
        ));
    }
}