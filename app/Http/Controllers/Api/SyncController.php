<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\Question;
use App\Models\Region;
use App\Models\Language;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function checkRegion(Request $request)
    {
        $request->validate(['postal_code' => 'required']);
        $region = Region::where('postal_code', $request->postal_code)->first();

        if (!$region) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode pos tidak ditemukan.'
            ], 404);
        }

        $language = Language::where('code', $region->language_code)->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'district' => $region->district_name,
                'language_code' => $region->language_code,
                'language_name' => $language->name ?? 'Unknown',
                'dictionary_url' => $language->json_file ? asset('storage/' . $language->json_file) : null,
                'dictionary_hash' => $language->version_hash,
            ]
        ]);
    }

    /**
     * ✅ FIX: Parse JSON strings ke array
     */
    public function getMaterials(Request $request)
    {
        try {
            $user = $request->user();
            $lastSync = $request->query('last_sync');

            $query = Material::query();
            
            

            $query->where(function($q) use ($user) {
            $q->where('language_code', 'global')
              ->orWhere('language_code', $user->language_code);
            })->where('school_origin', $user->school_origin);

            if ($lastSync) {
                try {
                    $date = Carbon::parse($lastSync);
                    $query->where('updated_at', '>=', $date);
                } catch (\Exception $e) {
                    Log::warning('Invalid lastSync format', ['lastSync' => $lastSync]);
                }
            }

            $query->withTrashed();

                \Illuminate\Support\Facades\Log::info('--- DEBUG START: GET MATERIALS ---');
                \Illuminate\Support\Facades\Log::info('USER REQ:', ['id' => $user->id, 'school' => $user->school_origin]);
                \Illuminate\Support\Facades\Log::info('RAW SQL:', [$query->toSql()]);
                \Illuminate\Support\Facades\Log::info('BINDINGS:', [$query->getBindings()]);

                $debugResults = $query->pluck('materials.id')->toArray();
                
                \Illuminate\Support\Facades\Log::info('RAW IDS RETURNED:', $debugResults);
                \Illuminate\Support\Facades\Log::info('TOTAL COUNT:', [count($debugResults)]);
                \Illuminate\Support\Facades\Log::info('--- DEBUG END ---');
            
            $materials = $query->get()->map(function($item) {
                if ($item->trashed()) {
                    return ['id' => $item->id, 'status' => 'deleted'];
                }
                
                // ✅ FIX: Decode content_indo dari JSON string
                $contentData = $item->content_indo;
                if (is_string($contentData)) {
                    $contentData = json_decode($contentData, true);
                }
                // Jika decode gagal, set empty array
                if (!is_array($contentData)) {
                    $contentData = [];
                }
                
                // ✅ FIX: Decode ai_embeddings
                $aiEmbeddings = $item->ai_embeddings;
                if (is_string($aiEmbeddings) && !empty($aiEmbeddings)) {
                    $aiEmbeddings = json_decode($aiEmbeddings, true);
                }
                
                return [
                    'id' => $item->id,
                    'title_indo' => $item->title_indo,
                    'category' => $item->category,
                    'image_url' => $item->image_url,
                    'level_difficulty' => $item->level_difficulty,
                    'language_code' => $item->language_code,
                    'content_indo' => $contentData, // ✅ Guaranteed array
                    'updated_at' => $item->updated_at->toDateTimeString(),
                    'ai_embeddings' => $aiEmbeddings,
                    'ai_status' => $item->ai_status ?? 'pending',
                ];
            });

            Log::info('Sync Materials', [
                'user_id' => $user->id,
                'user_language' => $user->language_code,
                'materials_count' => $materials->count(),
            ]);

            return response()->json([
                'status' => 'success',
                'server_time' => now()->toDateTimeString(),
                'data' => $materials
            ]);

        } catch (\Exception $e) {
            Log::error('Sync Materials Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIX: Parse options_json
     */
    public function getQuestions(Request $request)
    {
        try {
            $user = $request->user();
            $lastSync = $request->query('last_sync');

            $query = Question::query()
                ->join('materials', 'questions.material_id', '=', 'materials.id')
                ->where(function($q) use ($user) {
                    $q->where('materials.language_code', 'global')
                      ->orWhere('materials.language_code', $user->language_code);
                })
                ->where('materials.deleted_at', null);

            if ($lastSync) {
                try {
                    $date = Carbon::parse($lastSync);
                    $query->where('questions.updated_at', '>=', $date);
                } catch (\Exception $e) {
                    Log::warning('Invalid lastSync format', ['lastSync' => $lastSync]);
                }
            }

            $query->withTrashed();
            $query->select('questions.*');

            $questions = $query->get()->map(function($item) {
                if ($item->trashed()) {
                    return ['id' => $item->id, 'status' => 'deleted'];
                }
                
                // ✅ FIX: Decode options_json
                $optionsData = $item->options_json;
                if (is_string($optionsData)) {
                    $optionsData = json_decode($optionsData, true);
                }
                if (!is_array($optionsData)) {
                    $optionsData = [];
                }
                
                return [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'question_text_indo' => $item->question_text_indo,
                    'question_text_tolaki' => $item->question_text_tolaki,
                    'options_json' => $optionsData, // ✅ Guaranteed array
                    'correct_answer_key' => $item->correct_answer_key,
                    'difficulty_weight' => $item->difficulty_weight,
                    'updated_at' => $item->updated_at->toDateTimeString(),
                ];
            });

            Log::info('Sync Questions', [
                'user_id' => $user->id,
                'user_language' => $user->language_code,
                'questions_count' => $questions->count(),
            ]);

            return response()->json([
                'status' => 'success',
                'server_time' => now()->toDateTimeString(),
                'data' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Sync Questions Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function syncProgress(Request $request)
    {
        $validated = $request->validate([
            'progress' => 'required|array',
            'progress.*.question_id' => 'required|exists:questions,id',
            'progress.*.student_answer' => 'required',
            'progress.*.is_correct' => 'required',
            'progress.*.time_spent_seconds' => 'nullable|integer',
            'progress.*.answered_at' => 'nullable|date',
        ]);

        $user = $request->user();
        $insertedCount = 0;

        foreach ($validated['progress'] as $item) {
            $isCorrect = filter_var($item['is_correct'], FILTER_VALIDATE_BOOLEAN);

            \App\Models\StudentProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'question_id' => $item['question_id'],
                    'answered_at' => $item['answered_at'] ?? now(),
                ],
                [
                    'student_answer' => $item['student_answer'],
                    'is_correct' => $isCorrect,
                    'time_spent_seconds' => $item['time_spent_seconds'] ?? 0,
                ]
            );
            $insertedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menyinkronkan $insertedCount data progres.",
        ]);
    }
}
