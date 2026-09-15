@extends('layouts.app')

@section('title', 'Manajemen Staff')

@section('content')
<div class="mb-4 pb-3" style="border-bottom: 1px solid var(--bs-border-color);">
    <span class="text-uppercase fw-semibold" style="font-size: 0.75rem; color: var(--bs-secondary); letter-spacing: 0.06em;">Admin</span>
    <h3 class="mb-0 mt-1">Manajemen Staff</h3>
</div>

@if (session('success'))
    <div class="alert alert-success" style="border-radius: var(--bs-border-radius);">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h6 class="mb-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-secondary);">
            <span class="signal-dot signal-dot--medium"></span>
            Menunggu Persetujuan ({{ $pendingStaff->count() }})
        </h6>

        @if ($pendingStaff->isEmpty())
            <p class="text-muted small mb-0">Tidak ada pendaftaran yang menunggu.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Mendaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingStaff as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="small" style="font-family: var(--font-mono); color: var(--bs-secondary);">{{ $user->created_at->diffForHumans() }}</td>
                                <td>
                                    <form method="POST" action="/staff/{{ $user->id }}/approve" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary">Setujui</button>
                                    </form>
                                    <form method="POST" action="/staff/{{ $user->id }}/reject" class="d-inline" onsubmit="return confirm('Tolak dan hapus pendaftaran {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="mb-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bs-secondary);">
            <span class="signal-dot signal-dot--low"></span>
            Staff Aktif ({{ $approvedStaff->count() }})
        </h6>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedStaff as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge" style="background: transparent; font-weight: 500; border: 1px solid {{ $user->isAdmin() ? 'var(--bs-dark)' : 'var(--bs-border-color)' }}; color: {{ $user->isAdmin() ? 'var(--bs-dark)' : 'var(--bs-secondary)' }};">
                                    {{ $user->isAdmin() ? 'Admin' : 'Staff' }}
                                </span>
                            </td>
                            <td class="small" style="font-family: var(--font-mono); color: var(--bs-secondary);">{{ $user->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection