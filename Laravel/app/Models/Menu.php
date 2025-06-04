<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = [
        'id_admin',
        'nama_menu',
        'kategori',
        'harga',
        'stok',
        'gambar',
        'deskripsi',
        'rating'
    ];

    protected $casts = [
        'harga' => 'integer',
        'stok' => 'integer',
        'rating' => 'float',
    ];

    // Relasi dengan Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id');
    }

    // Relasi dengan DetailPemesanan
    public function detailPemesanans()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_menu', 'id');
    }

    // Akses total penjualan
    public function getTotalTerjualAttribute()
    {
        return $this->detailPemesanans()->sum('jumlah');
    }

    // Ambil 8 menu dengan penjualan terbanyak
    public static function getTopMenuItems()
    {
        return self::with('detailPemesanans')
            ->get()
            ->sortByDesc(function ($menu) {
                return $menu->total_terjual;
            })
            ->take(8)
            ->values();
    }
    public function updateAverageRating($id_menu)
{
    $avg = DetailPemesanan::where('id_menu', $id_menu)
              ->whereNotNull('rating')
              ->avg('rating');

    $menu = Menu::find($id_menu);
    $menu->rating = $avg ?? 0;
    $menu->save();
}

}
