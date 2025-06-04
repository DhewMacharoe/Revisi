<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';

    protected $fillable = [
        'nama',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Relasi ke Menu
    public function menu()
    {
        return $this->hasMany(Menu::class, 'id_admin');
    }

    // Relasi ke Pemesanan
    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'id_admin');
    }

    // Relasi ke StokBahan
    public function stokBahan()
    {
        return $this->hasMany(StokBahan::class, 'id_admin');
    }

    // Relasi ke Laporan
    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'id_admin');
    }
}
