<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\PelangganWebController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\AppStatusAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute Autentikasi
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang memerlukan autentikasi
Route::middleware(['auth:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/detail-pesanan/{id}', [DashboardController::class, 'getDetailPesanan']);

    // Rute Pesanan
    Route::resource('pesanan', PesananController::class);
    Route::get('/pesanan/cetak-struk/{id}', [PesananController::class, 'cetakStruk'])->name('pesanan.cetak-struk');
    Route::put('/pesanan/batalkan/{id}', [PesananController::class, 'batalkanPesanan'])->name('pesanan.batalkan');
    Route::post('/pesanan/{id}/status/{status}', [PesananController::class, 'ubahStatus'])->name('pesanan.status');
    Route::post('/pesanan/update-status/{id}', [PesananController::class, 'updateStatus']);

    // Rute Produk (Menu)
    Route::resource('produk', ProdukController::class);

    // Rute Pelanggan
    Route::get('/pelanggan', [PelangganWebController::class, 'index'])->name('pelanggan.index');
    Route::delete('/pelanggan/{id}', [PelangganWebController::class, 'destroy'])->name('pelanggan.destroy');

    // Rute Laporan
    Route::resource('reports', ReportController::class);
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/receipt/{pesanan}', [ReportController::class, 'receipt'])->name('reports.receipt');

    // Rute Stok Bahan
    Route::resource('stok', StokController::class);

    // Rute Profil Admin
    Route::middleware('auth:admin')->group(function () {
        Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
        Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    });

    Route::get('/pelanggan/by-telepon', [PelangganController::class, 'getByTelepon']);

    Route::post('/admin/app-status/toggle', [AppStatusAdminController::class, 'toggleStatus'])->name('admin.app_status.toggle');
    Route::get('/admin/app-status/current', [AppStatusAdminController::class, 'getCurrentStatus'])->name('admin.app_status.current');
});
