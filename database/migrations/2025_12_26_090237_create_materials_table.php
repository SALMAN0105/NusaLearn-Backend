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
            Schema::create('materials', function (Blueprint $table) {
                $table->id();
                $table->string('school_origin')->nullable();
                $table->string('title_indo');
                $table->json('content_indo');
                $table->enum('category', ['literasi', 'numerasi','budaya']);
                $table->string('language_code')->default('global');              
                $table->string('image_url')->nullable(); // URL gambar cover/aset
                $table->integer('level_difficulty')->default(1); // 1: Mudah, 2: Sedang, 3: Sulit
                $table->softDeletes(); // PENTING untuk sinkronisasi (Delta Sync - PULL)
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
