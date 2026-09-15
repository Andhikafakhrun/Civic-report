@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="mb-5 mt-3 pb-4" style="border-bottom: 1px solid var(--bs-border-color);">
    <span class="text-uppercase fw-semibold" style="font-size: 0.75rem; color: var(--bs-secondary); letter-spacing: 0.06em;">Profil</span>
    <h1 class="mt-2 mb-3">Tentang Civic Report</h1>
</div>

<div class="row">
    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-secondary);">Apa itu Civic Report?</h6>
                <p class="text-muted mb-0">
                    Civic Report adalah platform pelaporan masalah infrastruktur kota — jalan berlubang dan sampah menumpuk — yang menghubungkan warga dengan dinas terkait secara langsung. Berbeda dari saluran pelaporan konvensional, setiap laporan yang masuk diproses secara otomatis: diklasifikasi tingkat keparahannya, dikelompokkan dengan laporan serupa di sekitarnya, dan diprioritaskan berdasarkan urgensi — sehingga penanganan bisa lebih cepat dan tepat sasaran.
                </p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <h6 class="mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-primary);">Visi</h6>
                        <p class="text-muted small mb-0">
                            Mewujudkan penanganan infrastruktur kota yang responsif, transparan, dan berbasis data — di mana setiap laporan warga benar-benar didengar dan ditindaklanjuti.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <h6 class="mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-primary);">Misi</h6>
                        <ul class="text-muted small mb-0 ps-3">
                            <li>Mempermudah warga melaporkan masalah tanpa hambatan birokrasi</li>
                            <li>Membantu dinas terkait memprioritaskan penanganan secara objektif</li>
                            <li>Menjaga akuntabilitas lewat transparansi data dan bukti penanganan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-secondary);">Wilayah Cakupan</h6>
                <p class="text-muted mb-0">
                    Saat ini platform beroperasi di wilayah Bandung dan sekitarnya, mencakup dua kategori laporan: jalan berlubang dan sampah menumpuk. Cakupan wilayah dan kategori akan terus dikembangkan seiring kebutuhan.
                </p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-secondary);">Kontak</h6>
                <p class="text-muted mb-1">Untuk pertanyaan atau kerja sama, hubungi kami:</p>
                <p class="text-muted small mb-0" style="font-family: var(--font-mono);">
                    kontak@civicreport.id<br>
                    (022) 000-0000
                </p>
            </div>
        </div>

        <div class="p-3" style="border: 1px solid var(--bs-border-color); font-size: 0.85rem; color: var(--bs-secondary);">
            Civic Report adalah prototipe yang dikembangkan untuk kompetisi Next Gen Developer. Data kontak dan wilayah cakupan pada halaman ini bersifat ilustratif.
        </div>

    </div>
</div>
@endsection