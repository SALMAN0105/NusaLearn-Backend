<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // Wajib ditambahkan

class AdminRegisterController extends Controller
{
    public function showRegisterForm()
    {
        $schools = \App\Models\Sekolah::orderBy('nama', 'asc')->get();
        return view('auth.register', compact('schools'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255|unique:pengguna',
            'email' => 'required|string|email|max:255|unique:pengguna',
            'asal_sekolah' => 'required|string|max:255',
            'kelas' => 'required|array|min:1',
            'kelas.*' => 'integer|min:1|max:3',
            
            // Aturan Kompleksitas Password Laravel
            'kata_sandi' => [
                'required',
                'confirmed',
                Password::min(8) // Minimal 8 karakter
                    ->mixedCase() // Harus ada Huruf Besar dan Kecil
                    ->letters() // Harus mengandung huruf
                    ->numbers() // Harus mengandung angka
                    ->symbols(), // Harus mengandung simbol (@, #, dsb)
            ],
        ], [
            // Custom Error Message (opsional) agar bahasa Indonesia
            'kelas.required' => 'Wajib memilih minimal satu kelas yang diampu.',
            'kelas.min' => 'Wajib memilih minimal satu kelas yang diampu.',
            'kata_sandi.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.mixed' => 'Password wajib mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password wajib mengandung minimal 1 angka.',
            'password.symbols' => 'Password wajib mengandung minimal 1 simbol khusus.',
        ]);

        // Simpan ke Database secara Imutabel
        Pengguna::create([
            'nama' => $request->nama,
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'asal_sekolah' => $request->asal_sekolah,
            'kelas_diampu' => $request->kelas,
            'peran' => 'admin', // Role dikunci statis
            'kode_bahasa' => 'id-1', // Guru menggunakan bahasa Indonesia
            'kata_sandi' => Hash::make($request->kata_sandi), // Enkripsi tervalidasi
        ]);

        return redirect()->route('login')->with('success', 'Registrasi Admin Sekolah Berhasil! Silakan login.');
    }
}
