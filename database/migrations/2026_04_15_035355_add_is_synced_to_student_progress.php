<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek dulu apakah kolom sudah ada (idempotent)
        if (!Schema::hasColumn('student_progress', 'is_synced')) {
            Schema::table('student_progress', function (Blueprint $table) {
                // Fix #03: is_synced ada di fillable & syncProgress()
                // tapi tidak ada di migration → Column not found error
                $table->boolean('is_synced')
                      ->default(false)
                      ->after('answered_at')
                      ->comment('Flag: sudah tersinkron ke server');

                // Index untuk query pending sync yang efisien
                $table->index('is_synced', 'idx_is_synced');
            });
        }
    }

    public function down(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->dropIndex('idx_is_synced');
            $table->dropColumn('is_synced');
        });
    }
};