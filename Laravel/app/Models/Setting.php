<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'updated_by_admin_id',
    ];

    /**
     * Relasi ke admin yang terakhir memperbarui.
     */
    public function updatedByAdmin()
    {
        // Ganti 'admin' dengan nama model admin Anda jika berbeda (misal: 'User')
        return $this->belongsTo(Admin::class, 'updated_by_admin_id');
    }
}
