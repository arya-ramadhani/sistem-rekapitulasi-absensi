<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Matkul;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Inisialisasi variabel dengan nilai default
        $data = [
            'totalDosen' => 0,
            'totalProdi' => 0, 
            'totalMatkul' => 0,
            'totalKelas' => 0,
            'totalMahasiswa' => 0,
            'totalAbsensi' => 0,
            'recentAbsensi' => collect(),
            'statusAbsensiHariIni' => collect()
        ];
        
        // Mengambil data per statistik dengan penanganan error individu
        try {
            $data['totalDosen'] = Dosen::count();
        } catch (\Exception $e) {
            Log::error('Error counting dosen: ' . $e->getMessage());
        }
        
        try {
            $data['totalProdi'] = Prodi::count();
        } catch (\Exception $e) {
            Log::error('Error counting prodi: ' . $e->getMessage());
        }
        
        try {
            $data['totalMatkul'] = Matkul::count();
        } catch (\Exception $e) {
            Log::error('Error counting matkul: ' . $e->getMessage());
        }
        
        try {
            $data['totalKelas'] = Kelas::count();
        } catch (\Exception $e) {
            Log::error('Error counting kelas: ' . $e->getMessage());
        }
        
        try {
            $data['totalMahasiswa'] = Mahasiswa::count();
        } catch (\Exception $e) {
            Log::error('Error counting mahasiswa: ' . $e->getMessage());
        }
        
        try {
            $data['totalAbsensi'] = Absensi::count();
        } catch (\Exception $e) {
            Log::error('Error counting absensi: ' . $e->getMessage());
        }
        
        try {
            $data['recentAbsensi'] = Absensi::with(['mahasiswa', 'kelas'])
                ->latest()
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching recent absensi: ' . $e->getMessage());
        }
        
        try {
            $data['statusAbsensiHariIni'] = Absensi::whereDate('created_at', Carbon::today())
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching status absensi: ' . $e->getMessage());
        }
            
        // Menggunakan view dashboard.blade.php di root views
        return view('dashboard', $data);
    }
}