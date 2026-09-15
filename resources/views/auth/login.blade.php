@extends('layouts.app')

@section('title', 'Login Staff')

@section('content')
<div class="row justify-content-center">

    <div class="col-md-6 col-lg-4">
        <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--bs-border-color);">
            <span class="text-uppercase fw-semibold" style="font-size: 0.75rem; color: var(--bs-secondary); letter-spacing: 0.06em;">Area Staff</span>
            <h3 class="mt-2 mb-0">Masuk ke Dashboard</h3>
        </div>

        <div class="card">
            <div class="card-body p-4">
                @if (session('info'))
                    <div class="alert alert-info" style="border-radius: var(--bs-border-radius);">{{ session('info') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" style="border-radius: var(--bs-border-radius);">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/login">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="text-end mt-1">
                            <a href="/forgot-password" class="small">Lupa password?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>

                <p class="text-center text-muted small mt-3 mb-0">
                    Belum punya akun? <a href="/register">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection         