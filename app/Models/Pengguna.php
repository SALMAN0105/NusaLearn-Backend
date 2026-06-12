<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'pengguna';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nama_pengguna',
        'kata_sandi',
        'email',
        'peran',
        'asal_sekolah',
        'kelas',
        'kelas_diampu',
        'level_saat_ini',
        'kode_pos',
        'kode_bahasa',
        'url_gambar',
        'kode_akses',
        'aktif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'kata_sandi',
        'token_ingat',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kata_sandi' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName(): string
    {
        return 'token_ingat';
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): string
    {
        return $this->kata_sandi;
    }

    protected $casts = [
        'kelas_diampu' => 'array',
    ];

    public function progres()
    {
        return $this->hasMany(ProgresSiswa::class, 'pengguna_id');
    }
}
