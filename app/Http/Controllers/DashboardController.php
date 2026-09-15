<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateSummaryJob;
use App\Models\ReportCluster;
use Illuminate\Http\Request;
use App\Services\TrendSummaryService;
use App\Services\PriorityScoringService;
use App\Models\StatusLog;
use App\Models\Report;
use App\Jobs\NotifyStatusChangeJob;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request, PriorityScoringService $priorityService)
    {
        $activeClusters = ReportCluster::where('status', '!=', 'resolved')->get();

        foreach ($activeClusters as $cluster) {
            $priorityService->updateClusterScore($cluster);
        }

        $mapClusters = ReportCluster::select('id', 'category', 'center_latitude', 'center_longitude', 'priority_score', 'report_count', 'status')
            ->get();

        $query = ReportCluster::query()->with(['reports', 'statusLogs.user']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clusters = $query->orderByDesc('priority_score')->paginate(10)->withQueryString();

        $dailyTrend = $this->dailyReportTrend();

        $cachedSummary = Cache::get('dashboard_trend_summary');
        $stats = [
            'total_clusters' => ReportCluster::count(),
            'total_reports' => ReportCluster::sum('report_count'),
            'open_clusters' => ReportCluster::where('status', '!=', 'resolved')->count(),
            'high_priority' => ReportCluster::where('priority_score', '>=', 70)
                ->where('status', '!=', 'resolved')
                ->count(),
        ];

    return view('dashboard.index', compact('clusters', 'stats', 'dailyTrend', 'cachedSummary', 'mapClusters'));
    }
    private function dailyReportTrend(): array
    {
        $startDate = now()->subDays(29)->startOfDay();

        $reports = Report::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as report_date, category, COUNT(*) as total')
            ->groupBy('report_date', 'category')
            ->get();

        $labels = [];
        $potholeData = [];
        $trashData = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('d M');

            $potholeCount = $reports->first(fn ($r) => $r->report_date == $dateKey && $r->category == 'pothole');
            $trashCount = $reports->first(fn ($r) => $r->report_date == $dateKey && $r->category == 'trash');

            $potholeData[] = $potholeCount ? $potholeCount->total : 0;
            $trashData[] = $trashCount ? $trashCount->total : 0;
        }

        return [
            'labels' => $labels,
            'pothole' => $potholeData,
            'trash' => $trashData,
        ];
    }
    public function updateStatus(Request $request, ReportCluster $cluster)
    {
        $validated = $request->validate([
            'status' => 'required|in:reported,in_progress,resolved',
            'note' => 'nullable|string|max:500',
            'proof_photo' => $request->status === 'resolved'
                ? 'required|image|max:5120'
                : 'nullable|image|max:5120',
        ], [
            'proof_photo.required' => 'Foto bukti penanganan wajib diupload saat menandai status Resolved.',
        ]);

        $previousStatus = $cluster->status;

        $cluster->update(['status' => $validated['status']]);
        $cluster->reports()->update(['status' => $validated['status']]);

        $photoPath = null;
        if ($request->hasFile('proof_photo')) {
            $photoPath = $request->file('proof_photo')->store('proofs', 'public');
        }

        StatusLog::create([
            'cluster_id' => $cluster->id,
            'user_id' => auth()->id(),
            'previous_status' => $previousStatus,
            'new_status' => $validated['status'],
            'note' => $validated['note'] ?? null,
            'photo_path' => $photoPath,
        ]);

        if ($previousStatus !== $validated['status']) {
            NotifyStatusChangeJob::dispatch($cluster, $validated['status']);
        }

        return redirect('/dashboard')->with('success', 'Status cluster berhasil diperbarui.');
    }
    public function generateSummary()
    {
        GenerateSummaryJob::dispatch();

        return redirect('/dashboard')->with('info', 'Ringkasan sedang diproses, tunggu sebentar...');
    }

    public function summaryStatus()
    {
        $status = Cache::get('dashboard_summary_status', 'idle');

        return response()->json(['status' => $status]);
    }
}