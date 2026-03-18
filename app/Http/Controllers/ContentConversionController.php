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

    public function index()
    {
        $histories = FileConversion::latest()->limit(10)->get();
        return view('admin.conversions.index', compact('histories'));
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

        $file = $request->file('upload_file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize() / 1024;
        $timestamp = now()->format('Ymd_His');

        try {
            $jsonResult = [];
            $outputFilePath = '';
            $conversionTypeDB = '';

            // =========================================================================
            // PIPELINE A: EXCEL/CSV -> JSON
            // =========================================================================
            if ($request->type === 'kamus') {
                $conversionTypeDB = 'kamus_excel_to_json';
                $data = Excel::toArray([], $file)[0] ?? [];

                if (empty($data) || count($data) < 2) throw new \Exception('Integritas Data Gagal: File kosong.');

                $headers = array_map(function ($col) { return strtolower(trim((string)$col)); }, $data[0]);
                $expectedHeaders = ['source_text', 'target_text', 'type', 'category'];
                $missingHeaders = array_diff($expectedHeaders, $headers);
                if (count($missingHeaders) > 0) throw new \Exception('Kolom tidak valid: Kurang ' . implode(', ', $missingHeaders));

                $headerMap = array_flip($headers);
                $kosakata = [];

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

                $jsonResult = $kosakata;
                $outputFilePath = 'json_exports/' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '_' . $timestamp . '.json';
                if (!Storage::disk('local')->exists('json_exports')) Storage::disk('local')->makeDirectory('json_exports');
                Storage::disk('local')->put($outputFilePath, json_encode($jsonResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            // =========================================================================
            // PIPELINE B: PDF -> AI -> EXCEL
            // =========================================================================
            elseif ($request->type === 'pdf_to_excel') {
                $conversionTypeDB = 'pdf_to_excel_ai';
                if (!Storage::disk('local')->exists('temp_pdfs')) Storage::disk('local')->makeDirectory('temp_pdfs');
                $tempPdfPath = $file->storeAs('temp_pdfs', $timestamp . '.pdf');
                $absolutePdfPath = Storage::disk('local')->path($tempPdfPath);

                $processText = new Process([$this->pdfToTextPath, $absolutePdfPath, '-']);
                $processText->run();
                if (!$processText->isSuccessful()) throw new \Exception("Gagal ekstrak PDF.");

                $extractedText = clean_pdf_text($processText->getOutput());
                Storage::delete($tempPdfPath);
                if (empty($extractedText)) throw new \Exception("PDF kosong.");

                $safeTextForKamus = substr($extractedText, 0, 12000);

                $client = OpenAI::factory()
                    ->withApiKey(env('OPENAI_API_KEY'))
                    ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'))
                    ->make();

                $response = $client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Ekstrak kosakata ke JSON. WAJIB object dengan key "data" berisi array. Tiap item HANYA memiliki 4 keys: "source_text", "target_text", "type" ("word"/"phrase"), "category".'
                        ],
                        ['role' => 'user', 'content' => $safeTextForKamus]
                    ]
                ]);

                $parsedData = json_decode($response->choices[0]->message->content, true);
                if (!isset($parsedData['data'])) throw new \Exception("AI format salah.");

                $excelData = [];
                foreach ($parsedData['data'] as $row) {
                    $excelData[] = [
                        $row['source_text'] ?? '', $row['target_text'] ?? '', $row['type'] ?? 'word', $row['category'] ?? 'umum',
                    ];
                }

                $outputFilePath = 'excel_exports/' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '_ai_converted_' . $timestamp . '.xlsx';
                if (!Storage::disk('local')->exists('excel_exports')) Storage::disk('local')->makeDirectory('excel_exports');
                Excel::store(new KamusExport($excelData), $outputFilePath, 'local');
            }

            // =========================================================================
            // PIPELINE C: HYBRID ENGINE - MATERI EDUKASI PDF -> JSON
            // FORMAT OUTPUT IDENTIK DENGAN materi_edukasi.json
            // =========================================================================
            else {
                $conversionTypeDB = 'materi_pdf_to_json';

                if (!Storage::disk('local')->exists('temp_pdfs')) Storage::disk('local')->makeDirectory('temp_pdfs');
                $tempPdfPath = $file->storeAs('temp_pdfs', $timestamp . '.pdf');
                $absolutePdfPath = Storage::disk('local')->path($tempPdfPath);

                // -----------------------------------------------------------------
                // LANGKAH 1: Ekstrak Teks Mentah
                // -----------------------------------------------------------------
                $processText = new Process([$this->pdfToTextPath, $absolutePdfPath, '-']);
                $processText->run();
                if (!$processText->isSuccessful()) throw new \Exception("Kegagalan baca PDF materi.");
                $rawExtractedOutput = $processText->getOutput();

                // -----------------------------------------------------------------
                // LANGKAH 2: Ekstrak Gambar ke public/assets
                // -----------------------------------------------------------------
                $imageOutputDir = storage_path('app/public/assets');
                if (!file_exists($imageOutputDir)) mkdir($imageOutputDir, 0755, true);

                $tempImgDir = storage_path('app/temp_pdfs/img_' . $timestamp);
                if (!file_exists($tempImgDir)) mkdir($tempImgDir, 0755, true);

                $processImages = new Process([$this->pdfImagesPath, '-all', $absolutePdfPath, $tempImgDir . DIRECTORY_SEPARATOR . 'img']);
                $processImages->run();

                // Kumpulkan gambar yang valid (>50KB) ke dalam array terindeks
                $extractedImages = [];
                $imgCount = 1;
                foreach (glob($tempImgDir . '/*.*') as $imgFile) {
                    $size = filesize($imgFile);
                    if ($size > 51200) {
                        $ext = pathinfo($imgFile, PATHINFO_EXTENSION);
                        $newFileName = 'img_mat_' . $timestamp . '_' . $imgCount . '.' . $ext;
                        $newFilePath = $imageOutputDir . DIRECTORY_SEPARATOR . $newFileName;
                        rename($imgFile, $newFilePath);
                        $extractedImages[] = 'assets/' . $newFileName;
                        $imgCount++;
                    } else {
                        unlink($imgFile);
                    }
                }
                foreach (glob($tempImgDir . '/*.*') as $f) { if (is_file($f)) unlink($f); }
                @rmdir($tempImgDir);

                // -----------------------------------------------------------------
                // LANGKAH 3: PHP CHUNKER DETERMINISTIK
                // Menghasilkan chunks dengan tipe yang sesuai format target:
                // heading, paragraph, image, definition, subheading
                // -----------------------------------------------------------------
                $rawParagraphs = preg_split('/(\r?\n){2,}/', $rawExtractedOutput);
                $chunks = [];
                $cId = 1;
                $imageIndex = 0; // pointer untuk distribusi gambar ke chunk bertipe "image"

                foreach ($rawParagraphs as $p) {
                    $cleanP = clean_pdf_text($p);
                    if (strlen($cleanP) < 30) continue;

                    // --- Deteksi tipe chunk ---

                    // 1. HEADING: teks pendek (maks 80 karakter), tidak ada tanda baca kalimat, huruf kapital dominan
                    $isHeading = (strlen($cleanP) <= 80 && !preg_match('/[.!?,;:]$/', $cleanP) && preg_match('/^[A-Z\(\)0-9 ]+$|^\p{Lu}/u', $cleanP));

                    // 2. DEFINITION: pola "Kata : definisi..." atau "Kata - definisi..."
                    $isDefinition = preg_match('/^([A-Za-z\s]+)\s*[:–-]\s+(.+)/', $cleanP, $defMatch);

                    // 3. SUBHEADING: mirip heading tapi lebih panjang, atau kata kunci seperti "Glosarium"
                    $isSubheading = preg_match('/^(Glosarium|Daftar Istilah|Ringkasan|Catatan|Tentang|Profil)/i', $cleanP);

                    if ($isSubheading) {
                        $chunks[] = [
                            "id"       => "c" . str_pad($cId, 0, '0', STR_PAD_LEFT),
                            "type"     => "subheading",
                            "content"  => $cleanP,
                            "keywords" => $this->extractKeywords($cleanP),
                        ];
                    } elseif ($isHeading) {
                        $chunks[] = [
                            "id"       => "c" . str_pad($cId, 0, '0', STR_PAD_LEFT),
                            "type"     => "heading",
                            "content"  => $cleanP,
                            "keywords" => $this->extractKeywords($cleanP),
                        ];
                    } elseif ($isDefinition) {
                        // FIX: type "definition" harus punya field "term" terpisah
                        $chunks[] = [
                            "id"       => "c" . str_pad($cId, 0, '0', STR_PAD_LEFT),
                            "type"     => "definition",
                            "term"     => trim($defMatch[1]),
                            "content"  => trim($defMatch[2]),
                            "keywords" => [$defMatch[1]],
                        ];
                    } else {
                        // Paragraph biasa
                        $chunks[] = [
                            "id"       => "c" . str_pad($cId, 0, '0', STR_PAD_LEFT),
                            "type"     => "paragraph",
                            "content"  => $cleanP,
                            "keywords" => $this->extractKeywords($cleanP),
                        ];

                        // FIX: Sisipkan chunk "image" SETELAH paragraph yang relevan
                        // (bukan ditempel ke chunk pertama sebagai "extracted_images")
                        // Distribusi merata: 1 gambar setiap ~5 paragraf
                        if ($imageIndex < count($extractedImages) && ($cId % 5 === 0)) {
                            $cId++;
                            $chunks[] = [
                                "id"       => "c" . str_pad($cId, 0, '0', STR_PAD_LEFT),
                                "type"     => "image",
                                "content"  => "Ilustrasi bagian " . ceil($cId / 5),
                                "url"      => $extractedImages[$imageIndex],
                                "keywords" => [],
                            ];
                            $imageIndex++;
                        }
                    }

                    $cId++;
                }

                // Sisipkan sisa gambar yang belum terdistribusi di akhir section utama
                while ($imageIndex < count($extractedImages)) {
                    $cId++;
                    $chunks[] = [
                        "id"       => "c" . str_pad($cId, 0, '0', STR_PAD_LEFT),
                        "type"     => "image",
                        "content"  => "Ilustrasi tambahan " . ($imageIndex + 1),
                        "url"      => $extractedImages[$imageIndex],
                        "keywords" => [],
                    ];
                    $imageIndex++;
                }

                // -----------------------------------------------------------------
                // LANGKAH 4: MICRO-TASKING AI
                // FIX: System prompt diperluas agar menghasilkan semua field yang dibutuhkan:
                // entities (characters, locations, objects), glossary_bilingual,
                // entity_relations, themes — identik dengan format materi_edukasi.json
                // -----------------------------------------------------------------
                $safeTextForAI = substr(clean_pdf_text($rawExtractedOutput), 0, 10000);

                $client = OpenAI::factory()
                    ->withApiKey(env('OPENAI_API_KEY'))
                    ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'))
                    ->make();

                // FIX UTAMA: System prompt baru yang menghasilkan struktur lengkap
                $systemPrompt = <<<'PROMPT'
Anda adalah analis teks sastra/edukasi. Analisis teks berikut dan kembalikan HANYA JSON valid tanpa markdown, tanpa komentar, dengan struktur PERSIS seperti ini:

{
  "metadata": {
    "title": "Judul lengkap karya",
    "subject": "Mata pelajaran atau subjek (contoh: Bahasa Indonesia & Budaya Lokal)",
    "level": "Tingkat kesulitan (Dasar/Menengah/Lanjutan)"
  },
  "knowledge_map": {
    "context_summary": "Ringkasan cerita/isi dalam 2-4 kalimat.",
    "key_concepts": [
      {"term": "Istilah kunci", "explanation": "Penjelasan singkat"}
    ],
    "entities": {
      "characters": [
        {
          "name": "Nama tokoh",
          "role": "Peran tokoh dalam cerita",
          "attributes": ["Sifat 1", "Sifat 2"]
        }
      ],
      "locations": ["Lokasi 1", "Lokasi 2"],
      "objects": ["Benda penting 1", "Benda penting 2"]
    },
    "glossary_bilingual": [
      {
        "indo": "Kata Bahasa Indonesia",
        "regional": "Kata bahasa daerah/asing",
        "definition": "Definisi atau penjelasan kata"
      }
    ],
    "entity_relations": [
      "Tokoh A berhubungan dengan Tokoh B karena ...",
      "Benda X digunakan oleh Tokoh Y untuk ..."
    ],
    "themes": [
      "Tema utama 1",
      "Tema utama 2"
    ]
  },
  "sections": [
    {"name": "Nama Bagian 1", "description": "Deskripsi singkat bagian ini"},
    {"name": "Nama Bagian 2", "description": "Deskripsi singkat"}
  ]
}

PENTING:
- "sections" adalah pembagian logis teks (misal: Pembukaan, Konflik, Klimaks, Penyelesaian, Glosarium).
- Isi "glossary_bilingual" dengan kata-kata non-Indonesia (daerah/asing) yang ada dalam teks.
- Isi "entity_relations" dengan hubungan antar tokoh/benda yang eksplisit dalam cerita.
- Semua array minimal berisi 1 item. Jika tidak ada data, isi dengan string "Tidak ditemukan".
- JANGAN tambahkan field lain di luar struktur di atas.
PROMPT;

                $response = $client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $safeTextForAI]
                    ]
                ]);

                $aiData = json_decode($response->choices[0]->message->content, true);

                // -----------------------------------------------------------------
                // LANGKAH 5: Bagi chunks ke sections sesuai arahan AI
                // FIX: Sections dinamis berdasarkan respons AI, bukan 1 section generik
                // -----------------------------------------------------------------
                $aiSections = $aiData['sections'] ?? [['name' => 'Konten Utama', 'description' => '']];
                $totalSections = count($aiSections);
                $chunksPerSection = max(1, (int)ceil(count($chunks) / $totalSections));

                $contentStructured = [];
                foreach ($aiSections as $idx => $sec) {
                    $start = $idx * $chunksPerSection;
                    $sectionChunks = array_slice($chunks, $start, $chunksPerSection);

                    if (!empty($sectionChunks)) {
                        $contentStructured[] = [
                            "section" => $sec['name'],
                            "chunks"  => $sectionChunks,
                        ];
                    }
                }

                // Jika ada sisa chunks yang tidak masuk section manapun, tambah ke section terakhir
                $assignedCount = array_sum(array_map(fn($s) => count($s['chunks']), $contentStructured));
                if ($assignedCount < count($chunks)) {
                    $remainingChunks = array_slice($chunks, $assignedCount);
                    if (!empty($contentStructured)) {
                        $lastIdx = count($contentStructured) - 1;
                        $contentStructured[$lastIdx]['chunks'] = array_merge(
                            $contentStructured[$lastIdx]['chunks'],
                            $remainingChunks
                        );
                    } else {
                        $contentStructured[] = ['section' => 'Konten', 'chunks' => $remainingChunks];
                    }
                }

                // -----------------------------------------------------------------
                // LANGKAH 6: RAKIT JSON FINAL
                // FIX: Struktur identik dengan materi_edukasi.json
                // - key "content_structured" (bukan "content")
                // - knowledge_map lengkap: entities, glossary_bilingual, entity_relations, themes
                // -----------------------------------------------------------------
                $jsonResult = [
                    "metadata" => [
                        "material_id"  => "MAT-" . strtoupper(Str::random(5)),
                        "title"        => $aiData['metadata']['title'] ?? pathinfo($fileName, PATHINFO_FILENAME),
                        "category"     => "Literasi",
                        "subject"      => $aiData['metadata']['subject'] ?? "Cerita Rakyat",
                        "level"        => $aiData['metadata']['level'] ?? "Menengah",
                        "last_updated" => now()->format('Y-m-d'),
                    ],

                    "knowledge_map" => [
                        // Field dasar
                        "context_summary" => $aiData['knowledge_map']['context_summary'] ?? "Cerita hasil ekstraksi PDF.",
                        "key_concepts"    => $aiData['knowledge_map']['key_concepts'] ?? [],

                        // FIX: Field tambahan yang sebelumnya tidak ada
                        "entities" => [
                            "characters" => $aiData['knowledge_map']['entities']['characters'] ?? [],
                            "locations"  => $aiData['knowledge_map']['entities']['locations'] ?? [],
                            "objects"    => $aiData['knowledge_map']['entities']['objects'] ?? [],
                        ],
                        "glossary_bilingual" => $aiData['knowledge_map']['glossary_bilingual'] ?? [],
                        "entity_relations"   => $aiData['knowledge_map']['entity_relations'] ?? [],
                        "themes"             => $aiData['knowledge_map']['themes'] ?? [],
                    ],

                    // FIX: Nama key "content_structured" (bukan "content")
                    "content_structured" => $contentStructured,
                ];

                Storage::delete($tempPdfPath);

                $outputFilePath = 'json_exports/' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '_' . $timestamp . '.json';
                if (!Storage::disk('local')->exists('json_exports')) Storage::disk('local')->makeDirectory('json_exports');
                Storage::disk('local')->put($outputFilePath, json_encode($jsonResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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
                'download_url' => route('converter.download', ['path' => base64_encode($outputFilePath)])
            ]);

        } catch (\Exception $e) {
            FileConversion::create([
                'original_filename' => $fileName,
                'conversion_type'   => $request->type ?? 'unknown',
                'json_output_path'  => '-',
                'file_size_kb'      => $fileSize ?? 0,
                'status'            => 'failed',
                'error_log'         => $e->getMessage()
            ]);

            return response()->json(['status' => 'error', 'errors' => [$e->getMessage()]], 422);
        }
    }

    /**
     * Ekstrak keyword sederhana dari teks: ambil kata-kata unik yang panjangnya > 4 karakter,
     * bukan kata sambung umum, maksimal 5 kata.
     */
    private function extractKeywords(string $text): array
    {
        $stopWords = ['yang', 'dengan', 'untuk', 'pada', 'dari', 'dalam', 'adalah', 'akan', 'itu', 'ini', 'dan', 'atau', 'juga', 'sudah', 'saja', 'bisa', 'lebih', 'tidak', 'karena', 'tetapi', 'namun', 'bahwa', 'kepada', 'oleh', 'ketika', 'saat', 'setelah', 'sebelum'];

        preg_match_all('/\b[A-Za-z\x{00C0}-\x{024F}]{4,}\b/u', $text, $matches);
        $words = array_unique(array_map('mb_strtolower', $matches[0]));
        $filtered = array_filter($words, fn($w) => !in_array(mb_strtolower($w), $stopWords));

        return array_values(array_slice($filtered, 0, 5));
    }

    public function download($path)
    {
        $decodedPath = base64_decode($path);
        $absolutePath = Storage::disk('local')->path($decodedPath);
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        if (file_exists($absolutePath)) {
            return response()->download($absolutePath);
        }

        abort(404, 'File sistem tidak ditemukan di: ' . $absolutePath);
    }
}

// Helper Pembersihan Teks
function clean_pdf_text($text)
{
    $text = preg_replace('/[\x00-\x09\x0B-\x1F\x7F-\xFF]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}