@extends('layouts.app_admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Pengguna</h5>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                <i class="fas fa-plus"></i> Tambah Pengguna
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Role</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $index => $user)
                        <tr class="{{ $user->role == 'admin' ?: '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                {{ $user->nama_lengkap }}
                                @if ($user->role == 'admin')
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">{{ $user->role }}</td>
                            <td class="text-center">
                                <!-- Tombol Edit dan Hapus tetap sama -->
                                <a href="#" class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#modalEditPengguna" data-id="{{ $user->id }}"
                                    data-nama_lengkap="{{ $user->nama_lengkap }}" data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.pengguna.destroy', $user->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                        {{ Auth::user()->id == $user->id ? 'disabled' : '' }}>
                                        Hapus
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahPenggunaLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahPengguna" method="POST" action="{{ route('admin.pengguna.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahPenggunaLabel">Tambah Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="dosen">Dosen</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pengguna -->
    <div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-labelledby="modalEditPenggunaLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditPengguna" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditPenggunaLabel">Edit Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit-nama_lengkap" name="nama_lengkap"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-password" class="form-label">Password (Kosongkan jika tidak ingin
                                mengubah)</label>
                            <input type="password" class="form-control" id="edit-password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit-role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="dosen">Dosen</option>
                            </select>
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
            // Form Tambah
            const formTambah = document.getElementById('formTambahPengguna');
            formTambah.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formTambah);

                fetch('{{ route('admin.pengguna.store') }}', {
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
                            return response.json().then(errData => Promise.reject(errData));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'modalTambahPengguna'));
                            modal.hide();
                            formTambah.reset();
                            location.reload();
                        }
                    })
                    .catch(error => {
                        if (error.errors) {
                            let messages = '';
                            for (const field in error.errors) {
                                messages += error.errors[field].join(', ') + '\n';
                            }
                            alert('Validasi gagal:\n' + messages);
                        } else {
                            alert('Terjadi kesalahan.');
                        }
                    });
            });

            // Saat tombol edit diklik
            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('edit-id').value = this.dataset.id;
                    document.getElementById('edit-nama_lengkap').value = this.dataset.nama_lengkap;
                    document.getElementById('edit-email').value = this.dataset.email;
                    document.getElementById('edit-role').value = this.dataset.role;

                    // Ubah action form sesuai ID pengguna
                    const form = document.getElementById('formEditPengguna');
                    form.action = `/admin/pengguna/${this.dataset.id}`;
                });
            });

            // Submit form edit via fetch
            const formEdit = document.getElementById('formEditPengguna');
            formEdit.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formEdit);
                const id = document.getElementById('edit-id').value;

                fetch(`/admin/pengguna/${id}`, {
                        method: 'POST', // Laravel perlu POST + method spoofing
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errData => Promise.reject(errData));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'modalEditPengguna'));
                            modal.hide();
                            formEdit.reset();
                            location.reload();
                        }
                    })
                    .catch(error => {
                        if (error.errors) {
                            let messages = '';
                            for (const field in error.errors) {
                                messages += error.errors[field].join(', ') + '\n';
                            }
                            alert('Validasi gagal:\n' + messages);
                        } else {
                            alert('Terjadi kesalahan.');
                        }
                    });
            });
        });
    </script>
@endpush
