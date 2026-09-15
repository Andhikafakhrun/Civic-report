@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--bs-border-color);">
            <span class="text-uppercase fw-semibold" style="font-size: 0.75rem; color: var(--bs-secondary); letter-spacing: 0.06em;">Area Staff</span>
            <h3 class="mt-2 mb-0">Lupa Password</h3>
            <p class="text-muted small mt-2 mb-0">Masukkan email kamu, kami akan kirimkan link untuk reset password.</p>
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

                <form method="POST" action="/forgot-password">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
                </form>

                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="/login">Kembali ke login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection