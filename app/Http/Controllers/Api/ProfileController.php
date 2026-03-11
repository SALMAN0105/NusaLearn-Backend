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
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        $user = $request->user();
        $user->name = $request->name;

        // Upload Foto jika ada
        if ($request->hasFile('image')) {
            // Hapus foto lama jika bukan default (opsional)
            // if ($user->image_url) Storage::disk('public')->delete($user->image_url);

            $path = $request->file('image')->store('profile_photos', 'public');
            // Simpan path relatif agar mudah diakses via API
            // Pastikan Anda punya accessor atau logic URL di Flutter
            $user->image_url = $path; 
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'name' => $user->name,
                // Pastikan kirim Full URL
                'image_url' => $user->image_url ? asset('storage/' . $user->image_url) : null,
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
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata sandi lama salah.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }
}