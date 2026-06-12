<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgresSiswa extends Model
{
    use HasFactory;

    protected $table = 'progres_siswa';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'pengguna_id',
        'soal_id',
        'jawaban_siswa',
        'jawaban_siswa_json',
        'data_jawaban',
        'benar',
        'poin_diperoleh',
        'waktu_detik',
        'dijawab_pada',
        'tipe_template',
        'sinkron',
    ];

    protected $casts = [
        'jawaban_siswa_json' => 'array',
        'data_jawaban'       => 'array',
        'benar'              => 'boolean',
        'poin_diperoleh'     => 'integer',
        'dijawab_pada'       => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}
