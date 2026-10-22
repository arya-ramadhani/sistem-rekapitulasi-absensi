<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dashboard_DosenController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\MatkulController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HasilAbsensiController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\PenggunaController;
use Termwind\Components\Hr;

// Authentication Routes
Auth::routes();

// Redirect Routes
Route::get('/', [RedirectController::class, 'redirect']);
Route::get('/home', [RedirectController::class, 'redirect'])->middleware('auth')->name('home');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        // Dashboard Routes
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/dashboard/stats', [DashboardController::class, 'getStats'])->name('api.dashboard.stats');
        Route::get('/api/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('api.dashboard.chart');

        // Resource Routes
        Route::resource('dosen', DosenController::class)->except(['show']);
        Route::resource('prodi', ProdiController::class)->except(['show']);
        Route::resource('matkul', MatkulController::class)->except(['show']);
        Route::resource('kelas', KelasController::class)->except(['show']);
        Route::resource('absensi', AbsensiController::class)->except(['show']);
        Route::resource('pengguna', PenggunaController::class)->except(['show']);
        Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);

        // Pastikan route sudah benar di web.php
        Route::prefix('data_absensi')->name('data_absensi.')->group(function () {
            Route::get('/create', [HasilAbsensiController::class, 'create'])->name('create');
            Route::get('/{kelas_id}', [HasilAbsensiController::class, 'index'])->name('index');
            Route::post('/', [HasilAbsensiController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [HasilAbsensiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [HasilAbsensiController::class, 'update'])->name('update');
            Route::delete('/{id}', [HasilAbsensiController::class, 'destroy'])->name('destroy');
        });

        // Kelas-Mahasiswa Relationship Routes (untuk halaman mahasiswa per kelas)
        Route::get('/kelas/{id}/mahasiswa', [MahasiswaController::class, 'indexByKelas'])->name('kelas.mahasiswa');

        // Update route untuk kelas
        Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');

        // Tambahkan ini di dalam grup route admin
        Route::post('/absensi/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
    });

/*
|--------------------------------------------------------------------------
| Dosen Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dosen')
    ->name('dashboard_dosen.')
    ->middleware(['auth', 'role:dosen'])
    ->group(function () {
        // Dashboard Routes
        Route::get('/dashboard', [Dashboard_DosenController::class, 'dashboard'])->name('dashboard');
        Route::get('/api/stats', [Dashboard_DosenController::class, 'getStats'])->name('stats');

        // Profile Routes
        Route::get('/profil', [Dashboard_DosenController::class, 'profil'])->name('profil');
        Route::post('/profil/update', [Dashboard_DosenController::class, 'updateProfil'])->name('profil.update');

        // Absensi Routes
        Route::get('/absensi', [Dashboard_DosenController::class, 'absensi'])->name('absensi');
        Route::post('/absensi/export', [Dashboard_DosenController::class, 'exportAbsensi'])->name('absensi.export');
    });

/*
|--------------------------------------------------------------------------
| Development/Testing Routes (Only in Debug Mode)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    Route::get('/test-auth', function () {
        $user = Auth::user();
        return [
            'authenticated' => Auth::check(),
            'user' => $user,
            'guest' => Auth::guest(),
            'role' => $user ? $user->role : null,
        ];
    })->name('test.auth');

    Route::get('/test-role', function () {
        $user = Auth::user();
        return [
            'user' => $user->nama_lengkap ?? null,
            'role' => $user->role ?? null,
            'is_admin' => $user && $user->role === 'admin',
            'is_dosen' => $user && $user->role === 'dosen',
        ];
    })->middleware('auth')->name('test.role');
}

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});
