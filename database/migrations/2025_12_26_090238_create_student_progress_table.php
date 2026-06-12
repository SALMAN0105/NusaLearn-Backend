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
        Schema::create('progres_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade');

            $table->string('jawaban_siswa')->nullable();
            $table->json('jawaban_siswa_json')->nullable()->comment('JSON answer untuk template kompleks');
            $table->json('data_jawaban')->nullable()->comment('JSON data jawaban mentah dari Flutter');
            $table->boolean('benar');
            $table->unsignedInteger('poin_diperoleh')->default(0)->comment('Poin yang diperoleh siswa');
            $table->enum('tipe_template', ['multiple_choice', 'drag_and_drop', 'matching_game', 'fill_blank', 'image_quiz'])->default('multiple_choice');
            $table->integer('waktu_detik')->default(0);
            $table->boolean('sinkron')->default(false)->comment('Sudah di-sync ke server?');
            $table->timestamp('disinkron_pada')->nullable()->comment('Waktu data di-sync');
            $table->timestamp('dijawab_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres_siswa');
    }
};
