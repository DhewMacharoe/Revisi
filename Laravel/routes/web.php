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
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\Admin\JadwalOperasionalController;

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
Route::get('/pengaturan/pin', [PengaturanController::class, 'showPinForm'])->name('pin.edit');
Route::post('/pengaturan/pin', [PengaturanController::class, 'updatePin'])->name('pin.update');

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
    Route::post('/pesanan/{pesanan}/update-status', [AdminController::class, 'updateStatus'])->name('pesanan.status');

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

    Route::middleware(['auth:admin'])->group(function () { // Pastikan guard 'admin' sesuai dengan setup Anda
        Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
        Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit'); // TAMBAHKAN ROUTE INI
        Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::get('/jadwal-operasional', [JadwalOperasionalController::class, 'index'])->name('admin.jadwal.index');
        Route::post('/jadwal-operasional/update', [JadwalOperasionalController::class, 'update'])->name('admin.jadwal.update');
    });
});

Route::get('/pelanggan/by-telepon', [PelangganController::class, 'getByTelepon']);

Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');

Route::post('/kirim-notifikasi', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'judul' => 'required|string',
        'pesan' => 'required|string',
    ]);
});
