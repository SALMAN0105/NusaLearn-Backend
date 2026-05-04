<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\FileConversion;
use App\Exports\KamusExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Process\Process;

use Illuminate\Support\Str;
use OpenAI;

class ContentConversionController extends Controller
{
    private $pdfToTextPath = 'C:\\Program Files\\poppler-25.12.0\\Library\\bin\\pdftotext.exe';
    private $pdfImagesPath = 'C:\\Program Files\\poppler-25.12.0\\Library\\bin\\pdfimages.exe';


    public function index()
    {
        $histories = FileConversion::latest()->limit(10)->get();
        return view('admin.conversions.index', compact('histories'));
    }

    public function destroyLog($id)
    {
        $log = \App\Models\FileConversion::findOrFail($id);
        $log->delete();
        return response()->json(['status' => 'ok', 'message' => 'Log berhasil dihapus.']);
    }

    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:kamus,materi,pdf_to_excel',
            'upload_file' => [
                'required', 'file', 'max:512000',
                function ($attribute, $value, $fail) use ($request) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($request->type === 'kamus' && !in_array($ext, ['xls', 'xlsx', 'csv'])) {
                        $fail('Resolusi File Ditolak: Operasi Kamus mewajibkan Excel/CSV.');
                    }
                    if (($request->type === 'materi' || $request->type === 'pdf_to_excel') && $ext !== 'pdf') {
                        $fail('Resolusi File Ditolak: Operasi Ekstraksi Teks mewajibkan PDF.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        set_time_limit(0);

        $file      = $request->file('upload_file');
        $fileName  = $file->getClientOriginalName();
        $fileSize  = $file->getSize() / 1024;
        $timestamp = now()->format('Ymd_His');

        try {
            $jsonResult       = [];
            $outputFilePath   = '';
            $conversionTypeDB = '';

            // =========================================================================
            // PIPELINE A: EXCEL/CSV -> JSON
            // =========================================================================
            if ($request->type === 'kamus') {
                $conversionTypeDB = 'kamus_excel_to_json';
                $data = Excel::toArray([], $file)[0] ?? [];

                if (empty($data) || count($data) < 2) {
                    throw new \Exception('Integritas Data Gagal: File kosong.');
                }

                $headers         = array_map(fn($col) => strtolower(trim((string)$col)), $data[0]);
                $expectedHeaders = ['source_text', 'target_text', 'type', 'category'];
                $missingHeaders  = array_diff($expectedHeaders, $headers);
                if (count($missingHeaders) > 0) {
                    throw new \Exception('Kolom tidak valid: Kurang ' . implode(', ', $missingHeaders));
                }

                $headerMap = array_flip($headers);
                $kosakata  = [];

                for ($i = 1; $i < count($data); $i++) {
                    $row = $data[$i];
                    if (empty(array_filter($row))) continue;
                    $kosakata[] = [
                        'source_text' => (string)($row[$headerMap['source_text']] ?? ''),
                        'target_text' => (string)($row[$headerMap['target_text']] ?? ''),
                        'type'        => (string)($row[$headerMap['type']] ?? 'word'),
                        'category'    => (string)($row[$headerMap['category']] ?? 'umum'),
                    ];
                }

                $jsonResult     = $kosakata;
                $outputFilePath = 'json_exports/' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '_' . $timestamp . '.json';
                if (!Storage::disk('local')->exists('json_exports')) {
                    Storage::disk('local')->makeDirectory('json_exports');
                }
                Storage::disk('local')->put(
                    $outputFilePath,
                    json_encode($jsonResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            }

            // =========================================================================
            // PIPELINE B: PDF -> AI -> EXCEL (Kamus)
            // =========================================================================
            elseif ($request->type === 'pdf_to_excel') {
                $conversionTypeDB = 'pdf_to_excel_ai';
                if (!Storage::disk('local')->exists('temp_pdfs')) {
                    Storage::disk('local')->makeDirectory('temp_pdfs');
                }
                $tempPdfPath     = $file->storeAs('temp_pdfs', $timestamp . '.pdf');
                $absolutePdfPath = Storage::disk('local')->path($tempPdfPath);

                $processText = new Process([$this->pdfToTextPath, $absolutePdfPath, '-']);
                $processText->run();
                if (!$processText->isSuccessful()) {
                    throw new \Exception("Gagal ekstrak PDF.");
                }

                $extractedText = clean_pdf_text($processText->getOutput());
                Storage::delete($tempPdfPath);
                if (empty($extractedText)) {
                    throw new \Exception("PDF kosong.");
                }

                $safeTextForKamus = substr($extractedText, 0, 12000);

                $client = OpenAI::factory()
                    ->withApiKey(env('OPENAI_API_KEY'))
                    ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.chatanywhere.tech/v1'))
                    ->make();

                $response = $client->chat()->create([
                    'model'           => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => [
                        [
                            'role'    => 'system',
                            'content' => 'Ekstrak kosakata ke JSON. WAJIB object dengan key "data" berisi array. Tiap item HANYA memiliki 4 keys: "source_text", "target_text", "type" ("word"/"phrase"), "category".',
                        ],
                        ['role' => 'user', 'content' => $safeTextForKamus],
                    ],
                ]);

                $parsedData = json_decode($response->choices[0]->message->content, true);
                if (!isset($parsedData['data'])) {
                    throw new \Exception("AI format salah.");
                }

                $excelData = [];
                foreach ($parsedData['data'] as $row) {
                    $excelData[] = [
                        $row['source_text'] ?? '',
                        $row['target_text'] ?? '',
                        $row['type']        ?? 'word',
                        $row['category']    ?? 'umum',
                    ];
                }

                $outputFilePath = 'excel_exports/' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '_ai_converted_' . $timestamp . '.xlsx';
                if (!Storage::disk('local')->exists('excel_exports')) {
                    Storage::disk('local')->makeDirectory('excel_exports');
                }
                Excel::store(new KamusExport($excelData), $outputFilePath, 'local');
            }

            // =========================================================================
            // PIPELINE C: MATERI EDUKASI PDF -> JSON RAPI
            // =========================================================================
            else {
                $conversionTypeDB = 'materi_pdf_to_json';

                $folderName = Str::slug($request->input('folder_name', 'materi_' . $timestamp));

                if (!Storage::disk('local')->exists('temp_pdfs')) {
                    Storage::disk('local')->makeDirectory('temp_pdfs');
                }
                $tempPdfPath     = $file->storeAs('temp_pdfs', $timestamp . '.pdf');
                $absolutePdfPath = Storage::disk('local')->path($tempPdfPath);

                $pageStart = $request->input('page_start');
                $pageEnd   = $request->input('page_end');

                // â”€â”€ 1. Ekstrak Teks
                $textArgs = [$this->pdfToTextPath, '-layout'];
                if ($pageStart) array_push($textArgs, '-f', $pageStart);
                if ($pageEnd)   array_push($textArgs, '-l', $pageEnd);
                array_push($textArgs, $absolutePdfPath, '-');

                $processText = new Process($textArgs);
                $processText->setTimeout(300);
                $processText->run();
                if (!$processText->isSuccessful()) {
                    throw new \Exception("Kegagalan baca PDF materi.");
                }
                $rawExtractedOutput = $processText->getOutput();

                // â”€â”€ 2. Ekstrak Gambar
                $imageOutputDir = storage_path('app/public/assets/' . $folderName);
                if (!file_exists($imageOutputDir)) {
                    mkdir($imageOutputDir, 0755, true);
                }

                $tempImgDir = storage_path('app/temp_pdfs/img_' . $timestamp);
                if (!file_exists($tempImgDir)) {
                    mkdir($tempImgDir, 0755, true);
                }

                $imgArgs = [$this->pdfImagesPath];
                if ($pageStart) array_push($imgArgs, '-f', $pageStart);
                if ($pageEnd)   array_push($imgArgs, '-l', $pageEnd);
                array_push($imgArgs, '-all', $absolutePdfPath, $tempImgDir . DIRECTORY_SEPARATOR . 'img');

                $processImages = new Process($imgArgs);
                $processImages->setTimeout(600);
                $processImages->run();

                // =========================================================================
                // PHASE 1: Defensive Image Pre-Filtering (Local)
                // Filter images by minimum dimensions and aspect ratio before sending
                // to the Vision API to prevent hallucinations from decorative elements.
                // Threshold: min 100px on each side, skewed ratios discarded.
                // File-size is NOT used as a filter â€” AI Vision will decide relevance.
                // =========================================================================
                $client = OpenAI::factory()
                    ->withApiKey(env('OPENAI_API_KEY'))
                    ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.chatanywhere.tech/v1'))
                    ->make();

                $validImageFiles = [];
                foreach (glob($tempImgDir . '/*.*') as $imgFile) {
                    // Suppress errors on corrupt/unreadable files
                    $imageInfo = @getimagesize($imgFile);

                    if ($imageInfo === false) {
                        unlink($imgFile);
                        continue;
                    }

                    $width  = $imageInfo[0];
                    $height = $imageInfo[1];

                    // Discard images that are too small (under 100px on either side)
                    if ($width < 100 || $height < 100) {
                        unlink($imgFile);
                        continue;
                    }

                    // Discard images with extremely skewed aspect ratios (banners, borders, icons)
                    $ratio = $width / $height;
                    if ($ratio > 4.0 || $ratio < 0.25) {
                        unlink($imgFile);
                        continue;
                    }

                    $validImageFiles[] = $imgFile;
                }

                // =========================================================================
                // PHASE 2: Batch Vision API Call (Single Request for All Images)
                // Step A â€” Rename files, register assets, build base64 payload map.
                // Step B â€” Send ALL images in one chat()->create() call after the loop.
                // =========================================================================
                $extractedImages = [];
                $imgCount        = 1;
                // Map: newFileName => ['relativeFilename', 'newFilePath', 'dataUri', 'index']
                $imagePayloads   = [];

                // â”€â”€ Step A: Rename + encode each image; register asset in DB
                foreach ($validImageFiles as $imgFile) {
                    $ext         = pathinfo($imgFile, PATHINFO_EXTENSION);
                    $newFileName = $folderName . '_img_' . $imgCount . '.' . $ext;
                    $newFilePath = $imageOutputDir . DIRECTORY_SEPARATOR . $newFileName;
                    rename($imgFile, $newFilePath);

                    $relativeFilename = 'assets/' . $folderName . '/' . $newFileName;
                    $mimeType         = mime_content_type($newFilePath);
                    $dataUri          = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($newFilePath));

                    $imagePayloads[$newFileName] = [
                        'relativeFilename' => $relativeFilename,
                        'newFilePath'      => $newFilePath,
                        'dataUri'          => $dataUri,
                        'index'            => $imgCount,
                    ];

                    \App\Models\AssetLibrary::firstOrCreate(
                        ['filename' => $relativeFilename],
                        [
                            'original_name' => $newFileName,
                            'asset_type'    => 'image',
                            'extension'     => strtolower($ext),
                            'mime_type'     => $mimeType,
                            'size_kb'       => round(filesize($newFilePath) / 1024),
                            'source_api'    => 'manual',
                            'tags'          => explode('-', $folderName),
                            'is_active'     => true,
                        ]
                    );

                    $imgCount++;
                }

                // â”€â”€ Step B: Single batch Vision API call for all images
                $batchCaptions = []; // Map: newFileName => captionString
                if (!empty($imagePayloads)) {
                    try {
                        $visionSystemPrompt = 'Anda adalah sistem ekstraksi materi edukasi. '
                            . 'JANGAN mendeskripsikan elemen dekoratif, nomor halaman, ikon kecil, atau background. '
                            . 'JANGAN buat objek chunk untuk angka tunggal atau simbol. '
                            . 'HANYA hasilkan JSON murni tanpa markdown berisikan teks paragraf utama dan ilustrasi penting. '
                            . 'Gunakan skema berikut PERSIS: '
                            . '{"content_structured":[{"section":"Nama Bab/Sub-bab","chunks":['
                            . '{"id":"c1","type":"paragraph","content":"Isi teks paragraf di sini secara utuh."},'
                            . '{"id":"c2","type":"image","url":"nama_file_gambar_yang_relevan.jpg",'
                            . '"caption":"Deskripsi singkat namun bermakna tentang gambar ini."}]}]}';

                        // Build user content: prefix text + one image_url block per image
                        $userContent = [
                            [
                                'type' => 'text',
                                'text' => 'Berikut adalah ' . count($imagePayloads) . ' gambar dari materi edukasi. '
                                        . 'Untuk SETIAP gambar, berikan caption 1-2 kalimat dalam bahasa Indonesia: '
                                        . 'apa yang digambar, detail penting, dan fungsi pedagogisnya. '
                                        . 'Kembalikan HANYA JSON: {"captions":{"nama_file_1.ext":"caption 1","nama_file_2.ext":"caption 2"}}. '
                                        . 'Jika gambar adalah elemen dekoratif, ikon kecil, atau tidak relevan secara edukatif, '
                                        . 'isi nilainya dengan string kosong "".',
                            ],
                        ];

                        foreach ($imagePayloads as $fileName => $payload) {
                            $userContent[] = [
                                'type' => 'text',
                                'text' => 'Gambar: ' . $fileName,
                            ];
                            $userContent[] = [
                                'type'      => 'image_url',
                                'image_url' => ['url' => $payload['dataUri'], 'detail' => 'low'],
                            ];
                        }

                        $visionResponse = $client->chat()->create([
                            'model'      => 'gpt-4o',
                            'max_tokens' => 150 * count($imagePayloads),
                            'messages'   => [
                                ['role' => 'system', 'content' => $visionSystemPrompt],
                                ['role' => 'user',   'content' => $userContent],
                            ],
                        ]);

                        $visionJson    = json_decode($visionResponse->choices[0]->message->content, true);
                        $batchCaptions = $visionJson['captions'] ?? [];
                    } catch (\Throwable $e) {
                        // Batch call failed â€” all images fall back to generic caption
                        $batchCaptions = [];
                    }
                }

                // â”€â”€ Step C: Assemble $extractedImages using captions from the batch response
                foreach ($imagePayloads as $fileName => $payload) {
                    $caption = trim($batchCaptions[$fileName] ?? '');
                    // If AI returned empty string (decorative), use a neutral fallback
                    $altText = ($caption !== '')
                        ? $caption
                        : 'Ilustrasi materi ' . str_replace('-', ' ', $folderName) . '.';

                    $extractedImages[] = [
                        'filename' => $payload['relativeFilename'],
                        'name'     => $fileName,
                        'path'     => $payload['newFilePath'],
                        'index'    => $payload['index'],
                        'alt_text' => $altText,
                    ];
                }

                // Cleanup any remaining temp files and the temp directory
                foreach (glob($tempImgDir . '/*.*') as $f) {
                    if (is_file($f)) unlink($f);
                }
                @rmdir($tempImgDir);

                // â”€â”€ 4. AI PROCESSING (LLM-Driven Structuring)
                // Send raw text + image manifest to GPT-4o-mini. The LLM builds the
                // complete content_structured array directly, replacing local chunking.
                $safeTextForAI = substr(clean_pdf_text($rawExtractedOutput), 0, 10000);

                // Build image manifest JSON for the LLM
                $imageManifest = [];
                foreach ($extractedImages as $img) {
                    $imageManifest[] = [
                        'url'      => $img['filename'],
                        'alt_text' => $img['alt_text'],
                    ];
                }
                $imageManifestJson = json_encode($imageManifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $systemPrompt = <<<PROMPT
Anda adalah analis teks buku teks edukasi. Analisis teks mentah dan daftar gambar yang diberikan.
Kembalikan HANYA JSON valid tanpa markdown, tanpa komentar, dengan struktur PERSIS seperti ini:
{
  "metadata": {
    "title": "Judul lengkap buku/materi",
    "subject": "Mata pelajaran",
    "level": "Tingkat kesulitan (Dasar/Menengah/Lanjutan)"
  },
  "knowledge_map": {
    "context_summary": "Ringkasan isi materi dalam 2-4 kalimat.",
    "key_concepts": [ {"term": "Istilah kunci", "explanation": "Penjelasan singkat"} ],
    "entities": {
      "characters": [ {"name": "Nama tokoh", "role": "Peran", "attributes": ["Sifat 1"]} ],
      "locations": ["Lokasi 1"],
      "objects": ["Benda penting 1"]
    },
    "glossary_bilingual": [ {"indo": "Kata", "regional": "Kata daerah", "definition": "Definisi"} ],
    "entity_relations": [ "Relasi antar entitas" ],
    "themes": [ "Tema utama" ]
  },
  "content_structured": [
    {
      "section_id": "sec-001",
      "section": "Nama Bab",
      "section_type": "intro/concept/example/summary",
      "chunks": [
        {"id": "c1", "type": "paragraph", "content": "Teks materi..."},
        {"id": "c2", "type": "table", "headers": ["Puluhan", "Satuan"], "rows": [["2","6"],["3","2"]]},
        {"id": "img-01", "type": "image", "url": "assets/folder/file.jpg", "alt_text": "Deskripsi gambar", "caption": ""}
      ]
    }
  ]
}

INSTRUKSI PENTING:
- Format ulang teks mentah menjadi array chunks yang rapi.
- Jika menemukan deret angka atau tabel nilai tempat (seperti Puluhan dan Satuan), WAJIB ubah menjadi chunk dengan type: "table" yang memiliki "headers" dan "rows".
- Sisipkan chunk type: "image" di lokasi yang tepat berdasarkan konteks teks, menggunakan data daftar gambar yang saya berikan. Gunakan "url" dan "alt_text" PERSIS dari daftar gambar.
- Nilai "section_type" WAJIB salah satu dari: "intro", "concept", "example", "summary".
- JANGAN mendeskripsikan elemen dekoratif, nomor halaman, ikon kecil, atau background.
- JANGAN buat chunk untuk angka tunggal, simbol, atau nomor halaman.
- Abaikan anomali nomor halaman acak.
PROMPT;

                $userMessage = "TEKS MENTAH:\n" . $safeTextForAI
                    . "\n\nDAFTAR GAMBAR TERSEDIA:\n" . $imageManifestJson;

                $response = $client->chat()->create([
                    'model'           => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userMessage],
                    ],
                ]);

                $aiData = json_decode($response->choices[0]->message->content, true);

                // =========================================================================
                // PHASE 3: JSON Post-Processing & Sanitization
                // =========================================================================
                $contentStructured = $aiData['content_structured'] ?? [];
                $contentStructured = $this->sanitizeContentChunks($contentStructured);

                $jsonResult = [
                    'metadata' => [
                        'material_id'  => 'MAT-' . strtoupper(Str::random(5)),
                        'title'        => $aiData['metadata']['title']   ?? $folderName,
                        'category'     => 'Literasi',
                        'subject'      => $aiData['metadata']['subject'] ?? 'Materi Edukasi',
                        'level'        => $aiData['metadata']['level']   ?? 'Menengah',
                        'last_updated' => now()->format('Y-m-d'),
                    ],
                    'knowledge_map' => [
                        'context_summary'    => $aiData['knowledge_map']['context_summary']    ?? '',
                        'key_concepts'       => $aiData['knowledge_map']['key_concepts']       ?? [],
                        'entities'           => [
                            'characters' => $aiData['knowledge_map']['entities']['characters'] ?? [],
                            'locations'  => $aiData['knowledge_map']['entities']['locations']  ?? [],
                            'objects'    => $aiData['knowledge_map']['entities']['objects']    ?? [],
                        ],
                        'glossary_bilingual' => $aiData['knowledge_map']['glossary_bilingual'] ?? [],
                        'entity_relations'   => $aiData['knowledge_map']['entity_relations']   ?? [],
                        'themes'             => $aiData['knowledge_map']['themes']             ?? [],
                    ],
                    'content_structured' => $contentStructured,
                ];

                Storage::delete($tempPdfPath);

                $outputFilePath = 'json_exports/' . $folderName . '.json';
                if (!Storage::disk('local')->exists('json_exports')) {
                    Storage::disk('local')->makeDirectory('json_exports');
                }
                Storage::disk('local')->put(
                    $outputFilePath,
                    json_encode($jsonResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            }

            FileConversion::create([
                'original_filename' => $fileName,
                'conversion_type'   => $conversionTypeDB,
                'json_output_path'  => $outputFilePath,
                'file_size_kb'      => $fileSize,
                'status'            => 'success',
            ]);

            return response()->json([
                'status'       => 'success',
                'message'      => 'Protokol Konversi Berhasil',
                'download_url' => route('converter.download', ['path' => base64_encode($outputFilePath)]),
            ]);

        } catch (\Exception $e) {
            FileConversion::create([
                'original_filename' => $fileName ?? '-',
                'conversion_type'   => $request->type ?? 'unknown',
                'json_output_path'  => '-',
                'file_size_kb'      => $fileSize ?? 0,
                'status'            => 'failed',
                'error_log'         => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error', 'errors' => [$e->getMessage()]], 422);
        }
    }

    // =========================================================================
    // PHASE 3: Sanitize content_structured chunks from LLM output
    // Removes hallucinated images and trivial paragraphs, re-indexes arrays.
    // =========================================================================
    private function sanitizeContentChunks(array $contentStructured): array
    {
        $hallucPatterns = [
            'tidak tersedia',
            'tidak ada deskripsi',
            'dekoratif',
            'description not available',
        ];

        foreach ($contentStructured as $sectionIndex => $section) {
            $rawChunks = $section['chunks'] ?? [];

            foreach ($rawChunks as $chunkIndex => $chunk) {
                $type = $chunk['type'] ?? '';

                if ($type === 'image') {
                    $caption  = mb_strtolower(trim($chunk['caption']  ?? ''));
                    $altText  = mb_strtolower(trim($chunk['alt_text'] ?? ''));
                    $checkStr = $caption . ' ' . $altText;

                    $isHallucinated = empty(trim($checkStr));
                    if (!$isHallucinated) {
                        foreach ($hallucPatterns as $pattern) {
                            if (str_contains($checkStr, $pattern)) {
                                $isHallucinated = true;
                                break;
                            }
                        }
                    }

                    if ($isHallucinated) {
                        unset($contentStructured[$sectionIndex]['chunks'][$chunkIndex]);
                    }

                } elseif ($type === 'paragraph') {
                    $content = trim($chunk['content'] ?? '');

                    if (mb_strlen($content) <= 3 || preg_match('/^[\d\W]+$/u', $content)) {
                        unset($contentStructured[$sectionIndex]['chunks'][$chunkIndex]);
                    } else {
                        $contentStructured[$sectionIndex]['chunks'][$chunkIndex]['content'] = $content;
                    }
                }
            }

            // Re-index to prevent JSON dictionaries
            $contentStructured[$sectionIndex]['chunks'] = array_values(
                $contentStructured[$sectionIndex]['chunks'] ?? []
            );
        }

        // Remove sections that became empty after sanitization
        return array_values(
            array_filter($contentStructured, fn($s) => !empty($s['chunks']))
        );
    }

    public function download($path)
    {
        $decodedPath  = base64_decode($path);
        $absolutePath = Storage::disk('local')->path($decodedPath);
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        if (file_exists($absolutePath)) {
            return response()->download($absolutePath);
        }

        abort(404, 'File sistem tidak ditemukan di: ' . $absolutePath);
    }
}

// =========================================================================
// HELPER GLOBAL -- Pembersihan Teks PDF
// =========================================================================
if (!function_exists('clean_pdf_text')) {
    function clean_pdf_text(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $text);
        $text = str_replace(["\r", "\n"], ' ', $text); 
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
}
