<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\AssetController;

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES — Tidak butuh token
// ═══════════════════════════════════════════════════════════════════════════

// Auth
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Region check (dipanggil sebelum login saat registrasi)
Route::post('/check-region',      [SyncController::class, 'checkRegion']);
Route::post('/sync/check-region', [SyncController::class, 'checkRegion']); // alias lama

// Daftar sekolah untuk dropdown registrasi Flutter
Route::get('/schools', function () {
    $schools = \App\Models\User::where('role', 'admin')
        ->whereNotNull('school_origin')
        ->distinct()
        ->pluck('school_origin');

    return response()->json([
        'status' => 'success',
        'data'   => $schools,
    ]);
});

// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED ROUTES — Butuh Bearer Token (Sanctum)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ───────────────────────────────────────────────────────────
    Route::get('/user',      fn(Request $r) => $r->user());
    Route::post('/logout',   [AuthController::class, 'logout']);

    // ── Profile ────────────────────────────────────────────────────────
    Route::post('/update-profile',   [ProfileController::class, 'updateProfile']);
    Route::post('/change-password',  [ProfileController::class, 'changePassword']);

    // ── Sync: Materi & Soal ────────────────────────────────────────────
    Route::prefix('sync')->name('api.sync.')->group(function () {
        Route::get('/materials',  [SyncController::class, 'getMaterials'])->name('materials');
        Route::get('/questions',  [SyncController::class, 'getQuestions'])->name('questions');
        Route::post('/progress',  [SyncController::class, 'syncProgress'])->name('progress');

        // ── Asset Sync (BARU) ──────────────────────────────────────────
        // Flutter kirim daftar filename → server balas URL + info
        // Dipakai untuk "pre-flight check" sebelum kuis dimulai
        Route::post('/assets',    [SyncController::class, 'getAssets'])->name('assets');
    });

    // ── Admin: Regenerate AI (dipanggil dari Flutter admin panel) ──────
    Route::post('/admin/materials/{id}/regenerate-ai',
        [MaterialController::class, 'regenerateAI']
    )->name('api.materials.regenerate-ai');

    // ── Quiz State (BARU) ──────────────────────────────────────────────
    // Endpoint untuk mendapatkan state kuis aktif siswa
    // Berguna untuk resume kuis yang terputus
    Route::get('/quiz/state',     [SyncController::class, 'getQuizState'])->name('api.quiz.state');
    Route::post('/quiz/complete', [SyncController::class, 'completeQuiz'])->name('api.quiz.complete');
});