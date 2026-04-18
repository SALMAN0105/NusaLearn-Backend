<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            // Fix #02: student_answer harus nullable
            // Template drag_and_drop bisa kirim null
            // atau jawaban disimpan di student_answer_json saja
            $table->string('student_answer')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->string('student_answer')->nullable(false)->change();
        });
    }
};