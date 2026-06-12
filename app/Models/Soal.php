<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Soal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'soal';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    const TEMPLATE_MULTIPLE_CHOICE = 'multiple_choice';
    const TEMPLATE_DRAG_AND_DROP   = 'drag_and_drop';
    const TEMPLATE_MATCHING_GAME   = 'matching_game';
    const TEMPLATE_FILL_BLANK      = 'fill_blank';
    const TEMPLATE_IMAGE_QUIZ      = 'image_quiz';

    const ALL_TEMPLATES = [
        self::TEMPLATE_MULTIPLE_CHOICE => 'Pilihan Ganda',
        self::TEMPLATE_DRAG_AND_DROP   => 'Drag & Drop',
        self::TEMPLATE_MATCHING_GAME   => 'Pasangkan',
        self::TEMPLATE_FILL_BLANK      => 'Isi Bagian Kosong',
        self::TEMPLATE_IMAGE_QUIZ      => 'Kuis Gambar',
    ];

    protected $fillable = [
        'materi_id',
        'kelas',
        'teks_soal',
        'opsi_json',
        'kunci_jawaban',
        'bobot_kesulitan',
        'poin',
        'tipe_template',
        'data_soal',
        'aset_diperlukan',
        'aktif',
    ];

    protected $casts = [
        'opsi_json'       => 'array',
        'data_soal'       => 'array',
        'aset_diperlukan' => 'array',
        'aktif'           => 'boolean',
        'poin'            => 'integer',
    ];

    // =========================================================================
    // RELATIONS
    // =========================================================================

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function percobaan()
    {
        return $this->hasMany(ProgresSiswa::class, 'soal_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Hanya soal yang aktif (dipakai untuk sync ke Flutter).
     */
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isLegacyFormat(): bool
    {
        return is_null($this->tipe_template);
    }

    public function getTemplateLabelAttribute(): string
    {
        return self::ALL_TEMPLATES[$this->tipe_template ?? 'multiple_choice'] ?? 'Pilihan Ganda';
    }

    public function getMissingAssets(): array
    {
        if (empty($this->aset_diperlukan)) {
            return [];
        }

        $hashToFilename = [];
        foreach ($this->aset_diperlukan as $filename) {
            $hash = pathinfo($filename, PATHINFO_FILENAME);
            $hashToFilename[$hash] = $filename;
        }

        $existingHashes = PustakaAset::whereIn('nama_file', array_keys($hashToFilename))
            ->where('aktif', true)
            ->pluck('nama_file')
            ->toArray();

        $missing = [];
        foreach ($hashToFilename as $hash => $originalFilename) {
            if (!in_array($hash, $existingHashes)) {
                $missing[] = $originalFilename;
            }
        }

        return $missing;
    }
}
