<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelasList = Kelas::orderBy('prodi')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();
        $prodis = Prodi::all();

        return view('kelas', compact('kelasList', 'prodis'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi' => 'required',
            'tingkat' => 'required|in:1,2,3,4',
            'nama' => 'nullable|string|max:1',
        ]);

        // Cek apakah kombinasi sudah ada
        $exists = \App\Models\Kelas::where('prodi', $validated['prodi'])
            ->where('tingkat', $validated['tingkat'])
            ->where('nama', $validated['nama'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas dengan nama yang sama tidak bisa.'
            ], 422);
        }

        Kelas::create($validated);

        return response()->json(['success' => true]);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'prodi' => 'required',
            'tingkat' => 'required|in:1,2,3,4',
            'nama' => 'nullable|string|max:1',
        ]);

        // Cek apakah kombinasi sudah ada di kelas lain
        $exists = \App\Models\Kelas::where('prodi', $validated['prodi'])
            ->where('tingkat', $validated['tingkat'])
            ->where('nama', $validated['nama'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas dengan nama yang sama tidak bisa.'
            ], 422);
        }

        $kelas = Kelas::findOrFail($id);
        $kelas->update($validated);

        return response()->json(['success' => true]);
    }




    public function destroy($id)
    {
        Kelas::findOrFail($id)->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Data kelas berhasil dihapus.');
    }



    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        $mahasiswas = Mahasiswa::where('kelas_id', $kelas->id)->get();

        return view('mahasiswa', compact('kelas', 'mahasiswas'));
    }
    
}
