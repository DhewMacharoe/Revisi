<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'nama' => 'Admin DelBites',
            'email' => 'admin@delbites.com',
            'password' => Hash::make('password'),
        ]);

        Admin::create([
            'nama' => 'Manager DelBites',
            'email' => ' ',
            'password' => Hash::make('password'),
        ]);
    }
}
