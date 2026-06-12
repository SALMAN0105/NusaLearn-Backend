<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;
use App\Models\Wilayah;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nama_pengguna' => 'required',
            'kata_sandi' => 'required',
        ]);

        // Cek kredensial
        if (!Auth::attempt(['nama_pengguna' => $request->nama_pengguna, 'password' => $request->kata_sandi])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah',
            ], 401);
        }

        $user = Pengguna::where('nama_pengguna', $request->nama_pengguna)->firstOrFail();

        // Cek apakah akun aktif
        if (!$user->aktif) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda telah dinonaktifkan oleh Administrator.',
            ], 403);
        }

        // Cek Role (Hanya Siswa yang boleh login di Mobile)
        if ($user->peran !== 'siswa') {
            return response()->json([
                'status' => 'error',
                'message' => 'Aplikasi ini khusus untuk Siswa.',
            ], 403);
        }

        // Buat Token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'peran' => $user->peran,
                    'kelas' => $user->kelas,
                    'asal_sekolah' => $user->asal_sekolah,
                    'kode_pos' => $user->kode_pos,
                    'kode_bahasa' => $user->kode_bahasa,
                    'url_gambar' => $user->url_gambar 
                        ? asset('storage/' . $user->url_gambar) 
                        : null,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        // ✅ Validasi sesuai nama kolom database ASLI
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:50|unique:pengguna',
            'kata_sandi' => 'required|string|min:6',
            'asal_sekolah' => 'required|string',
            'kode_pos' => 'required|string|max:10',
            'kelas' => 'required|integer|min:1|max:3',
        ]);

        // ✅ Query dengan nama kolom ASLI: postal_code (dengan underscore)
        $region = Wilayah::where('kode_pos', $validated['kode_pos'])->first();

        // ✅ Validasi region
        if (!$region) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode pos tidak terdaftar di sistem. Silakan hubungi admin untuk mendaftarkan wilayah Anda.',
                'kode_pos' => $validated['kode_pos'],
            ], 422);
        }

        // ✅ Create user dengan language_code dari region
        $user = Pengguna::create([
            'nama' => $validated['nama'],
            'nama_pengguna' => $validated['nama_pengguna'],
            'kata_sandi' => Hash::make($validated['kata_sandi']),
            'asal_sekolah' => $validated['asal_sekolah'],
            'kode_pos' => $validated['kode_pos'],
            'kode_bahasa' => $region->kode_bahasa, // ✅ DENGAN UNDERSCORE
            'peran' => 'siswa',
            'kelas' => $validated['kelas'],
            'level_saat_ini' => 1,
            'aktif' => 1
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'peran' => $user->peran,
                    'kelas' => $user->kelas,
                    'asal_sekolah' => $user->asal_sekolah,
                    'kode_pos' => $user->kode_pos,
                    'kode_bahasa' => $user->kode_bahasa,
                    'url_gambar' => $user->url_gambar 
                        ? asset('storage/' . $user->url_gambar) 
                        : null,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
