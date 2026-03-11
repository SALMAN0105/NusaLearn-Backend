<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pemulihan Akses - Tolaki Learning</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { emerald: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' } },
                    animation: { 'blob': 'blob 10s infinite', 'fade-in-up': 'fadeInUp 0.6s ease-out forwards' },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } }
                    }
                }
            }
        }
    </script>
    <style>
        #preloader { position: fixed; inset: 0; background-color: #ffffff; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.6s ease, visibility 0.6s ease; }
        .diamond-loader { position: relative; width: 64px; height: 64px; animation: spin-container 2s infinite cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .diamond { position: absolute; width: 24px; height: 24px; border-radius: 4px; transform: rotate(45deg); animation: assemble 2s infinite ease-in-out; }
        .diamond:nth-child(1) { top: 4px; left: 4px; background: linear-gradient(135deg, #10b981, #059669); --tx: -20px; --ty: -20px; }
        .diamond:nth-child(2) { top: 4px; right: 4px; background: linear-gradient(135deg, #059669, #047857); --tx: 20px; --ty: -20px; }
        .diamond:nth-child(3) { bottom: 4px; left: 4px; background: linear-gradient(135deg, #34d399, #10b981); --tx: -20px; --ty: 20px; }
        .diamond:nth-child(4) { bottom: 4px; right: 4px; background: linear-gradient(135deg, #047857, #065f46); --tx: 20px; --ty: 20px; }
        @keyframes assemble { 0% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; } 40%, 60% { transform: translate(0, 0) rotate(45deg) scale(1); opacity: 1; } 100% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; } }
        @keyframes spin-container { 0%, 30% { transform: rotate(0deg); } 70%, 100% { transform: rotate(180deg); } }
        .animation-delay-2000 { animation-delay: 2s; } .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center relative overflow-x-hidden font-sans antialiased selection:bg-emerald-200 selection:text-emerald-900 py-6 sm:py-12">

    <div id="preloader">
        <div class="diamond-loader"><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div></div>
    </div>

    <div class="absolute inset-0 w-full h-full overflow-hidden z-0 pointer-events-none fixed">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        <div class="absolute top-0 -left-4 w-72 h-72 sm:w-96 sm:h-96 bg-emerald-300 rounded-full mix-blend-multiply filter blur-[80px] sm:blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 sm:w-96 sm:h-96 bg-teal-300 rounded-full mix-blend-multiply filter blur-[80px] sm:blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-10 sm:left-20 w-72 h-72 sm:w-96 sm:h-96 bg-green-300 rounded-full mix-blend-multiply filter blur-[80px] sm:blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="w-full max-w-md relative z-10 px-4 flex flex-col items-center my-auto">
        
        <div class="text-center mb-5 sm:mb-6 animate-fade-in-up">
            <div class="w-14 h-14 sm:w-16 sm:h-16 mx-auto bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl shadow-lg flex items-center justify-center mb-3 transform rotate-12 transition-transform">
                <i class="fa-solid fa-shield-halved text-white text-2xl sm:text-3xl -rotate-12"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Pemulihan <span class="text-emerald-600">Akses</span></h1>
        </div>

        <div class="w-full bg-white/85 backdrop-blur-xl rounded-2xl sm:rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50 p-6 sm:p-8 animate-fade-in-up" style="animation-delay: 0.1s;">
            
            @if (session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm text-sm font-medium">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm text-sm font-medium">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i> <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if($step === 'request_email')
            <form method="POST" action="{{ route('password.sendOtp') }}" class="space-y-5">
                @csrf
                <p class="text-sm text-slate-500 text-center mb-2">Masukkan alamat email yang terdaftar. Kami akan mengirimkan kode verifikasi (OTP).</p>
                <div>
                    <label class="block text-slate-700 text-[13px] font-bold mb-2 ml-1">Alamat Email Valid</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-envelope"></i></div>
                        <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-3 pl-11 pr-4 text-slate-800 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" type="email" name="email" required autofocus placeholder="admin@sekolah.sch.id">
                    </div>
                </div>
                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-all flex justify-center items-center gap-2" type="submit">
                    Kirim Kode OTP <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>

            @elseif($step === 'verify_otp')
            <form method="POST" action="{{ route('password.verifyOtp') }}" class="space-y-5 text-center">
                @csrf
                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-2 text-2xl"><i class="fa-solid fa-envelope-open-text"></i></div>
                <h3 class="font-bold text-slate-800">Cek Email Anda</h3>
                <p class="text-xs text-slate-500 px-4">Kami telah mengirimkan 6 digit kode OTP ke <strong>{{ session('reset_email') }}</strong></p>
                
                <div>
                    <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-4 text-center text-2xl tracking-[0.5em] font-mono font-bold text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 shadow-inner" type="text" name="otp" required maxlength="6" autofocus placeholder="------" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                
                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-all mt-2" type="submit">Verifikasi Kode</button>
            </form>

            @elseif($step === 'reset_password')
            <form method="POST" action="{{ route('password.resetPassword') }}" class="space-y-5">
                @csrf
                <p class="text-[11px] text-amber-600 bg-amber-50 px-3 py-2 rounded-lg font-medium border border-amber-100"><i class="fa-solid fa-shield-cat"></i> Buat password baru minimal 8 karakter, mencakup huruf besar, kecil, angka, dan simbol.</p>
                
                <div>
                    <label class="block text-slate-700 text-[13px] font-bold mb-2 ml-1">Password Baru</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-key text-sm"></i></div>
                        <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-3 pl-11 pr-12 text-slate-800 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" id="password" type="password" name="password" required placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password', 'eye-pass')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600"><i id="eye-pass" class="fa-regular fa-eye"></i></button>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-[13px] font-bold mb-2 ml-1">Konfirmasi Password Baru</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-lock text-sm"></i></div>
                        <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-3 pl-11 pr-12 text-slate-800 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password_confirmation', 'eye-conf')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600"><i id="eye-conf" class="fa-regular fa-eye"></i></button>
                    </div>
                </div>

                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-all mt-4" type="submit">Update Password & Login</button>
            </form>
            @endif

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-[13px] text-slate-500 hover:text-slate-800 font-bold transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.style.opacity = '0';
            setTimeout(() => { preloader.style.visibility = 'hidden'; }, 600);
        });
    </script>
</body>
</html>