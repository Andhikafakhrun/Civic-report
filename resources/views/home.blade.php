@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="hero">
    <span class="hero-label">Civic Report</span>
    <h1 class="hero-title">Laporkan. Lacak. Lihat Hasilnya.</h1>
    <p class="hero-subtitle">
        Jalan berlubang atau sampah menumpuk di sekitarmu? Laporkan di sini. Sistem kami mengelompokkan dan memprioritaskan laporan secara otomatis — dan kamu bisa lihat sendiri bagaimana laporanmu ditindaklanjuti.
    </p>
    <div class="d-flex gap-2 flex-wrap mt-4">
        <a href="/lapor" class="btn btn-cta">
            <i class="bi bi-camera"></i> Lapor Sekarang
        </a>
        <a href="/track" class="btn btn-cta-outline">
            <i class="bi bi-search"></i> Lacak Laporan Saya
        </a>
    </div>
</div>

<div class="row mb-5 animate-in">
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Laporan Masuk</p>
                <p class="stat-value mb-0">{{ $totalReports }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Kasus Selesai</p>
                <p class="stat-value mb-0" style="color: var(--bs-primary);">{{ $resolvedClusters }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Tingkat Penyelesaian</p>
                <p class="stat-value mb-0">{{ $completionRate }}%</p>
            </div>
        </div>
    </div>
</div>

<div class="mb-5 animate-in animate-in-delay-1">
    <h6 class="section-heading">Bagaimana Laporanmu Ditindaklanjuti</h6>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="step-number">1</div>
            <h6 class="step-title">Lapor</h6>
            <p class="text-muted small">Foto, deskripsi, dan lokasi otomatis lewat GPS. Dapat kode pelacakan tanpa perlu akun.</p>
        </div>
        <div class="col-md-4 mb-3">
            <div class="step-number">2</div>
            <h6 class="step-title">Diproses Sistem</h6>
            <p class="text-muted small">AI mengklasifikasi tingkat keparahan, mengelompokkan laporan serupa, dan menghitung prioritas penanganan.</p>
        </div>
        <div class="col-md-4 mb-3">
            <div class="step-number">3</div>
            <h6 class="step-title">Ditindaklanjuti</h6>
            <p class="text-muted small">Dinas terkait menangani sesuai prioritas. Kamu dapat notifikasi WhatsApp dan bukti foto saat selesai.</p>
        </div>
    </div>
</div>

@if ($recentlyResolved->isNotEmpty())
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 1px solid var(--bs-border-color);">
        <h5 class="mb-0">Baru Selesai Ditangani</h5>
        <a href="/bukti-nyata" class="small" style="color: var(--bs-primary);">Lihat semua &rarr;</a>
    </div>
    <div class="row">
        @foreach ($recentlyResolved as $log)
            <div class="col-md-4 mb-3">
                <div class="card h-100" role="button" data-bs-toggle="modal" data-bs-target="#case-detail-home-{{ $log->id }}">
                    <img src="{{ asset('storage/' . $log->photo_path) }}" alt="Bukti penanganan" style="height: 160px; object-fit: cover; border-bottom: 1px solid var(--bs-border-color);">
                    <div class="card-body">
                        @if ($log->cluster)
                            <span class="badge mb-2" style="background: transparent; color: {{ \App\Models\Report::categoryColor($log->cluster->category) }}; border: 1px solid {{ \App\Models\Report::categoryColor($log->cluster->category) }}; font-weight: 500;">
                                {{ \App\Models\Report::categoryLabel($log->cluster->category) }}
                            </span>
                        @endif
                        <p class="small text-muted mb-0" style="font-family: var(--font-mono); font-size: 0.75rem;">Selesai {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@foreach ($recentlyResolved as $log)
    @include('transparency._case-modal', ['log' => $log, 'idPrefix' => 'home'])
@endforeach
@endif

@endsection