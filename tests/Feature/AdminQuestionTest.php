<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Materi;
use App\Models\Soal;
use Illuminate\Support\Facades\Hash;

class AdminQuestionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $materi;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Pengguna::create([
            'nama' => 'Admin Soal', 'nama_pengguna' => 'admin_soal',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'admin', 
            'asal_sekolah' => 'SDN 1 Kendari', 'aktif' => 1,
        ]);
        
        $this->materi = Materi::create([
            'judul' => 'Materi Soal', 'kategori' => 'literasi',
            'tingkat_kesulitan' => 1, 'kode_bahasa' => 'id', 'konten' => '{}'
        ]);
    }

    public function test_admin_bisa_membuat_soal_manual()
    {
        $response = $this->actingAs($this->admin)->post('/admin/questions/manual', [
            'materi_id' => $this->materi->id,
            'tipe_template' => 'multiple_choice',
            'bobot_kesulitan' => 1,
            'teks_soal' => 'Siapa yang menemukan benua Amerika?',
            'options' => ['Columbus', 'Magellan', 'Polo'],
            'correct_option' => 0,
            'points' => 10,
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('soal', [
            'materi_id' => $this->materi->id,
            'teks_soal' => 'Siapa yang menemukan benua Amerika?',
            'kunci_jawaban' => 'a',
            'poin' => 10,
        ]);
    }
}
