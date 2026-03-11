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
    Schema::create('student_progress', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
        
        $table->string('student_answer'); // Jawaban yang dipilih siswa
        $table->boolean('is_correct'); // Benar/Salah
        $table->integer('time_spent_seconds')->default(0); // Lama pengerjaan (untuk analisis)
        
        $table->timestamp('answered_at'); // Waktu mengerjakan di HP (bukan waktu upload ke server)
        $table->timestamps(); // Waktu data masuk ke server
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
