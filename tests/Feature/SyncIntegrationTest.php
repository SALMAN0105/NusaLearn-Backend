<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\Wilayah;
use App\Models\Bahasa;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SyncIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up common scenario
    }

    public function test_end_to_end_sinkronisasi_flutter_ke_laravel()
    {
        // 1. Setup Data Pangkalan
        $bahasa = Bahasa::create(['nama' => 'Indonesia', 'kode' => 'id', 'aktif' => 1]);
        $wilayah = Wilayah::create(['kode_pos' => '93111', 'nama_kecamatan' => 'Kendari Barat', 'kode_bahasa' => 'id']);
        
        $siswa = Pengguna::create([
            'nama' => 'Andi',
            'nama_pengguna' => 'andi123',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'siswa',
            'asal_sekolah' => 'SDN 1',
            'kode_pos' => '93111',
            'aktif' => 1
        ]);

        $materi = Materi::create([
            'judul' => 'Materi E2E',
            'kategori' => 'literasi',
            'tingkat_kesulitan' => 1,
            'kode_bahasa' => 'id',
            'konten' => '{}'
        ]);

        $soal = Soal::create([
            'materi_id' => $materi->id,
            'teks_soal' => 'Soal E2E?',
            'kunci_jawaban' => 'a',
            'poin' => 10,
            'tipe_template' => 'multiple_choice',
            'opsi_json' => '[{"id":"a", "text":"A"}, {"id":"b", "text":"B"}]',
            'data_soal' => '{"correct_answer": "a"}',
            'aktif' => 1
        ]);

        // 2. Simulasi Login dari Flutter (Get Token)
        $loginResponse = $this->postJson('/api/login', [
            'nama_pengguna' => 'andi123',
            'kata_sandi' => 'password',
            'peran' => 'siswa'
        ]);
        
        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');

        // 3. Simulasi Gawai (Flutter) menarik Materi
        $materiResponse = $this->withHeader('Authorization', "Bearer $token")
                               ->getJson('/api/sync/materials?last_sync=1970-01-01T00:00:00Z');
        $materiResponse->assertStatus(200);
        $this->assertCount(1, $materiResponse->json('data'));

        // 4. Simulasi Gawai menarik Soal
        $soalResponse = $this->withHeader('Authorization', "Bearer $token")
                             ->getJson("/api/sync/questions?material_id={$materi->id}&last_sync=1970-01-01T00:00:00Z");
        $soalResponse->assertStatus(200);
        $this->assertCount(1, $soalResponse->json('data'));

        // 5. Simulasi Gawai mengerjakan soal (Offline) lalu Push Progres (Online)
        $syncProgressResponse = $this->withHeader('Authorization', "Bearer $token")
                                     ->postJson('/api/sync/progress', [
                                         'answers' => [
                                             [
                                                 'soal_id' => $soal->id,
                                                 'data_jawaban' => ['selected' => 'a'],
                                                 'waktu_detik' => 15,
                                             ]
                                         ]
                                     ]);
        
        $syncProgressResponse->assertStatus(200);

        // 6. Verifikasi Peladen menyimpan data progres menggunakan skema Indonesia (progres_siswa)
        $this->assertDatabaseHas('progres_siswa', [
            'pengguna_id' => $siswa->id,
            'soal_id' => $soal->id,
            'benar' => 1,
        ]);
    }
}
