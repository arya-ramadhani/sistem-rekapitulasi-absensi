<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::all();
        return view('prodi.prodi', compact('prodis'));
    }

    // Store via AJAX
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_studi' => 'required|string|max:255',
        ]);

        Prodi::create($validated);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $prodi = Prodi::findOrFail($id);

        $request->validate([
            'program_studi' => 'required|string|max:255',
        ]);

        $prodi->update([
            'program_studi' => $request->program_studi,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Prodi::findOrFail($id)->delete();
        return redirect()->route('admin.prodi.index')->with('success', 'Data prodi berhasil dihapus.');
    }
}
