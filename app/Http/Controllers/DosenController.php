<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;
use App\Models\Prodi;

class DosenController extends Controller
{


    public function index()
    {
        $dosens = Dosen::all();
        $prodis = Prodi::all(); // <--- Ambil semua prodi
        return view('dosen.dosen', compact('dosens', 'prodis'));
    }


    // Tambahkan store method yang return JSON agar bisa diproses AJAX
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required',
            'nama' => 'required',
            'program_studi' => 'required',
        ]);

        Dosen::create($validated);

        return response()->json(['success' => true]);
    }


    public function update(Request $request, $id)
    {
        $dosen = Dosen::findOrFail($id);

        $request->validate([
            'nip' => 'required',
            'nama' => 'required',
            'program_studi' => 'required',
        ]);

        $dosen->update($request->all());

        return response()->json(['success' => true]);
    }


    public function destroy($id)
    {
        Dosen::findOrFail($id)->delete();
        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil dihapus.');
    }
}
