<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'material_id',
        'question_text_indo',
        'options_json',         // Pilihan ganda disimpan sebagai JSON
        'correct_answer_key',   // Kunci jawaban (misal: 'a')
        'difficulty_weight',    // Bobot kesulitan (1-5) untuk AI Adaptif
    ];

    // Otomatis ubah JSON di database jadi Array di PHP
    protected $casts = [
        'options_json' => 'array', 
    ];

    // Relasi: Soal milik satu Materi
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // Relasi: Soal ini sudah dijawab oleh siapa saja?
    public function attempts()
    {
        return $this->hasMany(StudentProgress::class);
    }
}