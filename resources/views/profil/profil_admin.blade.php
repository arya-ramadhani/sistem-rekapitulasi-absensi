@extends('profil_admin.app')

@section('content')
<div class="container py-5">
    <!-- Header dengan judul halaman -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary fw-bold">Profil Admin Jurusan</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profil</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert untuk notifikasi sukses -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Kolom kiri - Foto dan informasi dasar -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ $admin->foto ?? asset('images/default-avatar.png') }}" alt="Foto Profil" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <h4 class="card-title">{{ $admin->nama ?? 'Nama Admin' }}</h4>
                    <p class="text-muted">{{ $admin->jabatan ?? 'Jabatan Admin' }}</p>
                    <p class="text-muted"><i class="bi bi-building"></i> {{ $admin->jurusan->nama ?? 'Nama Jurusan' }}</p>
                    <button class="btn btn-primary w-100" id="btnEditProfil">Edit Profil</button>
                </div>
            </div>

            <!-- Social Media dan Kontak -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Kontak</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="me-3"><i class="bi bi-envelope text-primary"></i></span>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <span>{{ $admin->email ?? 'email@example.com' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <span class="me-3"><i class="bi bi-telephone text-primary"></i></span>
                        <div>
                            <small class="text-muted d-block">Telepon</small>
                            <span>{{ $admin->telepon ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-3"><i class="bi bi-geo-alt text-primary"></i></span>
                        <div>
                            <small class="text-muted d-block">Alamat</small>
                            <span>{{ $admin->alamat ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom kanan - Informasi detail dan form edit profil -->
        <div class="col-md-8">
            <!-- Informasi Detail -->
            <div class="card shadow-sm mb-4" id="detailProfil">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Detail</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">NIP/ID</div>
                        <div class="col-md-8">{{ $admin->nip ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Tanggal Lahir</div>
                        <div class="col-md-8">{{ $admin->tanggal_lahir ? date('d F Y', strtotime($admin->tanggal_lahir)) : '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Jenis Kelamin</div>
                        <div class="col-md-8">{{ $admin->jenis_kelamin ?? '-' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-muted">Tentang Saya</div>
                        <div class="col-md-8">{{ $admin->bio ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Form Edit Profil -->
            <div class="card shadow-sm" id="formEditProfil" style="display: none;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-12 text-center">
                                <div class="mb-3 position-relative mx-auto" style="width: 150px;">
                                    <img id="previewFoto" src="{{ $admin->foto ?? asset('images/default-avatar.png') }}" alt="Foto Profil" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                    <label for="foto" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" style="cursor: pointer;">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" name="foto" id="foto" class="d-none" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $admin->nama ?? '') }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nip" class="form-label">NIP/ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip', $admin->nip ?? '') }}" required>
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $admin->email ?? '') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="telepon" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon', $admin->telepon ?? '') }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $admin->tanggal_lahir ?? '') }}">
                                    @error('tanggal_lahir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki" {{ old('jenis_kelamin', $admin->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin', $admin->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" name="jabatan" value="{{ old('jabatan', $admin->jabatan ?? '') }}" required>
                                    @error('jabatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir</label>
                                    <select class="form-select @error('pendidikan_terakhir') is-invalid @enderror" id="pendidikan_terakhir" name="pendidikan_terakhir">
                                        <option value="">Pilih Pendidikan Terakhir</option>
                                        <option value="SMA/SMK" {{ old('pendidikan_terakhir', $admin->pendidikan_terakhir ?? '') == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                                        <option value="D3" {{ old('pendidikan_terakhir', $admin->pendidikan_terakhir ?? '') == 'D3' ? 'selected' : '' }}>D3</option>
                                        <option value="S1" {{ old('pendidikan_terakhir', $admin->pendidikan_terakhir ?? '') == 'S1' ? 'selected' : '' }}>S1</option>
                                        <option value="S2" {{ old('pendidikan_terakhir', $admin->pendidikan_terakhir ?? '') == 'S2' ? 'selected' : '' }}>S2</option>
                                        <option value="S3" {{ old('pendidikan_terakhir', $admin->pendidikan_terakhir ?? '') == 'S3' ? 'selected' : '' }}>S3</option>
                                    </select>
                                    @error('pendidikan_terakhir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2">{{ old('alamat', $admin->alamat ?? '') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="bio" class="form-label">Tentang Saya</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4">{{ old('bio', $admin->bio ?? '') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" id="btnBatalEdit" class="btn btn-secondary">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle antara detail profil dan form edit
        const btnEditProfil = document.getElementById('btnEditProfil');
        const btnBatalEdit = document.getElementById('btnBatalEdit');
        const detailProfil = document.getElementById('detailProfil');
        const formEditProfil = document.getElementById('formEditProfil');

        btnEditProfil.addEventListener('click', function() {
            detailProfil.style.display = 'none';
            formEditProfil.style.display = 'block';
        });

        btnBatalEdit.addEventListener('click', function() {
            formEditProfil.style.display = 'none';
            detailProfil.style.display = 'block';
        });

        // Preview foto profil saat upload
        const inputFoto = document.getElementById('foto');
        const previewFoto = document.getElementById('previewFoto');
        
        inputFoto.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFoto.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush
@endsection