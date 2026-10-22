@extends('layouts.app_admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Mata Kuliah</h5>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMatkul">
                <i class="fas fa-plus"></i> Tambah Mata Kuliah
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Mata Kuliah</th>
                        <th class="text-center">SKS Teori</th>
                        <th class="text-center">SKS Praktikum</th>
                        <th class="text-center">Jumlah SKS</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matkuls as $index => $matkul)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $matkul->kode }}</td>
                            <td>{{ $matkul->nama }}</td>
                            <td class="text-center">{{ $matkul->sks_teori }}</td>
                            <td class="text-center">{{ $matkul->sks_praktikum }}</td>
                            <td class="text-center">{{ $matkul->sks_teori + $matkul->sks_praktikum }}</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#modalEditMatkul" data-id="{{ $matkul->id }}"
                                    data-kode="{{ $matkul->kode }}" data-nama="{{ $matkul->nama }}"
                                    data-sks_teori="{{ $matkul->sks_teori }}"
                                    data-sks_praktikum="{{ $matkul->sks_praktikum }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.matkul.destroy', $matkul->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data mata kuliah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Mata Kuliah -->
    <div class="modal fade" id="modalTambahMatkul" tabindex="-1" aria-labelledby="modalTambahMatkulLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahMatkul" method="POST" action="{{ route('admin.matkul.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahMatkulLabel">Tambah Mata Kuliah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode</label>
                            <input type="text" class="form-control" id="kode" name="kode" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Mata Kuliah</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="sks_teori" class="form-label">SKS Teori</label>
                            <input type="number" class="form-control" id="sks_teori" name="sks_teori" min="0"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="sks_praktikum" class="form-label">SKS Praktikum</label>
                            <input type="number" class="form-control" id="sks_praktikum" name="sks_praktikum"
                                min="0" required>
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

    <!-- Modal Edit Mata Kuliah -->
    <div class="modal fade" id="modalEditMatkul" tabindex="-1" aria-labelledby="modalEditMatkulLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditMatkul" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditMatkulLabel">Edit Mata Kuliah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-kode" class="form-label">Kode</label>
                            <input type="text" class="form-control" id="edit-kode" name="kode" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-nama" class="form-label">Mata Kuliah</label>
                            <input type="text" class="form-control" id="edit-nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-sks_teori" class="form-label">SKS Teori</label>
                            <input type="number" class="form-control" id="edit-sks_teori" name="sks_teori"
                                min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-sks_praktikum" class="form-label">SKS Praktikum</label>
                            <input type="number" class="form-control" id="edit-sks_praktikum" name="sks_praktikum"
                                min="0" required>
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
            // Tombol Edit
            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('edit-id').value = this.dataset.id;
                    document.getElementById('edit-kode').value = this.dataset.kode;
                    document.getElementById('edit-nama').value = this.dataset.nama;
                    document.getElementById('edit-sks_teori').value = this.dataset.sks_teori;
                    document.getElementById('edit-sks_praktikum').value = this.dataset
                    .sks_praktikum;
                    document.getElementById('formEditMatkul').action = `/admin/matkul/${this.dataset.id}`;
                });
            });

            // Submit Tambah
            document.getElementById('formTambahMatkul').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalTambahMatkul'))
                            .hide();
                        form.reset();
                        location.reload();
                    }
                }).catch(() => alert('Gagal menyimpan data.'));
            });

            // Submit Edit
            document.getElementById('formEditMatkul').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                const id = document.getElementById('edit-id').value;

                fetch(`/admin/matkul/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditMatkul'))
                            .hide();
                        form.reset();
                        location.reload();
                    }
                }).catch(() => alert('Gagal memperbarui data.'));
            });
        });
    </script>
@endpush
