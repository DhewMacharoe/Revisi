<?php

namespace Database\Seeders;

use App\Models\JadwalOperasional;
use Illuminate\Database\Seeder;

class JadwalOperasionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        JadwalOperasional::truncate();

        foreach ($hari as $h) {
            JadwalOperasional::create([
                'hari' => $h,
                'jam_buka' => '08:00:00',
                'jam_tutup' => '22:00:00',
                'is_tutup' => false,
            ]);
        }
    }
}
