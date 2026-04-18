<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Tambahkan kolom is_active secara aman jika belum ada
            if (!Schema::hasColumn('questions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('assets_required');
            }
        });
    }

    /**
     * Kembalikan migrasi (Rollback).
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};