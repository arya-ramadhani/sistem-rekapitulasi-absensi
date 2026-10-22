@extends('layouts.app')

@section('content')
<div class="text-center">
    <i class="fas fa-envelope fa-4x text-primary mb-3"></i>
    
    <h4 class="mb-4">{{ __('Verifikasi Email Anda') }}</h4>
    
    @if (session('resent'))
        <div class="alert alert-success" role="alert">
            {{ __('Link verifikasi baru telah dikirim ke email Anda.') }}
        </div>
    @endif

    <p class="text-muted mb-4">
        {{ __('Sebelum melanjutkan, silakan cek email Anda untuk link verifikasi.') }}
        {{ __('Jika Anda tidak menerima email') }},
    </p>

    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-redo me-2"></i>{{ __('Kirim ulang email verifikasi') }}
        </button>
    </form>

    <div class="mt-3">
        <a href="{{ route('logout') }}" class="btn btn-link"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>
    </div>
</div>
@endsection