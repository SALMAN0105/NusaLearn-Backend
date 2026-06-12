<?php
// app/Http/Controllers/Admin/QuestionController.php
// Update: tambah auto_fetch_log di response generate

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Materi;
use App\Models\PustakaAset;
use App\Services\QuizGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    public function __construct(
        private QuizGeneratorService $quizGenerator,
    ) {}

    // =========================================================================
    // INDEX
    // =========================================================================

    private function checkMateriOwnership($materiId)
    {
        $user = auth()->user();
        if ($user->peran === 'guru' || $user->peran === 'admin') {
            $materi = Materi::findOrFail($materiId);
            if ($materi->asal_sekolah !== $user->asal_sekolah) {
                abort(403, 'Unauthorized Action. Anda tidak memiliki akses ke materi ini.');
            }
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $materiQuery = Materi::select('id', 'judul');
        $soalQuery = Soal::with('materi');

        if ($user->peran === 'guru' || $user->peran === 'admin') {
            $materiQuery->where('asal_sekolah', $user->asal_sekolah);
            $soalQuery->whereHas('materi', function ($q) use ($user) {
                $q->where('asal_sekolah', $user->asal_sekolah);
            });
        }

        $materials = $materiQuery->get();
        $query     = $soalQuery;

        if ($request->filled('materi_id') && $request->materi_id !== 'all') {
            $query->where('materi_id', $request->materi_id);
        }

        if ($request->filled('search')) {
            $query->where('teks_soal', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('tipe_template')) {
            $query->where('tipe_template', $request->tipe_template);
        }

        $questions = $query->latest()->paginate(5)->withQueryString();

        if ($user->peran === 'administrator') {
            return view('administrator.questions', compact('questions', 'materials'));
        }
        return view('admin.questions', compact('questions', 'materials'));
    }

    // =========================================================================
    // GENERATE — Trigger AI (AJAX)
    // =========================================================================

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'materi_id'   => 'required|exists:materi,id',
            'tipe_template' => 'required|in:' . implode(',', array_keys(Soal::ALL_TEMPLATES)),
            'difficulty'    => 'required|integer|min:1|max:3',
        ]);

        $this->checkMateriOwnership($validated['materi_id']);

        try {
            $result = $this->quizGenerator->generate(
                materialId:   (int) $validated['materi_id'],
                templateType: $validated['tipe_template'],
                difficulty:   (int) $validated['difficulty'],
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[QuestionController@generateExplanationApi] ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke layanan AI atau terjadi timeout. Silakan coba lagi.'
            ], 500);
        }

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['error'],
            ], 422);
        }

        $fetchLog   = $result['auto_fetch_log'] ?? ['fetched' => [], 'failed' => []];
        $hasWarnings = !empty($result['missing_assets']);

        return response()->json([
            'status'           => 'success',
            'data_soal'    => $result['data_soal'],
            'aset_diperlukan'  => $result['aset_diperlukan'],
            'missing_assets'   => $result['missing_assets'],
            'raw_json'         => $result['raw_json'],
            'has_warnings'     => $hasWarnings,

            // Info baru: berapa aset yang berhasil di-fetch otomatis
            'auto_fetch'       => [
                'fetched_count' => count($fetchLog['fetched']),
                'failed_count'  => count($fetchLog['failed']),
                'fetched'       => $fetchLog['fetched'],
                'failed'        => $fetchLog['failed'],
            ],
        ]);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        $templateType = $request->input('tipe_template', Soal::TEMPLATE_MULTIPLE_CHOICE);

        if ($templateType === Soal::TEMPLATE_MULTIPLE_CHOICE
            && $request->filled('option_a')
        ) {
            return $this->storeManualMultipleChoice($request);
        }

        return $this->storeMultimediaQuestion($request);
    }

    public function destroy(Soal $soal)
    {
        $this->checkMateriOwnership($soal->materi_id);

        $question = $soal;
        $question->delete();
        
        $routePrefix = auth()->user()->peran === 'administrator' ? 'administrator.soal.index' : 'soal.index';
        return redirect()->route($routePrefix)->with('success', 'Soal berhasil dihapus.');
    }

    public function update(Request $request, Soal $soal)
    {
        $this->checkMateriOwnership($soal->materi_id);

        $question = $soal;
        // Untuk sementara, kita gunakan storeManual logic atau similar
        // Namun karena ini update, kita perlu menyesuaikan
        
        // Kita bisa refactor logic storeManual ke private method agar bisa dipakai bersama
        return $this->updateManual($request, $question);
    }

    private function updateManual(Request $request, Soal $question)
    {
        $validated = $request->validate([
            'materi_id'        => 'required|integer|exists:materi,id',
            'kelas'            => 'required|integer|min:1|max:3',
            'tipe_template'      => 'required|in:multiple_choice,drag_and_drop,matching_game,fill_blank,image_quiz',
            'bobot_kesulitan'  => 'required|integer|min:1|max:5',
            'teks_soal' => 'required|string|max:2000',
            'explanation'        => 'nullable|string|max:2000',
            'options'            => 'nullable|array|min:2|max:4',
            'options.*'          => 'nullable|string|max:500',
            'correct_option'     => 'nullable|integer|min:0|max:3',
            'dnd_images'         => 'nullable|array|max:4',
            'dnd_images.*'       => 'nullable|image|mimes:jpeg,png,webp|max:3072',
            'dnd_correct_index'  => 'nullable|integer|min:0|max:3',
            'dnd_zones'          => 'nullable|string',
            'pair_left'          => 'nullable|array|min:2|max:10',
            'pair_left.*'        => 'nullable|string|max:300',
            'pair_right'         => 'nullable|array|min:2|max:10',
            'pair_right.*'       => 'nullable|string|max:300',
            'fill_sentence'           => 'nullable|string|max:2000',
            'fill_correct_answers'    => 'nullable|array',
            'fill_correct_answers.*'  => 'nullable|string|max:300',
            'word_bank'               => 'nullable|string',
            'main_image'         => 'nullable|image|mimes:jpeg,png,webp|max:3072',
        ]);

        $this->checkMateriOwnership($validated['materi_id']);

        $templateType   = $validated['tipe_template'];
        $questionData   = $question->data_soal ?? [];
        $assetsRequired = $question->aset_diperlukan ?? [];
        $correctKey     = $question->kunci_jawaban;
        $optionsJson    = $question->opsi_json;

        // Logic build data_soal (Mirip storeManual tapi update field yang ada)
        // Kita bisa copy logic dari storeManual atau buat yang lebih generic
        
        switch ($templateType) {
            case 'multiple_choice':
                $options    = array_values(array_filter($request->input('options', []), 'strlen'));
                $correctIdx = min((int) $request->input('correct_option', 0), count($options) - 1);
                $letters     = ['a', 'b', 'c', 'd'];
                $optionsJson = collect($options)->map(fn($txt, $i) => [
                    'id'         => $letters[$i] ?? "opt_{$i}",
                    'text'       => $txt,
                    'is_correct' => $i === $correctIdx,
                ])->values()->all();
                $correctKey   = $letters[$correctIdx] ?? 'a';
                $questionData = [
                    'teks_soal' => $validated['teks_soal'],
                    'tipe_template'      => 'multiple_choice',
                    'options'            => $optionsJson,
                    'kunci_jawaban' => $correctKey,
                    'explanation'        => $validated['explanation'] ?? null,
                    'aset_diperlukan'    => [],
                ];
                break;

            case 'drag_and_drop':
                $correctIdx = (int) $request->input('dnd_correct_index', -1);
                $dndZones   = json_decode($request->input('dnd_zones', '[]'), true) ?? [];
                
                $items      = $questionData['items'] ?? [];
                $imageFiles = $request->file('dnd_images', []);
                
                // Jika ada upload baru, update assetsRequired dan items
                foreach ($imageFiles as $i => $file) {
                    if (!$file || !$file->isValid()) continue;
                    $hash     = md5_file($file->getRealPath());
                    $ext      = $file->getClientOriginalExtension() ?: 'jpg';
                    $nama_file = $hash . '.' . $ext;
                    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $nama_file)) {
                        $file->storeAs('quiz-assets', $nama_file, 'public');
                    }
                    \App\Models\PustakaAset::firstOrCreate(
                        ['nama_file' => $nama_file],
                        [
                            'nama_asli' => $file->getClientOriginalName(), 
                            'tipe_mime' => $file->getMimeType(), 
                            'ekstensi' => $ext,
                            'tipe_aset' => 'image',
                            'sumber_api' => 'manual_upload',
                            'ukuran_kb' => round($file->getSize() / 1024),
                            'aktif' => true
                        ]
                    );
                    
                    if (!in_array($nama_file, $assetsRequired)) {
                        $assetsRequired[] = $nama_file;
                    }
                    
                    // Update item di index tersebut atau tambah jika baru
                    $items[$i] = [
                        'id'          => 'item_temp', // Akan di-fix di bawah
                        'image_asset' => $nama_file,
                        'correct_zone'=> null, // Reset dulu, akan di-set di bawah
                    ];
                }
                
                // Pastikan array re-indexed dan ID urut kembali
                $items = array_values($items);
                // Re-set correct zone dan ID berdasarkan index yang dipilih
                foreach($items as $i => &$item) {
                    $item['id'] = 'item_' . ($i + 1);
                    $item['correct_zone'] = ($i === $correctIdx) ? 'zone_1' : null;
                }

                $zones = [];
                if (!empty($dndZones)) {
                    foreach ($dndZones as $j => $zoneLabel) {
                        $zones[] = ['id' => 'zone_' . ($j + 1), 'label' => $zoneLabel];
                    }
                } else {
                    $zones[] = ['id' => 'zone_1', 'label' => 'Zona Jawaban'];
                }

                $correctMapping = [];
                foreach ($items as $item) {
                    if ($item['correct_zone']) {
                        $correctMapping[$item['id']] = $item['correct_zone'];
                    }
                }

                $questionData = [
                    'teks_soal' => $validated['teks_soal'],
                    'tipe_template'      => 'drag_and_drop',
                    'items'              => $items,
                    'zones'              => $zones,
                    'correct_mapping'    => $correctMapping,
                    'explanation'        => $validated['explanation'] ?? null,
                    'aset_diperlukan'    => $assetsRequired,
                ];
                break;

            case 'matching_game':
                $lefts  = array_values(array_filter($request->input('pair_left', []), 'strlen'));
                $rights = array_values(array_filter($request->input('pair_right', []), 'strlen'));
                $count  = min(count($lefts), count($rights));
                $pairs = $correctPairs = [];
                for ($i = 0; $i < $count; $i++) {
                    $leftId  = 'L' . ($i + 1);
                    $rightId = 'R' . ($i + 1);
                    $pairs[] = [
                        'left'  => ['id' => $leftId,  'text' => $lefts[$i]],
                        'right' => ['id' => $rightId, 'text' => $rights[$i]],
                    ];
                    $correctPairs[] = ['left' => $leftId, 'right' => $rightId];
                }
                $questionData = [
                    'teks_soal' => $validated['teks_soal'],
                    'tipe_template'      => 'matching_game',
                    'pairs'              => $pairs,
                    'correct_pairs'      => $correctPairs,
                    'explanation'        => $validated['explanation'] ?? null,
                    'aset_diperlukan'    => [],
                ];
                break;

            case 'fill_blank':
                $sentence       = $request->input('fill_sentence', $validated['teks_soal']);
                $correctAnswers = array_values(array_filter($request->input('fill_correct_answers', []), 'strlen'));
                $wordBankRaw    = json_decode($request->input('word_bank', '[]'), true) ?? [];
                $wordBank       = array_values(array_filter($wordBankRaw, 'strlen'));
                
                $blankCount  = substr_count($sentence, '___');
                if ($blankCount === 0) {
                    return back()->withErrors(['fill_sentence' => 'Kalimat harus mengandung ___ sebagai penanda kosong.'])->withInput();
                }
                if (count($correctAnswers) !== $blankCount) {
                    return back()->withErrors(['fill_correct_answers' => "Jumlah jawaban ({$blankCount} blank) tidak sesuai."])->withInput();
                }

                $blanks = [];
                foreach ($correctAnswers as $i => $ans) {
                    $blanks[] = ['id' => 'blank_' . ($i + 1), 'correct_answer' => $ans, 'hint' => null];
                    if (!in_array($ans, $wordBank)) $wordBank[] = $ans;
                }
                shuffle($wordBank);
                $questionData = [
                    'teks_soal' => $sentence,
                    'tipe_template'      => 'fill_blank',
                    'blanks'             => $blanks,
                    'word_bank'          => $wordBank,
                    'correct_answers'    => $correctAnswers,
                    'explanation'        => $validated['explanation'] ?? null,
                    'aset_diperlukan'    => [],
                ];
                break;

            case 'image_quiz':
                $mainImagenama_file = $questionData['main_image'] ?? null;
                if ($request->hasFile('main_image') && $request->file('main_image')->isValid()) {
                    $file     = $request->file('main_image');
                    $hash     = md5_file($file->getRealPath());
                    $ext      = $file->getClientOriginalExtension() ?: 'jpg';
                    $nama_file = $hash . '.' . $ext;
                    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $nama_file)) {
                        $file->storeAs('quiz-assets', $nama_file, 'public');
                    }
                    \App\Models\PustakaAset::firstOrCreate(
                        ['nama_file' => $nama_file],
                        [
                            'nama_asli' => $file->getClientOriginalName(), 
                            'tipe_mime' => $file->getMimeType(), 
                            'ekstensi' => $ext,
                            'tipe_aset' => 'image',
                            'sumber_api' => 'manual_upload',
                            'ukuran_kb' => round($file->getSize() / 1024),
                            'aktif' => true
                        ]
                    );
                    $mainImagenama_file = $nama_file;
                    if (!in_array($nama_file, $assetsRequired)) {
                        $assetsRequired[] = $nama_file;
                    }
                }
                $options    = array_values(array_filter($request->input('options', []), 'strlen'));
                $correctIdx = min((int) $request->input('correct_option', 0), max(count($options) - 1, 0));
                $letters    = ['a', 'b', 'c', 'd'];
                $optionsJson = collect($options)->map(fn($txt, $i) => [
                    'id'         => $letters[$i] ?? "opt_{$i}",
                    'text'       => $txt,
                    'is_correct' => $i === $correctIdx,
                ])->values()->all();
                $correctKey   = $letters[$correctIdx] ?? 'a';
                $questionData = [
                    'teks_soal' => $validated['teks_soal'],
                    'tipe_template'      => 'image_quiz',
                    'main_image'         => $mainImagenama_file,
                    'options'            => $optionsJson,
                    'kunci_jawaban' => $correctKey,
                    'explanation'        => $validated['explanation'] ?? null,
                    'aset_diperlukan'    => $assetsRequired,
                ];
                break;
        }

        $question->update([
            'materi_id'        => $validated['materi_id'],
            'kelas'            => $validated['kelas'],
            'tipe_template'      => $templateType,
            'bobot_kesulitan'  => $validated['bobot_kesulitan'],
            'teks_soal' => $validated['teks_soal'],
            'data_soal'      => $questionData,
            'aset_diperlukan'    => $assetsRequired,
            'kunci_jawaban' => $correctKey,
            'opsi_json'       => $optionsJson,
        ]);

        return back()->with('success', "Soal berhasil diperbarui!");
    }

    // =========================================================================
    // PRIVATE — Store Helpers
    // =========================================================================

    private function storeManualMultipleChoice(Request $request)
    {
        $request->validate([
            'materi_id'        => 'required|exists:materi,id',
            'kelas'            => 'required|integer|min:1|max:3',
            'teks_soal' => 'required|string|max:1000',
            'bobot_kesulitan'  => 'required|integer|min:1|max:3',
            'option_a'           => 'required|string|max:500',
            'option_b'           => 'required|string|max:500',
            'option_c'           => 'required|string|max:500',
            'option_d'           => 'required|string|max:500',
            'kunci_jawaban' => 'required|in:a,b,c,d',
        ]);

        $this->checkMateriOwnership($request->materi_id);

        $options = [
            ['id' => 'a', 'text' => $request->option_a],
            ['id' => 'b', 'text' => $request->option_b],
            ['id' => 'c', 'text' => $request->option_c],
            ['id' => 'd', 'text' => $request->option_d],
        ];

        Soal::create([
            'materi_id'        => $request->materi_id,
            'kelas'            => $request->kelas,
            'teks_soal' => $request->teks_soal,
            'opsi_json'       => $options,
            'kunci_jawaban' => $request->kunci_jawaban,
            'bobot_kesulitan'  => $request->bobot_kesulitan,
            'tipe_template'      => Soal::TEMPLATE_MULTIPLE_CHOICE,
            'data_soal'      => null,
            'aset_diperlukan'    => [],
        ]);

        return back()->with('success', 'Soal Pilihan Ganda berhasil ditambahkan.');
    }

    private function storeMultimediaQuestion(Request $request)
    {
        $request->validate([
            'materi_id'        => 'required|exists:materi,id',
            'kelas'            => 'required|integer|min:1|max:3',
            'bobot_kesulitan'  => 'required|integer|min:1|max:3',
            'tipe_template'      => 'required|in:' . implode(',', array_keys(Soal::ALL_TEMPLATES)),
            'data_soal_json' => 'required|string',
        ]);

        $this->checkMateriOwnership($request->materi_id);

        $questionData = json_decode($request->data_soal_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors([
                'data_soal_json' => 'Data soal tidak valid (JSON rusak): ' . json_last_error_msg(),
            ]);
        }

        // Fix #11: Fallback teks_soal dari data_soal
        $questionTextIndo = trim($request->input('teks_soal', ''));
        if (empty($questionTextIndo)) {
            $questionTextIndo = $questionData['teks_soal'] ?? '';
        }

        if (empty($questionTextIndo)) {
            return back()->withErrors([
                'teks_soal' => 'Teks pertanyaan tidak boleh kosong.',
            ]);
        }

        $assetsRequired = $questionData['aset_diperlukan'] ?? [];
        if (!is_array($assetsRequired)) {
            $assetsRequired = [];
        }

        // Filter placeholder yang belum di-resolve
        $assetsRequired = array_values(array_filter(
            $assetsRequired,
            fn($f) => !str_starts_with((string)$f, '__SLOT_')
        ));

        // Validasi aset missing (hanya untuk file nyata, bukan placeholder)
        $missingAssets = [];
        if (!empty($assetsRequired)) {
            $existing = PustakaAset::whereIn('nama_file', $assetsRequired)
                ->where('aktif', true)
                ->pluck('nama_file')
                ->toArray();

            foreach ($assetsRequired as $nama_file) {
                if (!in_array($nama_file, $existing)) {
                    $missingAssets[] = $nama_file;
                }
            }
        }

        if (!empty($missingAssets) && !$request->boolean('force_save')) {
            return back()
                ->withInput()
                ->withErrors([
                    'assets' => 'Aset berikut tidak ditemukan di library: '
                        . implode(', ', $missingAssets)
                        . '. Centang "Simpan paksa" untuk tetap menyimpan.',
                ]);
        }

        $correctAnswerKey = null;
        $optionsJson      = null;

        if ($request->tipe_template === Soal::TEMPLATE_MULTIPLE_CHOICE) {
            $correctAnswerKey = $questionData['kunci_jawaban'] ?? null;
            $optionsJson      = $questionData['options'] ?? null;
        }

        Soal::create([
            'materi_id'        => $request->materi_id,
            'kelas'            => $request->kelas,
            'teks_soal' => $questionTextIndo,
            'bobot_kesulitan'  => $request->bobot_kesulitan,
            'tipe_template'      => $request->tipe_template,
            'aset_diperlukan'    => $assetsRequired,
            'opsi_json'       => $optionsJson,
            'kunci_jawaban' => $correctAnswerKey,
        ]);

        $templateLabel = Soal::ALL_TEMPLATES[$request->tipe_template] ?? $request->tipe_template;

        Log::info("[QuestionController] Soal berhasil disimpan", [
            'template'    => $request->tipe_template,
            'materi_id' => $request->materi_id,
            'assets'      => $assetsRequired,
        ]);

        return back()->with('success', "Soal {$templateLabel} berhasil ditambahkan ke bank soal!");
    }

    /**
     * Memproses pembuatan soal manual dari Guru (Bypass AI)
     */
    public function storeManual(Request $request)
{
    $validated = $request->validate([
        'materi_id'        => 'required|integer|exists:materi,id',
        'kelas'            => 'required|integer|min:1|max:3',
        'tipe_template'      => 'required|in:multiple_choice,drag_and_drop,matching_game,fill_blank,image_quiz',
        'bobot_kesulitan'  => 'required|integer|min:1|max:5',
        'teks_soal' => 'required|string|max:2000',
        'explanation'        => 'nullable|string|max:2000',
        'options'            => 'nullable|array|min:2|max:4',
        'options.*'          => 'nullable|string|max:500',
        'correct_option'     => 'nullable|integer|min:0|max:3',
        'dnd_images'         => 'nullable|array|max:4',
        'dnd_images.*'       => 'nullable|image|mimes:jpeg,png,webp|max:3072',
        'dnd_correct_index'  => 'nullable|integer|min:0|max:3',
        'dnd_zones'          => 'nullable|string',
        'pair_left'          => 'nullable|array|min:2|max:10',
        'pair_left.*'        => 'nullable|string|max:300',
        'pair_right'         => 'nullable|array|min:2|max:10',
        'pair_right.*'       => 'nullable|string|max:300',
        'fill_sentence'           => 'nullable|string|max:2000',
        'fill_correct_answers'    => 'nullable|array',
        'fill_correct_answers.*'  => 'nullable|string|max:300',
        'word_bank'               => 'nullable|string',
        'main_image'         => 'nullable|image|mimes:jpeg,png,webp|max:3072',
    ]);

    $this->checkMateriOwnership($validated['materi_id']);

    $templateType   = $validated['tipe_template'];
    $questionData   = [];
    $assetsRequired = [];
    $correctKey     = null;
    $optionsJson    = null;
    $errorResponse  = null; // ← Tampung error sebelum transaksi

    // ── BUILD data_soal di LUAR transaksi ──────────────────────────
    switch ($templateType) {

        case 'multiple_choice':
            $options    = array_values(array_filter($request->input('options', []), 'strlen'));
            $correctIdx = min((int) $request->input('correct_option', 0), count($options) - 1);
            if (count($options) < 2) {
                return back()->withErrors(['options' => 'Minimal 2 opsi harus diisi.'])->withInput();
            }
            $letters     = ['a', 'b', 'c', 'd'];
            $optionsJson = collect($options)->map(fn($txt, $i) => [
                'id'         => $letters[$i] ?? "opt_{$i}",
                'text'       => $txt,
                'is_correct' => $i === $correctIdx,
            ])->values()->all();
            $correctKey   = $letters[$correctIdx] ?? 'a';
            $questionData = [
                'teks_soal' => $validated['teks_soal'],
                'tipe_template'      => 'multiple_choice',
                'options'            => $optionsJson,
                'kunci_jawaban' => $correctKey,
                'explanation'        => $validated['explanation'] ?? null,
                'aset_diperlukan'    => [],
            ];
            break;

        case 'drag_and_drop':
            $correctIdx = (int) $request->input('dnd_correct_index', -1);
            $dndZones   = json_decode($request->input('dnd_zones', '[]'), true) ?? [];
            if ($correctIdx < 0) {
                return back()->withErrors(['dnd_correct_index' => 'Pilih foto jawaban yang benar.'])->withInput();
            }
            $items      = [];
            $imageFiles = $request->file('dnd_images', []);
            foreach ($imageFiles as $i => $file) {
                if (!$file || !$file->isValid()) continue;
                $hash     = md5_file($file->getRealPath());
                $ext      = $file->getClientOriginalExtension() ?: 'jpg';
                $nama_file = $hash . '.' . $ext;
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $nama_file)) {
                    $file->storeAs('quiz-assets', $nama_file, 'public');
                }
                \App\Models\PustakaAset::firstOrCreate(
                    ['nama_file' => $nama_file],
                    [
                        'nama_asli' => $file->getClientOriginalName(),
                        'tipe_mime'     => $file->getMimeType() ?? 'image/jpeg',
                        'ekstensi'      => $ext,
                        'tipe_aset'     => 'image',
                        'ukuran_kb'       => round($file->getSize() / 1024),
                        'sumber_api'    => 'manual_upload',
                        'kata_kunci'          => ['drag_and_drop', 'manual'],
                        'aktif'     => true,
                    ]
                );
                $assetsRequired[] = $nama_file;
                $items[] = [
                    'id'          => 'item_temp',
                    'image_asset' => $nama_file,
                    'correct_zone'=> $i === $correctIdx ? 'zone_1' : null,
                ];
            }
            
            // Re-index array dan perbaiki ID agar selalu berurutan
            $items = array_values($items);
            foreach($items as $idx => &$item) {
                $item['id'] = 'item_' . ($idx + 1);
            }
            if (empty($items)) {
                return back()->withErrors(['dnd_images' => 'Upload minimal 1 foto.'])->withInput();
            }
            $zones = [];
            if (!empty($dndZones)) {
                foreach ($dndZones as $j => $zoneLabel) {
                    $zones[] = ['id' => 'zone_' . ($j + 1), 'label' => $zoneLabel];
                }
            } else {
                $zones[] = ['id' => 'zone_1', 'label' => 'Zona Jawaban'];
            }
            $correctMapping = [];
            foreach ($items as $item) {
                if ($item['correct_zone']) {
                    $correctMapping[$item['id']] = $item['correct_zone'];
                }
            }
            $questionData = [
                'teks_soal' => $validated['teks_soal'],
                'tipe_template'      => 'drag_and_drop',
                'items'              => $items,
                'zones'              => $zones,
                'correct_mapping'    => $correctMapping,
                'explanation'        => $validated['explanation'] ?? null,
                'aset_diperlukan'    => $assetsRequired,
            ];
            break;

        case 'matching_game':
            $lefts  = array_values(array_filter($request->input('pair_left', []), 'strlen'));
            $rights = array_values(array_filter($request->input('pair_right', []), 'strlen'));
            $count  = min(count($lefts), count($rights));
            if ($count < 2) {
                return back()->withErrors(['pair_left' => 'Minimal 2 pasangan harus diisi.'])->withInput();
            }
            $pairs = $correctPairs = [];
            for ($i = 0; $i < $count; $i++) {
                $leftId  = 'L' . ($i + 1);
                $rightId = 'R' . ($i + 1);
                $pairs[] = [
                    'left'  => ['id' => $leftId,  'text' => $lefts[$i]],
                    'right' => ['id' => $rightId, 'text' => $rights[$i]],
                ];
                $correctPairs[] = ['left' => $leftId, 'right' => $rightId];
            }
            $questionData = [
                'teks_soal' => $validated['teks_soal'],
                'tipe_template'      => 'matching_game',
                'pairs'              => $pairs,
                'correct_pairs'      => $correctPairs,
                'explanation'        => $validated['explanation'] ?? null,
                'aset_diperlukan'    => [],
            ];
            break;

        case 'fill_blank':
            $sentence       = $request->input('fill_sentence', $validated['teks_soal']);
            $correctAnswers = array_values(array_filter(
                $request->input('fill_correct_answers', []), 'strlen'
            ));
            $wordBankRaw = json_decode($request->input('word_bank', '[]'), true) ?? [];
            $wordBank    = array_values(array_filter($wordBankRaw, 'strlen'));
            $blankCount  = substr_count($sentence, '___');
            if ($blankCount === 0) {
                return back()->withErrors(['fill_sentence' => 'Kalimat harus mengandung ___ sebagai penanda kosong.'])->withInput();
            }
            if (count($correctAnswers) !== $blankCount) {
                return back()->withErrors(['fill_correct_answers' => "Jumlah jawaban ({$blankCount} blank) tidak sesuai."])->withInput();
            }
            $blanks = [];
            foreach ($correctAnswers as $i => $ans) {
                $blanks[] = ['id' => 'blank_' . ($i + 1), 'correct_answer' => $ans, 'hint' => null];
            }
            foreach ($correctAnswers as $ans) {
                if (!in_array($ans, $wordBank)) $wordBank[] = $ans;
            }
            shuffle($wordBank);
            $questionData = [
                'teks_soal' => $sentence,
                'tipe_template'      => 'fill_blank',
                'blanks'             => $blanks,
                'word_bank'          => $wordBank,
                'correct_answers'    => $correctAnswers,
                'explanation'        => $validated['explanation'] ?? null,
                'aset_diperlukan'    => [],
            ];
            break;

        case 'image_quiz':
            $mainImagenama_file = null;
            if ($request->hasFile('main_image') && $request->file('main_image')->isValid()) {
                $file     = $request->file('main_image');
                $hash     = md5_file($file->getRealPath());
                $ext      = $file->getClientOriginalExtension() ?: 'jpg';
                $nama_file = $hash . '.' . $ext;
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $nama_file)) {
                    $file->storeAs('quiz-assets', $nama_file, 'public');
                }
                \App\Models\PustakaAset::firstOrCreate(
                    ['nama_file' => $nama_file],
                    [
                        'nama_asli' => $file->getClientOriginalName(),
                        'tipe_mime'     => $file->getMimeType() ?? 'image/jpeg',
                        'ekstensi'      => $ext,
                        'tipe_aset'     => 'image',
                        'ukuran_kb'       => round($file->getSize() / 1024),
                        'sumber_api'    => 'manual_upload',
                        'kata_kunci'          => ['image_quiz', 'manual'],
                        'aktif'     => true,
                    ]
                );
                $mainImagenama_file = $nama_file;
                $assetsRequired[]  = $nama_file;
            }
            $options    = array_values(array_filter($request->input('options', []), 'strlen'));
            $correctIdx = min((int) $request->input('correct_option', 0), max(count($options) - 1, 0));
            $letters    = ['a', 'b', 'c', 'd'];
            if (count($options) < 2) {
                return back()->withErrors(['options' => 'Minimal 2 opsi harus diisi.'])->withInput();
            }
            $optionsJson = collect($options)->map(fn($txt, $i) => [
                'id'         => $letters[$i] ?? "opt_{$i}",
                'text'       => $txt,
                'is_correct' => $i === $correctIdx,
            ])->values()->all();
            $correctKey   = $letters[$correctIdx] ?? 'a';
            $questionData = [
                'teks_soal' => $validated['teks_soal'],
                'tipe_template'      => 'image_quiz',
                'main_image'         => $mainImagenama_file,
                'options'            => $optionsJson,
                'kunci_jawaban' => $correctKey,
                'explanation'        => $validated['explanation'] ?? null,
                'aset_diperlukan'    => $assetsRequired,
            ];
            break;
    }

    // ── SIMPAN KE DATABASE ─────────────────────────────────────────────
    \App\Models\Soal::create([
        'materi_id'        => $validated['materi_id'],
        'kelas'            => $validated['kelas'],
        'tipe_template'      => $templateType,
        'bobot_kesulitan'  => $validated['bobot_kesulitan'],
        'teks_soal' => $validated['teks_soal'],
        'data_soal'      => $questionData,
        'aset_diperlukan'    => $assetsRequired,
        'kunci_jawaban' => $correctKey,
        'opsi_json'       => $optionsJson,
        'aktif'          => true,
    ]);

    if (!empty($assetsRequired)) {
        app(\App\Services\ManifestGeneratorService::class)->invalidateCache();
    }

    $templateLabels = [
        'multiple_choice' => 'Pilihan Ganda',
        'drag_and_drop'   => 'Drag & Drop',
        'matching_game'   => 'Pasangkan',
        'fill_blank'      => 'Isi Kosong',
        'image_quiz'      => 'Kuis Gambar',
    ];

    return back()->with('success', "Soal {$templateLabels[$templateType]} berhasil disimpan ke bank soal!");
}
public function generateExplanationApi(Request $request)
    {
        $request->validate([
            'question' => 'required|string'
        ]);

        try {
            $client = \OpenAI::factory()
                ->withApiKey(env('OPENAI_API_KEY', 'sk-o44DmAnE8ceq5OWSqECLINUi1ugCeTbWAdGYsPyh1QjBmXou'))
                ->withBaseUri('https://api.chatanywhere.tech/v1')
                ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 15]))
                ->make();

            $prompt = "Sebagai guru ahli, berikan penjelasan singkat dan mudah dipahami siswa mengapa pertanyaan berikut penting atau apa konsep utamanya. Maksimal 3 kalimat. Pertanyaan: '{$request->question}'";

            $response = $client->chat()->create([
                'model'           => 'gpt-4o-mini',
                'messages'        => [
                    ['role' => 'system', 'content' => 'Anda adalah guru SD/SMP yang ramah.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return response()->json([
                'status' => 'success',
                'explanation' => trim($response->choices[0]->message->content)
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}



