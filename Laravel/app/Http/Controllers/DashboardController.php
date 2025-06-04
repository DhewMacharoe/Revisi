<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\StokBahan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard
     */
    public function index()
    {
        // Mengambil data untuk ditampilkan di dashboard
        $totalPesanan = Pemesanan::count();
        $totalPelanggan = Pelanggan::count();
        $totalMenu = Menu::count();
        $totalStok = StokBahan::count();

        // Pesanan terbaru (FIFO - First In First Out)
        $pesananTerbaru = Pemesanan::with(['pelanggan'])
            ->whereIn('status', ['menunggu', 'pembayaran'])
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();


        // Menu terlaris
        $menuTerlaris = DB::table('detail_pemesanan')
            ->select('menu.id', 'menu.nama_menu', 'menu.harga', 'menu.kategori', DB::raw('SUM(detail_pemesanan.jumlah) as total_terjual'))
            ->join('menu', 'detail_pemesanan.id_menu', '=', 'menu.id')
            ->groupBy('menu.id', 'menu.nama_menu', 'menu.harga', 'menu.kategori')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalPesanan',
            'totalPelanggan',
            'totalMenu',
            'totalStok',
            'pesananTerbaru',
            'menuTerlaris'
        ));
    }

    /**
     * Mendapatkan detail pesanan untuk modal
     */
    public function getDetailPesanan($id)
    {
        $pesanan = Pemesanan::with(['pelanggan', 'detailPemesanans.menu'])
            ->findOrFail($id);

        return response()->json([
            'pesanan' => $pesanan,
            'details' => $pesanan->detailPemesanans,
        ]);
    }
}
