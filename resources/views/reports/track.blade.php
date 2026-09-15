@extends('layouts.app')

@section('title', 'Lacak Laporan')

@section('content')
<div class="hero">
    <span class="hero-label">Civic Report</span>
    <h1 class="hero-title">Lacak Status Laporanmu</h1>
    <p class="hero-subtitle">Masukkan kode pelacakan yang kamu dapat saat melapor untuk melihat status terkini.</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <form method="POST" action="/track" class="mb-4">
            @csrf
            <label for="tracking_code" class="form-label form-label-upper">Kode Pelacakan</label>
            <div class="input-group">
                <input type="text" class="form-control tracking-code @error('tracking_code') is-invalid @enderror"
                       id="tracking_code" name="tracking_code"
                       placeholder="Contoh: A3F9K2XZ"
                       value="{{ old('tracking_code') }}"
                       style="text-transform: uppercase; letter-spacing: 0.08em;" required>
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cek Status
                </button>
                @error('tracking_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </form>

        @if (isset($searched) && $searched)
            @if ($report)
                <div class="card result-card result-card--{{ $report->status }} animate-in">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3" style="border-bottom: 1px solid var(--bs-border-color);">
                                <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid {{ \App\Models\Report::categoryColor($report->category) }}; color: {{ \App\Models\Report::categoryColor($report->category) }};">
                                    {{ \App\Models\Report::categoryLabel($report->category) }}
                                </span>
                            @if ($report->status == 'resolved')
                                <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid var(--bs-success); color: var(--bs-success);">Selesai</span>
                            @elseif ($report->status == 'in_progress')
                                <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid var(--bs-warning); color: var(--bs-warning);">Sedang Ditangani</span>
                            @else
                                <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid var(--bs-secondary); color: var(--bs-secondary);">Dilaporkan</span>
                            @endif
                        </div>

                        @if ($report->original_category && $report->original_category !== $report->category)
                            <p class="small mb-3" style="color: var(--bs-secondary); border: 1px solid var(--bs-border-color); padding: 6px 10px;">
                                Awalnya dilaporkan sebagai "{{ \App\Models\Report::categoryLabel($report->original_category) }}", dikoreksi sistem setelah verifikasi foto.
                            </p>
                        @endif

                        <div id="track-mini-map" data-lat="{{ $report->latitude }}" data-lng="{{ $report->longitude }}" style="height: 180px; border: 1px solid var(--bs-border-color);" class="mb-3"></div>

                        <p class="mb-2">{{ $report->description }}</p>
                        <p class="small mb-0" style="font-family: var(--font-mono); color: var(--bs-secondary);">
                            Dilaporkan {{ $report->created_at->diffForHumans() }}
                        </p>
                        @if ($report->severity)
                            <p class="small mb-0" style="font-family: var(--font-mono); color: var(--bs-secondary);">
                                Tingkat keparahan:
                                <span class="signal-dot signal-dot--{{ $report->severity }}"></span>{{ ucfirst($report->severity) }}
                            </p>
                        @endif

                        @if ($report->cluster && $report->cluster->statusLogs->isNotEmpty())
                            <hr style="border-color: var(--bs-border-color);">
                            <h6 class="section-heading">Riwayat Penanganan</h6>
                            @foreach ($report->cluster->statusLogs as $log)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: var(--bs-border-color) !important;">
                                    <p class="small mb-1">
                                        <strong>{{ $log->statusLabel($log->previous_status) }} &rarr; {{ $log->statusLabel($log->new_status) }}</strong>
                                        <span style="color: var(--bs-secondary); font-family: var(--font-mono); font-size: 0.75rem;">&middot; {{ $log->created_at->diffForHumans() }}</span>
                                    </p>
                                    @if ($log->note)
                                        <p class="small fst-italic mb-1" style="color: var(--bs-secondary);">"{{ $log->note }}"</p>
                                    @endif
                                    @if ($log->photo_path)
                                        <img src="{{ asset('storage/' . $log->photo_path) }}" alt="Foto bukti" style="width: 70px; height: 70px; object-fit: cover; border: 1px solid var(--bs-border-color);">
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @else
                <div class="card animate-in">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-search"></i></div>
                        <p class="mb-0">Kode pelacakan tidak ditemukan. Periksa kembali kode yang kamu masukkan.</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const trackMapDiv = document.getElementById('track-mini-map');
    if (trackMapDiv) {
        const lat = parseFloat(trackMapDiv.dataset.lat);
        const lng = parseFloat(trackMapDiv.dataset.lng);

        const trackMap = L.map(trackMapDiv).setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(trackMap);

        L.marker([lat, lng]).addTo(trackMap);
    }
</script>
@endpush