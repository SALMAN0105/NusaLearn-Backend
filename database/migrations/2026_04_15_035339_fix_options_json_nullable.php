<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Fix #01: options_json harus nullable
            // Soal multimedia (drag_and_drop, matching_game, dll)
            // tidak punya options_json → sebelumnya INSERT gagal
            $table->json('options_json')->nullable()->change();

            // Fix sekalian: correct_answer_key juga nullable
            // untuk template yang tidak pakai (drag_and_drop, matching_game)
            $table->string('correct_answer_key', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->json('options_json')->nullable(false)->change();
            $table->string('correct_answer_key', 1)->nullable(false)->change();
        });
    }
};