<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Material;
use App\Models\Region; // Pastikan Model ini ada
use App\Models\StudentProgress;
use App\Models\Language;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Mengambil Statistik Utama (Counters)
        $stats = [
            'total_siswa' => User::where('role', 'siswa')->count(),
            'total_materi' => Material::count(),
            'total_wilayah' => Region::count(), // Mengambil data dari tabel regions
            // Menghitung siswa yang aktif hari ini (berdasarkan log progres)
            'siswa_aktif' => StudentProgress::whereDate('created_at', today())->distinct('user_id')->count('user_id'),
        ];

        // 2. Mengambil Data Tabel "Pemantauan Siswa" (5 Data Terbaru)
        $recentProgress = StudentProgress::with(['user', 'question.material'])
            ->latest()
            ->take(5)
            ->get();

        // 3. Mengambil Data untuk Widget "Deteksi Bahasa"
        // Kita tampilkan bahasa apa saja yang sudah terdaftar di sistem
        $activeLanguages = Language::where('is_active', true)->get();

        return view('admin.dashboard', compact('stats', 'recentProgress', 'activeLanguages'));
    }
}