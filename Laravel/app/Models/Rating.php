<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = [
        'id_menu',
        'id_pelanggan',
        'rating',
    ];

    /**
     * Relasi ke menu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    /**
     * Relasi ke pelanggan.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
}
