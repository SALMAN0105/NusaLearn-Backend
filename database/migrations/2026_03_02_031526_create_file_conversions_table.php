<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('conversion_type'); // 'kamus_excel' atau 'materi_pdf'
            $table->string('json_output_path');
            $table->integer('file_size_kb');
            $table->enum('status', ['success', 'failed']);
            $table->text('error_log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_conversions');
    }
};