<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Pembayaran;
use App\Models\Pemesanan;

class PaymentController extends Controller
{
    public function buatPembayaran(Request $request)
    {
        // Set Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY'); // Taruh di .env
        Config::$isProduction = false; // true kalau sudah production
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Ambil data dari request Flutter
        $orderId = 'ORDER-' . uniqid(); // ID unik untuk Midtrans
        $grossAmount = $request->gross_amount; // Total pembayaran dari Flutter
        $idPemesanan = $request->id_pemesanan;

        // Data transaksi untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $request->nama_pelanggan ?? 'Pelanggan',
                'email' => $request->email ?? 'email@kosong.com',
                'phone' => $request->phone ?? '08123456789',
            ]
        ];

        // Buat Snap Token
        $snapToken = Snap::getSnapToken($params);

        // Simpan transaksi ke database
        Pembayaran::create([
            'id_pemesanan' => $idPemesanan,
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'pending', // awalnya pending
        ]);

        return response()->json([
            'snap_token' => $snapToken
        ]);
    }

    public function notifikasi(Request $request)
    {
        // Set Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Ambil data notifikasi dari Midtrans
        $notif = new \Midtrans\Notification();

        // Data transaksi
        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderId = $notif->order_id;
        $fraud = $notif->fraud_status;

        // Cari data pembayaran di database
        $pembayaran = \App\Models\Pembayaran::where('order_id', $orderId)->first();

        if (!$pembayaran) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
        }

        // Update pembayaran berdasarkan status Midtrans
        $pembayaran->transaction_status = $transaction;
        $pembayaran->payment_type = $type;
        $pembayaran->transaction_id = $notif->transaction_id;
        $pembayaran->transaction_time = $notif->transaction_time;
        $pembayaran->settlement_time = $notif->settlement_time ?? null;
        $pembayaran->save();

        // Update status pemesanan
        $pemesanan = \App\Models\Pemesanan::find($pembayaran->id_pemesanan);

        if ($transaction == 'capture' || $transaction == 'settlement') {
            $pemesanan->status = 'dibayar';
        } elseif ($transaction == 'pending') {
            $pemesanan->status = 'pembayaran';
        } elseif ($transaction == 'cancel' || $transaction == 'deny' || $transaction == 'expire') {
            $pemesanan->status = 'dibatalkan';
        }

        $pemesanan->save();

        return response()->json(['message' => 'Notifikasi diproses']);
    }
}
