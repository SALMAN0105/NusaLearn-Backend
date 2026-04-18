<?php
// app/Services/ManifestGeneratorService.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\AssetLibrary;

class ManifestGeneratorService
{
    // Cache manifest selama 5 menit
    // Setiap kali aset baru ditambah, cache di-invalidate
    private const CACHE_KEY = 'asset_manifest_v1';
    private const CACHE_TTL = 300; // 5 menit

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 1: GENERATE MANIFEST
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Generate manifest aset yang akan dikirim ke AI.
     * Manifest berisi daftar file yang BENAR-BENAR ada di disk,
     * sehingga AI tidak bisa berhalusinasi nama file.
     *
     * Format output:
     * {
     *   "images":  [{"filename": "abc.jpg", "tags": ["rumah", "adat"]}, ...],
     *   "audio":   [{"filename": "def.mp3", "tags": ["gamelan", "jawa"]}, ...],
     *   "lottie":  [{"filename": "ghi.json","tags": ["animasi", "bintang"]}, ...],
     *   "icons":   [{"filename": "jkl.svg", "tags": ["mdi", "home"]}, ...],
     * }
     */
    public function generate(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->buildManifest();
        });
    }

    /**
     * Invalidate cache manifest.
     * Dipanggil setiap kali aset baru ditambah atau dihapus.
     */
    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('[ManifestGenerator] Cache manifest di-invalidate.');
    }

    /**
     * Generate manifest dalam format string yang siap dimasukkan ke AI prompt.
     * Lebih ringkas dari format JSON penuh.
     *
     * Contoh output:
     * === AVAILABLE ASSETS ===
     * [IMAGES]
     * - abc123.jpg (tags: rumah, adat, jawa)
     * - def456.png (tags: batik, motif)
     *
     * [AUDIO]
     * - ghi789.mp3 (tags: gamelan, musik, tradisional)
     */
    public function generateForPrompt(bool $forceRefresh = false): string
    {
        $manifest = $this->generate($forceRefresh);

        $lines = ['=== AVAILABLE ASSETS ==='];
        $lines[] = 'PENTING: Hanya gunakan filename yang tercantum di bawah ini.';
        $lines[] = 'Jangan mengarang nama file yang tidak ada dalam daftar ini.';
        $lines[] = '';

        $typeLabels = [
            'images' => 'IMAGES (Gunakan untuk field: image_url, option_image)',
            'audio'  => 'AUDIO  (Gunakan untuk field: audio_url, sound_file)',
            'lottie' => 'LOTTIE (Gunakan untuk field: animation_file)',
            'icons'  => 'ICONS  (Gunakan untuk field: icon_file)',
        ];

        foreach ($typeLabels as $key => $label) {
            $items = $manifest[$key] ?? [];

            if (empty($items)) {
                continue;
            }

            $lines[] = "[{$label}]";

            foreach ($items as $item) {
                $tags    = implode(', ', $item['tags'] ?? []);
                $tagStr  = $tags ? " (tags: {$tags})" : '';
                $lines[] = "  - {$item['filename']}{$tagStr}";
            }

            $lines[] = '';
        }

        $lines[] = '=== END OF ASSETS ===';

        return implode("\n", $lines);
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 2: BUILD MANIFEST
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Build manifest dari database + verifikasi file fisik di disk.
     * Hanya file yang ADA di disk yang dimasukkan ke manifest.
     * Ini adalah kunci untuk mencegah AI berhalusinasi nama file.
     */
    private function buildManifest(): array
    {
        $manifest = [
            'images' => [],
            'audio'  => [],
            'lottie' => [],
            'icons'  => [],
        ];

        // Ambil semua aset aktif dari database
        $assets = AssetLibrary::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $verifiedCount   = 0;
        $missingCount    = 0;

        foreach ($assets as $asset) {
            // Verifikasi file fisik ada di disk
            $filePath = str_starts_with($asset->filename, 'assets/') 
                        ? $asset->filename 
                        : 'quiz-assets/' . $asset->filename;

            if (!Storage::disk('public')->exists($filePath)) {
                // File ada di DB tapi tidak ada di disk
                // Skip dan log untuk investigasi
                $missingCount++;
                Log::warning('[ManifestGenerator] File missing dari disk', [
                    'filename' => $asset->filename,
                    'asset_id' => $asset->id,
                ]);
                continue;
            }

            $verifiedCount++;

            // Normalisasi tags
            $tags = $asset->tags ?? [];
            if (is_string($tags)) {
                $tags = json_decode($tags, true) ?? [];
            }

            $item = [
                'filename'    => $asset->filename,
                'tags'        => array_values(array_filter($tags)),
                'source'      => $asset->source_api ?? 'unknown',
                'file_size'   => $asset->file_size ?? 0,
            ];

            // Kategorikan berdasarkan asset_type
            match($asset->asset_type) {
                'image', 'photo', 'vector'
                    => $manifest['images'][] = $item,
                'audio', 'sound'
                    => $manifest['audio'][]  = $item,
                'lottie', 'animation'
                    => $manifest['lottie'][] = $item,
                'icon', 'svg'
                    => $manifest['icons'][]  = $item,
                default => null, // Skip tipe tidak dikenal
            };
        }

        Log::info('[ManifestGenerator] Manifest berhasil di-build', [
            'verified' => $verifiedCount,
            'missing'  => $missingCount,
            'images'   => count($manifest['images']),
            'audio'    => count($manifest['audio']),
            'lottie'   => count($manifest['lottie']),
            'icons'    => count($manifest['icons']),
        ]);

        return $manifest;
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 3: UTILITY
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Validasi apakah sebuah filename ada di manifest.
     * Digunakan oleh QuizGeneratorService untuk validasi output AI
     * sebelum disimpan ke database.
     */
    public function filenameExists(string $filename): bool
    {
        $manifest = $this->generate();

        foreach ($manifest as $typeItems) {
            foreach ($typeItems as $item) {
                if ($item['filename'] === $filename) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validasi array of filenames sekaligus.
     * Return array filename yang TIDAK ada di manifest.
     *
     * @param  array  $filenames
     * @return array  Filename yang tidak valid (halusinasi AI)
     */
    public function validateFilenames(array $filenames): array
    {
        if (empty($filenames)) {
            return [];
        }

        $manifest = $this->generate();

        // Buat flat set semua filename yang valid untuk O(1) lookup
        $validFilenames = collect($manifest)
            ->flatten(1)
            ->pluck('filename')
            ->flip() // flip untuk O(1) isset() check
            ->toArray();

        $invalid = [];
        foreach ($filenames as $filename) {
            if (!isset($validFilenames[$filename])) {
                $invalid[] = $filename;
            }
        }

        return $invalid;
    }

    /**
     * Ambil statistik manifest.
     * Digunakan untuk dashboard admin.
     */
    public function getStats(): array
    {
        $manifest = $this->generate();

        return [
            'total_assets' => array_sum(array_map('count', $manifest)),
            'by_type'      => array_map('count', $manifest),
            'cache_ttl'    => self::CACHE_TTL,
            'generated_at' => now()->toISOString(),
        ];
    }
}