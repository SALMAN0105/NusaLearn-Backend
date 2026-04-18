<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom multimedia ke tabel questions yang sudah ada.
     * BACKWARD COMPATIBLE: kolom lama (options_json, correct_answer_key) TIDAK dihapus.
     * Flutter lama tetap bisa baca format lama selama masa transisi.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // === TIPE TEMPLATE ===
            // 'multiple_choice' = format lama (kompatibel), semua template baru di bawahnya
            $table->enum('template_type', [
                'multiple_choice',  // Format lama: options_json + correct_answer_key
                'drag_and_drop',    // Seret item ke kategori/urutan yang benar
                'matching_game',    // Pasangkan kolom kiri dengan kolom kanan
                'fill_blank',       // Isi bagian yang kosong dari kalimat/teks
                'image_quiz',       // Identifikasi area/label pada gambar
            ])->default('multiple_choice')->after('difficulty_weight');

            // === DATA SOAL MULTIMEDIA (JSON Schema per template) ===
            // Null jika template_type = 'multiple_choice' (pakai options_json lama)
            $table->json('question_data')->nullable()->after('template_type')
                ->comment('JSON schema soal multimedia, struktur berbeda per template_type');

            // === MANIFEST ASET YANG DIBUTUHKAN ===
            // Flutter pakai ini untuk download aset sebelum kuis dimulai
            $table->json('assets_required')->nullable()->after('question_data')
                ->comment('Array filename aset yang dibutuhkan: ["a1b2c3d4.jpg", "e5f6a7b8.mp3"]');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['template_type', 'question_data', 'assets_required']);
        });
    }
};