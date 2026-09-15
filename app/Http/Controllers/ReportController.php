<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Jobs\ClassifyReportJob;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::latest()->paginate(20);

        return view('reports.index', compact('reports'));
    }
    public function trackForm()
    {
        return view('reports.track');
    }

    public function trackResult(Request $request)
    {
        $validated = $request->validate([
            'tracking_code' => 'required|string',
        ]);

        $report = Report::where('tracking_code', strtoupper($validated['tracking_code']))
            ->with('cluster.statusLogs')
            ->first();

        return view('reports.track', ['report' => $report, 'searched' => true]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:pothole,trash,streetlight,drainage,fallen_tree',
            'photo' => 'required|image|max:5120',
            'description' => 'required|string|max:1000',
            'phone_number' => 'required|string|regex:/^08[0-9]{8,12}$/',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'phone_number.regex' => 'Format nomor tidak valid. Gunakan format 08xxxxxxxxxx.',
        ]);

        $photoPath = $request->file('photo')->store('reports', 'public');

        $report = Report::create([
            'category' => $validated['category'],
            'original_category' => $validated['category'],
            'photo_path' => $photoPath,
            'description' => $validated['description'],
            'phone_number' => $validated['phone_number'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => 'reported',
        ]);

        ClassifyReportJob::dispatch($report);

        return redirect('/lapor')->with('success', 'Laporan kamu berhasil dikirim. Terima kasih!')
            ->with('tracking_code', $report->tracking_code);
    }
}