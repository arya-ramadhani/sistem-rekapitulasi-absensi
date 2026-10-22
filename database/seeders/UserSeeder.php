<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'nama_lengkap' => 'Administrator',
            'email' => 'admin@polmanbabel.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nip' => '1234567890'
        ]);

        // Create dosen user
        User::create([
            'nama_lengkap' => 'Dr. Budi Santoso',
            'email' => 'budi.santoso@polmanbabel.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'nip' => '1987654321'
        ]);

        // Create more dosen users
        User::factory(5)->create(['role' => 'dosen']);
    }
}