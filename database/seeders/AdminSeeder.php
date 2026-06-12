<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pengguna::create([
            'nama' => 'Super Administrator',
            'nama_pengguna' => 'admin',
            'kata_sandi' => bcrypt('admin123'),
            'peran' => 'administrator',
            'asal_sekolah' => 'Pusat',
        ]);
    }
}
