@extends('layouts.app_admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Kelas</h5>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
                <i class="fas fa-plus"></i> Tambah Kelas
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelasList as $index => $kelas)
                        <tr style="cursor: pointer;">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td onclick="window.location='{{ route('admin.kelas.mahasiswa', $kelas->id) }}'">
                                {{ $kelas->tingkat }} {{ $kelas->prodi_singkatan }} {{ $kelas->nama }}
                            </td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#modalEditKelas" data-id="{{ $kelas->id }}"
                                    data-prodi="{{ $kelas->prodi }}" data-tingkat="{{ $kelas->tingkat }}"
                                    data-nama="{{ $kelas->nama }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.kelas.destroy', $kelas->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Kelas -->
    <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahKelas" method="POST" action="{{ route('admin.kelas.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahKelasLabel">Tambah Kelas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tingkat</label>
                            <input type="number" class="form-control" name="tingkat" required>
                        </div>
                        <div class="mb-3">
                            <label for="prodi" class="form-label">Program Studi</label>
                            <select class="form-select" id="prodi" name="prodi" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->program_studi }}">{{ $prodi->program_studi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <input type="string" class="form-control" name="nama">
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

    <!-- Modal Edit Kelas -->
    <div class="modal fade" id="modalEditKelas" tabindex="-1" aria-labelledby="modalEditKelasLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditKelas" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditKelasLabel">Edit Kelas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label class="form-label">Tingkat</label>
                            <input type="number" class="form-control" id="edit-tingkat" name="tingkat" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prodi</label>
                            <select class="form-select" id="edit-prodi" name="prodi" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->program_studi }}">{{ $prodi->program_studi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Kelas</label>
                            <input type="string" class="form-control" id="edit-nama" name="nama">
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
            const tambahForm = document.getElementById('formTambahKelas');
            const editForm = document.getElementById('formEditKelas');

            // === SUBMIT TAMBAH KELAS ===
            if (tambahForm) {
                tambahForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(tambahForm);

                    fetch('{{ route('admin.kelas.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                bootstrap.Modal.getInstance(document.getElementById('modalTambahKelas'))
                                    .hide();
                                tambahForm.reset();
                                location.reload();
                            } else {
                                alert(data.message || 'Gagal menambahkan kelas.');
                            }
                        })
                        .catch(() => {
                            alert('Terjadi kesalahan saat menambahkan kelas.');
                        });
                });
            }

            // === TOMBOL EDIT ===
            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('edit-id').value = this.dataset.id;
                    document.getElementById('edit-prodi').value = this.dataset.prodi;
                    document.getElementById('edit-tingkat').value = this.dataset.tingkat;
                    document.getElementById('edit-nama').value = this.dataset.nama;

                    editForm.action = `/admin/kelas/${this.dataset.id}`;
                });
            });

            // === SUBMIT EDIT KELAS ===
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const id = document.getElementById('edit-id').value;
                    const formData = new FormData(editForm);
                    formData.append('_method', 'PUT');

                    fetch(`/admin/kelas/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                bootstrap.Modal.getInstance(document.getElementById('modalEditKelas'))
                                    .hide();
                                editForm.reset();
                                location.reload();
                            } else {
                                alert(data.message || 'Gagal memperbarui kelas.');
                            }
                        })
                        .catch(() => {
                            alert('Terjadi kesalahan saat mengedit kelas.');
                        });
                });
            }
        });
    </script>
@endpush
