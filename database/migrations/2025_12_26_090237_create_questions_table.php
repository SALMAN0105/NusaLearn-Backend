<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')->constrained('materi')->onDelete('cascade');
            $table->text('teks_soal');
            $table->json('opsi_json')->nullable();
            $table->string('kunci_jawaban', 10)->nullable();
            $table->integer('bobot_kesulitan')->default(1);
            $table->unsignedInteger('poin')->default(10)->comment('Poin yang didapat jika jawaban benar');
            $table->enum('tipe_template', ['multiple_choice', 'drag_and_drop', 'matching_game', 'fill_blank', 'image_quiz'])->default('multiple_choice');
            $table->json('data_soal')->nullable()->comment('JSON schema soal multimedia');
            $table->json('aset_diperlukan')->nullable()->comment('Array filename aset yang dibutuhkan');
            $table->boolean('aktif')->default(true)->comment('Soal aktif/nonaktif, dipakai untuk filter sync');
            $table->timestamp('dihapus_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
