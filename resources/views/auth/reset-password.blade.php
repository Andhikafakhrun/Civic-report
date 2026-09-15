@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--bs-border-color);">
            <span class="text-uppercase fw-semibold" style="font-size: 0.75rem; color: var(--bs-secondary); letter-spacing: 0.06em;">Area Staff</span>
            <h3 class="mt-2 mb-0">Atur Ulang Password</h3>
        </div>

        <div class="card">
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger" style="border-radius: var(--bs-border-radius);">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/reset-password">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $email }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--bs-secondary); font-weight: 600;">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection