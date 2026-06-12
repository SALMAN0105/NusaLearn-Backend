<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class PasswordRecoveryController extends Controller
{
    // 1. Render UI Terpadu
    public function showRecoveryView(Request $request)
    {
        // Secara default menampilkan form email, kecuali ada session step aktif
        $step = session('step', 'request_email');
        return view('auth.password-recovery', compact('step'));
    }

    // 2. Fase: Validasi Email & Kirim OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:pengguna,email']);
        $email = $request->email;

        // Generate 6 Digit OTP Kriptografik (Secure Random)
        $otp = random_int(100000, 999999);

        // Simpan OTP ke RAM/Cache dengan TTL 10 Menit (O(1) Time Complexity)
        Cache::put('otp_reset_' . $email, $otp, now()->addMinutes(10));

        // Kirim Email secara Sinkron (Di Production gunakan Queues/Jobs)
        Mail::send('emails.otp-reset', ['otp' => $otp], function($message) use ($email) {
            $message->to($email)->subject('Kode Verifikasi Reset Password - TolakiApp');
        });

        // Simpan state email ke session untuk divalidasi di step selanjutnya
        session(['reset_email' => $email, 'step' => 'verify_otp']);

        return back()->with('success', 'Kode verifikasi telah dikirim ke email Anda.');
    }

    // 3. Fase: Validasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $email = session('reset_email');
        $cachedOtp = Cache::get('otp_reset_' . $email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau telah kedaluwarsa.']);
        }

        // OTP Valid, transisi ke form password baru
        session(['step' => 'reset_password', 'otp_verified' => true]);
        return back()->with('success', 'Verifikasi berhasil. Silakan buat password baru.');
    }

    // 4. Fase: Eksekusi Ganti Password (Defensive)
    public function resetPassword(Request $request)
    {
        // Cegah akses langsung tanpa verifikasi OTP
        if (!session('otp_verified')) {
            return redirect()->route('password.recovery')->withErrors(['email' => 'Sesi tidak valid. Ulangi proses.']);
        }

        $request->validate([
            'kata_sandi' => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ]
        ]);

        $email = session('reset_email');
        $user = Pengguna::where('email', $email)->firstOrFail();

        // Update Kredensial
        $user->kata_sandi = Hash::make($request->kata_sandi);
        $user->save();

        // Garbage Collection: Hapus jejak cache dan session keamanan
        Cache::forget('otp_reset_' . $email);
        session()->forget(['reset_email', 'step', 'otp_verified']);

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui! Silakan login.');
    }
}
