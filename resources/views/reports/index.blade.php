@extends('layouts.app')

@section('title', 'Daftar Laporan')

@section('content')
<div class="hero">
    <span class="hero-label">Arsip</span>
    <h1 class="hero-title" style="font-size: 1.75rem;">Semua Laporan Masuk</h1>
</div>

@if ($reports->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
            <p class="mb-0">Belum ada laporan masuk.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Kategori</th>
                        <th>Severity</th>
                        <th>Deskripsi</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        <tr>
                            <td>
                                <img src="{{ asset('storage/' . $report->photo_path) }}" alt="Foto laporan" style="width: 56px; height: 56px; object-fit: cover; border: 1px solid var(--bs-border-color);">
                            </td>
                            <td>
                                <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid {{ \App\Models\Report::categoryColor($report->category) }}; color: {{ \App\Models\Report::categoryColor($report->category) }};">
                                    {{ \App\Models\Report::categoryLabel($report->category) }}
                                </span>
                            </td>
                            <td>
                                @if ($report->severity == 'high')
                                    <span class="signal-dot signal-dot--high"></span><span class="small">Tinggi</span>
                                @elseif ($report->severity == 'medium')
                                    <span class="signal-dot signal-dot--medium"></span><span class="small">Sedang</span>
                                @elseif ($report->severity == 'low')
                                    <span class="signal-dot signal-dot--low"></span><span class="small">Rendah</span>
                                @else
                                    <span class="small fst-italic" style="color: var(--bs-secondary);">Memproses...</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ Str::limit($report->description, 60) }}</td>
                            <td class="small" style="font-family: var(--font-mono); color: var(--bs-secondary);">{{ number_format($report->latitude, 5) }}, {{ number_format($report->longitude, 5) }}</td>
                            <td>
                                @if ($report->status == 'resolved')
                                    <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid var(--bs-success); color: var(--bs-success);">Selesai</span>
                                @elseif ($report->status == 'in_progress')
                                    <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid var(--bs-warning); color: var(--bs-warning);">Sedang Ditangani</span>
                                @else
                                    <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid var(--bs-secondary); color: var(--bs-secondary);">Dilaporkan</span>
                                @endif
                            </td>
                            <td class="small" style="font-family: var(--font-mono); color: var(--bs-secondary);">{{ $report->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $reports->links() }}
    </div>
@endif
@endsection