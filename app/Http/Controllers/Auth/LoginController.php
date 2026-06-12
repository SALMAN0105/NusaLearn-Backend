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
        if (Auth::check()) {
            if (Auth::user()->peran === 'administrator') {
                return redirect()->route('administrator.dashboard');
            } elseif (Auth::user()->peran === 'admin') {
                return redirect()->route('admin.dashboard');
            }
        }
        return view('auth.login');
    }

    // 2. Memproses Login
    public function login(Request $request)
    {
        // Validasi Input
        $credentials = $request->validate([
            'nama_pengguna' => ['required', 'string'],
            'kata_sandi' => ['required', 'string'],
        ]);

        $attemptCredentials = [
            'nama_pengguna' => $credentials['nama_pengguna'],
            'password' => $credentials['kata_sandi']
        ];

        // Coba Login (Attempt)
        if (Auth::attempt($attemptCredentials, $request->filled('remember'))) {
            
            // Regenerasi Session ID (PENTING: Mencegah Session Fixation Attack)
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->aktif) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'nama_pengguna' => 'Akun Anda telah dinonaktifkan oleh Administrator.',
                ]);
            }

            if ($user->peran === 'administrator') {
                // Administrator tidak butuh access code untuk login (asumsi)
                return redirect()->route('administrator.dashboard');
            } elseif ($user->peran === 'admin') {
                // 'admin' role in db is actually 'guru' conceptually
                // Cek apakah akun guru sudah diverifikasi (diberi access code)
                if (empty($user->kode_akses)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'nama_pengguna' => 'Akun Guru Anda belum diverifikasi oleh Administrator.',
                    ]);
                }

                // Cek apakah input access code sesuai secara aman (cegah timing attack)
                if (!hash_equals((string) $user->kode_akses, (string) $request->input('kode_akses'))) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'nama_pengguna' => 'Kode Akses Guru salah.',
                    ]);
                }

                return redirect()->intended('admin/dashboard');
            } else {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'nama_pengguna' => 'Akun Anda tidak memiliki akses ke panel ini.',
                ]);
            }
        }

        // Jika gagal login
        return back()->withErrors([
            'nama_pengguna' => 'Username atau password salah.',
        ])->onlyInput('nama_pengguna');
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
