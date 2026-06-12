<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Bahasa;
use Illuminate\Support\Facades\Hash;

class AdminMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Pengguna::create([
            'nama' => 'Admin Materi', 'nama_pengguna' => 'admin_materi',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'admin', 
            'asal_sekolah' => 'SDN 1 Kendari', 'aktif' => 1,
        ]);
        
        Bahasa::create(['nama' => 'Tolaki', 'kode' => 'tlk', 'aktif' => 1]);
    }

    public function test_admin_bisa_membuat_materi_dengan_json_valid()
    {
        Storage::fake('public');

        $jsonContent = json_encode([
            'metadata' => ['title' => 'Test'],
            'knowledge_map' => ['concept' => 'test']
        ]);
        $fileJson = UploadedFile::fake()->createWithContent('materi.json', $jsonContent);

        $response = $this->actingAs($this->admin)->post('/admin/materi', [
            'judul' => 'Materi Baru Bahasa',
            'kategori' => 'literasi',
            'tingkat_kesulitan' => 2,
            'kode_bahasa' => 'tlk',
            'file_json' => $fileJson,
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('materi', [
            'judul' => 'Materi Baru Bahasa',
            'kategori' => 'literasi',
            'kode_bahasa' => 'tlk',
        ]);
        
        // Memastikan field 'konten' menyimpan JSON string
        $materi = \App\Models\Materi::where('judul', 'Materi Baru Bahasa')->first();
        $this->assertNotNull($materi->konten);
    }
}
