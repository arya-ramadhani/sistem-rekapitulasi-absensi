@extends('layouts.app_admin')

@section('content')
    <div class="container">
        {{-- Upload Gambar Absensi - Lebar Penuh --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-camera"></i> Upload Gambar Absensi
                </h5>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.absensi.scan') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="gambar" class="form-label">
                            <strong>Pilih Gambar Absensi</strong>
                        </label>
                        <input type="file" name="gambar" id="gambar"
                            class="form-control @error('gambar') is-invalid @enderror" accept="image/*" required>
                        @error('gambar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Format yang didukung: JPEG, PNG, JPG, GIF. Maksimal 2MB.
                        </div>
                    </div>

                    {{-- Preview Area --}}
                    <div class="mb-4" id="imagePreview" style="display: none;">
                        <label class="form-label">Preview Gambar:</label>
                        <div class="text-center">
                            <img id="preview" src="" alt="Preview" class="img-fluid rounded border"
                                style="max-height: 400px;">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Proses OCR
                        </button>
                    </div>
                </form>

                {{-- Tips Section --}}
                <div class="mt-5">
                    <h6 class="text-muted">
                        <i class="fas fa-lightbulb"></i> Tips untuk hasil OCR yang baik:
                    </h6>
                    <ul class="text-muted small">
                        <li>Pastikan gambar memiliki pencahayaan yang cukup</li>
                        <li>Teks dalam gambar harus jelas dan tidak buram</li>
                        <li>Hindari bayangan atau refleksi pada dokumen</li>
                        <li>Posisi gambar sebaiknya tegak dan tidak miring</li>
                    </ul>
                </div>
            </div>
        </div>

        <br><br><br>

        {{-- Tabel Daftar Absensi Kelas --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Daftar Absensi Kelas</h5>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="10%">No</th>
                            <th class="text-center">Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelasList as $index => $kelas)
                            <tr style="cursor: pointer;">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td onclick="window.location='{{ route('admin.data_absensi.index', $kelas->id) }}'">
                                {{ $kelas->tingkat }} {{ $kelas->prodi_singkatan }} {{ $kelas->nama }}
                            </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Script untuk preview gambar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('gambar');
            const previewDiv = document.getElementById('imagePreview');
            const previewImg = document.getElementById('preview');

            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewDiv.style.display = 'block';
                    };

                    reader.readAsDataURL(file);
                } else {
                    previewDiv.style.display = 'none';
                }
            });
        });
    </script>
@endsection
