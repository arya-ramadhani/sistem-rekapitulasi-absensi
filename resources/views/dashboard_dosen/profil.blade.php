# Perbaikan Update Profile Dosen

## 1. Update Form di profile.blade.php (resources/views/dashboard_dosen/profile.blade.php)

```blade
@extends('layouts.app_dosen')

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profil Dosen</h5>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (isset($needsProfile) && $needsProfile)
                <div class="alert alert-warning">
                    <i
                        class="fas fa-exclamation-triangle me-2"></i>{{ $message ?? 'Silakan lengkapi profil Anda dengan mengisi NIP terlebih dahulu.' }}
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard_dosen.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        @if ($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="rounded-circle mb-3"
                                width="150" height="150" style="object-fit: cover;" alt="Foto Profil">
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 150px; height: 150px;">
                                <span style="font-size: 64px;">{{ substr($user->nama_lengkap, 0, 1) }}</span>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Foto Profil</label>
                            <input type="file" class="form-control @error('profile_photo') is-invalid @enderror"
                                id="profile_photo" name="profile_photo" accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">Format: JPG, PNG (Max: 2MB)</small>
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror"
                                    id="nama_lengkap" name="nama_lengkap"
                                    value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                                @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nip" class="form-label">NIP @if (!$user->nip)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip"
                                    name="nip" value="{{ old('nip', $user->nip) }}"
                                    @if (!$user->nip) required @endif>
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="no_telp" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control @error('no_telp') is-invalid @enderror"
                                    id="no_telp" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}"
                                    placeholder="08xxxxxxxxxx">
                                @error('no_telp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin"
                                    name="jenis_kelamin">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki"
                                        {{ old('jenis_kelamin', $user->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>
                                        Laki-laki
                                    </option>
                                    <option value="Perempuan"
                                        {{ old('jenis_kelamin', $user->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>
                                        Perempuan
                                    </option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                    id="tanggal_lahir" name="tanggal_lahir"
                                    value="{{ old('tanggal_lahir', $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '') }}"
                                    max="{{ date('Y-m-d', strtotime('-17 years')) }}">
                                @error('tanggal_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="agama" class="form-label">Agama</label>
                            <select class="form-select @error('agama') is-invalid @enderror" id="agama"
                                name="agama">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" {{ old('agama', $user->agama) == 'Islam' ? 'selected' : '' }}>Islam
                                </option>
                                <option value="Kristen" {{ old('agama', $user->agama) == 'Kristen' ? 'selected' : '' }}>
                                    Kristen</option>
                                <option value="Katolik" {{ old('agama', $user->agama) == 'Katolik' ? 'selected' : '' }}>
                                    Katolik</option>
                                <option value="Hindu" {{ old('agama', $user->agama) == 'Hindu' ? 'selected' : '' }}>Hindu
                                </option>
                                <option value="Buddha" {{ old('agama', $user->agama) == 'Buddha' ? 'selected' : '' }}>
                                    Buddha</option>
                                <option value="Konghucu" {{ old('agama', $user->agama) == 'Konghucu' ? 'selected' : '' }}>
                                    Konghucu</option>
                            </select>
                            @error('agama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3"
                                placeholder="Alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('dashboard_dosen.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Preview image before upload
            document.getElementById('profile_photo').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.querySelector('.rounded-circle');
                        if (img) {
                            img.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        </script>
    @endpush
@endsection
