<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StokBahan;

class StokBahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stokBahan = [
            [
                'id_admin' => 1,
                'nama_bahan' => 'Beras',
                'jumlah' => 50,
                'satuan' => 'kg',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Minyak Goreng',
                'jumlah' => 20,
                'satuan' => 'liter',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Ayam',
                'jumlah' => 30,
                'satuan' => 'kg',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Telur',
                'jumlah' => 100,
                'satuan' => 'pcs',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Gula',
                'jumlah' => 25,
                'satuan' => 'kg',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Teh',
                'jumlah' => 10,
                'satuan' => 'dus',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Kopi',
                'jumlah' => 15,
                'satuan' => 'kg',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Jeruk',
                'jumlah' => 20,
                'satuan' => 'kg',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Alpukat',
                'jumlah' => 15,
                'satuan' => 'kg',
            ],
            [
                'id_admin' => 1,
                'nama_bahan' => 'Pisang',
                'jumlah' => 5,
                'satuan' => 'tandan',
            ],
        ];
        
        foreach ($stokBahan as $sb) {
            StokBahan::create($sb);
        }
    }
}