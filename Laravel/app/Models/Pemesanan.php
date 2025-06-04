<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggan;
use App\Models\Admin;
use App\Models\DetailPemesanan;
use App\Models\Pembayaran;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'id_pelanggan',
        'admin_id',
        'total_harga',
        'metode_pembayaran',
        'bukti_pembayaran',
        'status',
        'waktu_pemesanan',
        'waktu_pengambilan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pemesanan');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pemesanan');
    }
    // app/Models/Pemesanan.php
    protected $casts = [
        'status' => 'string', // atau enum jika menggunakan PHP 8.1+
    ];
}
