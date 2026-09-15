@extends('layouts.app')

@section('title', 'Dashboard Agency')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Dashboard Prioritas Laporan</h3>
        <form method="POST" action="/dashboard/summary" id="summary-form">
            @csrf
            <button type="submit" class="btn" id="summary-btn" style="background-color: var(--bs-info); color: white;">
                <i class="bi bi-stars"></i> Generate Ringkasan AI
            </button>
        </form>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if (session('info'))
    <div class="alert alert-info" id="summary-processing-alert">
        <span class="spinner-border spinner-border-sm me-2"></span>{{ session('info') }}
    </div>
@endif

@if ($cachedSummary)
    <div class="mb-4 p-3 ai-summary-card">
        <div class="d-flex align-items-center mb-2">
            <span class="ai-label"><i class="bi bi-stars"></i> Ringkasan Tren — AI</span>
        </div>
        <p class="mb-1" style="font-size: 0.95rem; line-height: 1.6;">{{ $cachedSummary['text'] }}</p>
        <p class="small mb-0" style="color: var(--bs-secondary); font-family: var(--font-mono); font-size: 0.75rem;">Diperbarui {{ \Carbon\Carbon::parse($cachedSummary['generated_at'])->diffForHumans() }}</p>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body p-0 border-bottom" style="border-color: var(--bs-border-color) !important;">
        <div id="map" style="height: 400px;"></div>
    </div>
    <div class="card-body py-2 d-flex gap-3" style="font-size: 0.75rem; color: var(--bs-secondary); text-transform: uppercase; letter-spacing: 0.04em;">
        <span><span class="signal-dot signal-dot--high"></span>Tinggi</span>
        <span><span class="signal-dot signal-dot--medium"></span>Sedang</span>
        <span><span class="signal-dot signal-dot--low"></span>Rendah</span>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Total Cluster</p>
                <p class="stat-value mb-0">{{ $stats['total_clusters'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Total Laporan</p>
                <p class="stat-value mb-0">{{ $stats['total_reports'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label">Cluster Belum Selesai</p>
                <p class="stat-value mb-0">{{ $stats['open_clusters'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-4">
                <p class="stat-label"><span class="signal-dot signal-dot--high"></span>Prioritas Tinggi</p>
                <p class="stat-value mb-0" style="color: var(--bs-danger);">{{ $stats['high_priority'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="text-muted mb-3">Tren Laporan Harian (30 Hari Terakhir)</h6>
        <canvas id="trendChart" height="80"></canvas>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/dashboard" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="category" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Kategori</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach (\App\Models\Report::categories() as $slug => $info)
                        <option value="{{ $slug }}" {{ request('category') == $slug ? 'selected' : '' }}>{{ $info['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua status</option>
                        <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Dilaporkan</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Ditangani</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="/dashboard" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

@if ($clusters->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-map"></i></div>
            <p class="mb-0">Belum ada cluster laporan.</p>
        </div>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Prioritas</th>
                    <th>Kategori</th>
                    <th>Jumlah Laporan</th>
                    <th>Status</th>
                    <th>Laporan Terakhir</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clusters as $cluster)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 40px; height: 6px; background-color: var(--bs-light); border: 1px solid var(--bs-border-color);">
                                    <div style="width: {{ $cluster->priority_score }}%; height: 100%; background-color: {{ $cluster->priority_score >= 70 ? 'var(--bs-danger)' : ($cluster->priority_score >= 40 ? 'var(--bs-warning)' : 'var(--bs-secondary)') }};"></div>
                                </div>
                                <span class="fw-semibold" style="font-family: var(--font-mono); font-size: 0.85rem;">{{ $cluster->priority_score }}</span>
                            </div>
                        </td>
                            <td>
                                <span class="badge" style="background: transparent; color: {{ \App\Models\Report::categoryColor($cluster->category) }}; border: 1px solid {{ \App\Models\Report::categoryColor($cluster->category) }}; font-weight: 500;">{{ \App\Models\Report::categoryLabel($cluster->category) }}</span>
                            </td>
                        <td>
                            <strong>{{ $cluster->report_count }}</strong> laporan
                        </td>
                        <td>
                            @if ($cluster->status == 'resolved')
                                <span class="badge" style="background: transparent; color: var(--bs-success); border: 1px solid var(--bs-success); font-weight: 500;">Selesai</span>
                            @elseif ($cluster->status == 'in_progress')
                                <span class="badge" style="background: transparent; color: var(--bs-warning); border: 1px solid var(--bs-warning); font-weight: 500;">Sedang Ditangani</span>
                            @else
                                <span class="badge" style="background: transparent; color: var(--bs-secondary); border: 1px solid var(--bs-secondary); font-weight: 500;">Dilaporkan</span>
                            @endif
                        </td>
                        <td>{{ $cluster->last_reported_at->diffForHumans() }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cluster-detail-{{ $cluster->id }}">
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $clusters->links() }}
    </div>

    @foreach ($clusters as $cluster)
        <div class="modal fade" id="cluster-detail-{{ $cluster->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="badge" style="background: transparent; color: {{ \App\Models\Report::categoryColor($cluster->category) }}; border: 1px solid {{ \App\Models\Report::categoryColor($cluster->category) }}; font-weight: 500;">{{ \App\Models\Report::categoryLabel($cluster->category) }}</span>
                            <span class="ms-2 text-muted small">Skor prioritas: {{ $cluster->priority_score }}</span>
                            @php
                                $correctedCount = $cluster->reports->filter(fn ($r) => $r->original_category && $r->original_category !== $r->category)->count();
                            @endphp
                            @if ($correctedCount > 0)
                                <span class="ms-2" style="font-size: 0.7rem; color: var(--bs-secondary); border: 1px solid var(--bs-border-color); padding: 2px 6px;" title="Kategori dikoreksi AI setelah verifikasi foto">
                                    {{ $correctedCount }} DIKOREKSI AI
                                </span>
                            @endif
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

<div class="cluster-mini-map mb-4"
     data-lat="{{ $cluster->center_latitude }}"
     data-lng="{{ $cluster->center_longitude }}"
     style="height: 200px; border-radius: var(--bs-border-radius); overflow: hidden;">
</div>

<h6 class="small text-uppercase text-muted mb-2" style="letter-spacing: 0.06em;">Foto Laporan ({{ $cluster->reports->count() }})</h6>                        <div class="d-flex gap-2 flex-wrap mb-4">
                            @foreach ($cluster->reports as $report)
                                <img src="{{ asset('storage/' . $report->photo_path) }}" alt="Foto laporan" style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--bs-border-radius-sm);">
                            @endforeach
                        </div>

                        <h6 class="small text-uppercase text-muted mb-2" style="letter-spacing: 0.06em;">Update Status</h6>
                        <form method="POST" action="/clusters/{{ $cluster->id }}" enctype="multipart/form-data" class="mb-4">
                            @csrf
                            @method('PATCH')
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <select name="status" class="form-select status-select" data-cluster-id="{{ $cluster->id }}">
                                        <option value="reported" {{ $cluster->status == 'reported' ? 'selected' : '' }}>Dilaporkan</option>
                                        <option value="in_progress" {{ $cluster->status == 'in_progress' ? 'selected' : '' }}>Sedang Ditangani</option>
                                        <option value="resolved" {{ $cluster->status == 'resolved' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="note" class="form-control" placeholder="Catatan (opsional)">
                                </div>
                                <div class="col-12 proof-photo-field d-none" id="proof-field-{{ $cluster->id }}">
                                    <label class="form-label small mb-1">Foto bukti penanganan <span class="text-danger">*</span></label>
                                    <input type="file" name="proof_photo" class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn" style="background-color: var(--bs-primary); color: white;">Update Status</button>
                                </div>
                            </div>
                        </form>

                        @if ($cluster->statusLogs->isNotEmpty())
                            <h6 class="small text-uppercase text-muted mb-2" style="letter-spacing: 0.06em;">Riwayat Penanganan</h6>
                            @foreach ($cluster->statusLogs as $log)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <p class="small mb-1">
                                            <strong>{{ $log->user->name ?? 'Unknown' }}</strong>
                                            · {{ $log->statusLabel($log->previous_status) }} → {{ $log->statusLabel($log->new_status) }}
                                            · {{ $log->created_at->diffForHumans() }}
                                        </p>
                                    @if ($log->note)
                                        <p class="small text-muted fst-italic mb-1">"{{ $log->note }}"</p>
                                    @endif
                                    @if ($log->photo_path)
                                        <img src="{{ asset('storage/' . $log->photo_path) }}" alt="Foto bukti" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--bs-border-radius-sm);">
                                    @endif
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection

@php
    $clusterMapData = $mapClusters->map(function ($cluster) {
        return [
            'id' => $cluster->id,
            'lat' => $cluster->center_latitude,
            'lng' => $cluster->center_longitude,
            'category' => $cluster->category,
            'priority_score' => $cluster->priority_score,
            'report_count' => $cluster->report_count,
            'status' => $cluster->status,
        ];
    })->values();
@endphp

@push('scripts')
<script>
    const clusterData = @json($clusterMapData);

    const defaultCenter = [-6.9175, 107.6191]; // fallback: Bandung
    const map = L.map('map').setView(defaultCenter, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    function priorityColor(score) {
        if (score >= 70) return '#dc3545'; // merah
        if (score >= 40) return '#ffc107'; // kuning
        return '#6c757d'; // abu-abu
    }

    const categoryInfo = {
        pothole: { label: 'Jalan Berlubang', color: '#B45309' },
        trash: { label: 'Sampah Menumpuk', color: '#52525B' },
        streetlight: { label: 'Lampu Jalan Mati', color: '#6D28D9' },
        drainage: { label: 'Saluran Air Tersumbat', color: '#2B4C7E' },
        fallen_tree: { label: 'Pohon Tumbang', color: '#15803D' },
    };

    function categoryLabel(category) {
        return categoryInfo[category]?.label ?? category;
    }

    clusterData.forEach(function (cluster) {
        const marker = L.circleMarker([cluster.lat, cluster.lng], {
            radius: 8 + Math.min(cluster.report_count, 10),
            fillColor: priorityColor(cluster.priority_score),
            color: '#333',
            weight: 1,
            fillOpacity: 0.8,
        }).addTo(map);

        const modalExists = document.getElementById('cluster-detail-' + cluster.id) !== null;

        const popupContent = modalExists
            ? `<strong>${categoryLabel(cluster.category)}</strong><br>
            Prioritas: ${cluster.priority_score}<br>
            Jumlah laporan: ${cluster.report_count}<br>
            Status: ${cluster.status}<br>
            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="document.querySelector('[data-bs-target=\\'#cluster-detail-${cluster.id}\\']').click()">Lihat Detail</button>`
            : `<strong>${categoryLabel(cluster.category)}</strong><br>
            Prioritas: ${cluster.priority_score}<br>
            Jumlah laporan: ${cluster.report_count}<br>
            Status: ${cluster.status}<br>
            <span class="text-muted small">Buka di halaman lain untuk detail (filter/paginasi aktif)</span>`;

        marker.bindPopup(popupContent);
    });
    if (clusterData.length > 0) {
        const bounds = L.latLngBounds(clusterData.map(c => [c.lat, c.lng]));
        map.fitBounds(bounds, { padding: [30, 30] });
    }
</script>
@endpush
@push('scripts')
<script>
    document.querySelectorAll('.status-select').forEach(function (select) {
        select.addEventListener('change', function () {
            const clusterId = this.dataset.clusterId;
            const proofField = document.getElementById('proof-field-' + clusterId);
            const proofInput = proofField.querySelector('input[type="file"]');

            if (this.value === 'resolved') {
                proofField.classList.remove('d-none');
                proofInput.setAttribute('required', 'required');
            } else {
                proofField.classList.add('d-none');
                proofInput.removeAttribute('required');
            }
        });
    });

    document.querySelectorAll('.modal').forEach(function (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            const mapDiv = modalEl.querySelector('.cluster-mini-map');
            if (!mapDiv || mapDiv.dataset.initialized) {
                return;
            }

            const lat = parseFloat(mapDiv.dataset.lat);
            const lng = parseFloat(mapDiv.dataset.lng);

            const miniMap = L.map(mapDiv).setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(miniMap);

            L.marker([lat, lng]).addTo(miniMap);

            mapDiv.dataset.initialized = 'true';
        });
    }); 
</script>
@endpush

@push('scripts')
<script>
    const trendLabels = @json($dailyTrend['labels']);
    const trendPothole = @json($dailyTrend['pothole']);
    const trendTrash = @json($dailyTrend['trash']);

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Jalan berlubang',
                    data: trendPothole,
                    borderColor: '#B45309',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0,
                    pointRadius: 2,
                },
                {
                    label: 'Sampah menumpuk',
                    data: trendTrash,
                    borderColor: '#52525B',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0,
                    pointRadius: 2,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
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

@push('scripts')
<script>
    document.getElementById('summary-form').addEventListener('submit', function () {
        const btn = document.getElementById('summary-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Membuat ringkasan...';
    });

    const processingAlert = document.getElementById('summary-processing-alert');
    if (processingAlert) {
        let pollCount = 0;
        const maxPolls = 20;

        const pollInterval = setInterval(function () {
            pollCount++;

            fetch('/dashboard/summary/status')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'done' || pollCount >= maxPolls) {
                        clearInterval(pollInterval);
                        window.location.reload();
                    }
                })
                .catch(function () {
                    clearInterval(pollInterval);
                });
        }, 3000);
    }
</script>
@endpush