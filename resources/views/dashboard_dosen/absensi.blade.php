@extends('layouts.app_dosen')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Data Absensi</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(isset($needsProfile) && $needsProfile)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
            </div>
        @else
            <!-- Filter Form -->
            <form method="GET" action="{{ route('dashboard_dosen.absensi') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="kelas" class="form-label">Kelas</label>
                        <select name="kelas" id="kelas" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" {{ $selectedKelas == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }} - {{ $kelas->matkul->nama_matkul ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('dashboard_dosen.absensi') }}" class="btn btn-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Export Button -->
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-file-export me-2"></i>Export Data
                </button>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal & Waktu</th>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                            <th>Kelas</th>
                            <th>Mata Kuliah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensi as $item)
                            <tr>
                                <td>{{ $loop->iteration + $absensi->firstItem() - 1 }}</td>
                                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $item->mahasiswa->nama ?? 'N/A' }}</td>
                                <td>{{ $item->mahasiswa->nim ?? 'N/A' }}</td>
                                <td>{{ $item->kelas->nama_kelas ?? 'N/A' }}</td>
                                <td>{{ $item->kelas->matkul->nama_matkul ?? 'N/A' }}</td>
                                <td>
                                    @if($item->status == 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($item->status == 'izin')
                                        <span class="badge bg-warning">Izin</span>
                                    @elseif($item->status == 'sakit')
                                        <span class="badge bg-info">Sakit</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data absensi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end">
                {{ $absensi->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard_dosen.absensi.export') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Export Data Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="kelas" value="{{ $selectedKelas }}">
                    <input type="hidden" name="tanggal_mulai" value="{{ $tanggalMulai }}">
                    <input type="hidden" name="tanggal_selesai" value="{{ $tanggalSelesai }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Format Export</label>
                        <select name="format" class="form-select" required>
                            <option value="">Pilih Format</option>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection