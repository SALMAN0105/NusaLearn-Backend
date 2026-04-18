<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AssetLibrary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_libraries';

    protected $fillable = [
        'filename',
        'original_name',
        'extension',
        'mime_type',
        'size_kb',
        'source_api',
        'source_url',
        'source_id',
        'attribution',
        'tags',
        'category',
        'is_active',
    ];

    protected $casts = [
        'tags'      => 'array',
        'is_active' => 'boolean',
        'size_kb'   => 'integer',
    ];

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Kembalikan URL publik aset ini.
     * File disimpan di storage/app/public/assets/{filename}.{extension}
     */
    public function getUrl(): string
    {
        // Mendukung path baru (assets/...) dan path lama (hash MD5)
        if (str_starts_with($this->filename, 'assets/')) {
            return asset('storage/' . $this->filename);
        }
        return asset('storage/quiz-assets/' . $this->filename);
    }

    public function getStoragePath(): string
    {
        if (str_starts_with($this->filename, 'assets/')) {
            return Storage::disk('public')->path($this->filename);
        }
        return Storage::disk('public')->path('quiz-assets/' . $this->filename);
    }

    public function fileExists(): bool
    {
        return Storage::disk('public')->exists('quiz-assets/' . $this->filename);
    }

    /**
     * Cek apakah ini aset audio.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Cek apakah ini aset gambar.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Cek apakah ini animasi Lottie.
     */
    public function isLottie(): bool
    {
        return $this->extension === 'json' && $this->source_api === 'lottiefiles';
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Hanya aset yang aktif (dipakai di manifest AI).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter berdasarkan tag (mencari di dalam JSON array).
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Filter berdasarkan tipe MIME (gambar, audio, dll).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('mime_type', 'LIKE', $type . '/%');
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
            $query->whereJsonContains('tags', $tag);
        }

        if ($type) {
            $query->where('mime_type', 'LIKE', $type . '/%');
        }

        return $query->get();
    }

    public static function getMissingAssets(array $assetsRequired): array
        {
            if (empty($assetsRequired)) return [];

            $normalized = [];
            foreach ($assetsRequired as $asset) {
                // Evaluasi tipe data secara ketat
                if (!is_string($asset)) continue; 
                
                $hash = pathinfo($asset, PATHINFO_FILENAME);
                if (empty($hash)) continue;
                
                $normalized[$hash] = $asset; 
            }

            if (empty($normalized)) return [];

            $existingHashes = self::whereIn('filename', array_keys($normalized))
                ->pluck('filename')
                ->toArray();

            $missing = [];
            foreach ($normalized as $hash => $originalName) {
                if (!in_array($hash, $existingHashes)) {
                    $missing[] = $originalName;
                }
            }

            return $missing;
        }

    // ✅ BONUS: Helper untuk resolve filename → hash
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
        return static::where('filename', $hash)->exists();
    }
}