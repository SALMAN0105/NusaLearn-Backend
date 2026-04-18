<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProgress;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'siswa');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $students = $query->latest()->paginate(15);
        return view('admin.students', compact('students'));
    }

    /**
     * Mengambil data progres belajar siswa untuk ditampilkan di modal.
     * Dipanggil via AJAX dari halaman Data Siswa.
     */
    public function progress(User $student)
    {
        // Ambil semua progres siswa
        $progressData = StudentProgress::where('user_id', $student->id)
            ->orderBy('answered_at', 'asc')
            ->get();

        $total   = $progressData->count();
        $correct = $progressData->where('is_correct', true)->count();
        $wrong   = $total - $correct;

        // Rata-rata waktu pengerjaan
        $avgTime = $total > 0
            ? round($progressData->avg('time_spent_seconds'), 1)
            : 0;

        // ── Data Harian (30 hari terakhir) untuk Bar Chart ──
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay();
        $recentProgress = $progressData->filter(function ($item) use ($thirtyDaysAgo) {
            return Carbon::parse($item->answered_at)->gte($thirtyDaysAgo);
        });

        // Buat array semua 30 hari (agar tidak ada hari kosong di chart)
        $dailyMap = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateKey = Carbon::now()->subDays($i)->format('d/m');
            $dailyMap[$dateKey] = ['date' => $dateKey, 'correct' => 0, 'wrong' => 0];
        }

        // Isi data ke map
        foreach ($recentProgress as $item) {
            $key = Carbon::parse($item->answered_at)->format('d/m');
            if (isset($dailyMap[$key])) {
                if ($item->is_correct) {
                    $dailyMap[$key]['correct']++;
                } else {
                    $dailyMap[$key]['wrong']++;
                }
            }
        }

        // Filter hanya hari yang ada aktivitas untuk tampilan lebih bersih
        $daily = array_values(array_filter($dailyMap, function ($day) {
            return $day['correct'] > 0 || $day['wrong'] > 0;
        }));

        // ── Data Mingguan (4 minggu terakhir) untuk Tabel ──
        $weekly = [];
        for ($w = 3; $w >= 0; $w--) {
            $weekStart = Carbon::now()->subWeeks($w)->startOfWeek();
            $weekEnd   = Carbon::now()->subWeeks($w)->endOfWeek();

            $weekData = $progressData->filter(function ($item) use ($weekStart, $weekEnd) {
                $date = Carbon::parse($item->answered_at);
                return $date->between($weekStart, $weekEnd);
            });

            $weekCorrect = $weekData->where('is_correct', true)->count();
            $weekWrong   = $weekData->where('is_correct', false)->count();

            $weekly[] = [
                'week_label' => $weekStart->format('d M') . ' – ' . $weekEnd->format('d M'),
                'correct'    => $weekCorrect,
                'wrong'      => $weekWrong,
            ];
        }

        return response()->json([
            'total'              => $total,
            'correct'            => $correct,
            'wrong'              => $wrong,
            'avg_time_seconds'   => $avgTime,
            'daily'              => $daily,
            'weekly'             => $weekly,
        ]);
    }

    public function destroy(User $student)
    {
        $student->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}