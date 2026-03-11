<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_origin',
        'title_indo',
        'content_indo',
        'category',         // 'literasi' atau 'numerasi'
        'image_url',
        'level_difficulty', // 1 (Mudah) - 3 (Sulit)
        'language_code',    // Scope bahasa materi ini (Baru)
        'ai_embeddings',
        'ai_status',
        'ai_processed_at',
    ];

    protected $casts = [
        'content_indo' => 'array',
        'ai_embeddings' => 'array',
        'ai_processed_at' => 'datetime',
    ];

    // Relasi: Satu Materi punya banyak Soal
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}