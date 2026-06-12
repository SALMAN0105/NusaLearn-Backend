<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayah';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'kode_pos',
        'nama_kecamatan',
        'kode_bahasa',
    ];

    public function sekolah()
    {
        return $this->hasMany(Sekolah::class, 'kode_pos', 'kode_pos');
    }
}
