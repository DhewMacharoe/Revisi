<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    // Primary key default 'id' tidak perlu didefinisikan

    protected $fillable = [
        'report_date',
        'total_income',
        'total_orders',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];
}