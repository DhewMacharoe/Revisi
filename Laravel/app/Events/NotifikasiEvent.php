<?php

// app/Events/NotifikasiEvent.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotifikasiEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesan;

    public function __construct($pesan)
    {
        $this->pesan = [
            'judul' => 'Notifikasi Baru',
            'pesan' => $pesan,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('notifikasi-channel');
    }

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }
}
