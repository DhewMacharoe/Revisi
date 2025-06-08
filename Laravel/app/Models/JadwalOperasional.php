<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalOperasional extends Model
{
    use HasFactory;

    protected $table = 'jadwal_operasional';

    protected $fillable = [
        'hari',
        'jam_buka',
        'jam_tutup',
        'is_tutup',
    ];

    protected $casts = [
        'is_tutup' => 'boolean',
    ];
}
