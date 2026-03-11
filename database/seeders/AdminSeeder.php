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
    \App\Models\User::create([
        'name' => 'Super Administrator',
        'username' => 'admin',
        'password' => bcrypt('admin123'), // Password default
        'role' => 'admin',
        'school_origin' => 'Pusat',
    ]);
}
}
