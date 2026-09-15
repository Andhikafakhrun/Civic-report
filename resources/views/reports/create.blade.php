@extends('layouts.app')

@section('title', 'Lapor Masalah')

@section('content')
<div class="hero">
    <span class="hero-label">Civic Report</span>
    <h1 class="hero-title">Satu Laporan Bisa Jadi Awal Perubahan</h1>
    <p class="hero-subtitle">
        Foto, tandai lokasinya, dan sistem kami akan otomatis mengelompokkan dan memprioritaskan laporanmu bersama laporan warga lain di sekitar sana.
    </p>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <h6 class="section-heading">Formulir Laporan</h6>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                @if (session('tracking_code'))
                    <hr style="border-color: var(--bs-border-color);">
                    <p class="mb-1 small text-muted">Simpan kode ini untuk melacak status laporanmu:</p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="tracking-code" id="tracking-code-text" style="font-size: 1.1rem; font-weight: 700; border: 1px solid var(--bs-dark); padding: 4px 10px; letter-spacing: 0.08em;">{{ session('tracking_code') }}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-copy-code">Salin</button>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="/reports" method="POST" enctype="multipart/form-data" id="report-form">
            @csrf

            <!-- Kategori -->
            <div class="mb-3">
                <label for="category" class="form-label form-label-upper">Kategori</label>
                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                    <option value="" selected disabled>Pilih kategori</option>
                    @foreach (\App\Models\Report::categories() as $slug => $info)
                        <option value="{{ $slug }}" {{ old('category') == $slug ? 'selected' : '' }}>{{ $info['label'] }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Foto -->
            <div class="mb-3">
                <label for="photo" class="form-label form-label-upper">Foto</label>
                <input class="form-control @error('photo') is-invalid @enderror" type="file" id="photo" name="photo" accept="image/*" required>
                <div class="form-text">Upload foto kondisi di lapangan.</div>
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="photo-preview-container" id="photo-preview-container">
                    <img src="" alt="Preview foto" class="photo-preview-img" id="photo-preview-img">
                    <button type="button" class="photo-preview-remove" id="photo-preview-remove" title="Hapus foto">&times;</button>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mb-3">
                <label for="description" class="form-label form-label-upper">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Jelaskan singkat kondisinya..." required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nomor WhatsApp -->
            <div class="mb-3">
                <label for="phone_number" class="form-label form-label-upper">Nomor WhatsApp</label>
                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" placeholder="08xxxxxxxxxx" value="{{ old('phone_number') }}" required>
                <div class="form-text">Untuk mengirimkan notifikasi saat status laporanmu berubah.</div>
                @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Lokasi -->
            <div class="mb-4">
                <label class="form-label form-label-upper">Lokasi</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="location-display" placeholder="Lokasi belum diambil" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="btn-get-location">
                        <i class="bi bi-geo-alt"></i> Ambil Lokasi
                    </button>
                </div>
                @error('latitude')
                    <div class="text-danger small">Lokasi wajib diambil sebelum mengirim laporan.</div>
                @enderror

                <div id="location-map" class="d-none mb-2" style="height: 250px; border: 1px solid var(--bs-border-color); overflow: hidden;"></div>
                <div id="location-map-hint" class="d-none small text-muted mb-2">
                    <i class="bi bi-arrows-move"></i> Geser pin di peta kalau lokasinya kurang tepat.
                </div>

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>

            <button type="submit" class="btn btn-cta w-100" id="btn-submit">
                <i class="bi bi-send"></i> Kirim Laporan
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Copy tracking code
    const copyBtn = document.getElementById('btn-copy-code');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const code = document.getElementById('tracking-code-text').textContent.trim();
            navigator.clipboard.writeText(code).then(function () {
                copyBtn.textContent = 'Tersalin \u2713';
                setTimeout(function () {
                    copyBtn.textContent = 'Salin';
                }, 2000);
            });
        });
    }

    // Photo preview
    const photoInput = document.getElementById('photo');
    const previewContainer = document.getElementById('photo-preview-container');
    const previewImg = document.getElementById('photo-preview-img');
    const previewRemove = document.getElementById('photo-preview-remove');

    photoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewContainer.classList.add('active');
            };
            reader.readAsDataURL(file);
        }
    });

    previewRemove.addEventListener('click', function () {
        photoInput.value = '';
        previewContainer.classList.remove('active');
        previewImg.src = '';
    });

    // Leaflet map with draggable pin
    let locationMap = null;
    let locationMarker = null;

    function showLocationMap(lat, lng) {
        const mapContainer = document.getElementById('location-map');
        const hintContainer = document.getElementById('location-map-hint');

        mapContainer.classList.remove('d-none');
        hintContainer.classList.remove('d-none');

        if (!locationMap) {
            locationMap = L.map('location-map').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(locationMap);

            locationMarker = L.marker([lat, lng], { draggable: true }).addTo(locationMap);

            locationMarker.on('dragend', function () {
                const position = locationMarker.getLatLng();
                updateLocationFields(position.lat, position.lng);
            });
        } else {
            locationMap.setView([lat, lng], 16);
            locationMarker.setLatLng([lat, lng]);
            locationMap.invalidateSize();
        }
    }

    function updateLocationFields(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        document.getElementById('location-display').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    document.getElementById('btn-get-location').addEventListener('click', function () {
        const btn = this;
        const display = document.getElementById('location-display');

        if (!navigator.geolocation) {
            display.value = 'Browser tidak mendukung geolocation';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengambil...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                updateLocationFields(lat, lng);
                showLocationMap(lat, lng);

                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt"></i> Ambil Lokasi';
            },
            function (error) {
                display.value = 'Gagal mengambil lokasi: ' + error.message;
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt"></i> Ambil Lokasi';
            }
        );
    });

    // Prevent submit without location
    document.getElementById('report-form').addEventListener('submit', function (e) {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;

        if (!lat || !lng) {
            e.preventDefault();
            alert('Silakan klik tombol "Ambil Lokasi" terlebih dahulu sebelum mengirim laporan.');
            return;
        }

        // Show loading state on submit button
        const btn = document.getElementById('btn-submit');
        btn.classList.add('btn-loading');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
    });
</script>
@endpush