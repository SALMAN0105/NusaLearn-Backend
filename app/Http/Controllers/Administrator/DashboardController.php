<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $schoolCount = \App\Models\Sekolah::count();
        $teacherCount = \App\Models\Pengguna::where('peran', 'admin')->count(); // admin is guru
        
        $stats = [
            'total_siswa' => \App\Models\Pengguna::where('peran', 'siswa')->count(),
            'total_materi' => \App\Models\Materi::count(),
            'total_wilayah' => \App\Models\Wilayah::count(),
            'siswa_aktif' => \App\Models\ProgresSiswa::whereDate('dibuat_pada', today())->distinct('pengguna_id')->count('pengguna_id'),
        ];

        $recentProgress = \App\Models\ProgresSiswa::with(['pengguna', 'soal.materi'])
            ->latest()
            ->take(5)
            ->get();

        $activeLanguages = \App\Models\Bahasa::where('aktif', true)->get();

        return view('administrator.dashboard', compact('schoolCount', 'teacherCount', 'stats', 'recentProgress', 'activeLanguages'));
    }
}
