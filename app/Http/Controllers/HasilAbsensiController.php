<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Prodi;
use Illuminate\Http\Request;

class HasilAbsensiController extends Controller
{
    /**
     * Display attendance results for a specific class
     */
    public function index($kelas_id, Request $request)
    {
        // Get the class information
        $kelas = Kelas::with('prodi')->findOrFail($kelas_id);

        // Get semester from request
        $semester = $request->input('semester');

        // Build query for attendance data
        $query = Absensi::where('kelas_id', $kelas_id);

        // Apply semester filter if provided
        if ($semester) {
            $query->where('semester', $semester);
        }

        // Get attendance data ordered by date and mata_kuliah
        $absensiData = $query->orderBy('tanggal', 'desc')
            ->orderBy('mata_kuliah')
            ->get();

        // Group attendance data by date and mata_kuliah
        $groupedAbsensi = $absensiData->groupBy(function ($item) {
            return $item->tanggal . '|' . $item->mata_kuliah;
        });

        // Calculate statistics
        $totalPertemuan = $groupedAbsensi->count();
        $totalMataKuliah = $absensiData->pluck('kode_mk')->unique()->count();
        $totalMahasiswa = $absensiData->pluck('npm')->unique()->count();

        return view('data_absensi', [
            'kelas' => $kelas,
            'groupedAbsensi' => $groupedAbsensi,
            'totalPertemuan' => $totalPertemuan,
            'totalMataKuliah' => $totalMataKuliah,
            'totalMahasiswa' => $totalMahasiswa,
            'absensiData' => $absensiData,
            'selectedSemester' => $semester
        ]);
    }

    /**
     * Show the form for creating new attendance data
     */
    public function create()
    {
        $kelasOptions = Kelas::with('prodi')->get();
        return view('data_absensi', compact('kelasOptions'));
    }

    /**
     * Store newly created attendance data
     */
    public function store(Request $request)
    {
        // Implementation for storing attendance data
        // This would be similar to AbsensiController::store
    }

    /**
     * Show the form for editing attendance data
     */
    public function edit($id)
    {
        $absensi = Absensi::findOrFail($id);
        return view('data_absensi', compact('absensi'));
    }

    /**
     * Update the specified attendance data
     */
    public function update(Request $request, $id)
    {
        // Implementation for updating attendance data
    }

    /**
     * Remove the specified attendance data
     */
    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $kelas_id = $absensi->kelas_id;
        $absensi->delete();

        return redirect()->route('data_absensi', $kelas_id)
            ->with('success', 'Data absensi berhasil dihapus');
    }
}