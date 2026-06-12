<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bahasa extends Model
{
    use HasFactory;

    protected $table = 'bahasa';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama',
        'kode',
        'aktif',
        'file_json',
        'hash_versi',
    ];
}
