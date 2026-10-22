@extends('layouts.app')

@section('content')
<div class="auth-container-wide">
    <div class="row g-0 auth-card-wide">
        <!-- Left Side - Branding (Desktop & Tablet Landscape) -->
        <div class="col-lg-6 d-none d-lg-flex auth-left">
            <div class="auth-overlay"></div>
            <div class="auth-content">
                <div class="logo-animated">
                    <img src="{{ asset('images/LogoPolman.png') }}" alt="POLMAN BABEL" class="img-fluid auth-logo-img">
                </div>
                <h1 class="auth-title">Sistem Rekapitulasi Absensi</h1>
                <p class="auth-subtitle">POLITEKNIK MANUFAKTUR NEGERI BANGKA BELITUNG</p>
                <div class="auth-badge-list">
                    <span class="auth-badge"><i class="fas fa-check-circle me-1"></i> Presensi Digital Terpadu</span>
                    <span class="auth-badge"><i class="fas fa-shield-alt me-1"></i> Akurat & Aman</span>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Form Login -->
        <div class="col-12 col-lg-6 auth-right">
            <div class="auth-form-container">
                <!-- Mobile Branding Header (Visible only on mobile/tablet) -->
                <div class="auth-mobile-header d-flex d-lg-none flex-column align-items-center text-center">
                    <img src="{{ asset('images/LogoPolman.png') }}" alt="POLMAN BABEL" class="auth-mobile-logo mb-2">
                    <h3 class="auth-mobile-title mb-0">Sistem Rekapitulasi Absensi</h3>
                    <p class="auth-mobile-subtitle text-muted">POLMAN NEGERI BANGKA BELITUNG</p>
                    <div class="auth-mobile-divider"></div>
                </div>

                <div class="auth-header-text">
                    <h2 class="auth-form-heading">Selamat Datang</h2>
                    <p class="auth-form-subheading text-muted">Silakan masuk menggunakan akun Anda</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                                placeholder="nama@polman-babel.ac.id">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label mb-0">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="auth-link-forgot small">Lupa Password?</a>
                            @endif
                        </div>
                        <div class="input-group auth-input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password" placeholder="Masukkan password Anda">
                            <button class="btn btn-outline-secondary toggle-password-btn" type="button" 
                                onclick="togglePasswordVisibility('password', this)" aria-label="Tampilkan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-grid mt-4 mb-3">
                        <button type="submit" class="btn btn-primary btn-auth">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk Sekarang
                        </button>
                    </div>

                    @if (Route::has('register'))
                        <div class="text-center mt-3 pt-2 auth-switch-link">
                            <p class="small text-muted mb-0">
                                Belum punya akun? 
                                <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Daftar Sekarang</a>
                            </p>
                        </div>
                    @endif
                </form>

                <div class="auth-footer text-center mt-4">
                    <p class="small text-muted mb-0">&copy; 2025 POLMAN BABEL. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --auth-primary: #0674c2;
        --auth-primary-dark: #005088;
        --auth-text-dark: #1e293b;
        --auth-text-muted: #64748b;
        --auth-border: #e2e8f0;
    }

    /* Container */
    .auth-container-wide {
        width: 100%;
        max-width: 1020px;
        margin: auto;
        position: relative;
        z-index: 1;
    }

    /* Card Box */
    .auth-card-wide {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(6, 116, 194, 0.15), 0 5px 20px rgba(0, 0, 0, 0.12);
        min-height: 560px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: stretch;
    }

    /* Left Side - Desktop Branding */
    .auth-left {
        background-image: url('{{ asset('images/gambar_polman.jpg') }}');
        background-size: cover;
        background-position: center;
        position: relative;
        padding: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #ffffff;
        min-height: 100%;
    }

    .auth-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(6, 116, 194, 0.5) 0%, rgba(15, 30, 54, 0.6) 100%);
    }

    .auth-content {
        position: relative;
        z-index: 2;
        padding: 40px 35px;
        text-align: center;
        width: 100%;
    }

    .logo-animated {
        width: 110px;
        height: 110px;
        margin: 0 auto 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: floatPulse 4s infinite ease-in-out;
        filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.35));
    }

    .auth-logo-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .auth-title {
        font-weight: 800;
        font-size: 1.75rem;
        line-height: 1.25;
        margin-bottom: 10px;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .auth-subtitle {
        font-weight: 500;
        font-size: 0.92rem;
        letter-spacing: 0.8px;
        opacity: 0.92;
        color: #e0f2fe;
        margin-bottom: 24px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .auth-badge-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: center;
    }

    .auth-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.82rem;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Right Side - Form */
    .auth-right {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: #ffffff;
    }

    .auth-form-container {
        padding: 44px 40px;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
    }

    /* Mobile Branding Header */
    .auth-mobile-header {
        margin-bottom: 24px;
    }

    .auth-mobile-logo {
        width: 68px;
        height: 68px;
        object-fit: contain;
        filter: drop-shadow(0 4px 8px rgba(6, 116, 194, 0.25));
        animation: floatPulse 4s infinite ease-in-out;
    }

    .auth-mobile-title {
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--auth-primary);
        line-height: 1.3;
    }

    .auth-mobile-subtitle {
        font-weight: 600;
        font-size: 0.78rem;
        letter-spacing: 0.6px;
        margin-top: 2px;
        margin-bottom: 12px;
    }

    .auth-mobile-divider {
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, var(--auth-primary), #38bdf8);
        border-radius: 3px;
        margin-bottom: 8px;
    }

    /* Form Elements */
    .auth-header-text {
        margin-bottom: 24px;
    }

    .auth-form-heading {
        font-weight: 800;
        color: var(--auth-text-dark);
        font-size: 1.65rem;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .auth-form-subheading {
        font-size: 0.92rem;
        margin-bottom: 0;
    }

    .form-label {
        font-weight: 600;
        color: var(--auth-text-dark);
        font-size: 0.88rem;
        margin-bottom: 6px;
    }

    .auth-input-group {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--auth-border);
        transition: all 0.25s ease;
    }

    .auth-input-group:focus-within {
        border-color: var(--auth-primary);
        box-shadow: 0 0 0 3px rgba(6, 116, 194, 0.15);
    }

    .auth-input-group .input-group-text {
        background-color: #f8fafc;
        border: none;
        color: #64748b;
        padding-left: 14px;
        padding-right: 12px;
        font-size: 0.95rem;
    }

    .auth-input-group .form-control {
        border: none;
        padding: 12px 14px;
        font-size: 0.95rem;
        color: var(--auth-text-dark);
        font-weight: 500;
        background: #ffffff;
    }

    .auth-input-group .form-control:focus {
        box-shadow: none;
        background: #ffffff;
    }

    .toggle-password-btn {
        background-color: #f8fafc;
        border: none;
        color: #64748b;
        padding: 0 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .toggle-password-btn:hover,
    .toggle-password-btn:focus {
        background-color: #f1f5f9;
        color: var(--auth-primary);
        box-shadow: none;
    }

    .auth-link-forgot {
        color: var(--auth-primary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .auth-link-forgot:hover {
        color: var(--auth-primary-dark);
        text-decoration: underline;
    }

    .btn-auth {
        background: linear-gradient(135deg, #0674c2 0%, #055c9d 100%);
        border: none;
        border-radius: 10px;
        padding: 13px 20px;
        font-weight: 700;
        font-size: 1rem;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(6, 116, 194, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
    }

    .btn-auth:hover {
        background: linear-gradient(135deg, #055c9d 0%, #004578 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(6, 116, 194, 0.4);
        color: #ffffff;
    }

    .btn-auth:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(6, 116, 194, 0.3);
    }

    .auth-switch-link a {
        color: var(--auth-primary);
    }

    .auth-switch-link a:hover {
        color: var(--auth-primary-dark);
        text-decoration: underline !important;
    }

    /* Keyframes */
    @keyframes floatPulse {
        0%, 100% {
            transform: translateY(0) scale(1);
        }
        50% {
            transform: translateY(-8px) scale(1.02);
        }
    }

    /* Responsive Breakpoints */
    @media (max-width: 991.98px) {
        .auth-container-wide {
            max-width: 480px;
            padding: 10px 0;
        }

        .auth-card-wide {
            flex-direction: column;
            min-height: auto;
            border-radius: 18px;
        }

        .auth-right {
            width: 100%;
        }

        .auth-form-container {
            padding: 32px 28px;
        }

        .auth-header-text {
            text-align: center;
            margin-bottom: 20px;
        }

        .auth-form-heading {
            font-size: 1.45rem;
        }
    }

    @media (max-width: 575.98px) {
        .auth-form-container {
            padding: 24px 18px;
        }

        .auth-mobile-logo {
            width: 58px;
            height: 58px;
        }

        .auth-mobile-title {
            font-size: 1.15rem;
        }

        .auth-form-heading {
            font-size: 1.35rem;
        }

        .auth-input-group .form-control {
            font-size: 16px; /* Prevents auto-zoom on mobile Safari */
            padding: 11px 12px;
        }
    }

    @media (max-width: 380px) {
        .auth-form-container {
            padding: 20px 14px;
        }

        .auth-form-heading {
            font-size: 1.25rem;
        }
    }
</style>

<script>
    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            btn.setAttribute('aria-label', 'Sembunyikan password');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            btn.setAttribute('aria-label', 'Tampilkan password');
        }
    }
</script>
@endsection