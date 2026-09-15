@extends('layouts.app')

@section('title', 'Transparansi Publik')

@section('content')
<div class="hero">
    <span class="hero-label">Transparansi Publik</span>
    <h1 class="hero-title">Bagaimana Laporan Warga Ditindaklanjuti</h1>
    <p class="hero-subtitle">
        Setiap laporan yang masuk dilacak sampai selesai. Halaman ini terbuka untuk siapa saja, menunjukkan kinerja penanganan secara agregat dan bukti kasus yang sudah ditangani.
    </p>
</div>

<div class="row mb-4 animate-in">
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Total Laporan Masuk</p>
                <p class="stat-value mb-0">{{ $totalReports }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Tingkat Penyelesaian</p>
                <p class="stat-value mb-0" style="color: var(--bs-primary);">{{ $completionRate }}%</p>
                <p class="small text-muted mb-0">{{ $resolvedClusters }} dari {{ $totalClusters }} cluster</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Rata-rata Waktu Penanganan</p>
                <p class="stat-value mb-0">
                    @if ($avgResolutionDays !== null)
                        {{ $avgResolutionDays }}
                        <span class="fs-6 text-muted">hari</span>
                    @else
                        <span class="fs-6 text-muted">Belum ada data</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Kategori Laporan</p>
                <p class="mb-1 small">
                    <span class="signal-dot" style="background-color: var(--bs-warning);"></span>
                    Jalan berlubang: <strong style="font-family: var(--font-mono);">{{ $categoryBreakdown['pothole'] }}</strong>
                </p>
                <p class="mb-0 small">
                    <span class="signal-dot" style="background-color: var(--bs-secondary);"></span>
                    Sampah menumpuk: <strong style="font-family: var(--font-mono);">{{ $categoryBreakdown['trash'] }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 animate-in animate-in-delay-1">
    <div class="card-body">
        <h6 class="section-heading">Laporan Masuk vs Selesai Ditangani (6 Bulan Terakhir)</h6>
        <canvas id="monthlyChart" height="90"></canvas>
    </div>
</div>

<h6 class="section-heading animate-in animate-in-delay-2">Kasus Baru Selesai Ditangani</h6>

@if ($recentlyResolved->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">Belum ada kasus yang selesai dengan bukti foto.</p>
        </div>
    </div>
@else
    <div class="row">
        @foreach ($recentlyResolved as $log)
            <div class="col-md-4 mb-4">
                <div class="card h-100" role="button" data-bs-toggle="modal" data-bs-target="#case-detail-transparency-{{ $log->id }}">
                    <img src="/storage/{{ $log->photo_path }}" alt="Bukti penanganan" style="height: 180px; object-fit: cover; border-bottom: 1px solid var(--bs-border-color);">
                    <div class="card-body">
                        @if ($log->cluster)
                            <span class="badge mb-2" style="background: transparent; font-weight: 500; border: 1px solid {{ \App\Models\Report::categoryColor($log->cluster->category) }}; color: {{ \App\Models\Report::categoryColor($log->cluster->category) }};">
                                {{ \App\Models\Report::categoryLabel($log->cluster->category) }}
                            </span>
                        @endif
                        @if ($log->note)
                            <p class="small text-muted mb-2 fst-italic">"{{ $log->note }}"</p>
                        @endif
                        <p class="small mb-0" style="font-family: var(--font-mono); color: var(--bs-secondary); font-size: 0.75rem;">Selesai ditangani {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach ($recentlyResolved as $log)
        @include('transparency._case-modal', ['log' => $log, 'idPrefix' => 'transparency'])
    @endforeach
@endif
@endsection

@push('scripts')
<script>
    const monthlyLabels = @json($monthlyTrend['labels']);
    const monthlyReported = @json($monthlyTrend['reported']);
    const monthlyResolved = @json($monthlyTrend['resolved']);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    label: 'Laporan Masuk',
                    data: monthlyReported,
                    backgroundColor: '#18181B',
                },
                {
                    label: 'Selesai Ditangani',
                    data: monthlyResolved,
                    backgroundColor: '#2B4C7E',
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                },
            },
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });
</script>
@endpush