@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <h4 class="text-center mb-4">Reset Password</h4>
    <p class="text-center text-muted mb-4">Masukkan email Anda untuk menerima link reset password</p>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email Address') }}</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                   placeholder="Masukkan email terdaftar">
        </div>
        @error('email')
            <div class="invalid-feedback d-block">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane me-2"></i>{{ __('Kirim Link Reset Password') }}
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="btn btn-link">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
        </a>
    </div>
</form>
@endsection