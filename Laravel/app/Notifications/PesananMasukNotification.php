<?php

namespace App\Notifications;

use App\Models\Pemesanan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PesananMasukNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pemesanan;

    public function __construct(Pemesanan $pemesanan)
    {
        $this->pemesanan = $pemesanan;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->pemesanan->id,
            'id_pelanggan' => $this->pemesanan->id_pelanggan,
            'total_harga' => $this->pemesanan->total_harga,
            'metode_pembayaran' => $this->pemesanan->metode_pembayaran,
            'status' => $this->pemesanan->status,
            'created_at' => $this->pemesanan->created_at->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->pemesanan->id,
            'id_pelanggan' => $this->pemesanan->id_pelanggan,
            'total_harga' => $this->pemesanan->total_harga,
            'metode_pembayaran' => $this->pemesanan->metode_pembayaran,
            'status' => $this->pemesanan->status,
            'created_at' => $this->pemesanan->created_at->toDateTimeString(),
        ]);
    }
}
