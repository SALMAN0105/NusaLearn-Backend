<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pustaka_aset', function (Blueprint $table) {
            $table->id();

            // === IDENTITAS FILE (Content-Addressable Storage) ===
            $table->string('nama_file', 64)->unique()->comment('MD5 hash dari isi file, dipakai sebagai nama file di storage');
            $table->string('nama_asli')->comment('Nama file asli dari API sumber');
            $table->string('ekstensi', 10)->comment('Ekstensi file: jpg, png, svg, mp3, json (lottie)');
            $table->string('tipe_mime', 100)->comment('MIME type: image/jpeg, audio/mpeg, application/json, dll');
            $table->string('tipe_aset', 20)->default('image')->comment('Tipe aset: image, audio, video, lottie, icon');
            $table->unsignedInteger('ukuran_kb')->default(0)->comment('Ukuran file dalam KB');

            // === SUMBER ASET ===
            $table->enum('sumber_api', [
                'pixabay',
                'pexels',
                'freesound',
                'lottiefiles',
                'iconify',
                'manual',
                'manual_upload',
            ])->default('manual');

            $table->string('url_sumber')->nullable()->comment('URL asal dari API (untuk keperluan audit & atribusi)');
            $table->string('id_sumber')->nullable()->comment('ID unik dari API sumber (misal: pixabay_id, pexels_id)');
            $table->text('atribusi')->nullable()->comment('Teks atribusi wajib (khusus Pexels: nama fotografer + link)');

            // === METADATA PENCARIAN ===
            $table->json('kata_kunci')->nullable()->comment('Array tag untuk pencarian: ["alam", "hewan", "sapi"]');
            $table->string('kategori')->nullable()->comment('Kategori konten: literasi, numerasi, budaya, umum');

            // === STATUS ===
            $table->boolean('aktif')->default(true)->comment('Nonaktif = tidak muncul di manifest AI');

            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->timestamp('dihapus_pada')->nullable();

            $table->index('sumber_api');
            $table->index('ekstensi');
            $table->index('kategori');
            $table->index('aktif');
            $table->index('tipe_aset', 'idx_tipe_aset');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pustaka_aset');
    }
};