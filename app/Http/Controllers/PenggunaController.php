<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'nama_lengkap', 'email', 'role')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('nama_lengkap')
            ->get();

        return view('pengguna', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,dosen',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,dosen',
        ]);

        $data = $request->all();

        // Jika password diisi, hash password baru
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
       
        User::findOrFail($id)->delete();
        return redirect()->route('admin.pengguna.index')->with('success', 'Data pengguna berhasil dihapus.');
    }
}
