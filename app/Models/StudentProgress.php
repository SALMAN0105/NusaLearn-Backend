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
        'student_answer',
        'student_answer_json',
        'answer_data',
        'is_correct',
        'points_earned',
        'time_spent_seconds',
        'answered_at',
        'template_type',
        'is_synced',
    ];

    protected $casts = [
        'student_answer_json' => 'array',
        'answer_data'         => 'array',
        'is_correct'          => 'boolean',
        'points_earned'       => 'integer',
        'answered_at'         => 'datetime',
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