<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    // Menampilkan daftar mahasiswa berdasarkan kelas_id
    public function indexByKelas($kelas_id)
    {
        try {
            $kelas = Kelas::findOrFail($kelas_id);
            $mahasiswas = Mahasiswa::where('kelas_id', $kelas_id)
                ->with('kelas')
                ->orderBy('nama', 'ASC')
                ->get();

            // Kirim juga daftar semua kelas untuk form edit jika diperlukan
            $kelass = Kelas::all();


            return view('mahasiswa', compact('mahasiswas', 'kelas', 'kelass'));
        } catch (\Exception $e) {

            return redirect()->route('admin.kelas.index')->with('error', 'Kelas tidak ditemukan.');
        }
    }

    // Menampilkan daftar semua mahasiswa (untuk resource route)
    public function index()
    {
        $mahasiswas = Mahasiswa::with('kelas')
            ->orderBy('nama', 'ASC')
            ->get();

        return view('mahasiswa', compact('mahasiswas'));
    }

    // Menampilkan form create mahasiswa
    public function create()
    {
        $kelass = Kelas::all();
        $mahasiswas = collect();

        return view('mahasiswa', compact('kelass', 'mahasiswas'));
    }

    // Menyimpan data mahasiswa baru
    public function store(Request $request)
    {
        try {

            $request->merge([
                'npm' => preg_replace('/\s+/', '', $request->npm),
            ]);

            $validated = $request->validate([
                'npm' => 'required|string|regex:/^\d+$/|unique:mahasiswas,npm',
                'nama' => 'required|string|max:50',
                'kelas_id' => 'required|integer|exists:kelass,id',
            ]);


            $mahasiswa = Mahasiswa::create($validated);

            // Cek apakah request dari modal (AJAX) atau form biasa
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'mahasiswa' => $mahasiswa,
                    'message' => 'Mahasiswa berhasil ditambahkan'
                ]);
            }

            // Jika ada kelas_id, redirect ke halaman kelas tersebut
            if ($request->kelas_id) {
                return redirect()->route('admin.kelas.mahasiswa', ['id' => $request->kelas_id])
                    ->with('success', 'Mahasiswa berhasil ditambahkan.');
            }

            return redirect()->route('admin.mahasiswa.index')
                ->with('success', 'Mahasiswa berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            throw $e;
        }
    }

    // Menampilkan form edit mahasiswa
    public function edit($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $kelass = Kelas::all();
        $mahasiswas = collect([$mahasiswa]);

        return view('admin.mahasiswa', compact('mahasiswa', 'kelass', 'mahasiswas'));
    }

    // Memperbarui data mahasiswa berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->merge([
            'npm' => preg_replace('/\s+/', '', $request->npm),
        ]);

        $mahasiswa = Mahasiswa::findOrFail($id);

        $request->validate([
            'npm' => 'required|unique:mahasiswas,npm,' . $id,
            'nama' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelass,id',
        ]);

        $mahasiswa->update($request->all());

        // Jika request dari AJAX/modal
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil diperbarui.');
    }

    // Menghapus data mahasiswa berdasarkan ID
    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $kelas_id = $mahasiswa->kelas_id;
        $mahasiswa->delete();

        // Jika mahasiswa memiliki kelas, redirect ke halaman kelas tersebut
        if ($kelas_id) {
            return redirect()->route('admin.kelas.mahasiswa', ['id' => $kelas_id])
                ->with('success', 'Mahasiswa berhasil dihapus.');
        }

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus.');
    }
}
