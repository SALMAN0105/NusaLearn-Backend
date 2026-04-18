<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix inkonsistensi kolom antara kode dan database.
     *
     * Masalah:
     * 1. SyncController mereferensi questions.is_active & questions.points → tidak ada
     * 2. SyncController mereferensi student_progress.points_earned & answer_data → tidak ada
     * 3. AssetController & ManifestGeneratorService mereferensi asset_libraries.asset_type → tidak ada
     * 4. AssetController::store() menggunakan source_api='manual_upload' → tidak ada di enum
     */
    public function up(): void
    {
        // ── 1. Questions: tambah is_active & points ───────────────────
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'is_active')) {
                $table->boolean('is_active')
                      ->default(true)
                      ->after('assets_required')
                      ->comment('Soal aktif/nonaktif, dipakai untuk filter sync');
            }

            if (!Schema::hasColumn('questions', 'points')) {
                $table->unsignedInteger('points')
                      ->default(10)
                      ->after('difficulty_weight')
                      ->comment('Poin yang didapat jika jawaban benar');
            }
        });

        // ── 2. Student Progress: tambah points_earned & answer_data ───
        Schema::table('student_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('student_progress', 'points_earned')) {
                $table->unsignedInteger('points_earned')
                      ->default(0)
                      ->after('is_correct')
                      ->comment('Poin yang diperoleh siswa untuk jawaban ini');
            }

            if (!Schema::hasColumn('student_progress', 'answer_data')) {
                $table->json('answer_data')
                      ->nullable()
                      ->after('student_answer_json')
                      ->comment('JSON data jawaban mentah dari Flutter (format per template)');
            }
        });

        // ── 3. Asset Libraries: tambah asset_type ─────────────────────
        Schema::table('asset_libraries', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_libraries', 'asset_type')) {
                $table->string('asset_type', 20)
                      ->default('image')
                      ->after('mime_type')
                      ->comment('Tipe aset: image, audio, video, lottie, icon');

                $table->index('asset_type', 'idx_asset_type');
            }
        });

        // ── 4. Asset Libraries: perluas source_api enum ───────────────
        // MySQL enum harus di-alter dengan raw SQL
        // Tambah 'manual_upload' ke daftar enum yang sudah ada
        DB::statement("ALTER TABLE `asset_libraries` MODIFY COLUMN `source_api` ENUM(
            'pixabay',
            'pexels',
            'freesound',
            'lottiefiles',
            'iconify',
            'freepik',
            'manual',
            'manual_upload'
        ) DEFAULT 'manual'");
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('questions', 'points')) {
                $table->dropColumn('points');
            }
        });

        Schema::table('student_progress', function (Blueprint $table) {
            if (Schema::hasColumn('student_progress', 'points_earned')) {
                $table->dropColumn('points_earned');
            }
            if (Schema::hasColumn('student_progress', 'answer_data')) {
                $table->dropColumn('answer_data');
            }
        });

        Schema::table('asset_libraries', function (Blueprint $table) {
            if (Schema::hasColumn('asset_libraries', 'asset_type')) {
                $table->dropIndex('idx_asset_type');
                $table->dropColumn('asset_type');
            }
        });

        DB::statement("ALTER TABLE `asset_libraries` MODIFY COLUMN `source_api` ENUM(
            'pixabay',
            'pexels',
            'freesound',
            'lottiefiles',
            'iconify',
            'manual'
        ) DEFAULT 'manual'");
    }
};
