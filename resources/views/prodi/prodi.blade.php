@extends('layouts.app_admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Prodi</h5>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahProdi">
                <i class="fas fa-plus"></i> Tambah Prodi
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover ">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">Program Prodi</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prodis as $index => $prodi)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $prodi->program_studi }}</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#modalEditProdi" data-id="{{ $prodi->id }}"
                                    data-program_studi="{{ $prodi->program_studi }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.prodi.destroy', $prodi->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada data prodi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Prodi -->
    <div class="modal fade" id="modalTambahProdi" tabindex="-1" aria-labelledby="modalTambahProdiLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahProdi" method="POST" action="{{ route('admin.prodi.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahProdiLabel">Tambah Prodi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="program_studi" class="form-label">Program Prodi</label>
                            <input type="text" class="form-control" id="program_studi" name="program_studi" required>
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

    <!-- Modal Edit Prodi -->
    <div class="modal fade" id="modalEditProdi" tabindex="-1" aria-labelledby="modalEditProdiLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditProdi" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditProdiLabel">Edit Prodi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-program_studi" class="form-label">Program Prodi</label>
                            <input type="text" class="form-control" id="edit-program_studi" name="program_studi" required>
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
            // Tambah Prodi
            const formTambah = document.getElementById('formTambahProdi');
            formTambah.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formTambah);

                fetch('{{ route('admin.prodi.store') }}', {
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
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'modalTambahProdi'));
                            modal.hide();
                            formTambah.reset();
                            location.reload();
                        }
                    })
                    .catch(error => alert('Terjadi kesalahan saat menambahkan.'));
            });

            // Edit Prodi
            document.querySelectorAll('.btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('edit-id').value = this.dataset.id;
                    document.getElementById('edit-program_studi').value = this.dataset.program_studi;

                    const form = document.getElementById('formEditProdi');
                    form.action = `/admin/prodi/${this.dataset.id}`;
                });
            });

            const formEdit = document.getElementById('formEditProdi');
            formEdit.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formEdit);
                const id = document.getElementById('edit-id').value;

                fetch(`/admin/prodi/${id}`, {
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
                                'modalEditProdi'));
                            modal.hide();
                            formEdit.reset();
                            location.reload();
                        }
                    })
                    .catch(error => alert('Terjadi kesalahan saat mengedit.'));
            });
        });
    </script>
@endpush
