<?php
// app/Http/Controllers/Api/SyncController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Material;
use App\Models\Question;
use App\Models\StudentProgress;
use App\Models\AssetLibrary;
use App\Models\User;

class SyncController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════
    // SECTION 1: MATERI & SOAL
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /api/sync/materials
     * Flutter mengambil daftar materi yang tersedia untuk siswa.
     * Filter berdasarkan region siswa (jika ada).
     */
   public function getMaterials(Request $request)
    {
        try {
            $user = $request->user();
            
            // Gunakan withTrashed() agar Flutter tahu materi mana yang dihapus (Soft Delete)
            $query = Material::withTrashed();

            // 1. FILTER REGIONAL & BAHASA (Berdasarkan skema asli DB Anda)
            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('school_origin', $user->school_origin)
                      ->orWhereNull('school_origin')
                      ->orWhere('school_origin', '')
                      ->orWhere('language_code', $user->language_code)
                      ->orWhere('language_code', 'global');
                });
            }

            // 2. FILTER DELTA SYNC (Cegah unduh ulang data yang sama)
            if ($request->filled('last_sync')) {
                $query->where('updated_at', '>', $request->last_sync);
            }

            $materials = $query->orderBy('updated_at', 'asc')->get();

            $data = $materials->map(function ($material) {
                return [
                    'id'               => $material->id,
                    'title_indo'       => $material->title_indo,
                    'category'         => $material->category,
                    'image_url'        => $material->image_url,
                    'level_difficulty' => $material->level_difficulty,
                    'language_code'    => $material->language_code,
                    'content_indo'     => $material->content_indo,
                    'ai_embeddings'    => $material->ai_embeddings,
                    'ai_status'        => $material->ai_status,
                    'updated_at'       => $material->updated_at?->toISOString(),
                    // Flag status penting agar Flutter SQLite bisa melakukan Delete lokal
                    'status'           => $material->trashed() ? 'deleted' : 'active',
                ];
            });

            return response()->json([
                'status'      => 'success',
                'data'        => $data,
                'server_time' => now()->toISOString(), // WAJIB ada untuk memori HP
            ]);

        } catch (\Exception $e) {
            Log::error('[SyncController@getMaterials] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data materi.',
            ], 500);
        }
    }
    /**
     * GET /api/sync/questions?material_id=X&template_type=Y&limit=Z
     * Flutter mengambil soal kuis beserta metadata aset yang dibutuhkan.
     *
     * Response menyertakan `assets_required` → daftar filename yang harus
     * diunduh Flutter sebelum kuis dimulai (Atomic Sync pattern).
     */
    public function getQuestions(Request $request)
    {
        try {
            $user = $request->user();
            
            // Gunakan withTrashed() untuk sinkronisasi penghapusan soal
            $query = Question::withTrashed();

            // 1. FILTER REGIONAL (Hanya unduh soal dari materi sekolah/bahasa siswa)
            if ($user) {
                $query->whereHas('material', function ($q) use ($user) {
                    $q->where('school_origin', $user->school_origin)
                      ->orWhereNull('school_origin')
                      ->orWhere('school_origin', '')
                      ->orWhere('language_code', $user->language_code)
                      ->orWhere('language_code', 'global');
                });
            }

            // 2. FILTER DELTA SYNC
            if ($request->filled('last_sync')) {
                $query->where('updated_at', '>', $request->last_sync);
            }

            $questions = $query->orderBy('updated_at', 'asc')->get();

            $data = $questions->map(function ($question) {
                return [
                    'id'                   => $question->id,
                    'material_id'          => $question->material_id,
                    'question_text_indo'   => $question->question_text_indo,
                    'question_text_tolaki' => $question->question_text_tolaki,
                    'options_json'         => $question->options_json,
                    'correct_answer_key'   => $question->correct_answer_key,
                    'difficulty_weight'    => $question->difficulty_weight,
                    'template_type'        => $question->template_type,
                    'question_data'        => $question->question_data,
                    'assets_required'      => $question->assets_required,
                    'updated_at'           => $question->updated_at?->toISOString(),
                    'status'               => $question->trashed() ? 'deleted' : 'active',
                ];
            });

            return response()->json([
                'status'      => 'success',
                'data'        => $data,
                'server_time' => now()->toISOString(), // WAJIB ada untuk memori HP
            ]);

        } catch (\Exception $e) {
            Log::error('[SyncController@getQuestions] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data soal.',
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 2: ASSET SYNC
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/sync/assets
     * Body: { "filenames": ["abc123.png", "def456.mp3", ...] }
     *
     * Flutter mengirim daftar filename yang dibutuhkan.
     * Server membalas dengan URL download + metadata.
     * Ini adalah "pre-flight check" sebelum kuis dimulai.
     *
     * Pattern: Flutter hanya download file yang BELUM ada di local storage.
     */
    public function getAssets(Request $request)
    {
        try {
            $request->validate([
                'filenames'   => 'required|array|min:1|max:100',
                'filenames.*' => 'required|string|max:255',
            ]);

            $filenames     = $request->input('filenames');
            $assetManifest = $this->resolveAssetUrls($filenames);

            // Pisahkan aset yang ditemukan vs tidak ditemukan
            $found    = collect($assetManifest)->where('status', 'available')->values();
            $notFound = collect($assetManifest)->where('status', 'missing')->values();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'assets'    => $assetManifest,
                    'found'     => $found->count(),
                    'not_found' => $notFound->count(),
                    // Jika ada yang missing, Flutter harus handle gracefully
                    'has_missing' => $notFound->isNotEmpty(),
                ],
                'meta' => [
                    'requested'  => count($filenames),
                    'synced_at'  => now()->toISOString(),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter tidak valid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[SyncController@getAssets] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data aset.',
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 3: PROGRESS SYNC
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/sync/progress
     * Body: { "question_id": 1, "answer_data": {...}, "time_spent": 30 }
     *
     * Flutter mengirim jawaban siswa → server validasi → simpan progress.
     * Mendukung offline-first: Flutter bisa batch kirim banyak jawaban sekaligus.
     */
    public function syncProgress(Request $request)
    {
        try {
            $request->validate([
                // Support single answer
                'question_id'  => 'nullable|integer|exists:questions,id',
                'answer_data'  => 'nullable|array',
                'time_spent'   => 'nullable|integer|min:0',

                // Support batch answers
                'answers'      => 'nullable|array|min:1|max:50',
                'answers.*.question_id' => 'required_with:answers|integer|exists:questions,id',
                'answers.*.answer_data' => 'required_with:answers|array',
                'answers.*.time_spent'  => 'nullable|integer|min:0',
            ]);

            $user    = $request->user();
            $results = [];

            // ── Mode Batch ─────────────────────────────────────────────
            if ($request->filled('answers')) {
                foreach ($request->input('answers') as $answerItem) {
                    $result    = $this->processAnswer(
                        $user,
                        $answerItem['question_id'],
                        $answerItem['answer_data'],
                        $answerItem['time_spent'] ?? 0
                    );
                    $results[] = $result;
                }
            }
            // ── Mode Single ────────────────────────────────────────────
            elseif ($request->filled('question_id')) {
                $results[] = $this->processAnswer(
                    $user,
                    $request->input('question_id'),
                    $request->input('answer_data', []),
                    $request->input('time_spent', 0)
                );
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Harus menyertakan question_id atau answers.',
                ], 422);
            }

            // Hitung summary
            $totalCorrect = collect($results)->where('is_correct', true)->count();
            $totalPoints  = collect($results)->sum('points_earned');

            return response()->json([
                'status'  => 'success',
                'message' => 'Progress berhasil disimpan.',
                'data'    => [
                    'results'       => $results,
                    'summary'       => [
                        'total_answered' => count($results),
                        'total_correct'  => $totalCorrect,
                        'total_points'   => $totalPoints,
                        'accuracy'       => count($results) > 0
                            ? round(($totalCorrect / count($results)) * 100, 1)
                            : 0,
                    ],
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak valid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[SyncController@syncProgress] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan progress.',
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 4: QUIZ STATE
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /api/quiz/state?material_id=X
     * Mengambil state kuis siswa: soal mana yang sudah dijawab,
     * berapa poin yang sudah dikumpulkan, dll.
     * Berguna untuk RESUME kuis yang terputus (offline → online).
     */
    public function getQuizState(Request $request)
    {
        try {
            $request->validate([
                'material_id' => 'nullable|integer|exists:materials,id',
            ]);

            $user  = $request->user();
            $query = StudentProgress::where('user_id', $user->id)
                ->with('question:id,template_type,material_id,points');

            if ($request->filled('material_id')) {
                $query->whereHas('question', function ($q) use ($request) {
                    $q->where('material_id', $request->material_id);
                });
            }

            $progresses = $query->get();

            // Kelompokkan per material
            $byMaterial = $progresses->groupBy(function ($p) {
                return $p->question?->material_id;
            });

            $stateData = $byMaterial->map(function ($items, $materialId) {
                $totalPoints  = $items->sum('points_earned');
                $totalCorrect = $items->where('is_correct', true)->count();

                return [
                    'material_id'      => $materialId,
                    'answered_count'   => $items->count(),
                    'correct_count'    => $totalCorrect,
                    'total_points'     => $totalPoints,
                    'answered_ids'     => $items->pluck('question_id')->toArray(),
                    'last_answered_at' => $items->max('created_at'),
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'user_id'    => $user->id,
                    'by_material'=> $stateData,
                    'grand_total_points' => $progresses->sum('points_earned'),
                ],
                'meta' => [
                    'synced_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('[SyncController@getQuizState] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil state kuis.',
                            ], 500);
        }
    }

    /**
     * POST /api/quiz/complete
     * Body: { "material_id": 1, "total_time_spent": 300 }
     *
     * Dipanggil Flutter ketika siswa menyelesaikan SEMUA soal dalam satu sesi.
     * Server menghitung final score dan memberikan feedback.
     */
    public function completeQuiz(Request $request)
    {
        try {
            $request->validate([
                'material_id'      => 'required|integer|exists:materials,id',
                'total_time_spent' => 'nullable|integer|min:0',
            ]);

            $user       = $request->user();
            $materialId = $request->input('material_id');

            // Ambil semua progress siswa untuk material ini
            $progresses = StudentProgress::where('user_id', $user->id)
                ->whereHas('question', function ($q) use ($materialId) {
                    $q->where('material_id', $materialId);
                })
                ->with('question:id,points,template_type')
                ->get();

            // Hitung total soal yang tersedia
            $totalQuestions = Question::where('material_id', $materialId)
                ->where('is_active', true)
                ->count();

            $answeredCount  = $progresses->count();
            $correctCount   = $progresses->where('is_correct', true)->count();
            $totalPoints    = $progresses->sum('points_earned');
            $maxPoints      = Question::where('material_id', $materialId)
                ->where('is_active', true)
                ->sum('points');

            // Hitung grade
            $percentage = $maxPoints > 0
                ? round(($totalPoints / $maxPoints) * 100, 1)
                : 0;

            $grade = match(true) {
                $percentage >= 90 => 'A',
                $percentage >= 80 => 'B',
                $percentage >= 70 => 'C',
                $percentage >= 60 => 'D',
                default           => 'E',
            };

            $feedback = match($grade) {
                'A' => 'Luar biasa! Kamu menguasai materi ini dengan sangat baik! 🌟',
                'B' => 'Bagus sekali! Terus pertahankan semangat belajarmu! 👍',
                'C' => 'Cukup baik! Masih ada ruang untuk berkembang. 💪',
                'D' => 'Jangan menyerah! Coba pelajari lagi materinya. 📚',
                'E' => 'Ayo semangat! Setiap ahli pernah menjadi pemula. 🌱',
            };

            return response()->json([
                'status'  => 'success',
                'message' => 'Kuis selesai!',
                'data'    => [
                    'material_id'      => $materialId,
                    'summary'          => [
                        'total_questions'  => $totalQuestions,
                        'answered_count'   => $answeredCount,
                        'correct_count'    => $correctCount,
                        'wrong_count'      => $answeredCount - $correctCount,
                        'total_points'     => $totalPoints,
                        'max_points'       => $maxPoints,
                        'percentage'       => $percentage,
                        'grade'            => $grade,
                        'feedback'         => $feedback,
                        'total_time_spent' => $request->input('total_time_spent', 0),
                    ],
                    'breakdown_by_template' => $progresses
                        ->groupBy(fn($p) => $p->question?->template_type)
                        ->map(function ($items, $type) {
                            return [
                                'template_type' => $type,
                                'answered'      => $items->count(),
                                'correct'       => $items->where('is_correct', true)->count(),
                                'points'        => $items->sum('points_earned'),
                            ];
                        })->values(),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak valid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[SyncController@completeQuiz] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyelesaikan kuis.',
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 5: REGION CHECK
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/check-region
     * Body: { "region_name": "Jawa Barat" }
     *
     * Dipanggil saat registrasi Flutter untuk validasi region.
     */
    public function checkRegion(Request $request)
    {
        try {
            $request->validate([
                'region_name' => 'required|string|max:255',
            ]);

            $region = \App\Models\Region::where('name', 'like', '%' . $request->region_name . '%')
                ->first();

            if (!$region) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Region tidak ditemukan.',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Region ditemukan.',
                'data'    => [
                    'id'   => $region->id,
                    'name' => $region->name,
                    'code' => $region->code ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('[SyncController@checkRegion] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memeriksa region.',
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 6: PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Memproses satu jawaban siswa:
     * 1. Load question
     * 2. Validasi jawaban berdasarkan template_type
     * 3. Simpan/update StudentProgress
     * 4. Return result
     */
    private function processAnswer(
        User $user,
        int $questionId,
        array $answerData,
        int $timeSpent = 0
    ): array {
        $question = Question::findOrFail($questionId);

        // Parse question_data jika masih string
        $questionData = $question->question_data;
        if (is_string($questionData)) {
            $questionData = json_decode($questionData, true) ?? [];
        }

        // Validasi jawaban berdasarkan template
        $isCorrect    = $this->validateAnswer($question->template_type, $questionData, $answerData);
        $pointsEarned = $isCorrect ? ($question->points ?? 10) : 0;

        // Upsert progress (satu siswa, satu soal, satu record)
        $progress = StudentProgress::updateOrCreate(
            [
                'user_id'     => $user->id,
                'question_id' => $questionId,
            ],
            [
                'answer_data'  => json_encode($answerData),
                'is_correct'   => $isCorrect,
                'points_earned'=> $pointsEarned,
                'time_spent_seconds' => $timeSpent,
                'answered_at'  => now(),
            ]
        );

        return [
            'question_id'   => $questionId,
            'template_type' => $question->template_type,
            'is_correct'    => $isCorrect,
            'points_earned' => $pointsEarned,
            'correct_answer'=> $this->getCorrectAnswerHint($question->template_type, $questionData),
            'progress_id'   => $progress->id,
        ];
    }

    /**
     * Validasi jawaban per template type.
     * Setiap template punya struktur answer_data yang berbeda.
     *
     * ┌─────────────────┬──────────────────────────────────────────────┐
     * │ Template        │ answer_data structure                        │
     * ├─────────────────┼──────────────────────────────────────────────┤
     * │ multiple_choice │ { "selected": "A" }                          │
     * │ drag_and_drop   │ { "order": ["item1","item2","item3"] }       │
     * │ matching_game   │ { "pairs": [{"left":"A","right":"1"}, ...] } │
     * │ fill_blank      │ { "answers": ["kata1", "kata2"] }            │
     * │ image_quiz      │ { "selected": "B" }                          │
     * └─────────────────┴──────────────────────────────────────────────┘
     */
    private function validateAnswer(
        string $templateType,
        array $questionData,
        array $answerData
    ): bool {
        return match($templateType) {

            // ── Multiple Choice ────────────────────────────────────────
            // question_data.correct_answer = "A" | "B" | "C" | "D"
            'multiple_choice' => isset($answerData['selected'])
                                && strtoupper(trim($answerData['selected']))
                                === strtoupper(trim(
                                    $questionData['correct_answer_key']
                                    ?? $questionData['correct_answer']
                                    ?? ''
                                )),

            // ── Image Quiz ─────────────────────────────────────────────
            // Sama seperti multiple choice, hanya pilihan berupa gambar
            'image_quiz' => isset($answerData['selected'])
                                && strtoupper(trim($answerData['selected']))
                                === strtoupper(trim(
                                    $questionData['correct_answer_key']
                                    ?? $questionData['correct_answer']
                                    ?? ''
                                )),

            // ── Drag and Drop ──────────────────────────────────────────
            // question_data.correct_order = ["item1", "item2", "item3"]
            // answer_data.order = ["item1", "item2", "item3"]
            'drag_and_drop' => isset($answerData['order'], $questionData['correct_order'])
                && is_array($answerData['order'])
                && is_array($questionData['correct_order'])
                && array_values($answerData['order'])
                === array_values($questionData['correct_order']),

            // ── Matching Game ──────────────────────────────────────────
            // question_data.correct_pairs = [{"left":"A","right":"1"}, ...]
            // answer_data.pairs = [{"left":"A","right":"1"}, ...]
            'matching_game' => $this->validateMatchingGame(
                $questionData['correct_pairs'] ?? [],
                $answerData['pairs'] ?? []
            ),

            // ── Fill in the Blank ──────────────────────────────────────
            // question_data.correct_answers = ["kata1", "kata2"]
            // answer_data.answers = ["kata1", "kata2"]
            // Case-insensitive, trim whitespace
            'fill_blank' => $this->validateFillBlank(
                $questionData['correct_answers'] ?? [],
                $answerData['answers'] ?? []
            ),

            // Default: anggap salah jika template tidak dikenal
            default => false,
        };
    }

    /**
     * Validasi Matching Game:
     * Setiap pasangan left-right harus cocok, urutan tidak penting.
     */
    private function validateMatchingGame(array $correctPairs, array $userPairs): bool
    {
        if (empty($correctPairs) || empty($userPairs)) {
            return false;
        }

        if (count($correctPairs) !== count($userPairs)) {
            return false;
        }

        // Buat lookup map dari correct pairs: left => right
        $correctMap = [];
        foreach ($correctPairs as $pair) {
            if (!isset($pair['left'], $pair['right'])) {
                return false;
            }
            $correctMap[strtolower(trim($pair['left']))] = strtolower(trim($pair['right']));
        }

        // Validasi setiap pasangan user
        foreach ($userPairs as $pair) {
            if (!isset($pair['left'], $pair['right'])) {
                return false;
            }

            $left  = strtolower(trim($pair['left']));
            $right = strtolower(trim($pair['right']));

            // Jika left tidak ada di correct map, atau right tidak cocok
            if (!isset($correctMap[$left]) || $correctMap[$left] !== $right) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validasi Fill in the Blank:
     * Case-insensitive, trim whitespace, urutan harus sama.
     * Mendukung multiple blanks dalam satu soal.
     */
    private function validateFillBlank(array $correctAnswers, array $userAnswers): bool
    {
        if (empty($correctAnswers) || empty($userAnswers)) {
            return false;
        }

        if (count($correctAnswers) !== count($userAnswers)) {
            return false;
        }

        foreach ($correctAnswers as $index => $correct) {
            $userAnswer = $userAnswers[$index] ?? '';

            // Normalisasi: lowercase + trim
            $normalizedCorrect = strtolower(trim((string) $correct));
            $normalizedUser    = strtolower(trim((string) $userAnswer));

            if ($normalizedCorrect !== $normalizedUser) {
                return false;
            }
        }

        return true;
    }

    /**
     * Memberikan hint jawaban benar untuk ditampilkan di Flutter
     * setelah siswa menjawab (baik benar maupun salah).
     * TIDAK mengirim jawaban lengkap sebelum siswa menjawab.
     */
    private function getCorrectAnswerHint(string $templateType, array $questionData): mixed
    {
        return match($templateType) {
            'multiple_choice', 'image_quiz'
                => $questionData['correct_answer'] ?? null,

            'drag_and_drop'
                => $questionData['correct_order'] ?? [],

            'matching_game'
                => $questionData['correct_pairs'] ?? [],

            'fill_blank'
                => $questionData['correct_answers'] ?? [],

            default => null,
        };
    }

    /**
     * Resolve daftar filename menjadi URL download + metadata.
     * Digunakan oleh getQuestions() dan getAssets().
     *
     * Return format per item:
     * {
     *   "filename": "abc123def456.png",
     *   "url": "https://server.com/assets/serve/abc123def456.png",
     *   "mime_type": "image/png",
     *   "file_size": 12345,
     *   "status": "available" | "missing"
     * }
     */
        private function resolveAssetUrls(array $filenames): array
    {
        if (empty($filenames)) {
            return [];
        }

        // Ambil semua aset yang cocok dari database dalam 1 query
        // Lebih efisien daripada query per file
        $assets = AssetLibrary::whereIn('filename', $filenames)
            ->where('is_active', true)
            ->get()
            ->keyBy('filename'); // index by filename untuk O(1) lookup

        $result = [];

        foreach ($filenames as $filename) {
            $asset = $assets->get($filename);

            if ($asset) {
                // Cek apakah file fisik benar-benar ada di disk
                // Double-check: database bisa saja tidak sinkron dengan disk
                $fileExists = Storage::disk('public')->exists('quiz-assets/' . $filename);

                if ($fileExists) {
                    $result[] = [
                        'filename'   => $filename,
                        'url'        => route('assets.serve', ['hash' => $filename]),
                        'mime_type'  => $asset->mime_type ?? $this->guessMimeType($filename),
                        'file_size'  => $asset->file_size ?? 0,
                        'asset_type' => $asset->asset_type ?? 'unknown',
                        'tags'       => $asset->tags ?? [],
                        'status'     => 'available',
                        // Cache hint untuk Flutter: file tidak akan berubah
                        // karena nama = hash konten (CAS principle)
                        'cache_hint' => 'immutable',
                    ];
                } else {
                    // File ada di DB tapi tidak ada di disk
                    // Tandai sebagai missing dan log untuk investigasi
                    Log::warning('[SyncController@resolveAssetUrls] File di DB tapi tidak ada di disk', [
                        'filename' => $filename,
                        'asset_id' => $asset->id,
                    ]);

                    $result[] = [
                        'filename'   => $filename,
                        'url'        => null,
                        'mime_type'  => null,
                        'file_size'  => 0,
                        'asset_type' => $asset->asset_type ?? 'unknown',
                        'tags'       => [],
                        'status'     => 'missing',
                        'cache_hint' => null,
                    ];
                }
            } else {
                // File tidak ada di database sama sekali
                $result[] = [
                    'filename'   => $filename,
                    'url'        => null,
                    'mime_type'  => null,
                    'file_size'  => 0,
                    'asset_type' => 'unknown',
                    'tags'       => [],
                    'status'     => 'missing',
                    'cache_hint' => null,
                ];
            }
        }

        return $result;
    }

    /**
     * Tebak MIME type dari ekstensi file.
     * Fallback jika mime_type tidak tersimpan di database.
     */
    private function guessMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match($extension) {
            // Images
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',

            // Audio
            'mp3'         => 'audio/mpeg',
            'ogg'         => 'audio/ogg',
            'wav'         => 'audio/wav',
            'flac'        => 'audio/flac',

            // Video
            'mp4'         => 'video/mp4',
            'webm'        => 'video/webm',

            // Animation
            'json'        => 'application/json', // Lottie
            'lottie'      => 'application/json',

            // Default
            default       => 'application/octet-stream',
        };
    }
}