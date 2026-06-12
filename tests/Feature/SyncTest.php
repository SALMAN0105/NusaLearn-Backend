<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    protected $pengguna;
    protected $materi;
    protected $soal;

    protected function setUp(): void
    {
        parent::setUp();
        
        $wilayah = Wilayah::create([
            'kode_pos' => '93111',
            'nama_kecamatan' => 'Kambu',
            'kode_bahasa' => 'tlk',
        ]);

        $this->pengguna = Pengguna::create([
            'nama' => 'Siswa Sync',
            'nama_pengguna' => 'siswa_sync',
            'kata_sandi' => Hash::make('rahasia123'),
            'peran' => 'siswa',
            'asal_sekolah' => 'SMA 1 Kendari',
            'kode_pos' => '93111',
            'kode_bahasa' => 'tlk',
            'aktif' => 1,
        ]);

        $this->materi = Materi::create([
            'judul' => 'Materi Tes',
            'kategori' => 'literasi',
            'tingkat_kesulitan' => 1,
            'kode_bahasa' => 'tlk',
            'konten' => '{"text": "Halo Dunia"}',
            'asal_sekolah' => 'SMA 1 Kendari',
        ]);

        $this->soal = Soal::create([
            'materi_id' => $this->materi->id,
            'teks_soal' => 'Apa kabar?',
            'opsi_json' => '{"A":"Baik", "B":"Buruk"}',
            'kunci_jawaban' => 'A',
            'bobot_kesulitan' => 1,
            'poin' => 10,
            'tipe_template' => 'multiple_choice',
            'data_soal' => '{"correct_answer":"A"}',
            'aktif' => 1,
        ]);
    }

    /**
     * Uji Pengambilan Materi
     */
    public function test_mengambil_materi()
    {
        Sanctum::actingAs($this->pengguna, ['*']);

        $response = $this->getJson('/api/sync/materials');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'judul' => 'Materi Tes',
                     'kode_bahasa' => 'tlk',
                 ]);
    }

    /**
     * Uji Pengambilan Soal
     */
    public function test_mengambil_soal()
    {
        Sanctum::actingAs($this->pengguna, ['*']);

        $response = $this->getJson('/api/sync/questions');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'materi_id' => $this->materi->id,
                     'teks_soal' => 'Apa kabar?',
                 ]);
    }

    /**
     * Uji Sinkronisasi Progres Siswa
     */
    public function test_menyimpan_progres_jawaban_siswa()
    {
        Sanctum::actingAs($this->pengguna, ['*']);

        $response = $this->postJson('/api/sync/progress', [
            'answers' => [
                [
                    'soal_id' => $this->soal->id,
                    'data_jawaban' => ['selected' => 'A'],
                    'waktu_detik' => 10
                ]
            ]
        ]);

        // Catatan: Payload Flutter "question_id" masih diterima server atau diubah ke "soal_id"
        // di request validation. Di SyncController: 
        // 'answers.*.question_id' => 'required_with:answers|integer|exists:soal,id'
        // Jika server mengharapkan "soal_id" secara murni, test ini memvalidasi toleransi atau memicu perbaikan.
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'success',
                 ]);

        $this->assertDatabaseHas('progres_siswa', [
            'pengguna_id' => $this->pengguna->id,
            'soal_id' => $this->soal->id,
            'benar' => 1,
        ]);
    }
}
