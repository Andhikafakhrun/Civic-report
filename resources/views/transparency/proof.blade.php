@extends('layouts.app')

@section('title', 'Bukti Nyata Penanganan')

@section('content')
<div class="hero">
    <span class="hero-label">Bukti Nyata</span>
    <h1 class="hero-title">Setiap Laporan, Ada Tindak Lanjutnya</h1>
    <p class="hero-subtitle">
        Kumpulan kasus yang sudah selesai ditangani, lengkap dengan foto bukti dan catatan dari staff yang menangani.
    </p>
</div>

@if ($resolvedCases->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-image"></i></div>
            <p class="mb-0">Belum ada kasus yang selesai dengan bukti foto.</p>
        </div>
    </div>
@else
    <div class="row">
        @foreach ($resolvedCases as $log)
            <div class="col-md-4 mb-4">
                <div class="card card-interactive h-100 proof-card" role="button" data-bs-toggle="modal" data-bs-target="#case-detail-proof-{{ $log->id }}">
                    <div class="row g-0">
                        <div class="col-6 position-relative">
                            @php $beforePhoto = $log->cluster?->reports->first(); @endphp
                            @if ($beforePhoto)
                                <img src="{{ asset('storage/' . $beforePhoto->photo_path) }}" alt="Sebelum" style="width: 100%; height: 140px; object-fit: cover; border-right: 1px solid var(--bs-border-color); border-bottom: 1px solid var(--bs-border-color);">
                            @else
                                <div style="width: 100%; height: 140px; background-color: var(--bs-light); border-right: 1px solid var(--bs-border-color); border-bottom: 1px solid var(--bs-border-color);"></div>
                            @endif
                            <span class="proof-label proof-label--before">SEBELUM</span>
                        </div>
                        <div class="col-6 position-relative">
                            <img src="{{ asset('storage/' . $log->photo_path) }}" alt="Sesudah" style="width: 100%; height: 140px; object-fit: cover; border-bottom: 1px solid var(--bs-border-color);">
                            <span class="proof-label proof-label--after">SESUDAH</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($log->cluster)
                            <span class="badge mb-2" style="background: transparent; font-weight: 500; border: 1px solid {{ $log->cluster->category == 'pothole' ? 'var(--bs-warning)' : 'var(--bs-secondary)' }}; color: {{ $log->cluster->category == 'pothole' ? 'var(--bs-warning)' : 'var(--bs-secondary)' }};">
                                {{ $log->cluster->category == 'pothole' ? 'Jalan Berlubang' : 'Sampah Menumpuk' }}
                            </span>
                        @endif
                        @if ($log->note)
                            <p class="small text-muted mb-2 fst-italic">"{{ $log->note }}"</p>
                        @endif
                        <p class="small mb-0" style="font-family: var(--font-mono); color: var(--bs-secondary); font-size: 0.75rem;">Selesai ditangani {{ $log->created_at->diffForHumans() }}</p>
                        <p class="small mb-0" style="font-family: var(--font-mono); color: var(--bs-secondary); font-size: 0.75rem;">Oleh {{ $log->user->name ?? 'Staff' }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach ($resolvedCases as $log)
        @include('transparency._case-modal', ['log' => $log, 'idPrefix' => 'proof'])
    @endforeach

    <div class="mt-4">
        {{ $resolvedCases->links() }}
    </div>
@endif
@endsection