<?php
// routes/web.php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\ContentConversionController;
use App\Http\Controllers\Auth\PasswordRecoveryController;

// ── Root Redirect ──────────────────────────────────────────────────────────
Route::get('/', function () {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    
    if (Auth::check() && $user?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    
    return redirect()->route('login');
});

// ── Auth (Guest Only) ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login',   [LoginController::class, 'login'])->name('login.post');
    Route::get('/admin/register', [AdminRegisterController::class, 'showRegisterForm'])->name('admin.register');
    Route::post('/admin/register',[AdminRegisterController::class, 'register'])->name('admin.register.post');
});

// ── Password Recovery (Guest) ──────────────────────────────────────────────
Route::get('/password/recovery',         [PasswordRecoveryController::class, 'showRecoveryView'])->name('password.recovery');
Route::post('/password/recovery/send-otp',  [PasswordRecoveryController::class, 'sendOtp'])->name('password.sendOtp');
Route::post('/password/recovery/verify-otp',[PasswordRecoveryController::class, 'verifyOtp'])->name('password.verifyOtp');
Route::post('/password/recovery/reset',     [PasswordRecoveryController::class, 'resetPassword'])->name('password.resetPassword');

// ── Admin Panel (Auth Required) ────────────────────────────────────────────
Route::middleware('auth')->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Materi
    Route::resource('materials', MaterialController::class);
    Route::post('/materials/{id}/regenerate-ai', [MaterialController::class, 'regenerateAI'])
         ->name('materials.regenerate-ai');

    // Bahasa, Wilayah, Siswa
    Route::resource('languages', LanguageController::class);
    Route::resource('regions',   RegionController::class);
    Route::resource('students',  StudentController::class)->only(['index', 'destroy']);
    Route::get('/students/{student}/progress', [StudentController::class, 'progress'])
         ->name('students.progress');

    // ── Bank Soal ──────────────────────────────────────────────────────
    // PENTING: route 'questions.generate' harus SEBELUM resource
    // agar tidak tertangkap sebagai {question} parameter
    Route::post('questions/generate', [QuestionController::class, 'generate'])
         ->name('questions.generate');
    Route::resource('questions', QuestionController::class);
    // Di dalam grup route admin/teacher Anda
    Route::post('/questions/manual', [QuestionController::class, 'storeManual'])->name('questions.manual.store');
    Route::post('/questions/generate-explanation', [QuestionController::class, 'generateExplanationApi'])->name('questions.generate-explanation');

    // ── Asset Library ──────────────────────────────────────────────────
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/',                    [AssetController::class, 'index'])->name('index');
        Route::post('/',                   [AssetController::class, 'store'])->name('store');
        Route::post('/search-api',         [AssetController::class, 'searchApi'])->name('search-api');
        Route::post('/fetch-api',          [AssetController::class, 'fetchFromApi'])->name('fetch-api');
        Route::get('/manifest',            [AssetController::class, 'showManifest'])->name('manifest');
        Route::patch('/{asset}/toggle',    [AssetController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{asset}',          [AssetController::class, 'destroy'])->name('destroy');
    });

    // ── Content Converter Tools ────────────────────────────────────────
    Route::prefix('tools/converter')->name('converter.')->group(function () {
        Route::get('/',                    [ContentConversionController::class, 'index'])->name('index');
        Route::post('/process',            [ContentConversionController::class, 'process'])->name('process');
        Route::get('/download/{path}',     [ContentConversionController::class, 'download'])->name('download');
        Route::delete('/log/{id}',         [ContentConversionController::class, 'destroyLog'])->name('log.destroy');
    });

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/assets/serve/{path}', [AssetController::class, 'serveFile'])
     ->name('assets.serve')
     ->where('path', '.*');