<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminAssetTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Pengguna::create([
            'nama' => 'Super Admin', 'nama_pengguna' => 'super_admin',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'admin', 
            'aktif' => 1,
        ]);
    }

    public function test_admin_bisa_upload_aset()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('gambar.jpg');

        $response = $this->actingAs($this->admin)->post('/admin/assets', [
            'file' => $file,
            'tipe_aset' => 'image',
            'tag' => 'hewan,kucing',
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('pustaka_aset', [
            'nama_asli' => 'gambar.jpg',
            'tipe_mime' => 'image/jpeg',
            'tipe_aset' => 'image',
        ]);
    }
}
