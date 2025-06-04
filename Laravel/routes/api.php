<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PemesananController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\DetailPemesananController;
use App\Http\Controllers\LaporanController;

// Menu routes
Route::prefix('menu')->group(function () {
    Route::get('/', [MenuController::class, 'index']);
    Route::get('/top', [MenuController::class, 'topMenu']);
    Route::get('/{id}', [MenuController::class, 'show']);
    Route::post('/', [MenuController::class, 'store']);
    Route::put('/{id}', [MenuController::class, 'update']);
    Route::put('/{id}/rating-update', [MenuController::class, 'updateAverageRating']);
    Route::delete('/{id}', [MenuController::class, 'destroy']);
});

// Pelanggan routes
Route::prefix('pelanggan')->group(function () {
    Route::get('/', [PelangganController::class, 'index']);
    Route::get('/by-telepon', [PelangganController::class, 'getByTelepon']);
    Route::get('/by-device', [PelangganController::class, 'getByDevice']);
    Route::get('/{id}', [PelangganController::class, 'show']);
    Route::post('/', [PelangganController::class, 'store']);
    Route::put('/{id}', [PelangganController::class, 'update']);
    Route::delete('/{id}', [PelangganController::class, 'destroy']);
});

// Pemesanan routes
Route::prefix('pemesanan')->group(function () {
    Route::get('/', [PemesananController::class, 'index']);
    Route::get('/pelanggan/{id}', [PemesananController::class, 'getByPelanggan']);
    Route::get('/cek-baru', [PemesananController::class, 'cekPesananBaru']);  // polling baru
    Route::get('/{id}', [PemesananController::class, 'show']);
    Route::post('/', [PemesananController::class, 'store']);
    Route::put('/{id}', [PemesananController::class, 'update']);
    Route::delete('/{id}', [PemesananController::class, 'destroy']);
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/{id}', [AdminController::class, 'show']);
    Route::post('/', [AdminController::class, 'store']);
    Route::put('/{id}', [AdminController::class, 'update']);
    Route::delete('/{id}', [AdminController::class, 'destroy']);
});

// Keranjang routes
Route::prefix('keranjang')->group(function () {
    Route::get('/', [KeranjangController::class, 'index']);
    Route::get('/pelanggan/{id_pelanggan}', [KeranjangController::class, 'getByPelanggan']);
    Route::get('/{id}', [KeranjangController::class, 'show']);
    Route::post('/', [KeranjangController::class, 'store']);
    Route::put('/{id}', [KeranjangController::class, 'update']);
    Route::post('/checkout/{id_pelanggan}', [KeranjangController::class, 'checkout']);
    Route::delete('/{id}', [KeranjangController::class, 'destroy']);
    Route::delete('/pelanggan/{id_pelanggan}', [KeranjangController::class, 'clearCart']);
});

// Laporan routes
Route::prefix('laporan')->group(function () {
    Route::get('/', [LaporanController::class, 'index']);
    Route::get('/{id}', [LaporanController::class, 'show']);
    Route::post('/', [LaporanController::class, 'store']);
    Route::put('/{id}', [LaporanController::class, 'update']);
    Route::delete('/{id}', [LaporanController::class, 'destroy']);
});

// Detail Pemesanan routes
Route::prefix('detail-pemesanan')->group(function () {
    Route::get('/', [DetailPemesananController::class, 'index']);
    Route::get('/{id}', [DetailPemesananController::class, 'show']);
    Route::post('/', [DetailPemesananController::class, 'store']);
    Route::put('/{id}', [DetailPemesananController::class, 'update']);
    Route::put('/{id}/rating', [DetailPemesananController::class, 'updateRating']);
    Route::delete('/{id}', [DetailPemesananController::class, 'destroy']);
});

// Auth routes for pelanggan
Route::prefix('auth')->group(function () {
    Route::post('/pelanggan', [PelangganController::class, 'loginAtauRegister']);
    Route::post('/pelanggan/manual', [PelangganController::class, 'loginManual']);
});

// Middleware JWT group
Route::middleware('jwt.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Midtrans routes
Route::prefix('midtrans')->group(function () {
    Route::post('/create-transaction', [MidtransController::class, 'createTransaction']);
    Route::get('/status/{orderId}', [MidtransController::class, 'checkStatus']);
    Route::post('/notification', [MidtransController::class, 'handleNotification']);
});
