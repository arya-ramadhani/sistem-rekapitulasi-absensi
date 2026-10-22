<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    public function redirect()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen' => redirect()->route('dashboard_dosen.dashboard'),
            default => abort(403, 'Role tidak dikenali'),
        };
    }
}
