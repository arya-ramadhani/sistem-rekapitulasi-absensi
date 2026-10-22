@extends('layouts.app_admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Data Absensi
                            @isset($kelas)
                                Kelas {{ $kelas->tingkat }} {{ $kelas->prodi_singkatan }} {{ $kelas->nama }}
                            @else
                                Semua Kelas
                            @endisset
                        </h4>
                        <div>
                            <a href="{{ route('admin.absensi.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- Semester Filter -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.data_absensi.index', $kelas->id) }}"
                                    class="d-flex align-items-end gap-3">
                                    <div>
                                        <label for="semester" class="form-label mb-2">Filter Semester</label>
                                        <select name="semester" id="semester" class="form-select">
                                            <option value="">Semua Semester</option>
                                            @for ($i = 1; $i <= 8; $i++)
                                                <option value="{{ $i }}"
                                                    {{ request('semester') == $i ? 'selected' : '' }}>
                                                    Semester {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        @if (request('semester'))
                                            <a href="{{ route('admin.data_absensi.index', $kelas->id) }}"
                                                class="btn btn-secondary ms-2">
                                                <i class="fas fa-times me-1"></i> Reset
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>

                        @if ($absensiData->count() > 0)
                            <!-- Statistics Cards -->
                            <div class="row mb-4 g-3">
                                <div class="col-md-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center py-3">
                                            <h5 class="card-title text-primary mb-1">Total Pertemuan</h5>
                                            <p class="display-5 fw-bold text-primary mb-0">{{ $totalPertemuan }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center py-3">
                                            <h5 class="card-title text-success mb-1">Mata Kuliah</h5>
                                            <p class="display-5 fw-bold text-success mb-0">{{ $totalMataKuliah }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center py-3">
                                            <h5 class="card-title text-info mb-1">Mahasiswa</h5>
                                            <p class="display-5 fw-bold text-info mb-0">{{ $totalMahasiswa }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Attendance Summary Table -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="4%">No</th>
                                            <th class="text-center" width="9%">Tanggal</th>
                                            <th class="text-center" width="6%">Minggu</th>
                                            <th class="text-center" width="7%">Semester</th>
                                            <th width="18%">Mata Kuliah</th>
                                            <th width="15%">Dosen</th>
                                            <th width="18%">Pokok Pembahasan</th>
                                            <th class="text-center" width="6%">Hadir</th>
                                            <th class="text-center" width="6%">Sakit</th>
                                            <th class="text-center" width="6%">Izin</th>
                                            <th class="text-center" width="6%">Alpa</th>
                                            <th class="text-center" width="8%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupedAbsensi as $key => $group)
                                            @php
                                                $firstRecord = $group->first();
                                                $totalHadir = $group->sum(function ($item) {
                                                    $jamHadir = json_decode($item->jam_hadir, true);
                                                    return is_array($jamHadir) ? count($jamHadir) : 0;
                                                });
                                                $totalSakit = $group->sum('sakit');
                                                $totalIzin = $group->sum('izin');
                                                $totalAlpa = $group->sum('alpa');
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($firstRecord->tanggal)->isoFormat('DD/MM/Y') }}
                                                </td>
                                                <td class="text-center">{{ $firstRecord->minggu }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">{{ $firstRecord->semester }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $firstRecord->mata_kuliah }}</strong><br>
                                                    <small class="text-muted">{{ $firstRecord->kode_mk }}</small>
                                                </td>
                                                <td>{{ $firstRecord->dosen }}</td>
                                                <td>{{ Str::limit($firstRecord->pokok_pembahasan, 40) }}</td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-success rounded-pill px-3 py-1">{{ $totalHadir }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-warning rounded-pill px-3 py-1">{{ $totalSakit }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-info rounded-pill px-3 py-1">{{ $totalIzin }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-danger rounded-pill px-3 py-1">{{ $totalAlpa }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="toggleDetail('{{ $key }}')"
                                                        data-bs-toggle="tooltip" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ route('admin.absensi.edit', $firstRecord->id) }}"
                                                        class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip"
                                                        title="Edit Data">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Attendance Details (Hidden by default) -->
                            @foreach ($groupedAbsensi as $key => $group)
                                <div id="detail-{{ $key }}" class="card mb-4" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <i class="fas fa-list-alt me-2"></i>
                                            Detail Absensi - {{ $group->first()->mata_kuliah }}
                                            ({{ \Carbon\Carbon::parse($group->first()->tanggal)->isoFormat('dddd, DD MMMM Y') }})
                                            - Semester {{ $group->first()->semester }}
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="15%">NPM</th>
                                                        <th width="25%">Nama</th>
                                                        <th width="30%">Jam Hadir</th>
                                                        <th class="text-center" width="10%">Sakit</th>
                                                        <th class="text-center" width="10%">Izin</th>
                                                        <th class="text-center" width="10%">Alpa</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($group as $absensi)
                                                        @php
                                                            $jamHadir = json_decode($absensi->jam_hadir, true) ?? [];
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $absensi->npm }}</td>
                                                            <td>{{ $absensi->nama }}</td>
                                                            <td>
                                                                @if (count($jamHadir) > 0)
                                                                    @foreach ($jamHadir as $jam => $status)
                                                                        <span
                                                                            class="badge bg-success me-1 mb-1">{{ $jam }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">Tidak hadir</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($absensi->sakit > 0)
                                                                    <span
                                                                        class="badge bg-warning">{{ $absensi->sakit }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($absensi->izin > 0)
                                                                    <span
                                                                        class="badge bg-info">{{ $absensi->izin }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($absensi->alpa > 0)
                                                                    <span
                                                                        class="badge bg-danger">{{ $absensi->alpa }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                                </div>
                                <h4 class="text-muted mb-3">Belum Ada Data Absensi</h4>
                                <p class="text-muted mb-4">
                                    @if (request('semester'))
                                        Tidak ada data absensi untuk Semester {{ request('semester') }}.
                                    @else
                                        Silakan upload gambar absensi untuk kelas ini.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Toggle detail visibility
        function toggleDetail(key) {
            const detailDiv = document.getElementById('detail-' + key);
            if (detailDiv.style.display === 'none') {
                detailDiv.style.display = 'block';
            } else {
                detailDiv.style.display = 'none';
            }
        }
    </script>

    <style>
        .card-header {
            border-bottom: none;
        }

        .table th {
            font-weight: 600;
            vertical-align: middle;
        }

        .badge {
            font-weight: 500;
        }

        .btn-outline-primary:hover,
        .btn-outline-secondary:hover {
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .d-flex.gap-3 {
                flex-direction: column;
                align-items: stretch !important;
            }

            .d-flex.gap-3>div {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@endsection
