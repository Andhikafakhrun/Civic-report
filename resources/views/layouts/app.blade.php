<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Civic Report')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Custom App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg" style="background-color: #FFFFFF; border-bottom: 1px solid var(--bs-border-color);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <span class="signal-dot signal-dot--high"></span>
                Civic Report
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: 1px solid var(--bs-border-color);">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('lapor') ? 'active' : '' }}" href="/lapor">Lapor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('track') ? 'active' : '' }}" href="/track">Lacak Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('transparansi') ? 'active' : '' }}" href="/transparansi">Transparansi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('bukti-nyata') ? 'active' : '' }}" href="/bukti-nyata">Bukti Nyata</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center ps-lg-3 ms-lg-3" style="border-left: 1px solid var(--bs-border-color);">
                        @auth
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    {{ auth()->user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/reports">Semua Laporan</a></li>
                                    @if (auth()->user()->isAdmin())
                                        <li><a class="dropdown-item" href="/staff">Manajemen Staff</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="/logout">
                                            @csrf
                                            <button type="submit" class="dropdown-item">Keluar</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                        <a class="nav-link" href="/login">Login Staff</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Konten halaman -->
    <main class="container my-4 flex-grow-1">
        @yield('content')
    </main>

    <footer class="site-footer py-4">
        <div class="container small">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <span class="fw-semibold" style="color: #fff;">Civic Report</span>
                    <span class="ms-2">Platform pelaporan & akuntabilitas warga</span>
                </div>
                <div class="d-flex gap-3">
                    <a href="/transparansi">Transparansi</a>
                    <a href="/bukti-nyata">Bukti Nyata</a>
                    <a href="/profil">Profil</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    @stack('scripts')
</body>
</html>