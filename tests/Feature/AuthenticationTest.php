<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat data wilayah untuk referensi pendaftaran
        Wilayah::create([
            'kode_pos' => '93111',
            'nama_kecamatan' => 'Kambu',
            'kode_bahasa' => 'tlk',
        ]);
    }

    /**
     * Uji Pendaftaran Pengguna Berhasil dengan field Indonesia.
     */
    public function test_pengguna_bisa_mendaftar_dengan_field_indonesia()
    {
        $response = $this->postJson('/api/register', [
            'nama' => 'Siswa Test',
            'nama_pengguna' => 'siswa_baru',
            'kata_sandi' => 'rahasia123',
            'asal_sekolah' => 'SMA 1 Kendari',
            'kode_pos' => '93111',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'nama',
                             'nama_pengguna',
                             'peran',
                             'asal_sekolah',
                             'kode_pos',
                             'kode_bahasa',
                         ],
                         'access_token',
                     ]
                 ]);

        // Verifikasi database dengan tabel baru `pengguna`
        $this->assertDatabaseHas('pengguna', [
            'nama' => 'Siswa Test',
            'nama_pengguna' => 'siswa_baru',
            'peran' => 'siswa',
            'asal_sekolah' => 'SMA 1 Kendari',
            'kode_pos' => '93111',
            'kode_bahasa' => 'tlk',
        ]);
    }

    /**
     * Uji Pendaftaran Gagal jika menggunakan kode pos yang salah.
     */
    public function test_pendaftaran_gagal_jika_kode_pos_salah()
    {
        $response = $this->postJson('/api/register', [
            'nama' => 'Siswa Gagal',
            'nama_pengguna' => 'siswa_gagal',
            'kata_sandi' => 'rahasia123',
            'asal_sekolah' => 'SMA 2 Kendari',
            'kode_pos' => '99999', // Tidak ada di database
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'status' => 'error',
                     'message' => 'Kode pos tidak terdaftar di sistem. Silakan hubungi admin untuk mendaftarkan wilayah Anda.',
                 ]);
    }

    /**
     * Uji Masuk (Login) Berhasil.
     */
    public function test_pengguna_bisa_masuk_menggunakan_field_indonesia()
    {
        // Buat pengguna siswa
        $user = Pengguna::create([
            'nama' => 'Siswa Login',
            'nama_pengguna' => 'siswa_login',
            'kata_sandi' => Hash::make('rahasia123'),
            'peran' => 'siswa',
            'asal_sekolah' => 'SMA 1 Kendari',
            'kode_pos' => '93111',
            'kode_bahasa' => 'tlk',
            'aktif' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'nama_pengguna' => 'siswa_login',
            'kata_sandi' => 'rahasia123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'access_token',
                         'user' => [
                             'nama_pengguna',
                             'peran',
                         ]
                     ]
                 ]);
    }

    /**
     * Uji Masuk Gagal karena kata sandi salah.
     */
    public function test_masuk_gagal_jika_kata_sandi_salah()
    {
        Pengguna::create([
            'nama' => 'Siswa Login',
            'nama_pengguna' => 'siswa_login',
            'kata_sandi' => Hash::make('rahasia123'),
            'peran' => 'siswa',
            'asal_sekolah' => 'SMA 1 Kendari',
            'kode_pos' => '93111',
            'kode_bahasa' => 'tlk',
            'aktif' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'nama_pengguna' => 'siswa_login',
            'kata_sandi' => 'salah123',
        ]);

        $response->assertStatus(401)
                 ->assertJsonFragment([
                     'status' => 'error',
                     'message' => 'Username atau password salah',
                 ]);
    }
}
