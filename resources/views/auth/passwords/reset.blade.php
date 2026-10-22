@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <h4 class="text-center mb-4">Reset Password</h4>
    <p class="text-center text-muted mb-4">Masukkan password baru Anda</p>

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email Address') }}</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                   placeholder="Email terdaftar">
        </div>
        @error('email')
            <div class="invalid-feedback d-block">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password Baru') }}</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="new-password"
                   placeholder="Masukkan password baru (min. 8 karakter)">
        </div>
        @error('password')
            <div class="invalid-feedback d-block">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password-confirm" class="form-label">{{ __('Confirm Password Baru') }}</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password-confirm" type="password" class="form-control" 
                   name="password_confirmation" required autocomplete="new-password"
                   placeholder="Ulangi password baru">
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-key me-2"></i>{{ __('Reset Password') }}
        </button>
    </div>
</form>
@endsection