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
    Schema::create('languages', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: Bahasa Tolaki
        $table->string('code')->unique(); // Contoh: tolaki, bugis, muna (Untuk sistem)
        $table->string('flag_icon')->nullable(); // Opsional: URL icon bendera/simbol daerah
        $table->boolean('is_active')->default(true); // Fitur untuk mematikan bahasa sementara
        $table->string('json_file')->nullable();
        $table->string('version_hash', 64)->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
