<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';


    protected $fillable = [
        'judul',
        'pesan',
        'is_read',
        'dibuat_untuk',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
}
