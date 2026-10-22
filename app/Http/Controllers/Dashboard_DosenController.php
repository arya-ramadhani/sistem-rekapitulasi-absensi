<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Carbon\Carbon;

class Dashboard_DosenController extends Controller
{
    /**
     * Constructor - Apply middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:dosen');
    }

    /**
     * Display dashboard for dosen
     */
    public function dashboard()
    {
        $user = Auth::user();
        $nip = $user->nip;
        
        // Check if NIP is not filled
        if (!$nip) {
            return view('dashboard_dosen.dashboard', [
                'totalKelas' => 0,
                'totalMatkul' => 0,
                'totalMahasiswa' => 0,
                'totalDosen' => 0,
                'user' => $user,
                'recentAbsensi' => collect(),
                'statusAbsensiHariIni' => collect(),
                'message' => 'Silakan lengkapi profil Anda dengan mengisi NIP terlebih dahulu.',
                'needsProfile' => true
            ]);
        }

        try {
            // Initialize data array with default values
            $data = [
                'totalKelas' => 0,
                'totalMatkul' => 0,
                'totalMahasiswa' => 0,
                'totalDosen' => 0,
                'totalAbsensi' => 0,
                'user' => $user,
                'recentAbsensi' => collect(),
                'statusAbsensiHariIni' => collect(),
                'needsProfile' => false
            ];
            
            // Get each statistic with individual error handling
            try {
                $data['totalKelas'] = $this->getTotalKelas($nip);
            } catch (\Exception $e) {
                Log::error('Error counting kelas for dosen: ' . $e->getMessage());
            }
            
            try {
                $data['totalMatkul'] = $this->getTotalMatkul($nip);
            } catch (\Exception $e) {
                Log::error('Error counting matkul for dosen: ' . $e->getMessage());
            }
            
            try {
                // Total mahasiswa in lecturer's classes
                $data['totalMahasiswa'] = $this->getTotalMahasiswa($nip);
                
                // Total all lecturers (like admin dashboard)
                $data['totalDosen'] = Dosen::count();
            } catch (\Exception $e) {
                Log::error('Error counting mahasiswa/dosen: ' . $e->getMessage());
            }
            
            try {
                $data['totalAbsensi'] = Absensi::whereHas('kelas', function($q) use ($nip) {
                    $q->where('id_dosen', $nip);
                })->count();
            } catch (\Exception $e) {
                Log::error('Error counting absensi for dosen: ' . $e->getMessage());
            }
            
            try {
                $data['recentAbsensi'] = $this->getRecentAbsensi($nip);
            } catch (\Exception $e) {
                Log::error('Error fetching recent absensi for dosen: ' . $e->getMessage());
            }
            
            try {
                $data['statusAbsensiHariIni'] = Absensi::whereHas('kelas', function($q) use ($nip) {
                    $q->where('id_dosen', $nip);
                })
                ->whereDate('created_at', Carbon::today())
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching status absensi for dosen: ' . $e->getMessage());
            }

            return view('dashboard_dosen.dashboard', $data);

        } catch (\Exception $e) {
            Log::error('Error in dosen dashboard: ' . $e->getMessage());
            
            return view('dashboard_dosen.dashboard', [
                'totalKelas' => 0,
                'totalMatkul' => 0,
                'totalMahasiswa' => 0,
                'totalDosen' => 0,
                'user' => $user,
                'recentAbsensi' => collect(),
                'statusAbsensiHariIni' => collect(),
                'error' => 'Terjadi kesalahan saat memuat data dashboard.',
                'needsProfile' => false
            ]);
        }
    }

    // [Keep all other methods exactly the same as in your original controller]
    // ... (profile, absensi, exportAbsensi, getStats, helper methods, etc.)

    /**
     * Get total mahasiswa for dosen (updated to show both lecturer's students and all students)
     */
    private function getTotalMahasiswa($nip)
    {
        // Count students in lecturer's classes
        $mahasiswaDosen = Mahasiswa::whereHas('kelas', function($q) use ($nip) {
            $q->where('id_dosen', $nip);
        })->distinct()->count();
        
        return $mahasiswaDosen;
    }
    
    /**
     * Get total dosen (like admin dashboard)
     */
    private function getTotalDosen()
    {
        return Dosen::count();
    }
}