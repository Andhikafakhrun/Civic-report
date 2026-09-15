<div class="modal fade" id="case-detail-{{ $idPrefix }}-{{ $log->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    @if ($log->cluster)
                        <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid {{ \App\Models\Report::categoryColor($log->cluster->category) }}; color: {{ \App\Models\Report::categoryColor($log->cluster->category) }};">
                            {{ \App\Models\Report::categoryLabel($log->cluster->category) }}
                        </span>
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        @php $beforePhoto = $log->cluster?->reports->first(); @endphp
                        <p class="small mb-1" style="color: var(--bs-secondary); text-transform: uppercase; letter-spacing: 0.04em; font-size: 0.7rem;">Sebelum</p>
                        @if ($beforePhoto)
                            <img src="{{ asset('storage/' . $beforePhoto->photo_path) }}" alt="Sebelum" style="width: 100%; height: 220px; object-fit: cover; border: 1px solid var(--bs-border-color);">
                        @else
                            <div style="width: 100%; height: 220px; background-color: var(--bs-light); border: 1px solid var(--bs-border-color);"></div>
                        @endif
                    </div>
                    <div class="col-6">
                        <p class="small mb-1" style="color: var(--bs-secondary); text-transform: uppercase; letter-spacing: 0.04em; font-size: 0.7rem;">Sesudah</p>
                        <img src="{{ asset('storage/' . $log->photo_path) }}" alt="Sesudah" style="width: 100%; height: 220px; object-fit: cover; border: 1px solid var(--bs-border-color);">
                    </div>
                </div>

                @if ($log->note)
                    <p class="mb-3 fst-italic">"{{ $log->note }}"</p>
                @endif

                <p class="small mb-1" style="font-family: var(--font-mono); color: var(--bs-secondary);">
                    Selesai ditangani {{ $log->created_at->diffForHumans() }} oleh {{ $log->user->name ?? 'Staff' }}
                </p>

                @if ($log->cluster && $log->cluster->reports->count() > 1)
                    <hr style="border-color: var(--bs-border-color);">
                    <p class="small mb-2" style="text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-size: 0.75rem;">
                        Bagian dari {{ $log->cluster->reports->count() }} laporan warga di lokasi ini
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ($log->cluster->reports as $report)
                            <img src="{{ asset('storage/' . $report->photo_path) }}" alt="Foto laporan" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid var(--bs-border-color);">
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>