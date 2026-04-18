<?php
// app/Services/QuizGeneratorService.php
// Versi dengan Auto-Fetch Aset Otomatis

namespace App\Services;

use App\Models\Question;
use App\Models\Material;
use App\Models\AssetLibrary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuizGeneratorService
{
    const MAX_TOKENS = 2500;

    // Mapping template → tipe aset yang dibutuhkan
    // Dipakai untuk menentukan sumber API mana yang dicari
    const TEMPLATE_ASSET_NEEDS = [
        Question::TEMPLATE_MULTIPLE_CHOICE => ['image'],          // gambar untuk pilihan / soal
        Question::TEMPLATE_DRAG_AND_DROP   => ['image'],          // gambar untuk item drag
        Question::TEMPLATE_MATCHING_GAME   => ['image'],          // gambar untuk pasangan
        Question::TEMPLATE_FILL_BLANK      => ['audio'],          // audio opsional
        Question::TEMPLATE_IMAGE_QUIZ      => ['image', 'audio'], // gambar utama + audio
    ];

    // Sumber API per tipe aset (urutan prioritas)
    const ASSET_SOURCES = [
        'image' => ['pixabay', 'pexels','google'],
        'audio' => ['freesound'],
        'lottie' => ['lottiefiles'],
    ];

    public function __construct(
        private ManifestGeneratorService  $manifest,
        private AssetAcquisitionService   $acquisition,
    ) {}

    // =========================================================================
    // PUBLIC — Entry Point
    // =========================================================================

    public function generate(int $materialId, string $templateType, int $difficulty = 3): array
    {
        if (!array_key_exists($templateType, Question::ALL_TEMPLATES)) {
            return $this->fail("Template '{$templateType}' tidak dikenali.");
        }

        $material = Material::find($materialId);
        if (!$material) {
            return $this->fail("Material ID {$materialId} tidak ditemukan.");
        }

        // ── STEP 1: Generate soal via AI ──────────────────────────────
        $manifestText = $this->manifest->generateForPrompt();
        $systemPrompt = $this->buildSystemPrompt($templateType);
        $userContent  = $this->buildUserPrompt($material, $templateType, $difficulty, $manifestText);

        Log::info("[QuizGenerator] Memanggil AI", [
            'template'    => $templateType,
            'material_id' => $materialId,
            'difficulty'  => $difficulty,
        ]);

        $rawJson = $this->callAI($systemPrompt, $userContent);

        if ($rawJson === null) {
            return $this->fail('AI tidak merespons atau terjadi error jaringan.');
        }

        $parsed = $this->parseAndValidate($rawJson, $templateType);
        if (!$parsed['valid']) {
            return $this->fail($parsed['error'], $rawJson);
        }

        $questionData   = $parsed['question_data'];
        $assetsRequired = $parsed['assets_required'];
        $assetKeywords  = $parsed['asset_keywords'];

        // ── STEP 2: Auto-fetch aset yang belum ada ────────────────────
        $fetchResults = $this->autoFetchMissingAssets(
            $assetKeywords,
            $templateType,
            $material->title_indo ?? ''
        );

        // ── STEP 3: Ganti placeholder filename dengan file nyata ──────
        $questionData = $this->resolveAssetFilenames($questionData, $fetchResults['resolved']);

        // Update assets_required dengan filename yang sudah di-resolve
        $assetsRequired = array_values(array_filter(
            array_unique(array_merge(
                $assetsRequired,
                array_values($fetchResults['resolved'])
            ))
        ));

        // ── STEP 4: Cek aset yang masih kurang ───────────────────────
        $missingAssets = $this->checkMissingAssets($assetsRequired);

        // Invalidate manifest cache agar aset baru ikut masuk
        if (!empty($fetchResults['fetched'])) {
            $this->manifest->invalidateCache();
        }

        Log::info("[QuizGenerator] Selesai", [
            'template'      => $templateType,
            'material_id'   => $materialId,
            'assets_fetched'=> count($fetchResults['fetched']),
            'assets_failed' => count($fetchResults['failed']),
            'missing_count' => count($missingAssets),
        ]);

        return [
            'success'          => true,
            'question_data'    => $questionData,
            'assets_required'  => $assetsRequired,
            'missing_assets'   => $missingAssets,
            'raw_json'         => $rawJson,
            'auto_fetch_log'   => [
                'fetched' => $fetchResults['fetched'],
                'failed'  => $fetchResults['failed'],
            ],
            'error'            => null,
        ];
    }

    // =========================================================================
    // PRIVATE — Auto Fetch Assets
    // =========================================================================

    /**
     * Cari dan download aset otomatis berdasarkan keyword dari AI.
     *
     * @param array  $assetKeywords Format: [
     *   ['slot' => 'main_image', 'keywords' => ['rumah adat', 'tolaki'], 'type' => 'image'],
     *   ['slot' => 'audio_soal', 'keywords' => ['traditional music'],    'type' => 'audio'],
     * ]
     * @param string $templateType  Template soal
     * @param string $materialTitle Judul materi (dipakai sebagai konteks pencarian)
     *
     * @return array [
     *   'resolved' => ['slot_name' => 'filename.ext', ...],  // mapping slot → file
     *   'fetched'  => [...info aset berhasil],
     *   'failed'   => [...info aset gagal],
     * ]
     */
    private function autoFetchMissingAssets(
        array  $assetKeywords,
        string $templateType,
        string $materialTitle
    ): array {
        $resolved = [];
        $fetched  = [];
        $failed   = [];

        if (empty($assetKeywords)) {
            return compact('resolved', 'fetched', 'failed');
        }

        $neededTypes = self::TEMPLATE_ASSET_NEEDS[$templateType] ?? ['image'];

        $allowedTypes = array_unique(array_merge($neededTypes, ['lottie', 'audio']));

        foreach ($assetKeywords as $assetSlot) {
            $slot     = $assetSlot['slot']     ?? 'unknown';
            $keywords = $assetSlot['keywords'] ?? [];
            $type     = $assetSlot['type']     ?? 'image';

            // Skip jika tipe tidak dibutuhkan template ini
            if (!in_array($type, $allowedTypes)) {
                continue;
            }

            // Cek apakah sudah ada di library (cari berdasarkan tag)
            $existing = $this->findExistingAssetByKeywords($keywords, $type);
            if ($existing) {
                $resolved[$slot] = $existing->filename;
                Log::info("[QuizGenerator] Aset ditemukan di library: {$existing->filename}", compact('slot', 'keywords'));
                continue;
            }

            // Belum ada → fetch dari API eksternal
            $asset = $this->fetchAssetFromExternalApi($keywords, $type, $slot, $materialTitle);

            if ($asset) {
                $resolved[$slot] = $asset->filename;
                $fetched[] = [
                    'slot'     => $slot,
                    'filename' => $asset->filename,
                    'source'   => $asset->source_api,
                    'type'     => $type,
                    'keywords' => $keywords,
                ];
            } else {
                $failed[] = [
                    'slot'     => $slot,
                    'type'     => $type,
                    'keywords' => $keywords,
                    'reason'   => 'Tidak ditemukan di semua sumber API',
                ];
            }
        }

        return compact('resolved', 'fetched', 'failed');
    }

    /**
     * Cari aset yang sudah ada di library berdasarkan tag/keyword.
     */
    private function findExistingAssetByKeywords(array $keywords, string $type): ?AssetLibrary
    {
        if (empty($keywords)) return null;

        $query = AssetLibrary::where('is_active', true);

        // Filter tipe MIME
        if ($type === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($type === 'audio') {
            $query->where('mime_type', 'like', 'audio/%');
        }

        // Cari berdasarkan tag (minimal 1 keyword match)
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $kw) {
                $kw = strtolower(trim($kw));
                if ($kw) {
                    $q->orWhereJsonContains('tags', $kw)
                      ->orWhere('original_name', 'like', "%{$kw}%");
                }
            }
        });

        return $query->inRandomOrder()->first();
    }

    /**
     * Fetch aset dari API eksternal (Pixabay untuk gambar, Freesound untuk audio).
     * Mencoba sumber satu per satu sampai berhasil.
     */
    private function fetchAssetFromExternalApi(
        array  $keywords,
        string $type,
        string $slot,
        string $materialTitle
    ): ?AssetLibrary {
        $sources = self::ASSET_SOURCES[$type] ?? [];
        $query   = implode(' ', array_slice($keywords, 0, 3));

        foreach ($sources as $source) {
            try {
                Log::info("[QuizGenerator] Mencari aset dari {$source}", [
                    'query'  => $query,
                    'type'   => $type,
                    'slot'   => $slot,
                ]);

                // Search dulu (ambil hasil pertama)
                $results = $this->acquisition->search(
                    source: $source,
                    query:  $query,
                    type:   $type,
                    limit:  5,
                );

                if (empty($results)) {
                    Log::info("[QuizGenerator] Tidak ada hasil dari {$source} untuk: {$query}");
                    continue;
                }

                // Ambil hasil pertama yang punya download_url
                $best = null;
                foreach ($results as $result) {
                    if (!empty($result['download_url'])) {
                        $best = $result;
                        break;
                    }
                }

                if (!$best) continue;

                // Download & simpan
                $tags = array_merge(
                    $keywords,
                    [$type, $source]
                );
                $tags = array_values(array_unique(array_filter($tags)));

                $asset = $this->acquisition->fetchAndStore(
                    url:          $best['download_url'],
                    source:       $source,
                    assetType:    $type,
                    tags:         $tags,
                    externalId:   $best['external_id'] ?? null,
                    originalName: $best['original_name'] ?? null,
                );

                Log::info("[QuizGenerator] Aset berhasil di-fetch dari {$source}: {$asset->filename}");
                return $asset;

            } catch (\Exception $e) {
                Log::warning("[QuizGenerator] Gagal fetch dari {$source}: " . $e->getMessage(), [
                    'query' => $query,
                    'slot'  => $slot,
                ]);
                // Lanjut ke source berikutnya
            }
        }

        return null;
    }

    /**
     * Ganti placeholder slot di question_data dengan filename aset nyata.
     * AI menghasilkan placeholder seperti "__SLOT_main_image__",
     * kita ganti dengan filename asli setelah fetch.
     *
     * @param array $questionData  Data soal dari AI
     * @param array $resolved      ['slot_name' => 'filename.ext']
     */
    private function resolveAssetFilenames(array $questionData, array $resolved): array
    {
        if (empty($resolved)) return $questionData;

        // Encode ke string, replace, decode kembali
        // Cara ini aman untuk nested array dalam satu operasi
        $jsonStr = json_encode($questionData);

        foreach ($resolved as $slot => $filename) {
            $placeholder = '__SLOT_' . $slot . '__';
            $jsonStr     = str_replace(
                json_encode($placeholder), // placeholder dalam JSON (dengan quote)
                json_encode($filename),    // filename dalam JSON (dengan quote)
                $jsonStr
            );
        }

        $result = json_decode($jsonStr, true);

        // Fallback jika decode gagal
        return $result ?? $questionData;
    }

    // =========================================================================
    // PRIVATE — Prompt Builder
    // =========================================================================

    private function buildSystemPrompt(string $templateType): string
    {
        $schema = $this->getJsonSchema($templateType);

        return <<<PROMPT
        Kamu adalah Quiz Generator Engine tingkat lanjut untuk aplikasi pendidikan NusaLearn.
        Tugasmu: buat SATU soal interaktif bertipe "{$templateType}" dengan kualitas pedagogi tinggi.

        OUTPUT WAJIB JSON MURNI sesuai schema berikut:
        {$schema}

        ATURAN MUTLAK (TECHNICAL TRUTH):
        1. **PRIORITAS ASET LOKAL:** Periksa daftar === AVAILABLE ASSETS ===. Jika ada file yang cocok secara konteks (misal: gambar_anawai.jpg), TULISKAN FILENAME TERSEBUT SECARA LANGSUNG pada field gambar.
        2. **FALLBACK ASET (SLOT):** JIKA DAN HANYA JIKA tidak ada aset lokal yang cocok, gunakan format placeholder "__SLOT_kata_kunci_inggris__" (Contoh: "__SLOT_traditional_house__").
        3. **WAJIB ADA PENGECOH (DISTRACTORS):** Jangan berikan kuis yang terlalu mudah. Untuk Drag & Drop, Matching, Fill Blank, dan Image Quiz, WAJIB buat minimal 3-4 opsi/area, di mana hanya 1 yang benar dan sisanya adalah jebakan (distraktor) yang masuk akal.
        4. "asset_keywords" WAJIB diisi dalam Bahasa Inggris jika menggunakan "__SLOT_...__".
        5. Bahasa Indonesia baku untuk siswa SD/SMP. Tidak ada markdown, tidak ada teks sebelum/sesudah JSON.
        PROMPT;
            }

    private function buildUserPrompt(
        Material $material,
        string   $templateType,
        int      $difficulty,
        string   $manifestText
    ): string {
        $summary = '';

        if ($material->ai_embeddings) {
            $embeddings = is_array($material->ai_embeddings)
                ? $material->ai_embeddings
                : json_decode($material->ai_embeddings, true);
            $summary = $embeddings['knowledge_base']['summary'] ?? '';
        }

        if (empty($summary) && $material->content_indo) {
            $content = is_array($material->content_indo)
                ? $material->content_indo
                : json_decode($material->content_indo, true);
            $km      = $content['knowledge_map'] ?? [];
            $summary = $km['context_summary'] ?? '';
        }

        $difficultyLabel = match($difficulty) {
            1       => 'Sangat Dasar',
            2       => 'Dasar',
            3       => 'Menengah',
            4       => 'Lanjutan',
            5       => 'Kompleksitas Tinggi',
            default => 'Menengah',
        };

        return <<<PROMPT
=== INFORMASI MATERI ===
Judul: {$material->title_indo}
Kategori: {$material->category}
Tingkat Kesulitan Target: Level {$difficulty} ({$difficultyLabel})

Ringkasan Konten:
{$summary}

=== TEMPLATE YANG DIMINTA ===
{$templateType}

{$manifestText}

Buat SATU soal bertipe "{$templateType}" yang relevan dengan materi di atas.

PENTING untuk field aset:
- Gunakan placeholder "__SLOT_nama__" untuk field gambar/audio
- Isi "asset_keywords" dengan keyword Bahasa Inggris yang spesifik
- Sistem akan otomatis mencari dan download aset yang sesuai
PROMPT;
    }

    // =========================================================================
    // PRIVATE — JSON Schema per Template (dengan asset_keywords)
    // =========================================================================

    private function getJsonSchema(string $templateType): string
    {
                // asset_keywords adalah field BARU yang wajib diisi AI
                // Format: array of object dengan slot, keywords, type
                $assetKeywordsSchema = <<<'JSON'
        "asset_keywords": [
            {
            "slot": "nama_slot_placeholder",
            "keywords": ["keyword1 english", "keyword2 english", "keyword3 english"],
            "type": "image|audio"
            }
        ]
        JSON;

                return match($templateType) {
                    Question::TEMPLATE_MULTIPLE_CHOICE => <<<JSON
        {
        "question_text_indo": "string — teks pertanyaan",
        "template_type": "multiple_choice",
        "options": [
            {"id": "a", "text": "string", "image_asset": "__SLOT_option_a_image__ atau null"},
            {"id": "b", "text": "string", "image_asset": "__SLOT_option_b_image__ atau null"},
            {"id": "c", "text": "string", "image_asset": "__SLOT_option_c_image__ atau null"},
            {"id": "d", "text": "string", "image_asset": "__SLOT_option_d_image__ atau null"}
        ],
        "correct_answer_key": "a|b|c|d",
        "explanation": "string — penjelasan mengapa jawaban benar",
        "assets_required": [],
        "feedback_animation": null,
        {$assetKeywordsSchema}
        }

        CATATAN: Isi image_asset pada setiap opsi dengan "__SLOT_option_X_image__" jika gambar relevan untuk opsi tersebut, atau null jika tidak perlu. Isi asset_keywords sesuai opsi yang pakai gambar.
        JSON,

        Question::TEMPLATE_DRAG_AND_DROP => <<<JSON
        {
        "question_text_indo": "string — instruksi",
        "template_type": "drag_and_drop",
        "items": [
            {"id": "item_1", "text": "Jawaban Benar", "image_asset": null, "correct_zone": "zone_1"},
            {"id": "item_2", "text": "Pengecoh 1", "image_asset": null, "correct_zone": null},
            {"id": "item_3", "text": "Pengecoh 2", "image_asset": null, "correct_zone": null}
        ],
        "zones": [
            {"id": "zone_1", "label": "Kategori Utama", "image_asset": null}
        ],
        "correct_mapping": {"item_1": "zone_1"},
        "explanation": "string",
        "assets_required": [],
        "feedback_animation": null,
        {$assetKeywordsSchema}
        }
        JSON,

                    Question::TEMPLATE_MATCHING_GAME => <<<JSON
        {
        "question_text_indo": "string — instruksi pasangkan",
        "template_type": "matching_game",
        "pairs": [
            {
            "left":  {"id": "L1", "text": "string", "image_asset": "__SLOT_left_1_image__ atau null"},
            "right": {"id": "R1", "text": "string", "image_asset": "__SLOT_right_1_image__ atau null"}
            }
        ],
        "correct_pairs": [{"left": "L1", "right": "R1"}],
        "explanation": "string",
        "assets_required": [],
        "feedback_animation": null,
        {$assetKeywordsSchema}
        }
        JSON,

                    Question::TEMPLATE_FILL_BLANK => <<<JSON
        {
        "question_text_indo": "string — kalimat dengan ___ sebagai penanda blank",
        "template_type": "fill_blank",
        "blanks": [
            {"id": "blank_1", "correct_answer": "string", "hint": "string atau null"}
        ],
        "word_bank": ["pilihan_1", "pilihan_2", "pilihan_3", "jawaban_benar"],
        "audio_question": "__SLOT_audio_soal__ atau null",
        "explanation": "string",
        "assets_required": [],
        "feedback_animation": null,
        {$assetKeywordsSchema}
        }

        CATATAN: Untuk fill_blank, sertakan audio_question jika relevan (misal: siswa mendengar audio lalu mengisi blank). Isi asset_keywords dengan keyword audio jika audio_question dipakai.
        JSON,

        Question::TEMPLATE_IMAGE_QUIZ => <<<JSON
        {
        "question_text_indo": "string — pertanyaan tentang gambar",
        "template_type": "image_quiz",
        "main_image": "namafile_dari_manifest.jpg ATAU __SLOT_main_image__",
        "audio_hint": null,
        "tap_areas": [
            { "id": "area_1", "label": "Jawaban Benar", "x_percent": 0.2, "y_percent": 0.2, "width_percent": 0.2, "height_percent": 0.2, "is_correct": true },
            { "id": "area_2", "label": "Pengecoh 1", "x_percent": 0.5, "y_percent": 0.5, "width_percent": 0.2, "height_percent": 0.2, "is_correct": false },
            { "id": "area_3", "label": "Pengecoh 2", "x_percent": 0.8, "y_percent": 0.8, "width_percent": 0.2, "height_percent": 0.2, "is_correct": false }
        ],
        "correct_area": "area_1",
        "explanation": "string",
        "assets_required": [],
        "feedback_animation": null,
        {$assetKeywordsSchema}
        }
        JSON,

            default => '{}',
        };
    }

    // =========================================================================
    // PRIVATE — AI Call
    // =========================================================================

    private function callAI(string $systemPrompt, string $userContent): ?string
    {
        $baseUrl = rtrim(config('services.ai.base_url', 'https://api.chatanywhere.tech/v1'), '/');
        $model   = config('services.ai.model', 'gpt-4o-mini');
        $apiKey  = config('services.ai.key');

        if (empty($apiKey)) {
            Log::error('[QuizGenerator] services.ai.key tidak dikonfigurasi.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->retry(3, 1000, function (\Exception $e, $request) {
                if ($e instanceof \Illuminate\Http\Client\RequestException) {
                    $status = $e->response?->status() ?? 0;
                    return $status >= 500 || $status === 0;
                }
                return true;
            })
            ->timeout(60)
            ->post($baseUrl . '/chat/completions', [
                'model'           => $model,
                'max_tokens'      => self::MAX_TOKENS,
                'temperature'     => 0.3,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userContent],
                ],
            ]);
            
            /** @var \Illuminate\Http\Client\Response $response */
            if ($response->failed()) {
                Log::error('[QuizGenerator] AI API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $text = $response->json('choices.0.message.content');

            if (empty($text)) {
                Log::error('[QuizGenerator] Response content kosong');
                return null;
            }

            return $text;

        } catch (\Throwable $e) {
            Log::error('[QuizGenerator] Exception: ' . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // PRIVATE — Parse + Validate
    // =========================================================================

    private function parseAndValidate(string $rawJson, string $templateType): array
    {
        $clean = trim(preg_replace(
            '/^```(?:json)?\s*/m', '',
            preg_replace('/```\s*$/m', '', $rawJson)
        ));

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid'          => false,
                'error'          => 'Output AI bukan JSON valid: ' . json_last_error_msg(),
                'asset_keywords' => [],
            ];
        }

        if (empty($decoded['question_text_indo'])) {
            return [
                'valid'          => false,
                'error'          => 'Field "question_text_indo" kosong.',
                'asset_keywords' => [],
            ];
        }

        if (!isset($decoded['template_type'])) {
            $decoded['template_type'] = $templateType;
        }

        if (!isset($decoded['assets_required']) || !is_array($decoded['assets_required'])) {
            $decoded['assets_required'] = [];
        }

        // Ambil & validasi asset_keywords dari AI
        $assetKeywords = [];
        if (!empty($decoded['asset_keywords']) && is_array($decoded['asset_keywords'])) {
            foreach ($decoded['asset_keywords'] as $kw) {
                if (
                    isset($kw['slot'], $kw['keywords'], $kw['type'])
                    && is_array($kw['keywords'])
                    && !empty($kw['keywords'])
                    && in_array($kw['type'], ['image', 'audio', 'video', 'lottie'])
                ) {
                    $assetKeywords[] = [
                        'slot'     => (string) $kw['slot'],
                        'keywords' => array_filter(array_map('strval', $kw['keywords'])),
                        'type'     => (string) $kw['type'],
                    ];
                }
            }
        }

        // Hapus asset_keywords dari question_data (tidak perlu disimpan di DB)
        unset($decoded['asset_keywords']);

        // Validasi struktur per template
        $structureCheck = $this->validateTemplateStructure($decoded, $templateType);
        if (!$structureCheck['valid']) {
            return array_merge($structureCheck, ['asset_keywords' => []]);
        }

        return [
            'valid'           => true,
            'question_data'   => $decoded,
            'assets_required' => $decoded['assets_required'],
            'asset_keywords'  => $assetKeywords,
            'error'           => null,
        ];
    }

    private function validateTemplateStructure(array $data, string $templateType): array
    {
        switch ($templateType) {
            case Question::TEMPLATE_MULTIPLE_CHOICE:
                if (empty($data['options']) || !is_array($data['options'])) {
                    return ['valid' => false, 'error' => 'MC: field "options" kosong atau bukan array.'];
                }
                if (count($data['options']) < 2) {
                    return ['valid' => false, 'error' => 'MC: minimal 2 pilihan jawaban.'];
                }
                if (empty($data['correct_answer_key'])) {
                    return ['valid' => false, 'error' => 'MC: field "correct_answer_key" kosong.'];
                }
                break;

            case Question::TEMPLATE_DRAG_AND_DROP:
                if (empty($data['items']) || !is_array($data['items'])) {
                    return ['valid' => false, 'error' => 'DnD: field "items" kosong atau bukan array.'];
                }
                if (empty($data['zones']) || !is_array($data['zones'])) {
                    return ['valid' => false, 'error' => 'DnD: field "zones" kosong atau bukan array.'];
                }
                if (empty($data['correct_mapping']) || !is_array($data['correct_mapping'])) {
                    return ['valid' => false, 'error' => 'DnD: field "correct_mapping" kosong.'];
                }
                break;

            case Question::TEMPLATE_MATCHING_GAME:
                if (empty($data['pairs']) || !is_array($data['pairs'])) {
                    return ['valid' => false, 'error' => 'Matching: field "pairs" kosong atau bukan array.'];
                }
                if (empty($data['correct_pairs']) || !is_array($data['correct_pairs'])) {
                    return ['valid' => false, 'error' => 'Matching: field "correct_pairs" kosong.'];
                }
                break;

            case Question::TEMPLATE_FILL_BLANK:
                if (empty($data['blanks']) || !is_array($data['blanks'])) {
                    return ['valid' => false, 'error' => 'FillBlank: field "blanks" kosong atau bukan array.'];
                }
                foreach ($data['blanks'] as $idx => $blank) {
                    if (empty($blank['correct_answer'])) {
                        return ['valid' => false, 'error' => "FillBlank: blank_{$idx} tidak punya correct_answer."];
                    }
                }
                break;

            case Question::TEMPLATE_IMAGE_QUIZ:
                if (empty($data['main_image'])) {
                    return ['valid' => false, 'error' => 'ImageQuiz: field "main_image" kosong.'];
                }
                if (empty($data['tap_areas']) || !is_array($data['tap_areas'])) {
                    return ['valid' => false, 'error' => 'ImageQuiz: field "tap_areas" kosong atau bukan array.'];
                }
                if (empty($data['correct_area'])) {
                    return ['valid' => false, 'error' => 'ImageQuiz: field "correct_area" kosong.'];
                }
                break;
        }

        return ['valid' => true, 'error' => null];
    }

    // =========================================================================
    // PRIVATE — Check Missing Assets
    // =========================================================================

    private function checkMissingAssets(array $assetsRequired): array
    {
        if (empty($assetsRequired)) {
            return [];
        }

        // Filter placeholder yang belum di-resolve (masih __SLOT_xxx__)
        $realFilenames = array_filter($assetsRequired, fn($f) => !str_starts_with($f, '__SLOT_'));

        if (empty($realFilenames)) {
            return [];
        }

        $existing = AssetLibrary::whereIn('filename', $realFilenames)
            ->where('is_active', true)
            ->pluck('filename')
            ->toArray();

        return array_values(array_diff($realFilenames, $existing));
    }

    // =========================================================================
    // PRIVATE — Helpers
    // =========================================================================

    private function fail(string $error, ?string $rawJson = null): array
    {
        Log::warning("[QuizGenerator] Gagal: {$error}");
        return [
            'success'          => false,
            'question_data'    => null,
            'assets_required'  => [],
            'missing_assets'   => [],
            'auto_fetch_log'   => ['fetched' => [], 'failed' => []],
            'raw_json'         => $rawJson,
            'error'            => $error,
        ];
    }
}