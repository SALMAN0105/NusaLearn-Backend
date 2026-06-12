<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Pengguna::create([
            'nama' => 'Administrator',
            'nama_pengguna' => 'admin',
            'email' => 'admin@example.com',
            'peran' => 'administrator',
            'kata_sandi' => bcrypt('admin123'),
        ]);
    }
}
