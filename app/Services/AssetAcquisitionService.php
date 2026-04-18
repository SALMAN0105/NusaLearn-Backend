<?php
// app/Services/AssetAcquisitionService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\AssetLibrary;

class AssetAcquisitionService
{
    // ═══════════════════════════════════════════════════════════════════
    // SECTION 1: UNIFIED SEARCH
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Unified search interface untuk semua sumber API.
     * Return format yang konsisten agar frontend tidak perlu tahu
     * perbedaan struktur response tiap API.
     */
    public function search(
        string $source,
        string $query,
        string $type  = 'image',
        int    $limit = 12
    ): array {
        return match($source) {
            'pixabay'     => $this->searchPixabay($query, $type, $limit),
            'pexels'      => $this->searchPexels($query, $type, $limit),
            'freesound'   => $this->searchFreesound($query, $limit),
            'freepik'     => $this->searchFreepik($query, $type, $limit),
            'iconify'     => $this->searchIconify($query, $limit),
            'lottiefiles' => $this->searchLottieFiles($query, $limit),
            'google'      => $this->searchGoogleImages($query, $limit),
            default       => throw new \InvalidArgumentException("Source tidak dikenal: {$source}"),
        };
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 2: SEARCH PER SOURCE
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Search Pixabay — Gambar & Ilustrasi Vektor
     * Docs: https://pixabay.com/api/docs/
     */
    private function searchPixabay(string $query, string $type, int $limit): array
    {
        $imageType = match($type) {
            'vector' => 'vector',
            'photo'  => 'photo',
            default  => 'illustration',
        };

        $response = Http::timeout(15)->get('https://pixabay.com/api/', [
            'key'        => config('services.pixabay.key'),
            'q'          => $query,
            'image_type' => $imageType,
            'per_page'   => min($limit, 20),
            'safesearch' => 'true',
            'lang'       => 'id',
        ]);

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            Log::warning('[AssetAcquisition@searchPixabay] API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        $hits = $response->json('hits', []);

        return array_map(fn($hit) => [
            'external_id'   => (string) $hit['id'],
            'source'        => 'pixabay',
            'asset_type'    => 'image',
            'preview_url'   => $hit['previewURL']  ?? $hit['webformatURL'],
            'download_url'  => $hit['largeImageURL'] ?? $hit['webformatURL'],
            'original_name' => "pixabay_{$hit['id']}.jpg",
            'tags'          => array_map('trim', explode(',', $hit['tags'] ?? '')),
            'width'         => $hit['imageWidth']  ?? 0,
            'height'        => $hit['imageHeight'] ?? 0,
            'attribution'   => [
                'author'     => $hit['user'] ?? 'Unknown',
                'source_url' => $hit['pageURL'] ?? '',
                'license'    => 'Pixabay License',
            ],
        ], $hits);
    }

    /**
     * Search Pexels — Foto Berkualitas Tinggi
     * Docs: https://www.pexels.com/api/documentation/
     * WAJIB: Tampilkan atribusi fotografer di UI
     */
    private function searchPexels(string $query, string $type, int $limit): array
    {
        $endpoint = $type === 'video'
            ? 'https://api.pexels.com/videos/search'
            : 'https://api.pexels.com/v1/search';

        $response = Http::timeout(15)
            ->withHeaders(['Authorization' => config('services.pexels.key')])
            ->get($endpoint, [
                'query'    => $query,
                'per_page' => min($limit, 20),
                'locale'   => 'id-ID',
            ]);

            /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            Log::warning('[AssetAcquisition@searchPexels] API error', [
                'status' => $response->status(),
            ]);
            return [];
        }

        $data = $response->json();

        // Handle foto vs video
        if ($type === 'video') {
            $items = $data['videos'] ?? [];
            return array_map(fn($video) => [
                'external_id'   => (string) $video['id'],
                'source'        => 'pexels',
                'asset_type'    => 'video',
                'preview_url'   => $video['image'] ?? '',
                'download_url'  => $video['video_files'][0]['link'] ?? '',
                'original_name' => "pexels_{$video['id']}.mp4",
                'tags'          => [],
                'attribution'   => [
                    // WAJIB ditampilkan di UI sesuai syarat Pexels
                    'author'      => $video['user']['name'] ?? 'Unknown',
                    'author_url'  => $video['user']['url']  ?? '',
                    'source_url'  => $video['url'] ?? '',
                    'license'     => 'Pexels License — Wajib Atribusi',
                ],
            ], $items);
        }

        $photos = $data['photos'] ?? [];
        return array_map(fn($photo) => [
            'external_id'   => (string) $photo['id'],
            'source'        => 'pexels',
            'asset_type'    => 'image',
            'preview_url'   => $photo['src']['medium']   ?? '',
            'download_url'  => $photo['src']['large2x']  ?? $photo['src']['large'],
            'original_name' => "pexels_{$photo['id']}.jpg",
            'tags'          => [],
            'attribution'   => [
                // WAJIB ditampilkan di UI sesuai syarat Pexels
                'author'      => $photo['photographer']     ?? 'Unknown',
                'author_url'  => $photo['photographer_url'] ?? '',
                'source_url'  => $photo['url'] ?? '',
                'license'     => 'Pexels License — Wajib Atribusi',
            ],
        ], $photos);
    }

    /**
     * Search Freesound — Audio & Sound Effects
     * Docs: https://freesound.org/docs/api/
     * WAJIB: Gunakan endpoint yang sudah di-approve
     * http://freesound.org/home/app_permissions/permission_granted/
     */
    private function searchFreesound(string $query, int $limit): array
    {
        $response = Http::timeout(15)->get('https://freesound.org/apiv2/search/text/', [
            'token'    => config('services.freesound.key'),
            'query'    => $query,
            'page_size'=> min($limit, 15),
            'fields'   => 'id,name,previews,tags,license,username,duration',
            'filter'   => 'duration:[0 TO 30]', // Maks 30 detik untuk kuis
        ]);

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            Log::warning('[AssetAcquisition@searchFreesound] API error', [
                'status' => $response->status(),
            ]);
            return [];
        }

        $results = $response->json('results', []);

        return array_map(fn($sound) => [
            'external_id'   => (string) $sound['id'],
            'source'        => 'freesound',
            'asset_type'    => 'audio',
                        'preview_url'   => $sound['previews']['preview-hq-mp3'] ?? $sound['previews']['preview-lq-mp3'] ?? '',
            'download_url'  => $sound['previews']['preview-hq-mp3'] ?? $sound['previews']['preview-lq-mp3'] ?? '',
            'original_name' => "freesound_{$sound['id']}.mp3",
            'tags'          => $sound['tags'] ?? [],
            'duration'      => $sound['duration'] ?? 0,
            'attribution'   => [
                'author'     => $sound['username'] ?? 'Unknown',
                'source_url' => "https://freesound.org/s/{$sound['id']}/",
                'license'    => $sound['license'] ?? 'Unknown',
            ],
        ], $results);
    }

    /**
     * Search Freepik — Ilustrasi & Vektor Premium
     * Docs: https://api.freepik.com/v1/resources
     */
    private function searchFreepik(string $query, string $type, int $limit): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'x-freepik-api-key' => config('services.freepik.key'),
                'Accept-Language'   => 'id',
            ])
            ->get('https://api.freepik.com/v1/resources', [
                'term'     => $query,
                'filters[content_type][freevector]' => 1,
                'limit'    => min($limit, 20),
                'page'     => 1,
            ]);

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            Log::warning('[AssetAcquisition@searchFreepik] API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        $items = $response->json('data', []);

        return array_map(fn($item) => [
            'external_id'   => (string) ($item['id'] ?? ''),
            'source'        => 'freepik',
            'asset_type'    => 'image',
            'preview_url'   => $item['image']['source']['url'] ?? '',
            'download_url'  => $item['image']['source']['url'] ?? '',
            'original_name' => "freepik_{$item['id']}.jpg",
            'tags'          => array_column($item['tags'] ?? [], 'name'),
            'attribution'   => [
                'author'     => 'Freepik',
                'source_url' => $item['url'] ?? '',
                'license'    => 'Freepik License',
            ],
        ], $items);
    }

    /**
     * Search Iconify — Icon SVG (Public, Tanpa API Key)
     * Docs: https://iconify.design/docs/api/
     * Endpoint: https://api.iconify.design/search?query=home&limit=20
     */
    private function searchIconify(string $query, int $limit): array
    {
        $response = Http::timeout(15)->get('https://api.iconify.design/search', [
            'query' => $query,
            'limit' => min($limit, 30),
        ]);

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            Log::warning('[AssetAcquisition@searchIconify] API error', [
                'status' => $response->status(),
            ]);
            return [];
        }

        $icons = $response->json('icons', []);

        // Format: "mdi:home", "fluent:home-24-filled", dll
        return array_map(function ($iconName) {
            // Split prefix:name → "mdi:home" → prefix=mdi, name=home
            [$prefix, $name] = explode(':', $iconName, 2) + ['', $iconName];

            $svgUrl = config('services.iconify.base_url') . "/{$prefix}/{$name}.svg";

            return [
                'external_id'   => $iconName,
                'source'        => 'iconify',
                'asset_type'    => 'icon',
                'preview_url'   => $svgUrl,
                'download_url'  => $svgUrl,
                'original_name' => str_replace(':', '_', $iconName) . '.svg',
                'tags'          => [$prefix, $name],
                'attribution'   => [
                    'author'     => 'Iconify',
                    'source_url' => "https://icon-sets.iconify.design/{$prefix}/{$name}/",
                    'license'    => 'Various Open Source Licenses',
                ],
            ];
        }, $icons);
    }

    /**
     * Search LottieFiles — Animasi JSON (Public CDN)
     * Menggunakan LottieFiles GraphQL API untuk search
     * Docs: https://lottiefiles.com/developers
     */
    private function searchLottieFiles(string $query, int $limit): array
    {
        // LottieFiles menggunakan GraphQL API
        $graphqlQuery = <<<GQL
        {
            search(query: "{$query}", first: {$limit}, type: ANIMATION) {
                edges {
                    node {
                        id
                        name
                        lottieUrl
                        gifUrl
                        createdBy {
                            name
                        }
                    }
                }
            }
        }
        GQL;

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://graphql.lottiefiles.com/', [
                    'query' => $graphqlQuery,
                ]);

            /** @var \Illuminate\Http\Client\Response $response */
            if (!$response->successful()) {
                Log::warning('[AssetAcquisition@searchLottieFiles] API error', [
                    'status' => $response->status(),
                ]);
                return [];
            }

            $edges = $response->json('data.search.edges', []);

            return array_map(fn($edge) => [
                'external_id'   => (string) ($edge['node']['id'] ?? ''),
                'source'        => 'lottiefiles',
                'asset_type'    => 'lottie',
                'preview_url'   => $edge['node']['gifUrl']    ?? '',
                'download_url'  => $edge['node']['lottieUrl'] ?? '',
                'original_name' => "lottie_{$edge['node']['id']}.json",
                'tags'          => [],
                'attribution'   => [
                    'author'     => $edge['node']['createdBy']['name'] ?? 'Unknown',
                    'source_url' => "https://lottiefiles.com/animations/{$edge['node']['id']}",
                    'license'    => 'LottieFiles Free License',
                ],
            ], $edges);

        } catch (\Exception $e) {
            Log::warning('[AssetAcquisition@searchLottieFiles] Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search Google Images (Tier-3 Fallback)
     * Menggunakan Google Custom Search JSON API.
     * PENTING: Anda harus memasukkan GOOGLE_SEARCH_API_KEY dan GOOGLE_SEARCH_CX di file .env
     */
    private function searchGoogleImages(string $query, int $limit): array
    {
        $apiKey = env('GOOGLE_SEARCH_API_KEY');
        $cx = env('GOOGLE_SEARCH_CX'); // Search Engine ID (Hanya untuk gambar)

        if (empty($apiKey) || empty($cx)) {
            Log::warning('[AssetAcquisition@searchGoogleImages] Kunci API Google belum dikonfigurasi di .env');
            return [];
        }

        $response = Http::timeout(15)->get('https://customsearch.googleapis.com/customsearch/v1', [
            'key' => $apiKey,
            'cx' => $cx,
            'q' => $query,
            'searchType' => 'image',
            'num' => min($limit, 10), // Maksimal 10 hasil per request gratis
            'safe' => 'high', // Filter aman untuk pendidikan
            'imgSize' => 'large',
        ]);

        /** @var \Illuminate\Http\Client\Response $response */
        if (!$response->successful()) {
            Log::warning('[AssetAcquisition@searchGoogleImages] API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        $items = $response->json('items', []);

        return array_map(function ($item) {
            // Gunakan md5 dari URL sebagai eksternal ID karena Google tidak selalu memberikan ID unik numerik
            $extId = md5($item['link'] ?? rand());
            return [
                'external_id'   => $extId,
                'source'        => 'google',
                'asset_type'    => 'image',
                'preview_url'   => $item['image']['thumbnailLink'] ?? $item['link'],
                'download_url'  => $item['link'] ?? '',
                'original_name' => "google_{$extId}.jpg",
                'tags'          => [],
                'attribution'   => [
                    'author'      => $item['displayLink'] ?? 'Google Images',
                    'source_url'  => $item['image']['contextLink'] ?? '',
                    'license'     => 'Fair Use (Educational) / Subject to Source Copyright',
                ],
            ];
        }, $items);
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 3: FETCH & STORE (CAS)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Download file dari URL eksternal dan simpan ke server.
     * Menggunakan Content-Addressable Storage (CAS):
     * - Nama file = MD5 hash dari konten file
     * - Jika konten sama → file tidak diduplikasi
     *
     * @throws \Exception Jika download gagal atau file tidak valid
     */
public function fetchAndStore(
        string  $url,
        string  $source,
        string  $assetType,
        array   $tags         = [],
        ?string $externalId   = null,
        ?string $originalName = null,
    ): AssetLibrary {
        // ── KUNCI THREAD (ATOMIC LOCK) ─────────────────────────────────
        // Big-O: O(1) Lock mechanism untuk mencegah duplicate parallel downloads
        $lockKey = 'downloading_asset_' . md5($url);
        
        return \Illuminate\Support\Facades\Cache::lock($lockKey, 60)->block(60, function() use (
            $url, $source, $assetType, $tags, $externalId, $originalName
        ) {
            Log::info('[AssetAcquisition] Memulai akuisisi terisolasi', ['url' => $url]);

            $response = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'NusaLearn/1.0 (Educational App)'])
                ->get($url);

            /** @var \Illuminate\Http\Client\Response $response */
            if (!$response->successful()) {
                throw new \Exception("Gagal download dari {$url}. Status: {$response->status()}");
            }

            $fileContent = $response->body();
            if (empty($fileContent)) {
                throw new \Exception("File korup/kosong dari URL: {$url}");
            }

            $hash = md5($fileContent);
            
            /** @var \Illuminate\Http\Client\Response $response */
            $extension = $this->guessExtensionFromResponse($response, $url, $assetType);
            $filename  = $hash . '.' . $extension;
            return \Illuminate\Support\Facades\DB::transaction(function () use (
                $filename, $extension, $fileContent, $originalName, $url, $assetType, $source, $externalId, $tags, $response
            ) {
                try {
                    // Simpan Fisik I/O
                    $storagePath = 'quiz-assets/' . $filename;
                    if (!Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->put($storagePath, $fileContent);
                    }

                    $mimeType = explode(';', $response->header('Content-Type') ?? $this->guessMimeType($filename))[0];

                    // Terapkan firstOrCreate untuk atomic level DB
                    return AssetLibrary::firstOrCreate(
                        ['filename' => $filename],
                        [
                            'original_name' => $originalName ?? basename(parse_url($url, PHP_URL_PATH)),
                            'extension'     => $extension,
                            'asset_type'    => $assetType,
                            'mime_type'     => trim($mimeType),
                            'size_kb'       => round(strlen($fileContent) / 1024),
                            'source_api'    => $source,
                            'external_id'   => $externalId,
                            'tags'          => array_values(array_filter($tags)),
                            'is_active'     => true,
                        ]
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                    // Catch SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
                    if ($e->getCode() == '23000') {
                        Log::info('[AssetAcquisition] Race condition mitigated (DB Constraint Hit)', ['filename' => $filename]);
                        return AssetLibrary::where('filename', $filename)->firstOrFail();
                    }
                    throw $e;
                }
            });
        });
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 4: BULK FETCH
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Fetch banyak aset sekaligus (digunakan oleh QuizGeneratorService
     * saat auto-fetch aset untuk soal yang baru di-generate).
     *
     * @param array $items Format: [['url'=>'...','source'=>'...','type'=>'...','tags'=>[...]], ...]
     * @return array Format: ['success'=>[AssetLibrary], 'failed'=>[['url'=>'...','error'=>'...']]]
     */
    public function bulkFetch(array $items): array
    {
        $success = [];
        $failed  = [];

        foreach ($items as $item) {
            try {
                $asset = $this->fetchAndStore(
                    url:          $item['url'],
                    source:       $item['source']       ?? 'unknown',
                    assetType:    $item['asset_type']   ?? 'image',
                    tags:         $item['tags']         ?? [],
                    externalId:   $item['external_id']  ?? null,
                    originalName: $item['original_name'] ?? null,
                );
                $success[] = $asset;

            } catch (\Exception $e) {
                Log::warning('[AssetAcquisition@bulkFetch] Gagal fetch item', [
                    'url'   => $item['url'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $failed[] = [
                    'url'   => $item['url'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $success,
            'failed'  => $failed,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 5: PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Tebak ekstensi file dari HTTP response headers dan URL.
     * Prioritas: Content-Type header → URL path → asset_type default
     */
    private function guessExtensionFromResponse(
        \Illuminate\Http\Client\Response $response,
        string $url,
        string $assetType
    ): string {
        // Prioritas 1: Content-Type header
        $contentType = $response->header('Content-Type') ?? '';
        $contentType = explode(';', $contentType)[0];
        $contentType = trim($contentType);

        $fromMime = match($contentType) {
            'image/jpeg'       => 'jpg',
            'image/png'        => 'png',
            'image/gif'        => 'gif',
            'image/webp'       => 'webp',
            'image/svg+xml'    => 'svg',
            'audio/mpeg'       => 'mp3',
            'audio/ogg'        => 'ogg',
            'audio/wav'        => 'wav',
            'video/mp4'        => 'mp4',
            'video/webm'       => 'webm',
            'application/json' => 'json',
            default            => null,
        };

        if ($fromMime) {
            return $fromMime;
        }

        // Prioritas 2: Ekstensi dari URL
        $urlPath  = parse_url($url, PHP_URL_PATH) ?? '';
        $urlExt   = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
        $validExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp3', 'ogg', 'wav', 'mp4', 'webm', 'json'];

        if (in_array($urlExt, $validExt)) {
            return $urlExt === 'jpeg' ? 'jpg' : $urlExt;
        }

        // Prioritas 3: Default berdasarkan asset_type
        return match($assetType) {
            'image'  => 'jpg',
            'audio'  => 'mp3',
            'video'  => 'mp4',
            'lottie' => 'json',
            'icon'   => 'svg',
            default  => 'bin',
        };
    }

    /**
     * Tebak MIME type dari ekstensi file.
     */
    private function guessMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            'mp3'         => 'audio/mpeg',
            'ogg'         => 'audio/ogg',
            'wav'         => 'audio/wav',
            'flac'        => 'audio/flac',
            'mp4'         => 'video/mp4',
            'webm'        => 'video/webm',
            'json'        => 'application/json',
            'lottie'      => 'application/json',
            default       => 'application/octet-stream',
        };
    }
}