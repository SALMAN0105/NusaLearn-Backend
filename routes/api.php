<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ProfileController;

// 1. Public Routes (Tidak butuh Token)
// Untuk cek wilayah sebelum login & proses login itu sendiri
Route::post('/check-region', [SyncController::class, 'checkRegion']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/sync/check-region', [SyncController::class, 'checkRegion']);

Route::get('/schools', function () {
    // Ambil daftar sekolah unik dari user admin yang sudah terdaftar
    $schools = \App\Models\User::where('role', 'admin')
                ->select('school_origin')
                ->distinct()
                ->whereNotNull('school_origin')
                ->pluck('school_origin');
                
    return response()->json([
        'status' => 'success',
        'data' => $schools
    ]);
});

// 2. Protected Routes (Butuh Token Sanctum)
// Semua request di sini harus menyertakan Header: 'Authorization: Bearer <token>'
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Check
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Endpoint Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint Delta Sync
    Route::get('/sync/materials', [SyncController::class, 'getMaterials']);
    Route::get('/sync/questions', [SyncController::class, 'getQuestions']);
    
    Route::post('/sync/progress', [SyncController::class, 'syncProgress']);

    Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);
});