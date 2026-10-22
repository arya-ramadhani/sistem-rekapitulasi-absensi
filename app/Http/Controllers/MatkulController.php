<?php

namespace App\Http\Controllers;

use App\Models\Matkul;
use Illuminate\Http\Request;

class MatkulController extends Controller
{
    public function index()
    {
        $matkuls = Matkul::all();
        return view('matkul.matkul', compact('matkuls'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:matkuls,kode',
            'nama' => 'required',
            'sks_teori' => 'required|integer|min:0',
            'sks_praktikum' => 'required|integer|min:0',
        ]);

        // Hitung jumlah SKS
        $validated['jumlah_sks'] = $validated['sks_teori'] + $validated['sks_praktikum'];

        Matkul::create($validated);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $matkul = Matkul::findOrFail($id);

        $request->validate([
            'kode' => 'required|unique:matkuls,kode,' . $id,
            'nama' => 'required',
            'sks_teori' => 'required|integer|min:0',
            'sks_praktikum' => 'required|integer|min:0',
        ]);

        $data = $request->only(['kode', 'nama', 'sks_teori', 'sks_praktikum']);
        $data['jumlah_sks'] = $data['sks_teori'] + $data['sks_praktikum'];

        $matkul->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Matkul::findOrFail($id)->delete();
        return redirect()->route('admin.matkul.index')->with('success', 'Data mata kuliah berhasil dihapus.');
    }
}
