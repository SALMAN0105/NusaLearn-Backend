<?php
// app/Http/Controllers/Admin/QuestionController.php
// Update: tambah auto_fetch_log di response generate

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Material;
use App\Models\AssetLibrary;
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

    public function index(Request $request)
    {
        $materials = Material::select('id', 'title_indo')->get();
        $query     = Question::with('material');

        if ($request->filled('material_id') && $request->material_id !== 'all') {
            $query->where('material_id', $request->material_id);
        }

        if ($request->filled('search')) {
            $query->where('question_text_indo', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('template_type')) {
            $query->where('template_type', $request->template_type);
        }

        $questions = $query->latest()->paginate(10)->withQueryString();

        return view('admin.questions', compact('questions', 'materials'));
    }

    // =========================================================================
    // GENERATE — Trigger AI (AJAX)
    // =========================================================================

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'material_id'   => 'required|exists:materials,id',
            'template_type' => 'required|in:' . implode(',', array_keys(Question::ALL_TEMPLATES)),
            'difficulty'    => 'required|integer|min:1|max:3',
        ]);

        $result = $this->quizGenerator->generate(
            materialId:   (int) $validated['material_id'],
            templateType: $validated['template_type'],
            difficulty:   (int) $validated['difficulty'],
        );

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
            'question_data'    => $result['question_data'],
            'assets_required'  => $result['assets_required'],
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
        $templateType = $request->input('template_type', Question::TEMPLATE_MULTIPLE_CHOICE);

        if ($templateType === Question::TEMPLATE_MULTIPLE_CHOICE
            && $request->filled('option_a')
        ) {
            return $this->storeManualMultipleChoice($request);
        }

        return $this->storeMultimediaQuestion($request);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function update(Request $request, Question $question)
    {
        // Untuk sementara, kita gunakan storeManual logic atau similar
        // Namun karena ini update, kita perlu menyesuaikan
        
        // Kita bisa refactor logic storeManual ke private method agar bisa dipakai bersama
        return $this->updateManual($request, $question);
    }

    private function updateManual(Request $request, Question $question)
    {
        $validated = $request->validate([
            'material_id'        => 'required|integer|exists:materials,id',
            'template_type'      => 'required|in:multiple_choice,drag_and_drop,matching_game,fill_blank,image_quiz',
            'difficulty_weight'  => 'required|integer|min:1|max:5',
            'question_text_indo' => 'required|string|max:2000',
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

        $templateType   = $validated['template_type'];
        $questionData   = $question->question_data ?? [];
        $assetsRequired = $question->assets_required ?? [];
        $correctKey     = $question->correct_answer_key;
        $optionsJson    = $question->options_json;

        // Logic build question_data (Mirip storeManual tapi update field yang ada)
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
                    'question_text_indo' => $validated['question_text_indo'],
                    'template_type'      => 'multiple_choice',
                    'options'            => $optionsJson,
                    'correct_answer_key' => $correctKey,
                    'explanation'        => $validated['explanation'] ?? null,
                    'assets_required'    => [],
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
                    $filename = $hash . '.' . $ext;
                    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $filename)) {
                        $file->storeAs('quiz-assets', $filename, 'public');
                    }
                    \App\Models\AssetLibrary::firstOrCreate(
                        ['filename' => $filename],
                        ['original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'is_active' => true]
                    );
                    
                    if (!in_array($filename, $assetsRequired)) {
                        $assetsRequired[] = $filename;
                    }
                    
                    // Update item di index tersebut atau tambah jika baru
                    $items[$i] = [
                        'id'          => 'item_' . ($i + 1),
                        'image_asset' => $filename,
                        'correct_zone'=> null, // Reset dulu, akan di-set di bawah
                    ];
                }
                
                // Re-set correct zone berdasarkan index yang dipilih
                foreach($items as $i => &$item) {
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
                    'question_text_indo' => $validated['question_text_indo'],
                    'template_type'      => 'drag_and_drop',
                    'items'              => $items,
                    'zones'              => $zones,
                    'correct_mapping'    => $correctMapping,
                    'explanation'        => $validated['explanation'] ?? null,
                    'assets_required'    => $assetsRequired,
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
                    'question_text_indo' => $validated['question_text_indo'],
                    'template_type'      => 'matching_game',
                    'pairs'              => $pairs,
                    'correct_pairs'      => $correctPairs,
                    'explanation'        => $validated['explanation'] ?? null,
                    'assets_required'    => [],
                ];
                break;

            case 'fill_blank':
                $sentence       = $request->input('fill_sentence', $validated['question_text_indo']);
                $correctAnswers = array_values(array_filter($request->input('fill_correct_answers', []), 'strlen'));
                $wordBankRaw    = json_decode($request->input('word_bank', '[]'), true) ?? [];
                $wordBank       = array_values(array_filter($wordBankRaw, 'strlen'));
                
                $blanks = [];
                foreach ($correctAnswers as $i => $ans) {
                    $blanks[] = ['id' => 'blank_' . ($i + 1), 'correct_answer' => $ans, 'hint' => null];
                    if (!in_array($ans, $wordBank)) $wordBank[] = $ans;
                }
                shuffle($wordBank);
                $questionData = [
                    'question_text_indo' => $sentence,
                    'template_type'      => 'fill_blank',
                    'blanks'             => $blanks,
                    'word_bank'          => $wordBank,
                    'correct_answers'    => $correctAnswers,
                    'explanation'        => $validated['explanation'] ?? null,
                    'assets_required'    => [],
                ];
                break;

            case 'image_quiz':
                $mainImageFilename = $questionData['main_image'] ?? null;
                if ($request->hasFile('main_image') && $request->file('main_image')->isValid()) {
                    $file     = $request->file('main_image');
                    $hash     = md5_file($file->getRealPath());
                    $ext      = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = $hash . '.' . $ext;
                    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $filename)) {
                        $file->storeAs('quiz-assets', $filename, 'public');
                    }
                    \App\Models\AssetLibrary::firstOrCreate(
                        ['filename' => $filename],
                        ['original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'is_active' => true]
                    );
                    $mainImageFilename = $filename;
                    if (!in_array($filename, $assetsRequired)) {
                        $assetsRequired[] = $filename;
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
                    'question_text_indo' => $validated['question_text_indo'],
                    'template_type'      => 'image_quiz',
                    'main_image'         => $mainImageFilename,
                    'options'            => $optionsJson,
                    'correct_answer_key' => $correctKey,
                    'explanation'        => $validated['explanation'] ?? null,
                    'assets_required'    => $assetsRequired,
                ];
                break;
        }

        $question->update([
            'material_id'        => $validated['material_id'],
            'template_type'      => $templateType,
            'difficulty_weight'  => $validated['difficulty_weight'],
            'question_text_indo' => $validated['question_text_indo'],
            'question_data'      => $questionData,
            'assets_required'    => $assetsRequired,
            'correct_answer_key' => $correctKey,
            'options_json'       => $optionsJson,
        ]);

        return back()->with('success', "Soal berhasil diperbarui!");
    }

    // =========================================================================
    // PRIVATE — Store Helpers
    // =========================================================================

    private function storeManualMultipleChoice(Request $request)
    {
        $request->validate([
            'material_id'        => 'required|exists:materials,id',
            'question_text_indo' => 'required|string|max:1000',
            'difficulty_weight'  => 'required|integer|min:1|max:3',
            'option_a'           => 'required|string|max:500',
            'option_b'           => 'required|string|max:500',
            'option_c'           => 'required|string|max:500',
            'option_d'           => 'required|string|max:500',
            'correct_answer_key' => 'required|in:a,b,c,d',
        ]);

        $options = [
            ['id' => 'a', 'text' => $request->option_a],
            ['id' => 'b', 'text' => $request->option_b],
            ['id' => 'c', 'text' => $request->option_c],
            ['id' => 'd', 'text' => $request->option_d],
        ];

        Question::create([
            'material_id'        => $request->material_id,
            'question_text_indo' => $request->question_text_indo,
            'options_json'       => $options,
            'correct_answer_key' => $request->correct_answer_key,
            'difficulty_weight'  => $request->difficulty_weight,
            'template_type'      => Question::TEMPLATE_MULTIPLE_CHOICE,
            'question_data'      => null,
            'assets_required'    => [],
        ]);

        return back()->with('success', 'Soal Pilihan Ganda berhasil ditambahkan.');
    }

    private function storeMultimediaQuestion(Request $request)
    {
        $request->validate([
            'material_id'        => 'required|exists:materials,id',
            'difficulty_weight'  => 'required|integer|min:1|max:3',
            'template_type'      => 'required|in:' . implode(',', array_keys(Question::ALL_TEMPLATES)),
            'question_data_json' => 'required|string',
        ]);

        $questionData = json_decode($request->question_data_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors([
                'question_data_json' => 'Data soal tidak valid (JSON rusak): ' . json_last_error_msg(),
            ]);
        }

        // Fix #11: Fallback question_text_indo dari question_data
        $questionTextIndo = trim($request->input('question_text_indo', ''));
        if (empty($questionTextIndo)) {
            $questionTextIndo = $questionData['question_text_indo'] ?? '';
        }

        if (empty($questionTextIndo)) {
            return back()->withErrors([
                'question_text_indo' => 'Teks pertanyaan tidak boleh kosong.',
            ]);
        }

        $assetsRequired = $questionData['assets_required'] ?? [];
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
            $existing = AssetLibrary::whereIn('filename', $assetsRequired)
                ->where('is_active', true)
                ->pluck('filename')
                ->toArray();

            foreach ($assetsRequired as $filename) {
                if (!in_array($filename, $existing)) {
                    $missingAssets[] = $filename;
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

        if ($request->template_type === Question::TEMPLATE_MULTIPLE_CHOICE) {
            $correctAnswerKey = $questionData['correct_answer_key'] ?? null;
            $optionsJson      = $questionData['options'] ?? null;
        }

        Question::create([
            'material_id'        => $request->material_id,
            'question_text_indo' => $questionTextIndo,
            'difficulty_weight'  => $request->difficulty_weight,
            'template_type'      => $request->template_type,
            'assets_required'    => $assetsRequired,
            'options_json'       => $optionsJson,
            'correct_answer_key' => $correctAnswerKey,
        ]);

        $templateLabel = Question::ALL_TEMPLATES[$request->template_type] ?? $request->template_type;

        Log::info("[QuestionController] Soal berhasil disimpan", [
            'template'    => $request->template_type,
            'material_id' => $request->material_id,
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
        'material_id'        => 'required|integer|exists:materials,id',
        'template_type'      => 'required|in:multiple_choice,drag_and_drop,matching_game,fill_blank,image_quiz',
        'difficulty_weight'  => 'required|integer|min:1|max:5',
        'question_text_indo' => 'required|string|max:2000',
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

    $templateType   = $validated['template_type'];
    $questionData   = [];
    $assetsRequired = [];
    $correctKey     = null;
    $optionsJson    = null;
    $errorResponse  = null; // ← Tampung error sebelum transaksi

    // ── BUILD question_data di LUAR transaksi ──────────────────────────
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
                'question_text_indo' => $validated['question_text_indo'],
                'template_type'      => 'multiple_choice',
                'options'            => $optionsJson,
                'correct_answer_key' => $correctKey,
                'explanation'        => $validated['explanation'] ?? null,
                'assets_required'    => [],
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
                $filename = $hash . '.' . $ext;
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $filename)) {
                    $file->storeAs('quiz-assets', $filename, 'public');
                }
                \App\Models\AssetLibrary::firstOrCreate(
                    ['filename' => $filename],
                    [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType() ?? 'image/jpeg',
                        'size_kb'       => round($file->getSize() / 1024),
                        'source_api'    => 'manual_upload',
                        'tags'          => ['drag_and_drop', 'manual'],
                        'is_active'     => true,
                    ]
                );
                $assetsRequired[] = $filename;
                $items[] = [
                    'id'          => 'item_' . ($i + 1),
                    'image_asset' => $filename,
                    'correct_zone'=> $i === $correctIdx ? 'zone_1' : null,
                ];
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
                'question_text_indo' => $validated['question_text_indo'],
                'template_type'      => 'drag_and_drop',
                'items'              => $items,
                'zones'              => $zones,
                'correct_mapping'    => $correctMapping,
                'explanation'        => $validated['explanation'] ?? null,
                'assets_required'    => $assetsRequired,
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
                'question_text_indo' => $validated['question_text_indo'],
                'template_type'      => 'matching_game',
                'pairs'              => $pairs,
                'correct_pairs'      => $correctPairs,
                'explanation'        => $validated['explanation'] ?? null,
                'assets_required'    => [],
            ];
            break;

        case 'fill_blank':
            $sentence       = $request->input('fill_sentence', $validated['question_text_indo']);
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
                'question_text_indo' => $sentence,
                'template_type'      => 'fill_blank',
                'blanks'             => $blanks,
                'word_bank'          => $wordBank,
                'correct_answers'    => $correctAnswers,
                'explanation'        => $validated['explanation'] ?? null,
                'assets_required'    => [],
            ];
            break;

        case 'image_quiz':
            $mainImageFilename = null;
            if ($request->hasFile('main_image') && $request->file('main_image')->isValid()) {
                $file     = $request->file('main_image');
                $hash     = md5_file($file->getRealPath());
                $ext      = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = $hash . '.' . $ext;
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('quiz-assets/' . $filename)) {
                    $file->storeAs('quiz-assets', $filename, 'public');
                }
                \App\Models\AssetLibrary::firstOrCreate(
                    ['filename' => $filename],
                    [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType() ?? 'image/jpeg',
                        'size_kb'       => round($file->getSize() / 1024),
                        'source_api'    => 'manual_upload',
                        'tags'          => ['image_quiz', 'manual'],
                        'is_active'     => true,
                    ]
                );
                $mainImageFilename = $filename;
                $assetsRequired[]  = $filename;
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
                'question_text_indo' => $validated['question_text_indo'],
                'template_type'      => 'image_quiz',
                'main_image'         => $mainImageFilename,
                'options'            => $optionsJson,
                'correct_answer_key' => $correctKey,
                'explanation'        => $validated['explanation'] ?? null,
                'assets_required'    => $assetsRequired,
            ];
            break;
    }

    // ── SIMPAN KE DATABASE ─────────────────────────────────────────────
    \App\Models\Question::create([
        'material_id'        => $validated['material_id'],
        'template_type'      => $templateType,
        'difficulty_weight'  => $validated['difficulty_weight'],
        'question_text_indo' => $validated['question_text_indo'],
        'question_data'      => $questionData,
        'assets_required'    => $assetsRequired,
        'correct_answer_key' => $correctKey,
        'options_json'       => $optionsJson,
        'is_active'          => true,
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
                ->withApiKey(env('OPENAI_API_KEY'))
                ->withBaseUri(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'))
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