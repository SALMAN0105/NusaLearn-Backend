<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\ProgresSiswa;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminStudentTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $siswa;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Pengguna::create([
            'nama' => 'Admin Siswa', 'nama_pengguna' => 'admin_siswa',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'admin', 
            'asal_sekolah' => 'SDN 1 Kendari', 'aktif' => 1,
        ]);
        
        $this->siswa = Pengguna::create([
            'nama' => 'Budi', 'nama_pengguna' => 'budi',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'siswa', 
            'asal_sekolah' => 'SDN 1 Kendari', 'aktif' => 1,
        ]);
    }

    public function test_admin_bisa_melihat_progres_siswa()
    {
        $materi = Materi::create([
            'judul' => 'Materi Tes', 'kategori' => 'literasi',
            'tingkat_kesulitan' => 1, 'kode_bahasa' => 'id', 'konten' => '{}'
        ]);

        $soal = Soal::create([
            'materi_id' => $materi->id, 'teks_soal' => 'Tes Soal',
            'kunci_jawaban' => 'A', 'poin' => 10, 'tipe_template' => 'multiple_choice',
            'data_soal' => '{"correct_answer":"A"}', 'aktif' => 1,
        ]);

        ProgresSiswa::create([
            'pengguna_id' => $this->siswa->id,
            'soal_id' => $soal->id,
            'jawaban_siswa' => 'A',
            'benar' => true,
            'poin_diperoleh' => 10,
            'waktu_detik' => 15,
            'dijawab_pada' => Carbon::now()->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->admin)->getJson("/admin/students/{$this->siswa->id}/progress");

        $response->assertStatus(200);
        $response->assertJson([
            'total' => 1,
            'correct' => 1,
            'avg_time_seconds' => 15,
        ]);
    }
}
