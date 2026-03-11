<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileConversion extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database (opsional, tapi praktik yang baik untuk kepastian absolut)
     */
    protected $table = 'file_conversions';

    /**
     * Atribut yang diizinkan untuk mass assignment.
     * Ini krusial untuk mencegah kerentanan Mass-Assignment Vulnerability.
     * * @var array<int, string>
     */
    protected $fillable = [
        'original_filename',
        'conversion_type',
        'json_output_path',
        'file_size_kb',
        'status',
        'error_log',
    ];

    /**
     * Casting tipe data secara native agar ORM mengembalikan format yang presisi (bukan sekadar string).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size_kb' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}