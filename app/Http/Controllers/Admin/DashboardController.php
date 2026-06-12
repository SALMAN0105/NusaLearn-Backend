<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Materi;
use App\Models\Wilayah; // Pastikan Model ini ada
use App\Models\ProgresSiswa;
use App\Models\Bahasa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $siswaQuery = Pengguna::where('peran', 'siswa');
        $materiQuery = Materi::query();
        $progresQuery = ProgresSiswa::query();

        if ($user->peran === 'guru' || $user->peran === 'admin') {
            $siswaQuery->where('asal_sekolah', $user->asal_sekolah);
            $materiQuery->where('asal_sekolah', $user->asal_sekolah);
            $progresQuery->whereHas('pengguna', function ($query) use ($user) {
                $query->where('asal_sekolah', $user->asal_sekolah);
            });
        }

        // 1. Mengambil Statistik Utama (Counters)
        $stats = [
            'total_siswa' => $siswaQuery->count(),
            'total_materi' => $materiQuery->count(),
            'total_wilayah' => Wilayah::count(), // Global
            // Menghitung siswa yang aktif hari ini (berdasarkan log progres)
            'siswa_aktif' => (clone $progresQuery)->whereDate('dibuat_pada', today())->distinct('pengguna_id')->count('pengguna_id'),
        ];

        // 2. Mengambil Data Tabel "Pemantauan Siswa" (5 Data Terbaru)
        $recentProgress = (clone $progresQuery)->with(['pengguna', 'soal.materi'])
            ->latest()
            ->take(5)
            ->get();

        // 3. Mengambil Data untuk Widget "Deteksi Bahasa"
        // Kita tampilkan bahasa apa saja yang sudah terdaftar di sistem
        $activeLanguages = Bahasa::where('aktif', true)->get();

        return view('admin.dashboard', compact('stats', 'recentProgress', 'activeLanguages'));
    }
}
