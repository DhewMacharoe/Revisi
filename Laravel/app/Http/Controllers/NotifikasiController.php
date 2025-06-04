<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use App\Models\Pemesanan;
use App\Events\NotifikasiEvent;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasi = Notifikasi::latest()->get();
        return view('notifikasi.index', compact('notifikasi'));
    }

    public function kirimNotifikasi()
    {
        // Hitung jumlah total pesanan
        $jumlahPesanan = Pemesanan::count();

        // Format isi notifikasi
        $isiPesan = "Ada {$jumlahPesanan} pesanan yang masuk";

        // Simpan notifikasi
        $notifikasi = Notifikasi::create([
            'judul' => 'Pesanan Masuk',
            'pesan' => $isiPesan,
            'is_read' => false,
            'dibuat_untuk' => 'admin',
        ]);

        // Broadcast notifikasi ke WebSocket
        broadcast(new NotifikasiEvent($isiPesan))->toOthers();

        return response()->json(['success' => true, 'message' => $isiPesan]);
    }
}
