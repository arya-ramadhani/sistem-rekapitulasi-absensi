@extends('layouts.app_admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Dosen</h5>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahDosen">
                <i class="fas fa-plus"></i> Tambah Dosen
            </a>

        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th class="text-center">NIP</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Program Studi</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dosens as $index => $dosen)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $dosen->nip }}</td>
                            <td>{{ $dosen->nama }}</td>
                            <td>{{ $dosen->program_studi }}</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#modalEditDosen" data-id="{{ $dosen->id }}"
                                    data-nip="{{ $dosen->nip }}" data-nama="{{ $dosen->nama }}"
                                    data-program_studi="{{ $dosen->program_studi }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.dosen.destroy', $dosen->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data dosen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahDosen" tabindex="-1" aria-labelledby="modalTambahDosenLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahDosen" method="POST" action="{{ route(name: 'admin.dosen.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahDosenLabel">Tambah Dosen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="number" class="form-control" id="nip" name="nip" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="program_studi" class="form-label">Program Studi</label>
                            <select class="form-select" id="program_studi" name="program_studi" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->program_studi }}">{{ $prodi->program_studi }}</option>
                                @endforeach
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formTambahDosen');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                fetch('{{ route('admin.dosen.store') }}', {
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
                                'modalTambahDosen'));
                            modal.hide();
                            form.reset();
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

<!-- Modal Edit Dosen -->
<div class="modal fade" id="modalEditDosen" tabindex="-1" aria-labelledby="modalEditDosenLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditDosen" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditDosenLabel">Edit Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-3">
                        <label for="edit-nip" class="form-label">NIP</label>
                        <input type="text" class="form-control" id="edit-nip" name="nip" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="edit-nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-program_studi" class="form-label">Program Studi</label>
                        <select class="form-select" id="edit-program_studi" name="program_studi" required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->program_studi }}">{{ $prodi->program_studi }}</option>
                            @endforeach
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Saat tombol edit diklik
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit-id').value = this.dataset.id;
                document.getElementById('edit-nip').value = this.dataset.nip;
                document.getElementById('edit-nama').value = this.dataset.nama;
                document.getElementById('edit-program_studi').value = this.dataset
                    .program_studi;

                // Ubah action form sesuai ID dosen
                const form = document.getElementById('formEditDosen');
                form.action = `/admin/dosen/${this.dataset.id}`;
            });
        });



        // Submit form edit via fetch
        const formEdit = document.getElementById('formEditDosen');
        formEdit.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(formEdit);
            const id = document.getElementById('edit-id').value;

            fetch(`/admin/dosen/${id}`, {
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
                            'modalEditDosen'));
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
