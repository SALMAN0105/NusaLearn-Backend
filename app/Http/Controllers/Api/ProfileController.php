<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // 1. Update Profil (Nama & Foto)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'nullable|string|max:255|unique:pengguna,nama_pengguna,' . $user->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        $user->nama = $request->nama;
        if ($request->has('nama_pengguna') && $request->nama_pengguna != null) {
            $user->nama_pengguna = $request->nama_pengguna;
        }

        // Upload Foto jika ada
        if ($request->hasFile('image')) {
            // Hapus foto lama jika bukan default
            if ($user->url_gambar) {
                Storage::disk('public')->delete($user->url_gambar);
            }

            $path = $request->file('image')->store('profile_photos', 'public');
            // Simpan path relatif agar mudah diakses via API
            // Pastikan Anda punya accessor atau logic URL di Flutter
            $user->url_gambar = $path; 
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'nama' => $user->nama,
                'nama_pengguna' => $user->nama_pengguna,
                // Pastikan kirim Full URL
                'url_gambar' => $user->url_gambar ? asset('storage/' . $user->url_gambar) : null,
            ]
        ]);
    }

    // 2. Ganti Password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // butuh field new_password_confirmation
        ]);

        $user = $request->user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->kata_sandi)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata sandi lama salah.',
            ], 422);
        }

        $user->kata_sandi = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }
}
