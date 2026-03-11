<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    use HasFactory;

    protected $table = 'student_progress'; // Memastikan nama tabel benar

    protected $fillable = [
        'user_id',
        'question_id',
        'student_answer',    // Jawaban siswa (misal: 'b')
        'is_correct',        // 1 (Benar) atau 0 (Salah)
        'time_spent_seconds',// Lama mengerjakan (detik)
        'answered_at',       // Waktu pengerjaan (timestamp dari HP)
    ];

    // Relasi: Progres ini milik User siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Ini jawaban untuk Soal nomor berapa?
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}