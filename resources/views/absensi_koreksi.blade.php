@extends('layouts.app_admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-clipboard-check"></i> Hasil OCR Absensi - Koreksi Data
                        </h3>
                    </div>
                    <div class="card-body">
                        @if (isset($error))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> {{ $error }}
                            </div>
                        @endif

                        <form action="{{ route('admin.absensi.store') }}" method="POST" id="absensiForm">
                            @csrf

                            {{-- Informasi Umum --}}
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Informasi Perkuliahan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="kode_mk">Mata Kuliah/Kode MK:</label>
                                            <input type="text" name="mata_kuliah_display" class="form-control"
                                                value="{{ $mata_kuliah_display ?? '' }}" readonly>
                                            <input type="hidden" name="mata_kuliah" value="{{ $mata_kuliah ?? '' }}">
                                            <input type="hidden" name="kode_mk" value="{{ $kode_mk ?? '' }}">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Kelas:</label>
                                            <input name="kelas" class="form-control" value="{{ $kelas }}" required>
                                            <input type="hidden" name="kelas_id" value="{{ $kelas_id ?? '' }}">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Semester:</label>
                                            <select name="semester" class="form-select" required>
                                                <option value="" disabled {{ !isset($semester) ? 'selected' : '' }}>
                                                    Pilih Semester</option>
                                                @for ($i = 1; $i <= 8; $i++)
                                                    <option value="{{ $i }}"
                                                        {{ old('semester', $semester ?? '') == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                            @error('semester')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Pengajar:</label>
                                            <input name="dosen" class="form-control" value="{{ $dosen }}"
                                                required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Tanggal:</label>
                                            <input type="date" name="tanggal" class="form-control"
                                                value="{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('Y-m-d') : '' }}"
                                                required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Minggu Program:</label>
                                            <input name="minggu" class="form-control" value="{{ $minggu }}"
                                                required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Pokok Pembahasan:</label>
                                            <input name="pokok_pembahasan" class="form-control"
                                                value="{{ $pokok_pembahasan ?? '' }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Absensi Mahasiswa --}}
                            <div class="card">
                                <div
                                    class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Daftar Absensi Mahasiswa</h5>
                                        <small class="text-light">Koreksi data jika diperlukan sebelum menyimpan</small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-light" id="btnManageJam">
                                            <i class="fas fa-clock"></i> Kelola Jam
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover mb-0">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th width="5%" class="text-center">No</th>
                                                    <th width="10%" class="text-center">NPM</th>
                                                    <th width="20%" class="text-center">Nama Mahasiswa</th>
                                                    @foreach ($jamListFiltered as $jam)
                                                        <th width="10%" class="text-center jam-column"
                                                            data-jam="{{ $jam }}">
                                                            {{ $jam }}
                                                        </th>
                                                    @endforeach
                                                    <th width="5%" class="text-center">Sakit</th>
                                                    <th width="5%" class="text-center">Izin</th>
                                                    <th width="5%" class="text-center">Alpa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($npmList as $index => $npm)
                                                    <tr>
                                                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                                                        <td>
                                                            <input type="text"
                                                                name="mahasiswa[{{ $index }}][npm]"
                                                                class="form-control form-control-sm"
                                                                value="{{ $npm }}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="mahasiswa[{{ $index }}][nama]"
                                                                class="form-control form-control-sm"
                                                                value="{{ $namaList[$npm] ?? '' }}"
                                                                placeholder="Nama mahasiswa">
                                                        </td>
                                                        @foreach ($jamListFiltered as $jam)
                                                            <td class="text-center">
                                                                <select
                                                                    name="mahasiswa[{{ $index }}][absensi][{{ $jam }}]"
                                                                    class="form-select form-select-sm text-center">
                                                                    <option value="✓"
                                                                        {{ ($absenOCR[$npm][$jam] ?? '') === '✓' ? 'selected' : '' }}>
                                                                        ✓</option>
                                                                    <option value="-"
                                                                        {{ ($absenOCR[$npm][$jam] ?? '') === '-' ? 'selected' : '' }}>
                                                                        -</option>
                                                                </select>
                                                            </td>
                                                        @endforeach
                                                        <td>
                                                            <input type="number"
                                                                name="mahasiswa[{{ $index }}][sakit]"
                                                                class="form-control form-control-sm text-center"
                                                                value="{{ $sakitOCR[$npm] ?? 0 }}" min="0"
                                                                max="8">
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="mahasiswa[{{ $index }}][izin]"
                                                                class="form-control form-control-sm text-center"
                                                                value="{{ $izinOCR[$npm] ?? 0 }}" min="0"
                                                                max="8">
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="mahasiswa[{{ $index }}][alpa]"
                                                                class="form-control form-control-sm text-center"
                                                                value="{{ $alpaOCR[$npm] ?? 0 }}" min="0"
                                                                max="8">
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ 6 + count($jamListFiltered) }}"
                                                            class="text-center text-muted py-4">
                                                            <i class="fas fa-info-circle"></i> Tidak ada data mahasiswa
                                                            yang terdeteksi
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-warning me-2" onclick="resetForm()">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Simpan Absensi
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Kelola Jam --}}
    <div class="modal fade" id="jamModal" tabindex="-1" aria-labelledby="jamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="jamModalLabel">Kelola Jam Perkuliahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Jam yang Ingin Ditambahkan:</label>
                        <div class="jam-list">
                            @php
                                $allJamKuliah = [
                                    '07.30-08.20',
                                    '08.20-09.10',
                                    '09.30-10.20',
                                    '10.20-11.10',
                                    '11.10-12.00',
                                    '13.00-13.50',
                                    '13.50-14.40',
                                    '14.40-15.30',
                                ];
                            @endphp

                            @foreach ($allJamKuliah as $jam)
                                @if (!in_array($jam, $jamListFiltered))
                                    <div class="form-check mb-2">
                                        <input class="form-check-input jam-checkbox" type="checkbox"
                                            value="{{ $jam }}"
                                            id="jam-{{ str_replace(['.', '-'], '', $jam) }}">
                                        <label class="form-check-label"
                                            for="jam-{{ str_replace(['.', '-'], '', $jam) }}">
                                            {{ $jam }}
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnAddJam">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript untuk interaktivitas --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculation untuk jam hadir berdasarkan tanda centang
            const form = document.getElementById('absensiForm');
            const btnManageJam = document.getElementById('btnManageJam');
            const btnAddJam = document.getElementById('btnAddJam');
            const jamModal = new bootstrap.Modal(document.getElementById('jamModal'));

            // Event handler untuk tombol Kelola Jam
            btnManageJam.addEventListener('click', function() {
                jamModal.show();
            });

            // Event handler untuk tombol Tambahkan di modal
            btnAddJam.addEventListener('click', function() {
                const selectedJams = Array.from(document.querySelectorAll('.jam-checkbox:checked')).map(
                    cb => cb.value);

                if (selectedJams.length === 0) {
                    alert('Silakan pilih minimal satu jam untuk ditambahkan');
                    return;
                }

                addSelectedJams(selectedJams);
                jamModal.hide();
            });

            // Setup event handlers untuk tombol hapus jam
            setupRemoveJamButtons();

            if (form) {
                // Validasi form sebelum submit
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    let missingFields = [];

                    // Validasi field wajib
                    const requiredFields = ['mata_kuliah', 'kode_mk', 'dosen', 'tanggal', 'minggu',
                        'kelas', 'semester'
                    ];

                    requiredFields.forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (!input || !input.value.trim()) {
                            valid = false;
                            missingFields.push(field.replace('_', ' ').toUpperCase());
                            if (input) input.classList.add('is-invalid');
                        } else {
                            if (input) input.classList.remove('is-invalid');
                        }
                    });

                    if (!valid) {
                        e.preventDefault();
                        alert('Harap lengkapi field berikut: ' + missingFields.join(', '));
                        return false;
                    }

                    // Konfirmasi sebelum menyimpan
                    if (!confirm('Apakah Anda yakin ingin menyimpan data absensi ini?')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });

        // Fungsi untuk menambahkan jam yang dipilih
        function addSelectedJams(selectedJams) {
            const tbody = document.querySelector('tbody');
            const thead = document.querySelector('thead tr');
            const npmList = @json($npmList);

            // Tambahkan header kolom untuk setiap jam baru
            selectedJams.forEach(jam => {
                // Tambahkan header kolom
                const newHeader = document.createElement('th');
                newHeader.width = "10%";
                newHeader.className = "text-center jam-column";
                newHeader.setAttribute('data-jam', jam);
                newHeader.innerHTML = `
                    ${jam}
                    <button type="button" class="btn btn-sm btn-danger btn-remove-jam" 
                        data-jam="${jam}" title="Hapus Jam">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                // Sisipkan sebelum kolom Sakit
                const lastCols = thead.querySelectorAll('th:nth-last-child(-n+3)');
                thead.insertBefore(newHeader, lastCols[0]);

                // Tambahkan sel untuk setiap mahasiswa di kolom baru
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((row, rowIndex) => {
                    if (rowIndex < npmList.length) {
                        const newCell = document.createElement('td');
                        newCell.className = 'text-center';
                        newCell.innerHTML = `
                            <select
                                name="mahasiswa[${rowIndex}][absensi][${jam}]"
                                class="form-select form-select-sm text-center">
                                <option value="✓">✓</option>
                                <option value="-" selected>-</option>
                            </select>
                        `;

                        // Sisipkan sebelum kolom Sakit
                        const lastCells = row.querySelectorAll('td:nth-last-child(-n+3)');
                        row.insertBefore(newCell, lastCells[0]);
                    }
                });
            });

            // Perbarui event handler untuk tombol hapus
            setupRemoveJamButtons();

            // Perbarui tampilan modal (hapus jam yang sudah ditambahkan)
            updateJamModalDisplay();
        }

        // Fungsi untuk mengatur event handler tombol hapus jam
        function setupRemoveJamButtons() {
            document.querySelectorAll('.btn-remove-jam').forEach(btn => {
                btn.addEventListener('click', function() {
                    const jam = this.getAttribute('data-jam');
                    removeJam(jam);
                });
            });
        }

        // Fungsi untuk menghapus kolom jam
        function removeJam(jam) {
            if (!confirm(`Apakah Anda yakin ingin menghapus jam ${jam}?`)) {
                return;
            }

            // Hapus kolom dari header
            const jamHeaders = document.querySelectorAll(`th.jam-column[data-jam="${jam}"]`);
            jamHeaders.forEach(header => {
                const columnIndex = Array.from(header.parentNode.children).indexOf(header);

                // Hapus sel yang sesuai dari setiap baris
                const tbody = document.querySelector('tbody');
                tbody.querySelectorAll('tr').forEach(row => {
                    const cell = row.children[columnIndex];
                    if (cell) {
                        row.removeChild(cell);
                    }
                });

                // Hapus header
                header.parentNode.removeChild(header);
            });

            // Perbarui tampilan modal (tambahkan jam yang dihapus)
            updateJamModalDisplay();
        }

        // Fungsi untuk memperbarui tampilan modal jam
        function updateJamModalDisplay() {
            const jamList = document.querySelector('.jam-list');
            const currentJams = Array.from(document.querySelectorAll('th.jam-column')).map(th => th.getAttribute(
                'data-jam'));

            // Daftar semua jam kuliah
            const allJamKuliah = [
                '07.30-08.20', '08.20-09.10', '09.30-10.20', '10.20-11.10',
                '11.10-12.00', '13.00-13.50', '13.50-14.40', '14.40-15.30'
            ];

            // Bersihkan list jam di modal
            jamList.innerHTML = '';

            // Tambahkan hanya jam yang belum ditampilkan
            allJamKuliah.forEach(jam => {
                if (!currentJams.includes(jam)) {
                    jamList.innerHTML += `
                        <div class="form-check mb-2">
                            <input class="form-check-input jam-checkbox" type="checkbox" value="${jam}" 
                                id="jam-${jam.replace(/[.-]/g, '')}">
                            <label class="form-check-label" for="jam-${jam.replace(/[.-]/g, '')}">
                                ${jam}
                            </label>
                        </div>
                    `;
                }
            });
        }

        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset semua perubahan?')) {
                location.reload();
            }
        }

        // Auto-focus pada field kosong
        window.addEventListener('load', function() {
            const emptyFields = document.querySelectorAll('input[value=""], textarea:empty');
            if (emptyFields.length > 0) {
                emptyFields[0].focus();
            }
        });
    </script>

    <style>
        .table th {
            vertical-align: middle;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .form-control-sm,
        .form-select-sm {
            font-size: 0.875rem;
        }

        .table-responsive {
            border-radius: 0.375rem;
        }

        .card-header h5 {
            font-weight: 600;
        }

        .btn {
            font-weight: 500;
        }

        pre {
            max-height: 300px;
            overflow-y: auto;
            font-size: 0.75rem;
        }

        .btn-remove-jam {
            padding: 0.1rem 0.3rem;
            font-size: 0.7rem;
            margin-left: 5px;
        }

        .jam-column {
            position: relative;
        }
    </style>
@endsection
