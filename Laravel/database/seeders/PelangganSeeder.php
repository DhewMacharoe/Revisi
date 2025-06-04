<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelanggan;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = [
            ['nama' => 'Budi Santoso', 'telepon' => '081234567890'],
            ['nama' => 'Siti Rahayu', 'telepon' => '082345678901'],
            ['nama' => 'Ahmad Hidayat', 'telepon' => '083456789012'],
            ['nama' => 'Dewi Lestari', 'telepon' => '084567890123'],
            ['nama' => 'Eko Prasetyo', 'telepon' => '085678901234'],
        ];

        foreach ($pelanggan as $p) {
            Pelanggan::create($p);
        }
    }
}
