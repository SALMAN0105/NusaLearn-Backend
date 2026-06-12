<?php
// app/Http/Controllers/Api/SyncController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\ProgresSiswa;
use App\Models\PustakaAset;
use App\Models\Pengguna;

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
            $query = Materi::withTrashed();

            // 1. FILTER REGIONAL & BAHASA (Berdasarkan skema asli DB Anda)
            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('asal_sekolah', $user->asal_sekolah)
                      ->orWhereNull('asal_sekolah')
                      ->orWhere('asal_sekolah', '')
                      ->orWhere('kode_bahasa', $user->language_code)
                      ->orWhere('kode_bahasa', 'global');
                });
            }

            // 2. FILTER DELTA SYNC (Cegah unduh ulang data yang sama)
            if ($request->filled('last_sync')) {
                $parsedDate = \Carbon\Carbon::parse($request->last_sync)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                $query->where('diperbarui_pada', '>', $parsedDate);
            }

            $materials = $query->orderBy('diperbarui_pada', 'asc')->get();

            $data = $materials->map(function ($material) {
                return [
                    'id'               => $material->id,
                    'judul'            => $material->judul,
                    'kategori'         => $material->kategori,
                    'url_gambar'       => $material->url_gambar,
                    'tingkat_kesulitan'=> $material->tingkat_kesulitan,
                    'kode_bahasa'      => $material->kode_bahasa,
                    'konten'           => $material->konten,
                    'ai_embeddings'    => $material->ai_embeddings,
                    'status_ai'        => $material->ai_status,
                    'diperbarui_pada'       => $material->diperbarui_pada?->toISOString(),
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
            $query = Soal::withTrashed();

            // 1. FILTER REGIONAL (Hanya unduh soal dari materi sekolah/bahasa siswa)
            if ($user) {
                $query->whereHas('materi', function ($q) use ($user) {
                    $q->where('asal_sekolah', $user->asal_sekolah)
                      ->orWhereNull('asal_sekolah')
                      ->orWhere('asal_sekolah', '')
                      ->orWhere('kode_bahasa', $user->language_code)
                      ->orWhere('kode_bahasa', 'global');
                });
            }

            // 2. FILTER DELTA SYNC
            if ($request->filled('last_sync')) {
                $parsedDate = \Carbon\Carbon::parse($request->last_sync)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                $query->where('diperbarui_pada', '>', $parsedDate);
            }

            $questions = $query->orderBy('diperbarui_pada', 'asc')->get();

            $data = $questions->map(function ($question) {
                return [
                    'id'                   => $question->id,
                    'materi_id'            => $question->materi_id,
                    'teks_soal'            => $question->teks_soal,
                    'question_text_tolaki' => null,
                    'opsi_json'            => $question->opsi_json,
                    'kunci_jawaban'        => $question->kunci_jawaban,
                    'bobot_kesulitan'      => $question->bobot_kesulitan,
                    'tipe_template'        => $question->tipe_template,
                    'data_soal'            => $question->data_soal,
                    'aset_diperlukan'      => $question->aset_diperlukan,
                    'diperbarui_pada'           => $question->diperbarui_pada?->toISOString(),
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
                'soal_id'      => 'nullable|integer|exists:soal,id',
                'data_jawaban' => 'nullable',
                'waktu_detik'  => 'nullable|integer|min:0',

                // Support batch answers
                'answers'      => 'nullable|array|min:1|max:50',
                'answers.*.soal_id'      => 'required_with:answers|integer|exists:soal,id',
                'answers.*.data_jawaban' => 'required_with:answers',
                'answers.*.waktu_detik'  => 'nullable|integer|min:0',
            ]);

            $user    = $request->user();
            $results = [];

            // ── Mode Batch ─────────────────────────────────────────────
            if ($request->filled('answers')) {
                foreach ($request->input('answers') as $answerItem) {
                    $result    = $this->processAnswer(
                        $user,
                        $answerItem['soal_id'],
                        $answerItem['data_jawaban'],
                        $answerItem['waktu_detik'] ?? 0
                    );
                    $results[] = $result;
                }
            }
            // ── Mode Single ────────────────────────────────────────────
            elseif ($request->filled('soal_id')) {
                $results[] = $this->processAnswer(
                    $user,
                    $request->input('soal_id'),
                    $request->input('data_jawaban', []),
                    $request->input('waktu_detik', 0)
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
    // SECTION 4: QUIZ STATE & SYNC DOWN
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /api/sync/progress
     * Sinkronisasi data progress dari server ke klien (Flutter).
     */
    public function syncDownProgress(Request $request)
    {
        try {
            $user = $request->user();
            
            $query = ProgresSiswa::where('pengguna_id', $user->id)
                ->with('soal:id,material_id');

            if ($request->filled('last_sync')) {
                $parsedDate = \Carbon\Carbon::parse($request->last_sync)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                $query->where('diperbarui_pada', '>', $parsedDate);
            }

            $progresses = $query->orderBy('diperbarui_pada', 'asc')->get();

            $data = $progresses->map(function ($progress) {
                return [
                    'id'                 => $progress->id,
                    'question_id'        => $progress->question_id,
                    'material_id'        => $progress->question?->material_id,
                    'answer_data'        => json_decode($progress->answer_data, true),
                    'is_correct'         => (bool) $progress->is_correct,
                    'points_earned'      => $progress->points_earned,
                    'waktu_detik' => $progress->time_spent_seconds,
                    'answered_at'        => $progress->answered_at,
                    'diperbarui_pada'         => $progress->diperbarui_pada?->toISOString(),
                ];
            });

            return response()->json([
                'status'      => 'success',
                'data'        => $data,
                'server_time' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('[SyncController@syncDownProgress] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil progress siswa.',
            ], 500);
        }
    }
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
            $query = ProgresSiswa::where('pengguna_id', $user->id)
                ->with('soal:id,template_type,material_id,points');

            if ($request->filled('material_id')) {
                $query->whereHas('soal', function ($q) use ($request) {
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
                    'answered_ids'     => $items->pluck('soal_id')->toArray(),
                    'last_answered_at' => $items->max('dibuat_pada'),
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
            $progresses = ProgresSiswa::where('pengguna_id', $user->id)
                ->whereHas('soal', function ($q) use ($materialId) {
                    $q->where('material_id', $materialId);
                })
                ->with('soal:id,points,template_type')
                ->get();

            // Hitung total soal yang tersedia
            $totalQuestions = Soal::where('materi_id', $materialId)
                ->where('aktif', true)
                ->count();

            $answeredCount  = $progresses->count();
            $correctCount   = $progresses->where('is_correct', true)->count();
            $totalPoints    = $progresses->sum('points_earned');
            $maxPoints      = Soal::where('materi_id', $materialId)
                ->where('aktif', true)
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
            $regionName = $request->input('region_name');
            $postalCode = $request->input('postal_code') ?? $request->input('kode_pos') ?? $request->input('code');

            if (!$regionName && !$postalCode) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Parameter region_name atau postal_code wajib diisi.',
                ], 400);
            }

            $query = \App\Models\Wilayah::query();

            if ($postalCode) {
                $query->where('kode_pos', $postalCode);
            } else {
                $query->where('nama_kecamatan', 'like', '%' . $regionName . '%');
            }

            $region = $query->first();

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
                    'id'            => $region->id,
                    'name'          => $region->nama_kecamatan,
                    'district'      => $region->nama_kecamatan,
                    'code'          => $region->kode_pos,
                    'kode_bahasa' => $region->kode_bahasa,
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
        Pengguna $user,
        int $questionId,
        mixed $answerData,
        int $timeSpent = 0
    ): array {
        // Normalisasi: jika bukan array, bungkus menjadi array
        if (is_string($answerData)) {
            $answerData = ['jawaban' => $answerData];
        } elseif (!is_array($answerData)) {
            $answerData = ['jawaban' => (string) $answerData];
        }

        $question = Soal::with('materi')->findOrFail($questionId);

        // Validasi Kepemilikan Materi (Mencegah IDOR antar sekolah)
        $material = $question->materi;
        if ($material) {
            $isAllowed = false;
            if (empty($material->asal_sekolah) || strtolower($material->asal_sekolah) === 'global') {
                $isAllowed = true;
            } elseif ($material->asal_sekolah === $user->asal_sekolah) {
                $isAllowed = true;
            } elseif ($material->kode_bahasa === 'global' || $material->kode_bahasa === $user->kode_bahasa) {
                // Sesuai dengan filter di getQuestions
                $isAllowed = true;
            }
            
            if (!$isAllowed) {
                abort(403, 'Akses ditolak: Soal ini bukan untuk wilayah Anda.');
            }
        }

        // Parse data_soal jika masih string
        $questionData = $question->data_soal;
        if (is_string($questionData)) {
            $questionData = json_decode($questionData, true) ?? [];
        }
        if ($questionData === null) {
            $questionData = [];
        }

        // Validasi jawaban berdasarkan template
        $isCorrect    = $this->validateAnswer($question->tipe_template, $questionData, $answerData);
        $pointsEarned = $isCorrect ? ($question->points ?? 10) : 0;

        // Upsert progress (satu siswa, satu soal, satu record)
        $progress = ProgresSiswa::updateOrCreate(
            [
                'pengguna_id'     => $user->id,
                'soal_id' => $questionId,
            ],
            [
                'data_jawaban'  => json_encode($answerData),
                'benar'   => $isCorrect,
                'poin_diperoleh'=> $pointsEarned,
                'waktu_detik' => $timeSpent,
                'dijawab_pada'  => now(),
            ]
        );

        return [
            'question_id'   => $questionId,
            'template_type' => $question->tipe_template,
            'is_correct'    => $isCorrect,
            'points_earned' => $pointsEarned,
            'correct_answer'=> $this->getCorrectAnswerHint($question->tipe_template, $questionData),
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
        ?array $questionData,
        mixed $answerData
    ): bool {
        $questionData = $questionData ?? [];
        if (!is_array($answerData)) {
            $answerData = ['jawaban' => $answerData];
        }
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
            $normalizedPengguna    = strtolower(trim((string) $userAnswer));

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
    private function getCorrectAnswerHint(string $templateType, ?array $questionData): mixed
    {
        $questionData = $questionData ?? [];
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
        $assets = PustakaAset::whereIn('nama_file', $filenames)
            ->where('aktif', true)
            ->get()
            ->keyBy('nama_file'); // index by filename untuk O(1) lookup

        $result = [];

        foreach ($filenames as $filename) {
            $asset = $assets->get($filename);

            if ($asset) {
                // Cek apakah file fisik benar-benar ada di disk
                // Double-check: database bisa saja tidak sinkron dengan disk
                $fileExists = Storage::disk('public')->exists('quiz-assets/' . $filename);

                if ($fileExists) {
                    $result[] = [
                        'nama_file'  => $filename,
                        'url'        => '/storage/quiz-assets/' . $filename,
                        'mime_type'  => $asset->tipe_mime ?? $this->guessMimeType($filename),
                        'file_size'  => $asset->ukuran_file ?? 0,
                        'asset_type' => $asset->tipe_aset ?? 'unknown',
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
                        'nama_file' => $filename,
                        'asset_id' => $asset->id,
                    ]);

                    $result[] = [
                        'nama_file'   => $filename,
                        'url'        => null,
                        'mime_type'  => null,
                        'file_size'  => 0,
                        'asset_type' => $asset->tipe_aset ?? 'unknown',
                        'tags'       => [],
                        'status'     => 'missing',
                        'cache_hint' => null,
                    ];
                }
            } else {
                // File tidak ada di database sama sekali
                $result[] = [
                    'nama_file'   => $filename,
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

