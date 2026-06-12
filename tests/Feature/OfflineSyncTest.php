<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Materi;
use App\Models\Soal;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $pengguna, $materi, $soal1, $soal2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pengguna = Pengguna::create([
            'nama' => 'Siswa Offline', 'nama_pengguna' => 'siswa_offline',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'siswa', 'aktif' => 1,
        ]);
        
        $this->materi = Materi::create([
            'judul' => 'Materi Offline', 'kategori' => 'literasi',
            'tingkat_kesulitan' => 1, 'kode_bahasa' => 'id',
            'konten' => '{}',
        ]);
        
        $this->soal1 = Soal::create([
            'materi_id' => $this->materi->id, 'teks_soal' => 'Soal 1',
            'kunci_jawaban' => 'A', 'poin' => 10, 'tipe_template' => 'multiple_choice',
            'data_soal' => '{"correct_answer":"A"}', 'aktif' => 1,
        ]);

        $this->soal2 = Soal::create([
            'materi_id' => $this->materi->id, 'teks_soal' => 'Soal 2',
            'kunci_jawaban' => 'B', 'poin' => 15, 'tipe_template' => 'multiple_choice',
            'data_soal' => '{"correct_answer":"B"}', 'aktif' => 1,
        ]);
    }

    public function test_sinkronisasi_tumpukan_data_setelah_offline()
    {
        Sanctum::actingAs($this->pengguna, ['*']);

        // Mengunci waktu agar sinkronisasi dapat diverifikasi
        $waktuOffline = Carbon::now()->subMinutes(30)->toDateTimeString();

        $response = $this->postJson('/api/sync/progress', [
            'answers' => [
                [
                    'soal_id' => $this->soal1->id, 
                    'data_jawaban' => ['selected' => 'A'], 
                    'waktu_detik' => 15,
                    'dijawab_pada' => $waktuOffline, // Data lama saat offline
                ],
                [
                    'soal_id' => $this->soal2->id, 
                    'data_jawaban' => ['selected' => 'C'], // Jawaban Salah
                    'waktu_detik' => 20,
                    'dijawab_pada' => $waktuOffline,
                ]
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['total_answered' => 2])
                 ->assertJsonFragment(['total_correct' => 1]);

        // Verifikasi soal 1 (Benar)
        $this->assertDatabaseHas('progres_siswa', [
            'soal_id' => $this->soal1->id,
            'benar' => 1,
            'poin_diperoleh' => 10,
        ]);

        // Verifikasi soal 2 (Salah)
        $this->assertDatabaseHas('progres_siswa', [
            'soal_id' => $this->soal2->id,
            'benar' => 0,
            'poin_diperoleh' => 0,
        ]);
    }
}
