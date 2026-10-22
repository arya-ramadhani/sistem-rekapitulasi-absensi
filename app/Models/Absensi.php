<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'npm',
        'nama',
        'mata_kuliah',
        'kode_mk',
        'dosen',
        'tanggal',
        'minggu',
        'semester',
        'kelas',
        'kelas_id',
        'pokok_pembahasan',
        'jam_hadir',
        'sakit',
        'izin',
        'alpa'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'detail_absensi' => 'array', // Cast JSON ke array
    ];

    // Relasi ke Mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    // Relasi ke Dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Accessor untuk mendapatkan total jam hadir
    public function getTotalJamHadirAttribute()
    {
        if ($this->detail_absensi) {
            return collect($this->detail_absensi)->filter(function ($status) {
                return $status === '✓';
            })->count();
        }
        return $this->jam_hadir ?? 0;
    }

    // Accessor untuk format tanggal Indonesia
    public function getTanggalFormatAttribute()
    {
        return $this->tanggal ? $this->tanggal->format('d/m/Y') : '';
    }

    // Scope untuk filter berdasarkan periode
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // Scope untuk filter berdasarkan mahasiswa
    public function scopeByMahasiswa($query, $mahasiswaId)
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }

    // Scope untuk filter berdasarkan mata kuliah
    public function scopeByMataKuliah($query, $mataKuliah)
    {
        return $query->where('mata_kuliah', 'like', '%' . $mataKuliah . '%');
    }
}
