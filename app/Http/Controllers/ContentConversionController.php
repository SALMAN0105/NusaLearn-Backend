<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\FileConversion;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class ContentConversionController extends Controller
{
    public function index()
    {
        $histories = FileConversion::latest()->limit(10)->get();
        return view('admin.conversions.index', compact('histories'));
    }

    public function process(Request $request)
    {
        // 1. Validasi Ekstensi File Dasar
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:kamus,materi',
            'upload_file' => [
                'required',
                'file',
                'max:512000', // Max 500MB
                function ($attribute, $value, $fail) use ($request) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($request->type === 'kamus' && !in_array($ext, ['xls', 'xlsx', 'csv'])) {
                        $fail('Format wajib Excel/CSV untuk Tipe Kamus.');
                    }
                    if ($request->type === 'materi' && $ext !== 'pdf') {
                        $fail('Format wajib PDF untuk Tipe Materi.');
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
        $fileSize = $file->getSize() / 1024; // in KB
        $timestamp = now()->format('Ymd_His');

        try {
            $jsonResult = [];

            // ==========================================
            // LOGIKA KAMUS (EXCEL/CSV -> JSON) - STANDAR BARU
            // ==========================================
            if ($request->type === 'kamus') {
                $data = Excel::toArray([], $file)[0] ?? []; 
                
                if (empty($data) || count($data) < 2) {
                    throw new \Exception('File Excel/CSV kosong atau tidak memiliki baris data riil.');
                }

                // 1. Ambil Baris Pertama sebagai Header dan bersihkan spasi
                $headers = array_map(function($col) { return strtolower(trim((string)$col)); }, $data[0]);
                
                // 2. Definisi Format Wajib Sesuai Permintaan Anda
                $expectedHeaders = ['source_text', 'target_text', 'type', 'category', 'contoh_kalimat', 'sinonim', 'antonim', 'status'];
                
                // 3. Validasi Blokir Eksekusi Jika Tidak Sama Persis
                $missingHeaders = array_diff($expectedHeaders, $headers);
                if (count($missingHeaders) > 0) {
                    throw new \Exception('Format kolom Excel/CSV tidak valid! Anda kurang kolom: ' . implode(', ', $missingHeaders));
                }

                $headerMap = array_flip($headers);
                $kosakata = [];

                // 4. Parsing Data Baris per Baris
                for ($i = 1; $i < count($data); $i++) {
                    $row = $data[$i];
                    
                    if (empty(array_filter($row))) continue;

                    $sinonimStr = (string)($row[$headerMap['sinonim']] ?? '');
                    $antonimStr = (string)($row[$headerMap['antonim']] ?? '');

                    $kosakata[] = [
                        'id' => 'dict_'.str_pad($i, 5, '0', STR_PAD_LEFT), // Generate ID Otomatis
                        'source_text' => $row[$headerMap['source_text']] ?? '',
                        'target_text' => $row[$headerMap['target_text']] ?? '',
                        'type' => $row[$headerMap['type']] ?? '',
                        'category' => $row[$headerMap['category']] ?? '',
                        'contoh_kalimat' => $row[$headerMap['contoh_kalimat']] ?? null,
                        'sinonim' => $sinonimStr ? array_map('trim', explode(',', $sinonimStr)) : [],
                        'antonim' => $antonimStr ? array_map('trim', explode(',', $antonimStr)) : [],
                        'status' => $row[$headerMap['status']] ?? 'active',
                    ];
                }

                // 5. Output Struktur JSON Final
                $jsonResult = [
                    "metadata" => [
                        "dokumen" => "Kamus Regional",
                        "format" => "Standard Translation Matrix",
                        "versi" => "1.0",
                        "sumber_file" => $fileName,
                        "terakhir_diperbarui" => now()->toIso8601String(),
                    ],
                    "kosakata" => $kosakata
                ];
            } 
            // ==========================================
            // LOGIKA MATERI (PDF -> JSON + EKSTRAK GAMBAR)
            // ==========================================
            else {
                $tempPdfPath = $file->storeAs('temp_pdfs', $timestamp . '.pdf');
                $absolutePdfPath = storage_path('app/' . $tempPdfPath);
                
                $processText = new Process(['pdftotext', $absolutePdfPath, '-']);
                $processText->run();
                if (!$processText->isSuccessful()) {
                    throw new \Exception("Gagal mengekstrak teks PDF. Pastikan Poppler-utils terinstall.");
                }
                $extractedText = $processText->getOutput();

                $imageOutputDir = storage_path('app/public/assets/materi_images/' . $timestamp);
                if (!file_exists($imageOutputDir)) {
                    mkdir($imageOutputDir, 0755, true);
                }
                
                $processImages = new Process(['pdfimages', '-all', $absolutePdfPath, $imageOutputDir . '/img']);
                $processImages->run();

                $extractedImages = [];
                foreach (glob($imageOutputDir . '/*.*') as $imgFile) {
                    $extractedImages[] = 'assets/materi_images/' . $timestamp . '/' . basename($imgFile);
                }

                $jsonResult = [
                    "metadata" => [
                        "title" => pathinfo($fileName, PATHINFO_FILENAME),
                        "category" => "materi_pembelajaran",
                        "generated_at" => now()->toIso8601String()
                    ],
                    "content" => [
                        "text" => clean_pdf_text($extractedText),
                        "images" => $extractedImages
                    ]
                ];

                Storage::delete($tempPdfPath);
            }

            // ==========================================
            // FINALISASI & PENYIMPANAN
            // ==========================================
            $jsonFileName = 'json_exports/' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '_' . $timestamp . '.json';
            Storage::disk('local')->put($jsonFileName, json_encode($jsonResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            FileConversion::create([
                'original_filename' => $fileName,
                'conversion_type' => $request->type === 'kamus' ? 'kamus_excel' : 'materi_pdf',
                'json_output_path' => $jsonFileName,
                'file_size_kb' => $fileSize,
                'status' => 'success',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Konversi Selesai & Format Valid',
                'download_url' => route('converter.download', ['path' => base64_encode($jsonFileName)])
            ]);

        } catch (\Exception $e) {
            FileConversion::create([
                'original_filename' => $fileName,
                'conversion_type' => $request->type === 'kamus' ? 'kamus_excel' : 'materi_pdf',
                'json_output_path' => '-',
                'file_size_kb' => $fileSize,
                'status' => 'failed',
                'error_log' => $e->getMessage()
            ]);

            return response()->json(['status' => 'error', 'errors' => [$e->getMessage()]], 422);
        }
    }

    public function download($path)
    {
        $decodedPath = base64_decode($path);
        if (Storage::disk('local')->exists($decodedPath)) {
            $absolutePath = storage_path('app/' . $decodedPath);
            return response()->download($absolutePath);
        }
        abort(404, 'Data JSON tidak ditemukan di server.');
    }
}

function clean_pdf_text($text) {
    $text = preg_replace('/[\x00-\x09\x0B-\x1F\x7F-\xFF]/', ' ', $text);
    return trim(preg_replace('/[ \t]+/', ' ', $text));
}