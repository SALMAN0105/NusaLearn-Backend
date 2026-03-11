<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Region;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Cek kredensial
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah',
            ], 401);
        }

        $user = User::where('username', $request->username)->firstOrFail();

        // Cek Role (Hanya Siswa yang boleh login di Mobile)
        if ($user->role !== 'siswa') {
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
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'school_origin' => $user->school_origin,
                    'postal_code' => $user->postal_code,
                    'language_code' => $user->language_code,
                    'image_url' => $user->image_url 
                        ? asset('storage/' . $user->image_url) 
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
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'school_origin' => 'required|string',
            'postal_code' => 'required|string|max:10',
        ]);

        // ✅ Query dengan nama kolom ASLI: postal_code (dengan underscore)
        $region = Region::where('postal_code', $validated['postal_code'])->first();

        // ✅ Validasi region
        if (!$region) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode pos tidak terdaftar di sistem. Silakan hubungi admin untuk mendaftarkan wilayah Anda.',
                'postal_code' => $validated['postal_code'],
            ], 422);
        }

        // ✅ Create user dengan language_code dari region
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => bcrypt($validated['password']),
            'role' => 'siswa',
            'school_origin' => $validated['school_origin'],
            'postal_code' => $validated['postal_code'],
            'language_code' => $region->language_code, // ✅ DENGAN UNDERSCORE
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'school_origin' => $user->school_origin,
                    'postal_code' => $user->postal_code,
                    'language_code' => $user->language_code,
                    'image_url' => $user->image_url 
                        ? asset('storage/' . $user->image_url) 
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
