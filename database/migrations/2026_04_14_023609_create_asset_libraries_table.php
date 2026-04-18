<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_libraries', function (Blueprint $table) {
            $table->id();

            // === IDENTITAS FILE (Content-Addressable Storage) ===
            // Nama file adalah hash MD5 dari isi file → otomatis deduplikasi
            $table->string('filename', 64)->unique()->comment('MD5 hash dari isi file, dipakai sebagai nama file di storage');
            $table->string('original_name')->comment('Nama file asli dari API sumber');
            $table->string('extension', 10)->comment('Ekstensi file: jpg, png, svg, mp3, json (lottie)');
            $table->string('mime_type', 100)->comment('MIME type: image/jpeg, audio/mpeg, application/json, dll');
            $table->unsignedInteger('size_kb')->default(0)->comment('Ukuran file dalam KB');

            // === SUMBER ASET ===
            $table->enum('source_api', [
                'pixabay',      // Gambar vektor & ilustrasi
                'pexels',       // Foto & video
                'freesound',    // Audio & efek suara
                'lottiefiles',  // Animasi JSON
                'iconify',      // Ikon SVG
                'manual',       // Upload manual oleh admin
            ])->default('manual');

            $table->string('source_url')->nullable()->comment('URL asal dari API (untuk keperluan audit & atribusi)');
            $table->string('source_id')->nullable()->comment('ID unik dari API sumber (misal: pixabay_id, pexels_id)');
            $table->text('attribution')->nullable()->comment('Teks atribusi wajib (khusus Pexels: nama fotografer + link)');

            // === METADATA PENCARIAN ===
            $table->json('tags')->nullable()->comment('Array tag untuk pencarian: ["alam", "hewan", "sapi"]');
            $table->string('category')->nullable()->comment('Kategori konten: literasi, numerasi, budaya, umum');

            // === STATUS ===
            $table->boolean('is_active')->default(true)->comment('Nonaktif = tidak muncul di manifest AI');

            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa query
            $table->index('source_api');
            $table->index('extension');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_libraries');
    }
};