@extends('layouts.app')

@section('content')
<div class="auth-container-wide">
    <div class="row g-0 auth-card-wide">
        <!-- Left Side - Branding with Full Image Background -->
        <div class="col-lg-7 auth-left">
            <div class="auth-overlay"></div>
            <div class="auth-content">
                <div class="logo-animated">
                    <img src="{{ asset('images/logoPolman.png') }}" alt="POLMAN BABEL" class="img-fluid">
                </div>
                <h1 class="auth-title">Sistem Rekapitulasi Absensi</h1>
                <p class="auth-subtitle">POLITEKNIK MANUFAKTUR NEGERI BANGKA BELITUNG</p>
            </div>
        </div>
        
        <!-- Right Side - Form -->
        <div class="col-lg-5 auth-right">
            <div class="auth-form-container">
                <h2 class="text-center mb-1">Selamat Datang</h2>
                <br>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email Anda">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password" placeholder="Password">
                            <span class="input-group-text toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        {{-- <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Ingat Saya</label>
                        </div> --}}
                        {{-- @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-primary small">Lupa Password?</a>
                        @endif --}}
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>

                    @if (Route::has('register'))
                        <div class="text-center mt-3">
                            <p class="small mb-0">Belum punya akun? <a href="{{ route('register') }}" class="text-primary fw-bold">Daftar Sekarang</a></p>
                        </div>
                    @endif
                </form>

                <div class="auth-footer text-center mt-4">
                    <p class="small">&copy; 2025 POLMAN BABEL. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #3498db;
        --text: #2c3e50;
        --text-light: #7f8c8d;
        --white: #ffffff;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Perubahan hanya pada lebar container */
    .auth-container-wide {
        width: 100%;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .auth-card-wide {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        height: 600px; /* Tinggi tetap sama */
        background: white;
        width: 90%; /* Lebar diubah menjadi 90% */
        max-width: 1400px; /* Lebar maksimum diperbesar */
    }

    /* Left Side - Branding */
    .auth-left {
        background-image: url('{{ asset('images/gambar_polman.jpg') }}');
        background-size: cover;
        background-position: center;
        padding: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        color: white;
    }

    .auth-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
    }

    .auth-content {
        position: relative;
        z-index: 2;
        padding: 40px;
        text-align: center;
        width: 100%;
    }

    .auth-title {
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 15px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .auth-subtitle {
        font-weight: 600;
        font-size: 1.1rem;
        opacity: 0.9;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Logo Animasi */
    .logo-animated {
        width: 120px;
        height: 120px;
        margin: 0 auto 30px;
        animation: floatRotate 4s infinite ease-in-out;
        filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.3));
    }

    /* Right Side - Form */
    .auth-right {
        padding: 0;
        display: flex;
        align-items: center;
    }

    .auth-form-container {
        padding: 40px;
        width: 100%;
    }

    /* Style Form */
    h2 {
        font-weight: 700;
        color: var(--text);
        font-size: 1.8rem;
    }

    .form-label {
        font-weight: 700;
        color: var(--text);
        font-size: 0.95rem;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #d1d3e2;
        padding: 12px 15px;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .form-control:focus {
        border-color: var(--secondary);
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
        border-radius: 8px;
        padding: 12px 20px;
        font-weight: 700;
        width: 100%;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .btn-primary:hover {
        background-color: #1a252f;
        transform: translateY(-2px);
    }

    .input-group-text {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .toggle-password {
        cursor: pointer;
        background-color: #f8f9fa;
    }

    /* Animasi */
    @keyframes floatRotate {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        25% { transform: translateY(-10px) rotate(5deg); }
        50% { transform: translateY(0) rotate(0deg); }
        75% { transform: translateY(-5px) rotate(-5deg); }
    }

    @media (max-width: 992px) {
        .auth-card-wide {
            height: auto;
            width: 95%; /* Lebar disesuaikan untuk tablet */
        }
        
        .auth-left {
            padding: 40px 20px;
            height: 300px;
        }
        
        .auth-right {
            padding: 40px 20px;
        }
    }

    @media (max-width: 768px) {
        .auth-card-wide {
            width: 100%; /* Lebar penuh untuk mobile */
        }
        
        .auth-left {
            display: none;
        }
        
        .auth-right {
            width: 100%;
        }
    }
</style>

<script>
    // Toggle visibility password
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endsection