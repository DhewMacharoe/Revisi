<?php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function buatTransaksi($dataPesanan)
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $dataPesanan['order_id'],
                'gross_amount' => $dataPesanan['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => $dataPesanan['nama_pelanggan'],
                'email' => $dataPesanan['email_pelanggan'],
                'phone' => $dataPesanan['telepon_pelanggan'],
            ],
        ];

        return Snap::getSnapToken($payload);
    }
}
