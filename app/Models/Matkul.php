<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matkul extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'sks_teori', 'sks_praktikum', 'jumlah_sks'];
}