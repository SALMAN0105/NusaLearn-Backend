<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->enum('template_type', [
                'multiple_choice',
                'drag_and_drop',
                'matching_game',
                'fill_blank',
                'image_quiz',
            ])->default('multiple_choice')->after('is_correct');

            $table->json('student_answer_json')
                  ->nullable()
                  ->after('student_answer')
                  ->comment('JSON answer untuk template kompleks');
        });
    }

    public function down(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->dropColumn(['template_type', 'student_answer_json']);
        });
    }
};