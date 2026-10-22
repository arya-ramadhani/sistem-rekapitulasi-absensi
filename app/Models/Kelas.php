<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelass';
    protected $fillable = [
        'nama',
        'tingkat',
        'prodi_id', // atau field yang menghubungkan ke prodi
        'prodi_singkatan',
        // field lainnya
    ];

    // Optional: helper untuk menampilkan label
    public function getLabelAttribute()
    {
        return "{$this->tingkat} {$this->prodi} {$this->nama}";
    }


    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }


    // app/Models/Kelas.php
    public function getProdiSingkatanAttribute()
    {
        $singkatan = [
            'D-IV Teknologi Rekayasa Perangkat Lunak' => 'TRPL',
            'D-IV Bisnis Digital' => 'BD'
        ];

        return $singkatan[$this->prodi] ?? $this->prodi;
    }


    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    /**
     * Relasi ke Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    /**
     * Relasi ke Absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
