<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use App\Models\Pembayaran;
use App\Models\Pemesanan;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Request $request)
    {
        $orderId = 'ORDER-' . uniqid();
        $grossAmount = (int) $request->gross_amount;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $request->customer['first_name'] ?? 'User',
                'last_name' => $request->customer['last_name'] ?? '',
                'email' => $request->customer['email'] ?? 'default@email.com',
            ],
            'item_details' => $request->items ?? [],
        ];

        try {
            $transaction = \Midtrans\Snap::createTransaction($params);
            $snapToken = $transaction->token;
            $redirectUrl = $transaction->redirect_url;

            // Simpan transaksi ke database
            $pembayaran = \App\Models\Pembayaran::create([
                'id_pemesanan' => $request->id_pemesanan ?? null, // opsional
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'transaction_status' => 'pending',
                'snap_token' => $snapToken,
            ]);

            return response()->json([
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function checkStatus($orderId)
    {
        try {
            $status = \Midtrans\Transaction::status($orderId);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $orderId = $notif->order_id;
        $type = $notif->payment_type;

        $pembayaran = \App\Models\Pembayaran::where('order_id', $orderId)->first();
        if (!$pembayaran) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pembayaran->transaction_status = $transaction;
        $pembayaran->payment_type = $type;
        $pembayaran->transaction_id = $notif->transaction_id;
        $pembayaran->transaction_time = $notif->transaction_time;
        $pembayaran->settlement_time = $notif->settlement_time ?? null;
        $pembayaran->pdf_url = $notif->pdf_url ?? null;

        // Tambahan info berdasarkan tipe pembayaran
        if ($type == 'bank_transfer' && isset($notif->va_numbers[0])) {
            $pembayaran->va_number = $notif->va_numbers[0]->va_number;
            $pembayaran->bank = $notif->va_numbers[0]->bank;
        }

        if ($type == 'gopay' && isset($notif->actions)) {
            foreach ($notif->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $pembayaran->qr_code_url = $action->url;
                }
            }
        }

        $pembayaran->save();

        // Update status pemesanan (opsional)
        $pemesanan = \App\Models\Pemesanan::find($pembayaran->id_pemesanan);
        if ($pemesanan) {
            if ($transaction == 'settlement' || $transaction == 'capture') {
                $pemesanan->status = 'dibayar';
            } elseif ($transaction == 'pending') {
                $pemesanan->status = 'menunggu';
            } else {
                $pemesanan->status = 'gagal';
            }
            $pemesanan->save();
        }

        return response()->json(['message' => 'Notification processed']);
    }
}
