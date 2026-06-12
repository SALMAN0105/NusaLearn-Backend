<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class AdminReferenceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Pengguna::create([
            'nama' => 'Super Admin', 'nama_pengguna' => 'super_admin',
            'kata_sandi' => Hash::make('rahasia123'), 'peran' => 'administrator', 
            'aktif' => 1,
        ]);
    }

    public function test_administrator_bisa_membuat_sekolah_baru()
    {
        $response = $this->actingAs($this->admin)->post('/admin/administrator/sekolah', [
            'nama' => 'SDN 2 Kendari',
            'kode_pos' => '93112',
            'alamat' => 'Jl. Jenderal Sudirman No. 2',
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('sekolah', [
            'nama' => 'SDN 2 Kendari',
            'kode_pos' => '93112',
        ]);
    }

    public function test_admin_bisa_membuat_bahasa_baru()
    {
        $fileJson = UploadedFile::fake()->createWithContent('kamus_btg.json', '{"hello":"halo"}');

        $response = $this->actingAs($this->admin)->post('/admin/administrator/bahasa', [
            'nama' => 'Banteng',
            'kode' => 'btg',
            'dataset' => $fileJson,
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('bahasa', [
            'nama' => 'Banteng',
            'kode' => 'btg',
        ]);
    }
}
