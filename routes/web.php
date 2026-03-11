<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\ContentConversionController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/admin/register', [AdminRegisterController::class, 'showRegisterForm'])->name('admin.register');
    Route::post('/admin/register', [AdminRegisterController::class, 'register'])->name('admin.register.post');
});

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('materials', MaterialController::class);
    Route::resource('languages', LanguageController::class); // Cukup ini aja!
    Route::resource('regions', RegionController::class);
    Route::resource('students', StudentController::class)->only(['index', 'destroy']);
    Route::resource('questions', QuestionController::class);

    Route::get('/admin/tools/converter', [ContentConversionController::class, 'index'])->name('converter.index');
    Route::post('/admin/tools/converter/process', [ContentConversionController::class, 'process'])->name('converter.process');
    Route::get('/admin/tools/converter/download/{path}', [ContentConversionController::class, 'download'])->name('converter.download');
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/password/recovery', [App\Http\Controllers\Auth\PasswordRecoveryController::class, 'showRecoveryView'])->name('password.recovery');
Route::post('/password/recovery/send-otp', [App\Http\Controllers\Auth\PasswordRecoveryController::class, 'sendOtp'])->name('password.sendOtp');
Route::post('/password/recovery/verify-otp', [App\Http\Controllers\Auth\PasswordRecoveryController::class, 'verifyOtp'])->name('password.verifyOtp');
Route::post('/password/recovery/reset', [App\Http\Controllers\Auth\PasswordRecoveryController::class, 'resetPassword'])->name('password.resetPassword');