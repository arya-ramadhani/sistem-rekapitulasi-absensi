<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nama_lengkap' => 'Admin Utama',
            'email' => 'admin@polman-babel.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nip' => '0000000000',
            'jenis_kelamin' => 'Laki-laki',
            'no_telp' => '081234567890'
        ]);

        User::create([
            'nama_lengkap' => 'Dr. John Doe, M.Kom',
            'email' => 'dosen@polman-babel.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'nip' => '1234567890',
            'jenis_kelamin' => 'Laki-laki',
            'no_telp' => '081234567891'
        ]);
    }
}