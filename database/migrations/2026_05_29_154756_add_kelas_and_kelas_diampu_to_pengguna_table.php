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
        Schema::table('pengguna', function (Blueprint $table) {
            $table->integer('kelas')->nullable()->after('asal_sekolah');
            $table->json('kelas_diampu')->nullable()->after('kelas');
            $table->integer('level_saat_ini')->default(1)->after('kelas_diampu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['kelas', 'kelas_diampu', 'level_saat_ini']);
        });
    }
};
