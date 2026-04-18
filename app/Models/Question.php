<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

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
        'material_id',
        'question_text_indo',
        'options_json',
        'correct_answer_key',
        'difficulty_weight',
        'points',
        'template_type',
        'question_data',
        'assets_required',
        'is_active',
    ];

    protected $casts = [
        'options_json'    => 'array',
        'question_data'   => 'array',
        'assets_required' => 'array',
        'is_active'       => 'boolean',
        'points'          => 'integer',
    ];

    // =========================================================================
    // RELATIONS
    // =========================================================================

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function attempts()
    {
        return $this->hasMany(StudentProgress::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Hanya soal yang aktif (dipakai untuk sync ke Flutter).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isLegacyFormat(): bool
    {
        return $this->template_type === self::TEMPLATE_MULTIPLE_CHOICE
            || is_null($this->template_type);
    }

    public function getTemplateLabelAttribute(): string
    {
        return self::ALL_TEMPLATES[$this->template_type ?? 'multiple_choice'] ?? 'Pilihan Ganda';
    }

    public function getMissingAssets(): array
    {
        // Guard: jika tidak ada assets_required, return kosong
        if (empty($this->assets_required)) {
            return [];
        }

        $hashToFilename = [];
        foreach ($this->assets_required as $filename) {
            // Ambil hash (bagian sebelum titik pertama)
            $hash = pathinfo($filename, PATHINFO_FILENAME);
            $hashToFilename[$hash] = $filename; // map: hash → filename asli
        }

        // Query DB dengan hash (tanpa ekstensi)
        $existingHashes = AssetLibrary::whereIn('filename', array_keys($hashToFilename))
            ->where('is_active', true)
            ->pluck('filename')
            ->toArray();

        // Return filename asli (dengan ekstensi) yang tidak ditemukan
        $missing = [];
        foreach ($hashToFilename as $hash => $originalFilename) {
            if (!in_array($hash, $existingHashes)) {
                $missing[] = $originalFilename;
            }
        }

        return $missing;
    }
}