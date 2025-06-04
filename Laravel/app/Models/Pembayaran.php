<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_pemesanan',
        'order_id',
        'gross_amount',
        'payment_type',
        'transaction_id',
        'transaction_status',
        'transaction_time',
        'settlement_time',
        'snap_token',
        'pdf_url',
        'payment_code',
        'bank',
        'va_number',
        'qr_code_url',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
