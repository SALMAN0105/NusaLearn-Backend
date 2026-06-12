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
    /** @var \App\Models\Pengguna|null $user */
    $user = Auth::user();
    
    if (Auth::check()) {
        if ($user?->peran === 'administrator') {
            return redirect()->route('administrator.dashboard');
        } elseif ($user?->peran === 'admin') {
            return redirect()->route('admin.dashboard');
        }
    }
    
    return redirect()->route('login');
});

// ── Auth (Guest Only) ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login',   [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
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

    // Logout is shared
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ── GURU Panel (Auth Required) ────────────────────────────────────────────
    Route::middleware('guru')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Materi
        Route::resource('materi', MaterialController::class);
        Route::post('/materials/{id}/regenerate-ai', [MaterialController::class, 'regenerateAI'])
             ->middleware('throttle:5,1')
             ->name('materials.regenerate-ai');

        // Siswa
        Route::resource('students',  StudentController::class)->only(['index', 'destroy']);
        Route::get('/students/{student}/progress', [StudentController::class, 'progress'])
             ->name('students.progress');

        // Bank Soal
        Route::post('questions/generate', [QuestionController::class, 'generate'])->middleware('throttle:5,1')->name('questions.generate');
        Route::resource('soal', QuestionController::class);
        Route::post('/questions/manual', [QuestionController::class, 'storeManual'])->name('questions.manual.store');
        Route::post('/questions/generate-explanation', [QuestionController::class, 'generateExplanationApi'])->middleware('throttle:10,1')->name('questions.generate-explanation');

        // Asset Library
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/',                    [AssetController::class, 'index'])->name('index');
            Route::post('/',                   [AssetController::class, 'store'])->name('store');
            Route::post('/search-api',         [AssetController::class, 'searchApi'])->name('search-api');
            Route::post('/fetch-api',          [AssetController::class, 'fetchFromApi'])->name('fetch-api');
            Route::get('/manifest',            [AssetController::class, 'showManifest'])->name('manifest');
            Route::patch('/{asset}/toggle',    [AssetController::class, 'toggleActive'])->name('toggle');
            Route::delete('/{asset}',          [AssetController::class, 'destroy'])->name('destroy');
        });

        // Content Converter Tools
        Route::prefix('tools/converter')->name('converter.')->group(function () {
            Route::get('/',                    [ContentConversionController::class, 'index'])->name('index');
            Route::post('/process',            [ContentConversionController::class, 'process'])->name('process');
            Route::get('/download/{path}',     [ContentConversionController::class, 'download'])->name('download');
            Route::delete('/log/{id}',         [ContentConversionController::class, 'destroyLog'])->name('log.destroy');
        });
    });

    // ── ADMINISTRATOR Panel (Auth Required) ──────────────────────────────────
    Route::middleware('administrator')->prefix('administrator')->name('administrator.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Administrator\DashboardController::class, 'index'])->name('dashboard');
        
        // Kelola Sekolah
        Route::resource('sekolah', \App\Http\Controllers\Administrator\SchoolController::class);
        
        // Kelola Guru
        Route::resource('guru', \App\Http\Controllers\Administrator\TeacherController::class);
        Route::post('guru/{guru}/generate-code', [\App\Http\Controllers\Administrator\TeacherController::class, 'generateAccessCode'])->name('guru.generate-code');
        
        // Bahasa & Wilayah
        Route::resource('bahasa', LanguageController::class);
        Route::resource('wilayah',   RegionController::class);

        // Kelola Pengguna
        Route::resource('pengguna', \App\Http\Controllers\Administrator\UserController::class);
        Route::patch('pengguna/{pengguna}/toggle-active', [\App\Http\Controllers\Administrator\UserController::class, 'toggleActive'])->name('pengguna.toggle-active');

        // Materi Global
        Route::resource('materi', MaterialController::class);
        Route::post('/materials/{id}/regenerate-ai', [MaterialController::class, 'regenerateAI'])->middleware('throttle:5,1')->name('materials.regenerate-ai');

        // Bank Soal Global
        Route::post('questions/generate', [QuestionController::class, 'generate'])->middleware('throttle:5,1')->name('questions.generate');
        Route::resource('soal', QuestionController::class);
        Route::post('/questions/manual', [QuestionController::class, 'storeManual'])->name('questions.manual.store');
        Route::post('/questions/generate-explanation', [QuestionController::class, 'generateExplanationApi'])->middleware('throttle:10,1')->name('questions.generate-explanation');

        // Content Converter Tools Global
        Route::prefix('tools/converter')->name('converter.')->group(function () {
            Route::get('/',                    [ContentConversionController::class, 'index'])->name('index');
            Route::post('/process',            [ContentConversionController::class, 'process'])->name('process');
            Route::get('/download/{path}',     [ContentConversionController::class, 'download'])->name('download');
            Route::delete('/log/{id}',         [ContentConversionController::class, 'destroyLog'])->name('log.destroy');
        });
    });
});

Route::get('/assets/serve/{path}', [AssetController::class, 'serveFile'])
     ->name('assets.serve')
     ->where('path', '.*');