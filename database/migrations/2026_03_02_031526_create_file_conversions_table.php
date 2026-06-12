<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konversi_file', function (Blueprint $table) {
            $table->id();
            $table->string('nama_file_asli');
            $table->string('tipe_konversi');
            $table->string('jalur_output_json');
            $table->integer('ukuran_file_kb');
            $table->enum('status', ['success', 'failed']);
            $table->text('log_error')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konversi_file');
    }
};