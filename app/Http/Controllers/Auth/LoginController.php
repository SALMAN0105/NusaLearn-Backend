<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // 1. Menampilkan Halaman Login
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    // 2. Memproses Login
    public function login(Request $request)
    {
        // Validasi Input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Coba Login (Attempt)
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            
            // Regenerasi Session ID (PENTING: Mencegah Session Fixation Attack)
            $request->session()->regenerate();

            // Cek Role: Hanya Admin yang boleh masuk sini
            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Akun Anda tidak memiliki akses administrator.',
                ]);
            }

            // Jika sukses dan admin, arahkan ke dashboard
            return redirect()->intended('admin/dashboard');
        }

        // Jika gagal login
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}