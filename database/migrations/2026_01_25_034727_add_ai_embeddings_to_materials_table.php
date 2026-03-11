<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->text('ai_embeddings')->nullable()->after('content_indo'); // Vector hasil TensorFlow
            $table->enum('ai_status', ['pending', 'processing', 'ready', 'failed'])->default('pending')->after('ai_embeddings');
            $table->timestamp('ai_processed_at')->nullable()->after('ai_status');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['ai_embeddings', 'ai_status', 'ai_processed_at']);
        });
    }
};