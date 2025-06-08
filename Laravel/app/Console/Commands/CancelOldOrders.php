<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Pemesanan; // Pastikan namespace model Anda benar
use Carbon\Carbon;

class CancelOldOrders extends Command
{
    /**
     * Nama dan signature dari command konsol.
     * 'orders:cancel-old' adalah nama yang akan kita panggil.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-old';

    /**
     * Deskripsi dari command konsol.
     *
     * @var string
     */
    protected $description = 'Membatalkan pesanan yang statusnya masih menunggu/pembayaran lebih dari 15 menit';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Mulai memeriksa pesanan lama...');

        // Tentukan waktu batas, yaitu 15 menit yang lalu dari sekarang.
        $cutOffTime = Carbon::now()->subMinutes(15);

        // Cari pesanan yang memenuhi kriteria:
        // 1. Statusnya 'menunggu' ATAU 'pembayaran'
        // 2. Dibuat sebelum atau pada waktu batas (lebih dari 15 menit yang lalu)
        $ordersToCancel = Pemesanan::whereIn('status', ['menunggu', 'pembayaran'])
                                   ->where('created_at', '<=', $cutOffTime)
                                   ->get();

        if ($ordersToCancel->isEmpty()) {
            $this->info('Tidak ada pesanan lama yang perlu dibatalkan.');
            return 0;
        }

        // Ubah status pesanan yang ditemukan menjadi 'dibatalkan'
        foreach ($ordersToCancel as $order) {
            $order->status = 'dibatalkan';
            $order->save();
        }

        $count = $ordersToCancel->count();
        $this->info("Berhasil membatalkan {$count} pesanan.");

        // (Opsional) Catat ke log untuk debugging
        Log::info("Scheduler: Berhasil membatalkan {$count} pesanan lama.");

        return 0;
    }
}