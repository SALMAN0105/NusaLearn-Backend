<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'asal_sekolah',
        'judul',
        'konten',
        'kategori',
        'kelas',
        'url_gambar',
        'tingkat_kesulitan',
        'kode_bahasa',
        'ai_embeddings',
        'status_ai',
        'ai_diproses_pada',
    ];

    protected $casts = [
        'konten' => 'array',
        'ai_embeddings' => 'array',
        'ai_diproses_pada' => 'datetime',
    ];

    public function soal()
    {
        return $this->hasMany(Soal::class, 'materi_id');
    }
}
