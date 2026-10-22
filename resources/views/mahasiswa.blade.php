@extends('layouts.app_admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>
                Data Mahasiswa Kelas
                @if(isset($kelas))
                    : {{ $kelas->tingkat }} {{ $kelas->prodi_singkatan }} {{ $kelas->nama }}
                @endif
            </h5>
            <div>
                @if(isset($kelas))
                    <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Kelas
                    </a>
                @endif
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMahasiswa">
                    <i class="fas fa-plus"></i> Tambah Mahasiswa
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <small class="text-muted">Jumlah Mahasiswa : {{ $mahasiswas->count() }}</small>
                </div>

            </div>
            
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">NPM</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="mahasiswaTableBody">
                    @forelse ($mahasiswas as $index => $mhs)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $mhs->npm }}</td>
                            <td>{{ $mhs->nama }}</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#modalEditMahasiswa" data-id="{{ $mhs->id }}"
                                    data-npm="{{ $mhs->npm }}" data-nama="{{ $mhs->nama }}"
                                    data-kelas_id="{{ $mhs->kelas_id }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.mahasiswa.destroy', $mhs->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus mahasiswa {{ $mhs->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada mahasiswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Mahasiswa -->
    <div class="modal fade" id="modalTambahMahasiswa" tabindex="-1" aria-labelledby="modalTambahMahasiswaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahMahasiswa" method="POST" action="{{ route('admin.mahasiswa.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahMahasiswaLabel">Tambah Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="npm" class="form-label">NPM</label>
                            <input type="text" class="form-control" id="npm" name="npm" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Mahasiswa</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <!-- Auto-assign ke kelas yang dipilih -->
                        @if(isset($kelas) && $kelas->id)
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                        @else
                            <div class="alert alert-danger">
                                Error: Kelas tidak ditemukan. Silakan kembali ke halaman kelas dan coba lagi.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Mahasiswa -->
    <div class="modal fade" id="modalEditMahasiswa" tabindex="-1" aria-labelledby="modalEditMahasiswaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditMahasiswa" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditMahasiswaLabel">Edit Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-npm" class="form-label">NPM</label>
                            <input type="text" class="form-control" id="edit-npm" name="npm" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-nama" class="form-label">Nama Mahasiswa</label>
                            <input type="text" class="form-control" id="edit-nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" id="edit-kelas_id" name="kelas_id" value="{{ $kelas->id }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tambah Mahasiswa
            const formTambah = document.getElementById('formTambahMahasiswa');
            formTambah.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formTambah);
                
                // Debug: tampilkan data yang akan dikirim
                console.log('FormData contents:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                fetch('{{ route('admin.mahasiswa.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'modalTambahMahasiswa'));
                            modal.hide();
                            formTambah.reset();
                            location.reload();
                        } else {
                            alert(data.message || 'Gagal menambahkan mahasiswa.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (error.errors) {
                            let errorMessage = 'Validation errors:\n';
                            Object.keys(error.errors).forEach(key => {
                                errorMessage += `${key}: ${error.errors[key].join(', ')}\n`;
                            });
                            alert(errorMessage);
                        } else {
                            alert(error.message || 'Terjadi kesalahan saat menambahkan mahasiswa.');
                        }
                    });
            });

            // Edit Mahasiswa
            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('edit-id').value = this.dataset.id;
                    document.getElementById('edit-npm').value = this.dataset.npm;
                    document.getElementById('edit-nama').value = this.dataset.nama;
                    // Kelas sudah fix, tidak perlu diubah dari dataset

                    const form = document.getElementById('formEditMahasiswa');
                    form.action = `/admin/mahasiswa/${this.dataset.id}`;
                });
            });

            const formEdit = document.getElementById('formEditMahasiswa');
            formEdit.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formEdit);
                const id = document.getElementById('edit-id').value;

                fetch(`/admin/mahasiswa/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'modalEditMahasiswa'));
                            modal.hide();
                            formEdit.reset();
                            location.reload();
                        } else {
                            alert(data.message || 'Gagal memperbarui mahasiswa.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengedit mahasiswa.');
                    });
            });
        });
    </script>
@endpush