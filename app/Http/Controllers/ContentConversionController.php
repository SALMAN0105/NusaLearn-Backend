<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\FileConversion;
use App\Exports\KamusExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;
use OpenAI;

class ContentConversionController extends Controller
{
    private $pdfToTextPath = 'C:\\Program Files\\poppler-25.12.0\\Library\\bin\\pdftotext.exe';
    private $pdfImagesPath = 'C:\\Program Files\\poppler-25.12.0\\Library\\bin\\pdfimages.exe';

    private array $pageNumberPatterns = [
        '/^\d+\s+Matematika\s+untuk\s+SD\/MI\s+Kelas\s+\w+$/i',
        '/^\d+\s+Ayo\s+\w+.*\d+$/i',
        '/^\d+\s+[\w\s]+\s+untuk\s+SD\/MI.*$/i',
        '/^[\w\s]+\s+untuk\s+SD\/MI\s+Kelas\s+\w+\s+\d+$/i',
        '/^\d{1,3}$/',
        '/^\d+\s+\w[\w\s]{2,40}\d+$/',
    ];

    private array $exerciseKeywords = [
        'Ayo Mencoba', 'Ayo Berlatih', 'Ayo Berdiskusi',
        'Ayo Mengamati', 'Ayo Membaca', 'Ayo Menulis',
        'Tuliskan', 'Gambarlah', 'Isilah', 'Hitunglah',
        'Selesaikan', 'Kerjakanlah', 'Diskusikan',
    ];

    // =========================================================================
    // [PERBAIKAN #2] Flag untuk deteksi baris data setelah header tabel
    // =========================================================================
    private bool $lastWasTableHeader = false;

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
                    ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'))
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

                // ── 1. Ekstrak Teks
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

                // ── 2. Ekstrak Gambar
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
                // [PERBAIKAN #1] Ekstrak gambar + generate alt-text via GPT-4o Vision
                // Sebelumnya: alt_text hanya "Ilustrasi [folder] bagian [n]" (tidak deskriptif)
                // Sekarang:   setiap gambar dikirim ke GPT-4o untuk deskripsi otomatis
                // =========================================================================
                $client = OpenAI::factory()
                    ->withApiKey(env('OPENAI_API_KEY'))
                    ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'))
                    ->make();

                $extractedImages = [];
                $imgCount        = 1;
                foreach (glob($tempImgDir . '/*.*') as $imgFile) {
                    $size = filesize($imgFile);
                    if ($size > 5120) {
                        $ext         = pathinfo($imgFile, PATHINFO_EXTENSION);
                        $newFileName = $folderName . '_img_' . $imgCount . '.' . $ext;
                        $newFilePath = $imageOutputDir . DIRECTORY_SEPARATOR . $newFileName;
                        rename($imgFile, $newFilePath);

                        $relativeFilename = 'assets/' . $folderName . '/' . $newFileName;

                        // Generate alt-text deskriptif via Vision
                        $altText = $this->generateImageAltText($client, $newFilePath, $folderName);

                        $extractedImages[] = [
                            'filename' => $relativeFilename,
                            'name'     => $newFileName,
                            'path'     => $newFilePath,
                            'index'    => $imgCount,
                            'alt_text' => $altText, // [BARU] alt-text deskriptif
                        ];

                        \App\Models\AssetLibrary::firstOrCreate(
                            ['filename' => $relativeFilename],
                            [
                                'original_name' => $newFileName,
                                'asset_type'    => 'image',
                                'extension'     => strtolower($ext),
                                'mime_type'     => mime_content_type($newFilePath),
                                'size_kb'       => round(filesize($newFilePath) / 1024),
                                'source_api'    => 'manual',
                                'tags'          => explode('-', $folderName),
                                'is_active'     => true,
                            ]
                        );
                        $imgCount++;
                    } else {
                        unlink($imgFile);
                    }
                }

                foreach (glob($tempImgDir . '/*.*') as $f) {
                    if (is_file($f)) unlink($f);
                }
                @rmdir($tempImgDir);

                // ── 3. CHUNKER RAPI
                $chunks = $this->buildCleanChunks($rawExtractedOutput, $extractedImages, $folderName);

                // ── 4. AI PROCESSING
                $safeTextForAI = substr(clean_pdf_text($rawExtractedOutput), 0, 10000);

                $systemPrompt = <<<'PROMPT'
Anda adalah analis teks buku teks edukasi. Analisis teks berikut dan kembalikan HANYA JSON valid
tanpa markdown, tanpa komentar, dengan struktur PERSIS seperti ini:
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
  "sections": [
    {
      "name": "Nama Section",
      "section_type": "intro",
      "description": "Deskripsi singkat isi section",
      "heading_marker": "Kata kunci heading yang menandai awal section ini di teks"
    }
  ]
}

Nilai "section_type" WAJIB salah satu dari: "intro", "concept", "example", "summary".
Field "heading_marker" WAJIB diisi — tulis kata pertama dari judul/heading yang menandai awal section.
Buat section berdasarkan topik/bab yang terdeteksi di teks.

INSTRUKSI KHUSUS NUMERASI: 
Jika materi mengandung deret angka, persamaan, atau tabel nilai tempat matematika, JANGAN menggabungkannya menjadi paragraf kacau. Restrukturisasi deret tersebut ke dalam "key_concepts" atau masukkan ke deskripsi "sections" secara logis dan terstruktur. Abaikan anomali nomor halaman acak.
PROMPT;

                $response = $client->chat()->create([
                    'model'           => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $safeTextForAI],
                    ],
                ]);

                $aiData = json_decode($response->choices[0]->message->content, true);

                // =========================================================================
                // [PERBAIKAN #3] Distribusi Chunk ke Section berdasarkan HEADING, bukan merata
                // Sebelumnya: array_slice($chunks, $start, $chunksPerSection) — dibagi rata
                // Sekarang:   setiap chunk heading baru memicu pergantian section
                // =========================================================================
                $aiSections        = $aiData['sections'] ?? [['name' => 'Konten Utama', 'section_type' => 'concept', 'description' => '', 'heading_marker' => '']];
                $contentStructured = [];
                $seenContents      = [];

                $contentStructured = $this->distributeChunksByHeading($chunks, $aiSections, $seenContents);

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
    // [BARU — PERBAIKAN #1] Generate alt-text gambar via GPT-4o Vision
    // Menggantikan teks generik "Ilustrasi [folder] bagian [n]"
    // =========================================================================
    private function generateImageAltText($client, string $imagePath, string $folderName): string
    {
        try {
            $mimeType   = mime_content_type($imagePath);
            $base64Img  = base64_encode(file_get_contents($imagePath));
            $dataUri    = "data:{$mimeType};base64,{$base64Img}";

            $visionResponse = $client->chat()->create([
                'model'     => 'gpt-4o',
                'max_tokens' => 150,
                'messages'  => [
                    [
                        'role'    => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Deskripsikan gambar ini dalam 1-2 kalimat sebagai alt-text untuk buku teks SD. '
                                        . 'Sebutkan: apa yang digambar, detail penting (jumlah benda jika ada), '
                                        . 'dan fungsi pedagogisnya. Gunakan bahasa Indonesia.',
                            ],
                            [
                                'type'      => 'image_url',
                                'image_url' => ['url' => $dataUri, 'detail' => 'low'],
                            ],
                        ],
                    ],
                ],
            ]);

            return trim($visionResponse->choices[0]->message->content);
        } catch (\Throwable $e) {
            // Fallback jika vision gagal — tetap lebih deskriptif dari sebelumnya
            return 'Ilustrasi materi ' . str_replace('-', ' ', $folderName) . ' — deskripsi tidak tersedia.';
        }
    }

    // =========================================================================
    // [BARU — PERBAIKAN #3] Distribusi chunk ke section berdasarkan heading_marker
    // Menggantikan pembagian rata array_slice($chunks, $start, $chunksPerSection)
    // =========================================================================
    private function distributeChunksByHeading(array $chunks, array $aiSections, array &$seenContents): array
    {
        $totalSections     = count($aiSections);
        $contentStructured = [];

        if ($totalSections === 0) return [];

        // Buat peta: heading_marker (lowercase) => index section
        $markerMap = [];
        foreach ($aiSections as $idx => $sec) {
            $marker = mb_strtolower(trim($sec['heading_marker'] ?? ''));
            if (!empty($marker)) {
                $markerMap[$marker] = $idx;
            }
        }

        // Inisialisasi bucket section
        $buckets = array_fill(0, $totalSections, []);
        $currentSectionIdx = 0;

        foreach ($chunks as $chunk) {
            $content = mb_strtolower(trim($chunk['content'] ?? ''));

            // Cek apakah chunk ini heading yang menandai awal section baru
            if (in_array($chunk['type'], ['heading', 'subheading']) && !empty($content)) {
                foreach ($markerMap as $marker => $secIdx) {
                    if (str_contains($content, $marker) && $secIdx > $currentSectionIdx) {
                        $currentSectionIdx = $secIdx;
                        break;
                    }
                }
            }

            $buckets[$currentSectionIdx][] = $chunk;
        }

        // Bangun content_structured dari buckets
        foreach ($aiSections as $idx => $sec) {
            $sectionChunks = $buckets[$idx] ?? [];
            $uniqueChunks  = $this->deduplicateChunks($sectionChunks, $seenContents);

            foreach ($uniqueChunks as $uc) {
                if (!empty($uc['content'])) {
                    $seenContents[] = mb_strtolower(preg_replace('/\s+/', ' ', trim($uc['content'])));
                }
            }

            if (!empty($uniqueChunks)) {
                $contentStructured[] = [
                    'section_id'   => 'sec-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'section'      => $sec['name'],
                    'section_type' => $sec['section_type'] ?? 'concept',
                    'chunks'       => $uniqueChunks,
                ];
            }
        }

        return $contentStructured;
    }

    // =========================================================================
    // CHUNKER RAPI
    // =========================================================================
    private function buildCleanChunks(string $rawText, array $extractedImages, string $folderName): array
    {
        $rawParagraphs = preg_split('/(\r?\n){2,}/', $rawText);

        $chunks      = [];
        $cId         = 1;
        $prevContent = '';
        $imageIdx    = 0;
        $imgChunkIdx = 1;
        $tblIdx      = 1;
        $listIdx     = 1;
        $tableBuffer = [];
        $inTable     = false;

        // Reset flag tabel header
        $this->lastWasTableHeader = false;

        foreach ($rawParagraphs as $rawP) {
            $cleanP = clean_pdf_text($rawP);

            if (mb_strlen($cleanP) < 20) continue;
            if ($this->isPageNumber($cleanP)) continue;
            if ($this->isExercise($cleanP)) continue;

            // Sambungkan kalimat terpotong
            if (!empty($prevContent) && !preg_match('/[.!?;:»"\')\]]$/', $prevContent)) {
                $cleanP = $prevContent . ' ' . $cleanP;
                if (!empty($chunks)) {
                    array_pop($chunks);
                    $cId--;
                }
            }
            $prevContent = $cleanP;

            // =========================================================================
            // [PERBAIKAN #2] Deteksi tabel dengan flag lastWasTableHeader
            // Sebelumnya: baris angka pendek setelah header tabel tidak terdeteksi
            // Sekarang:   jika baris sebelumnya adalah header tabel, baris angka ikut masuk
            // =========================================================================
            if ($this->looksLikeTableRow($cleanP)) {
                $tableBuffer[] = $cleanP;
                $inTable       = true;
                continue;
            } elseif ($inTable && !empty($tableBuffer)) {
                $tableChunk = $this->buildTableChunk($tableBuffer, $tblIdx);
                if ($tableChunk !== null) {
                    $tableChunk['id'] = 'c' . $cId;
                    $chunks[]         = $tableChunk;
                    $cId++;
                    $tblIdx++;
                }
                $tableBuffer              = [];
                $inTable                  = false;
                $this->lastWasTableHeader = false;
            }

            $chunkType = $this->detectChunkType($cleanP);

            if ($chunkType === 'example') {
                $chunks[] = [
                    'id'       => 'c' . $cId,
                    'type'     => 'example',
                    'content'  => $cleanP,
                    'keywords' => $this->extractKeywords($cleanP),
                ];
                $cId++;
                $this->lastWasTableHeader = false;

            } elseif ($chunkType === 'callout') {
                $chunks[] = [
                    'id'       => 'c' . $cId,
                    'type'     => 'callout',
                    'content'  => $cleanP,
                    'keywords' => $this->extractKeywords($cleanP),
                ];
                $cId++;
                $this->lastWasTableHeader = false;

            } elseif ($chunkType === 'list') {
                $listItems = $this->parseListItems($cleanP);
                $lstIdx    = str_pad($listIdx, 2, '0', STR_PAD_LEFT);
                $chunks[]  = [
                    'id'       => 'lst-' . $lstIdx,
                    'type'     => 'list',
                    'intro'    => '',
                    'items'    => $listItems,
                    'keywords' => $this->extractKeywords($cleanP),
                ];
                $cId++;
                $listIdx++;
                $this->lastWasTableHeader = false;

            } elseif ($chunkType === 'subheading') {
                $chunks[] = [
                    'id'       => 'c' . $cId,
                    'type'     => 'subheading',
                    'content'  => $cleanP,
                    'keywords' => $this->extractKeywords($cleanP),
                ];
                $cId++;
                $this->lastWasTableHeader = false;

            } elseif ($chunkType === 'heading') {
                $chunks[] = [
                    'id'       => 'c' . $cId,
                    'type'     => 'heading',
                    'content'  => $cleanP,
                    'keywords' => $this->extractKeywords($cleanP),
                ];
                $cId++;
                // Set flag: heading nilai tempat bisa diikuti baris data tabel
                $this->lastWasTableHeader = $this->isTableHeader($cleanP);

            } else {
                $missingImage = $this->hasImageReference($cleanP);

                $chunk = [
                    'id'       => 'c' . $cId,
                    'type'     => 'paragraph',
                    'content'  => $this->stripTrailingPageNumber($cleanP),
                    'keywords' => $this->extractKeywords($cleanP),
                ];

                if ($missingImage) {
                    $chunk['missing_image'] = true;
                }

                $chunks[] = $chunk;
                $cId++;
                $this->lastWasTableHeader = false;

                // Sisipkan chunk gambar setelah paragraf
                if ($imageIdx < count($extractedImages)) {
                    $img      = $extractedImages[$imageIdx];
                    $chunks[] = [
                        'id'                => 'img-' . str_pad($imgChunkIdx, 2, '0', STR_PAD_LEFT),
                        'type'              => 'image',
                        'image_placeholder' => $img['name'],
                        'url'               => $img['filename'],
                        'alt_text'          => $img['alt_text'], // [PERBAIKAN #1] pakai alt-text dari Vision
                        'caption'           => '',
                        'keywords'          => [],
                    ];
                    $cId++;
                    $imgChunkIdx++;
                    $imageIdx++;
                }
            }
        }

        // Flush tabel terakhir
        if ($inTable && !empty($tableBuffer)) {
            $tableChunk = $this->buildTableChunk($tableBuffer, $tblIdx);
            if ($tableChunk !== null) {
                $tableChunk['id'] = 'c' . $cId;
                $chunks[]         = $tableChunk;
                $cId++;
            }
        }

        // Gambar sisa
        while ($imageIdx < count($extractedImages)) {
            $img      = $extractedImages[$imageIdx];
            $chunks[] = [
                'id'                => 'img-' . str_pad($imgChunkIdx, 2, '0', STR_PAD_LEFT),
                'type'              => 'image',
                'image_placeholder' => $img['name'],
                'url'               => $img['filename'],
                'alt_text'          => $img['alt_text'], // [PERBAIKAN #1]
                'caption'           => '',
                'keywords'          => [],
            ];
            $imgChunkIdx++;
            $imageIdx++;
        }

        return $chunks;
    }

    // =========================================================================
    // [BARU — PERBAIKAN #2] Cek apakah heading ini adalah header tabel nilai tempat
    // =========================================================================
    private function isTableHeader(string $text): bool
    {
        return (bool)preg_match(
            '/\b(Puluhan|Satuan|Ratusan|Ribuan|Nilai\s+Tempat)\b/i',
            $text
        );
    }

    // =========================================================================
    // [DIPERBARUI — PERBAIKAN #2] Deteksi baris tabel
    // Tambahan: jika lastWasTableHeader = true dan baris adalah angka pendek
    // =========================================================================
    private function looksLikeTableRow(string $text): bool
    {
        $text = trim($text);
        
        // Tabel dengan separator |
        if (substr_count($text, '|') >= 2) {
            return true;
        }

        // Tabel dengan spasi ganda teratur
        if (preg_match('/\S+\s{2,}\S+\s{2,}\S+/', $text)) {
            return true;
        }

        // Header tabel nilai tempat murni
        if (preg_match('/^(Puluhan|Satuan|Ratusan)(\s+(Puluhan|Satuan|Ratusan))+$/i', $text)) {
            return true;
        }

        // [PATCH] Deteksi deret matriks angka matematika murni (Cth: "10 20 30" atau "21 22 23")
        if (preg_match('/^(\d+[\s\.\,]+){2,}\d+$/', $text)) {
            return true;
        }

        // [PATCH] Deteksi baris ekuivalensi matematika/nilai tempat (Cth: "2 puluhan dan 6 satuan = 26")
        if (preg_match('/^(\d+\s+(puluhan|satuan|ratusan)[\s\+\=a-z]*)+/i', $text)) {
            return true;
        }

        // Baris data angka setelah header tabel
        if ($this->lastWasTableHeader && preg_match('/^[\d\s\.\,\+\-\=]+$/', trim($text))) {
            return true;
        }

        return false;
    }

    private function buildTableChunk(array $tableBuffer, int $tblIdx): ?array
    {
        if (empty($tableBuffer)) return null;

        $headers  = [];
        $rows     = [];
        $firstRow = $tableBuffer[0];

        if (str_contains($firstRow, '|')) {
            $headers = array_map('trim', explode('|', trim($firstRow, '|')));
            $headers = array_values(array_filter($headers, fn($h) => $h !== ''));

            foreach (array_slice($tableBuffer, 1) as $row) {
                if (str_contains($row, '---')) continue;
                $cells = array_map('trim', explode('|', trim($row, '|')));
                $cells = array_values(array_filter($cells, fn($c) => $c !== ''));
                if (!empty($cells)) $rows[] = $cells;
            }
        } else {
            $headers = preg_split('/\s{2,}/', $firstRow);
            $headers = array_values(array_filter(array_map('trim', $headers)));

            foreach (array_slice($tableBuffer, 1) as $row) {
                $cells = preg_split('/\s{2,}/', $row);
                $cells = array_values(array_filter(array_map('trim', $cells)));
                if (!empty($cells)) $rows[] = $cells;
            }
        }

        if (empty($rows)) return null;

        return [
            'type'    => 'table',
            'caption' => '',
            'headers' => $headers,
            'rows'    => $rows,
        ];
    }

    private function deduplicateChunks(array $chunks, array $alreadySeen = []): array
    {
        $seen   = $alreadySeen;
        $result = [];

        foreach ($chunks as $chunk) {
            $content = $chunk['content'] ?? ($chunk['alt_text'] ?? '');

            if (empty($content)) {
                $result[] = $chunk;
                continue;
            }

            $normalized = mb_strtolower(preg_replace('/\s+/', ' ', trim($content)));

            if (in_array($normalized, $seen)) {
                continue;
            }

            $seen[]   = $normalized;
            $result[] = $chunk;
        }

        return $result;
    }

    private function isPageNumber(string $text): bool
    {
        $text = trim($text);
        foreach ($this->pageNumberPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }

    private function isExercise(string $text): bool
    {
        foreach ($this->exerciseKeywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }
        if (preg_match('/^\d+[\.)\]]\s+.{5,}\.{3,}/', $text)) {
            return true;
        }
        return false;
    }

    private function hasImageReference(string $text): bool
        {
            $textLower = strtolower($text);
            return str_contains($textLower, 'gambar berikut')
                || str_contains($textLower, 'lihat ilustrasi')
                || str_contains($textLower, 'perhatikan gambar')
                || str_contains($textLower, 'seperti pada gambar');
        }

    private function stripTrailingPageNumber(string $text): string
    {
        return trim(preg_replace('/\s+\d{1,3}$/', '', $text));
    }

    private function detectChunkType(string $text): string
    {
        if (preg_match('/^(Glosarium|Daftar Istilah|Ringkasan|Catatan|Tentang|Profil|Kesimpulan)/i', $text)) {
            return 'subheading';
        }

        if (preg_match('/^(Ingat[!:]?|Perlu diketahui|Rumus|Definisi|Catatan[!:]?|Tahukah kamu)/i', $text)) {
            return 'callout';
        }

        if (preg_match('/^(Contoh|Misalnya|Perhatikan contoh)[:\s]/i', $text)) {
            return 'example';
        }

        if (preg_match('/^[\-\*•]\s+.+/m', $text) || preg_match('/^\d+\.\s+.{10,}/m', $text)) {
            return 'list';
        }

        $isShort     = mb_strlen($text) <= 80;
        $noEndPunct  = !preg_match('/[.!?,;:]$/', $text);
        $startsUpper = preg_match('/^\p{Lu}/u', $text);
        $allCaps     = preg_match('/^[A-Z0-9\s\(\)\.]+$/', $text);

        if ($isShort && $noEndPunct && $startsUpper && ($allCaps || mb_strlen($text) <= 50)) {
            return 'heading';
        }

        return 'paragraph';
    }

    private function parseListItems(string $text): array
    {
        $lines = preg_split('/\r?\n/', $text);
        $items = [];
        foreach ($lines as $line) {
            $line = preg_replace('/^[\-\*•\d\.]+\s*/', '', trim($line));
            if (!empty($line)) {
                $items[] = $line;
            }
        }
        return $items;
    }

    private function extractKeywords(string $text): array
    {
        $stopWords = [
            'yang', 'dengan', 'untuk', 'pada', 'dari', 'dalam', 'adalah',
            'akan', 'itu', 'ini', 'dan', 'atau', 'juga', 'sudah', 'saja',
            'bisa', 'lebih', 'tidak', 'karena', 'tetapi', 'namun', 'bahwa',
            'kepada', 'oleh', 'ketika', 'saat', 'setelah', 'sebelum',
        ];

        preg_match_all('/\b[A-Za-z\x{00C0}-\x{024F}]{4,}\b/u', $text, $matches);
        $words    = array_unique(array_map('mb_strtolower', $matches[0]));
        $filtered = array_filter($words, fn($w) => !in_array(mb_strtolower($w), $stopWords));

        return array_values(array_slice($filtered, 0, 5));
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
// HELPER GLOBAL — Pembersihan Teks PDF
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