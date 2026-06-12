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
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->string('asal_sekolah')->nullable();
            $table->string('judul');
            $table->json('konten');
            $table->longText('ai_embeddings')->nullable();
            $table->enum('status_ai', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            $table->timestamp('ai_diproses_pada')->nullable();
            $table->enum('kategori', ['literasi', 'numerasi', 'budaya']);
            $table->string('kode_bahasa')->default('global');
            $table->string('url_gambar')->nullable();
            $table->integer('tingkat_kesulitan')->default(1);
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
        Schema::dropIfExists('materi');
    }
};
