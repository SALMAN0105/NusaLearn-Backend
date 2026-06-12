<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonversiFile extends Model
{
    use HasFactory;

    protected $table = 'konversi_file';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama_file_asli',
        'tipe_konversi',
        'jalur_output_json',
        'ukuran_file_kb',
        'status',
        'log_error',
    ];

    protected $casts = [
        'ukuran_file_kb' => 'integer',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
    ];
}
