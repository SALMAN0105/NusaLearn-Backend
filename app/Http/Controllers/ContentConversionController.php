<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\KonversiFile;
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
        $histories = KonversiFile::latest()->limit(10)->get();
        if (auth()->user()?->peran === 'administrator') {
            return view('administrator.conversions.index', compact('histories'));
        }
        return view('admin.conversions.index', compact('histories'));
    }

    public function destroyLog($id)
    {
        \Illuminate\Support\Facades\Log::info("destroyLog accessed for ID: " . $id);
        try {
            $log = \App\Models\KonversiFile::findOrFail($id);
            if ($log->jalur_output_json && $log->jalur_output_json !== '-' && \Illuminate\Support\Facades\Storage::disk('local')->exists($log->jalur_output_json)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($log->jalur_output_json);
            }
            $log->delete();
            return response()->json(['status' => 'ok', 'message' => 'Log berhasil dihapus.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("destroyLog failed: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function process(Request $request)
    {
        $this->configureLongRunningRequest();
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:kamus,materi,pdf_to_excel',
            'upload_file' => [
                'required', 'file', 'max:512000', 'mimes:pdf,xls,xlsx,csv',
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
            'page_start' => 'nullable|integer|min:1|required_if:type,pdf_to_excel',
            'page_end'   => 'nullable|integer|min:1|gte:page_start|required_if:type,pdf_to_excel',
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
            // PIPELINE B: PDF -> AI (VISION) -> EXCEL (Kamus)
            // =========================================================================
            elseif ($request->type === 'pdf_to_excel') {
                $conversionTypeDB = 'pdf_to_excel_ai';
                
                $pageStart = (int) $request->page_start;
                $pageEnd   = (int) $request->page_end;

                if (($pageEnd - $pageStart) > 19) {
                    throw new \Exception("Pembatasan Sistem: Maksimal pemrosesan OCR adalah 20 halaman per request.");
                }

                if (!Storage::disk('local')->exists('temp_pdfs')) {
                    Storage::disk('local')->makeDirectory('temp_pdfs');
                }
                $tempPdfPath     = $file->storeAs('temp_pdfs', $timestamp . '.pdf');
                $absolutePdfPath = Storage::disk('local')->path($tempPdfPath);

                $tempImgDir = Storage::disk('local')->path('temp_pdfs') . DIRECTORY_SEPARATOR . 'ocr_' . Str::random(5);
                @mkdir($tempImgDir, 0755, true);

                $pdfToCairoPath = 'C:\\Program Files\\poppler-25.12.0\\Library\\bin\\pdftocairo.exe';
                $processImages = new \Symfony\Component\Process\Process([$pdfToCairoPath, '-jpeg', '-r', '72', '-f', (string)$pageStart, '-l', (string)$pageEnd, $absolutePdfPath, $tempImgDir . DIRECTORY_SEPARATOR . 'img']);
                $processImages->setTimeout(300);
                $processImages->run();

                if (!$processImages->isSuccessful()) {
                    throw new \Exception("Gagal meraster PDF ke Gambar (Proses OCR gagal).");
                }

                Storage::delete($tempPdfPath);

                $images = glob($tempImgDir . DIRECTORY_SEPARATOR . 'img*.jpg');
                if (empty($images)) {
                    throw new \Exception("Halaman PDF kosong atau di luar rentang.");
                }

                $geminiKey = env('GEMINI_API_KEY');
                if (!$geminiKey) {
                    throw new \Exception('GEMINI_API_KEY belum diatur di file .env. Buat API key Gemini di Google AI Studio atau aktifkan Generative Language API di Google Cloud, lalu simpan sebagai GEMINI_API_KEY.');
                }

                $guzzleClient = new \GuzzleHttp\Client([
                    'timeout'         => 300,
                    'connect_timeout' => 30,
                    'http_errors'     => false,
                ]);

                $parts = [];
                $parts[] = [
                    'text' => 'Ini adalah halaman-halaman pindaian (foto) kamus bahasa Tolaki. Tolong ekstrak seluruh baris kosakata daerah dan terjemahannya ke JSON. WAJIB mengembalikan JSON object dengan key "data" yang berisi array. Tiap item array HANYA memiliki 4 keys: "source_text", "target_text", "type" (diisi "word" atau "phrase"), dan "category". Ekstrak SETIAP baris dengan teliti tanpa terlewat.'
                ];

                foreach ($images as $imgPath) {
                    $base64 = base64_encode(file_get_contents($imgPath));
                    $parts[] = [
                        'inlineData' => [
                            'mimeType' => 'image/jpeg',
                            'data'     => $base64
                        ]
                    ];
                }

                $geminiResponse = $guzzleClient->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $geminiKey, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'contents' => [
                            [
                                'parts' => $parts
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json'
                        ]
                    ]
                ]);

                // Cleanup Images
                foreach ($images as $img) {
                    @unlink($img);
                }
                @rmdir($tempImgDir);

                $geminiBody = json_decode($geminiResponse->getBody()->getContents(), true);
                if ($geminiResponse->getStatusCode() >= 400) {
                    $message = $geminiBody['error']['message'] ?? 'Gemini mengembalikan error HTTP ' . $geminiResponse->getStatusCode();
                    throw new \Exception('Gemini API gagal: ' . $message);
                }

                if (isset($geminiBody['candidates'][0]['content']['parts'][0]['text'])) {
                    $responseText = $geminiBody['candidates'][0]['content']['parts'][0]['text'];
                } else {
                    throw new \Exception("AI format salah atau kosong dari Gemini.");
                }

                $parsedData = json_decode($responseText, true);
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

                $validImageFiles = [];
                if ($request->hasFile('teacher_images')) {
                    $images = $request->file('teacher_images');
                    $images = is_array($images) ? array_slice($images, 0, 5) : [$images];
                    
                    foreach ($images as $idx => $img) {
                        if ($img->isValid()) {
                            $extension = $img->getClientOriginalExtension();
                            $manualFileName = 'manual_' . $idx . '.' . $extension;
                            $absoluteDir = storage_path('app/temp_pdfs/img_' . $timestamp);
                            $img->move($absoluteDir, $manualFileName);
                            $validImageFiles[] = $absoluteDir . DIRECTORY_SEPARATOR . $manualFileName;
                        }
                    }
                }

                $client = OpenAI::factory()
                    ->withApiKey(env('OPENAI_API_KEY', 'sk-o44DmAnE8ceq5OWSqECLINUi1ugCeTbWAdGYsPyh1QjBmXou'))
                    ->withBaseUri('https://api.chatanywhere.tech/v1')
                    ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 60]))
                    ->make();

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
                    if (file_exists($imgFile)) {
                        copy($imgFile, $newFilePath);
                        unlink($imgFile);
                    } else {
                        throw new \Exception("File gambar gagal diproses karena rute tidak ditemukan: " . $imgFile);
                    }

                    $relativeFilename = 'assets/' . $folderName . '/' . $newFileName;
                    $mimeType         = mime_content_type($newFilePath);

                    $filesizeKb = filesize($newFilePath) / 1024;
                    if ($filesizeKb > 2048) {
                        continue;
                    }

                    $dataUri          = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($newFilePath));

                    $imagePayloads[$newFileName] = [
                        'relativeFilename' => $relativeFilename,
                        'newFilePath'      => $newFilePath,
                        'dataUri'          => $dataUri,
                        'index'            => $imgCount,
                    ];

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
- Format ulang teks mentah menjadi array chunks yang rapi. Ekstrak SELURUH substansi materi selengkap mungkin. Jangan meringkas atau membuang poin narasi/edukatif yang penting.
- Pecah paragraf panjang menjadi beberapa chunk "paragraph" yang pendek-pendek (maks. 2-3 kalimat per chunk) agar ramah anak dan mudah dibaca di layar HP (tidak menumpuk).
- Perbaiki hasil salah ketik (typo) atau format acak dari ekstraksi PDF (OCR), dan susun menjadi kalimat yang padu serta kaya akan informasi bagi siswa.
- Jika menemukan deret angka atau tabel nilai tempat (seperti Puluhan dan Satuan), WAJIB ubah menjadi chunk dengan type: "table" yang memiliki "headers" dan "rows".
- Sisipkan chunk type: "image" di lokasi yang tepat berdasarkan konteks teks, menggunakan data daftar gambar yang saya berikan. Gunakan "url" dan "alt_text" PERSIS dari daftar gambar.
- Tambahkan key `"layout": "center"` pada SETIAP chunk bertipe "image" agar aplikasi pembaca tahu gambar ini harus diletakkan rapi di tengah.
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

            KonversiFile::create([
                'nama_file_asli'    => $fileName,
                'tipe_konversi'     => $conversionTypeDB,
                'jalur_output_json' => $outputFilePath,
                'ukuran_file_kb'    => $fileSize,
                'status'            => 'success',
            ]);

            return response()->json([
                'status'       => 'success',
                'message'      => 'Protokol Konversi Berhasil',
                'download_url' => route($this->downloadRouteName(), ['path' => base64_encode($outputFilePath)]),
            ]);

        } catch (\Throwable $e) {
            \App\Models\KonversiFile::create([
                'nama_file_asli'    => $fileName ?? '-',
                'tipe_konversi'     => $request->type ?? 'unknown',
                'jalur_output_json' => '-',
                'ukuran_file_kb'    => $fileSize ?? 0,
                'status'            => 'failed',
                'log_error'         => substr($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 0, 200),
            ]);

            return response()->json(['status' => 'error', 'errors' => [$e->getMessage()]], 422);
        }
    }

    private function configureLongRunningRequest(): void
    {
        ini_set('memory_limit', '2048M');
        ini_set('default_socket_timeout', '300');
        ini_set('max_execution_time', '0');
        ignore_user_abort(true);
        set_time_limit(0);
    }

    private function downloadRouteName(): string
    {
        return auth()->user()?->peran === 'administrator'
            ? 'administrator.converter.download'
            : 'converter.download';
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

        if (strpos($decodedPath, '..') !== false || preg_match('/^[\/\\\\]|[a-zA-Z]:/', $decodedPath)) {
            abort(403, 'Akses ditolak: Jalur file tidak valid.');
        }

        if (!Str::startsWith($decodedPath, ['json_exports/', 'excel_exports/'])) {
            abort(403, 'Akses ditolak: Direktori tidak diizinkan.');
        }

        $absolutePath = Storage::disk('local')->path($decodedPath);
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        if (file_exists($absolutePath) && is_file($absolutePath)) {
            // Memory Limit check for large files
            if (filesize($absolutePath) > 50 * 1024 * 1024) {
                abort(500, 'File terlalu besar untuk diproses.');
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $absolutePath);
            finfo_close($finfo);

            $allowedMimes = ['application/json', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'];
            if (!in_array($mime, $allowedMimes)) {
                abort(403, 'Tipe file tidak diizinkan.');
            }

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
