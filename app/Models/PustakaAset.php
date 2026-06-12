<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PustakaAset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pustaka_aset';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'nama_file',
        'nama_asli',
        'ekstensi',
        'tipe_mime',
        'tipe_aset',
        'ukuran_kb',
        'sumber_api',
        'url_sumber',
        'id_sumber',
        'atribusi',
        'kata_kunci',
        'kategori',
        'aktif',
    ];

    protected $casts = [
        'kata_kunci'=> 'array',
        'aktif'     => 'boolean',
        'ukuran_kb' => 'integer',
    ];

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Kembalikan URL publik aset ini.
     * File disimpan di storage/app/public/assets/{nama_file}.{ekstensi}
     */
    public function getUrl(): string
    {
        if (str_starts_with($this->nama_file, 'assets/')) {
            return asset('storage/' . $this->nama_file);
        }
        return asset('storage/quiz-assets/' . $this->nama_file);
    }

    public function getStoragePath(): string
    {
        if (str_starts_with($this->nama_file, 'assets/')) {
            return Storage::disk('public')->path($this->nama_file);
        }
        return Storage::disk('public')->path('quiz-assets/' . $this->nama_file);
    }

    public function fileExists(): bool
    {
        return Storage::disk('public')->exists('quiz-assets/' . $this->nama_file);
    }

    /**
     * Cek apakah ini aset audio.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->tipe_mime, 'audio/');
    }

    /**
     * Cek apakah ini aset gambar.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->tipe_mime, 'image/');
    }

    /**
     * Cek apakah ini animasi Lottie.
     */
    public function isLottie(): bool
    {
        return $this->ekstensi === 'json' && $this->sumber_api === 'lottiefiles';
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Hanya aset yang aktif (dipakai di manifest AI).
     */
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Filter berdasarkan tag (mencari di dalam JSON array).
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tag', $tag);
    }

    /**
     * Filter berdasarkan tipe MIME (gambar, audio, dll).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('tipe_mime', 'LIKE', $type . '/%');
    }

    // =========================================================================
    // STATIC HELPERS
    // =========================================================================

    /**
     * Cari aset berdasarkan tag. Dipakai oleh AI untuk memilih aset.
     *
     * @param array $tags Tag yang dicari
     * @param string|null $type Tipe MIME ('image', 'audio', dll) - opsional
     */
    public static function findByTags(array $tags, ?string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::active();

        foreach ($tags as $tag) {
            $query->whereJsonContains('tag', $tag);
        }

        if ($type) {
            $query->where('tipe_mime', 'LIKE', $type . '/%');
        }

        return $query->get();
    }

    public static function getMissingAssets(array $assetsRequired): array
    {
        if (empty($assetsRequired)) return [];

        $normalized = [];
        foreach ($assetsRequired as $asset) {
            if (!is_string($asset)) continue;

            $hash = pathinfo($asset, PATHINFO_FILENAME);
            if (empty($hash)) continue;

            $normalized[$hash] = $asset;
        }

        if (empty($normalized)) return [];

        $existingHashes = self::whereIn('nama_file', array_keys($normalized))
            ->pluck('nama_file')
            ->toArray();

        $missing = [];
        foreach ($normalized as $hash => $originalName) {
            if (!in_array($hash, $existingHashes)) {
                $missing[] = $originalName;
            }
        }

        return $missing;
    }

    public static function resolveHash(string $filenameOrHash): string
    {
        return pathinfo($filenameOrHash, PATHINFO_FILENAME);
    }

    public static function generateHash(string $fileContent): string
    {
        return md5($fileContent);
    }

    /**
     * Cek apakah file dengan hash ini sudah ada di database.
     */
    public static function hashExists(string $hash): bool
    {
        return static::where('nama_file', $hash)->exists();
    }
}
