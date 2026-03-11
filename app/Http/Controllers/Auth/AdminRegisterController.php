<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // Wajib ditambahkan

class AdminRegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Parameter Keamanan Absolut (Konstanta Access Code)
        $expectedAccessCode = 'NUSALEARN-2026';

        $request->validate([
            // Validasi Kode Akses Kustom
            'access_code' => ['required', 'string', 'in:' . $expectedAccessCode],
            
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'school_origin' => 'required|string|max:255',
            
            // Aturan Kompleksitas Password Laravel
            'password' => [
                'required',
                'confirmed',
                Password::min(8) // Minimal 8 karakter
                    ->mixedCase() // Harus ada Huruf Besar dan Kecil
                    ->letters() // Harus mengandung huruf
                    ->numbers() // Harus mengandung angka
                    ->symbols() // Harus mengandung simbol (@, #, dsb)
                    ->uncompromised(), // Menolak password yang bocor di database publik (Optional tapi disarankan)
            ],
        ], [
            // Custom Error Message untuk Kode Akses agar user tahu alasannya
            'access_code.in' => 'Kode Akses Pendaftaran tidak valid. Silakan hubungi Super Admin.',
            'access_code.required' => 'Kode Akses wajib diisi.',
            
            // Custom Error Message (opsional) agar bahasa Indonesia
            'password.mixed' => 'Password wajib mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password wajib mengandung minimal 1 angka.',
            'password.symbols' => 'Password wajib mengandung minimal 1 simbol khusus.',
        ]);

        // Simpan ke Database secara Imutabel
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'school_origin' => $request->school_origin,
            'role' => 'admin', // Role dikunci statis
            'password' => Hash::make($request->password), // Enkripsi tervalidasi
        ]);

        return redirect()->route('login')->with('success', 'Registrasi Admin Sekolah Berhasil! Silakan login.');
    }
}