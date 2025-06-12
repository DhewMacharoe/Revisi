<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pemesanan;
use Carbon\Carbon;

class CancelPendingOrders extends Command
{
    protected $signature = 'orders:cancel-pending';
    protected $description = 'Cancel pending orders older than 1 day.';

    public function handle()
    {
        $threshold = Carbon::now()->subDays(1);

        $cancelledOrdersCount = Pemesanan::where('status', 'menunggu')
            ->where('created_at', '<', $threshold)
            ->update([
                'status' => 'dibatalkan',
                'catatan_pembatalan' => 'Dibatalkan otomatis: Pesanan tidak diterima dalam 1 hari.'
            ]);

        $this->info("{$cancelledOrdersCount} pending order(s) cancelled automatically.");
    }
}
