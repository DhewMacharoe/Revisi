<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\Menu;
use App\Models\StokBahan;
use App\Models\Pelanggan;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalPesanan = Pemesanan::count();
        $totalPelanggan = Pelanggan::count();
        $totalMenu = Menu::count();
        $totalStok = StokBahan::count();
        $pesananTerbaru = Pemesanan::latest()->take(5)->get();
        $menuTerlaris = Menu::withCount('pemesanans')->orderByDesc('pemesanans_count')->first();
        $isPaymentActive = true;

        return view('admin.dashboard', compact(
            'totalPesanan',
            'totalPelanggan',
            'totalMenu',
            'totalStok',
            'pesananTerbaru',
            'menuTerlaris',
            'isPaymentActive'
        ));
    }
}
