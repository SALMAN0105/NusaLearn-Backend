<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Tolaki Learning</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        emerald: {
                            50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 
                            600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        }
                    },
                    animation: {
                        'blob': 'blob 10s infinite',
                        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Animasi Preloader Sinkron Global */
        #preloader {
            position: fixed; inset: 0; background-color: #ffffff;
            z-index: 9999; display: flex; justify-content: center; align-items: center;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        .diamond-loader { position: relative; width: 64px; height: 64px; animation: spin-container 2s infinite cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .diamond { position: absolute; width: 24px; height: 24px; border-radius: 4px; transform: rotate(45deg); animation: assemble 2s infinite ease-in-out; }
        .diamond:nth-child(1) { top: 4px; left: 4px; background: linear-gradient(135deg, #10b981, #059669); --tx: -20px; --ty: -20px; }
        .diamond:nth-child(2) { top: 4px; right: 4px; background: linear-gradient(135deg, #059669, #047857); --tx: 20px; --ty: -20px; }
        .diamond:nth-child(3) { bottom: 4px; left: 4px; background: linear-gradient(135deg, #34d399, #10b981); --tx: -20px; --ty: 20px; }
        .diamond:nth-child(4) { bottom: 4px; right: 4px; background: linear-gradient(135deg, #047857, #065f46); --tx: 20px; --ty: 20px; }

        @keyframes assemble {
            0% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; }
            40%, 60% { transform: translate(0, 0) rotate(45deg) scale(1); opacity: 1; }
            100% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; }
        }
        @keyframes spin-container { 0%, 30% { transform: rotate(0deg); } 70%, 100% { transform: rotate(180deg); } }
        
        /* Delay utility untuk animasi */
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center relative overflow-hidden font-sans antialiased selection:bg-emerald-200 selection:text-emerald-900">

    <div id="preloader">
        <div class="diamond-loader">
            <div class="diamond"></div><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div>
        </div>
    </div>

    <div class="absolute inset-0 w-full h-full bg-slate-50 overflow-hidden z-0 pointer-events-none">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        
        <div class="absolute top-0 -left-4 w-96 h-96 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-96 h-96 bg-teal-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-96 h-96 bg-green-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="w-full max-w-md relative z-10 px-6 sm:px-0">
        
        <div class="text-center mb-8 animate-fade-in-up">
            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl shadow-lg flex items-center justify-center mb-4 transform rotate-12 hover:rotate-0 transition-transform duration-500">
                <i class="fa-solid fa-graduation-cap text-white text-3xl -rotate-12 hover:rotate-0 transition-transform duration-500"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Tolaki<span class="text-emerald-600">App</span></h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Administrator Gateway</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50 p-8 sm:p-10 animate-fade-in-up" style="animation-delay: 0.1s;">
            
            @if (session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm text-sm font-medium animate-fade-in-up" role="alert">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm animate-fade-in-up" role="alert">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <div>
                        <strong class="block font-bold text-sm">Otorisasi Ditolak!</strong>
                        <span class="block text-xs mt-0.5">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-slate-700 text-sm font-bold mb-2 ml-1" for="username">
                        Username Akses
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fa-solid fa-user-shield text-sm"></i>
                        </div>
                        <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-3.5 pl-11 pr-4 text-slate-800 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" 
                               id="username" type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="Masukkan ID identitas...">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-sm font-bold mb-2 ml-1" for="password">
                        Kunci Keamanan (Password)
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fa-solid fa-key text-sm"></i>
                        </div>
                        <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-3.5 pl-11 pr-12 text-slate-800 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" 
                               id="password" type="password" name="password" required placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password', 'eye-icon-login')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none">
                            <i id="eye-icon-login" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="remember" class="peer appearance-none w-5 h-5 border-2 border-gray-200 rounded-md checked:bg-emerald-500 checked:border-emerald-500 transition-all cursor-pointer">
                            <i class="fa-solid fa-check absolute text-white text-[10px] left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                        </div>
                        <span class="text-sm font-medium text-slate-600 group-hover:text-slate-800 transition-colors">Ingat sesi saya</span>
                    </label>

                    <a href="{{ route('password.recovery') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800 transition-colors">
                        Lupa kata sandi?
                    </a>
                </div>

                <div class="pt-4">
                    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-[0_4px_14px_0_rgba(16,185,129,0.39)] hover:shadow-[0_6px_20px_rgba(16,185,129,0.23)] hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2" type="submit">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem
                    </button>
                    
                    <div class="text-center mt-5">
                        <a href="{{ route('admin.register') }}" class="text-[13px] text-emerald-600 hover:text-emerald-800 font-bold transition-colors inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-user-plus"></i> Pendaftaran Administrator Baru
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-8 text-xs font-medium text-slate-400 animate-fade-in-up" style="animation-delay: 0.2s;">
            <p>&copy; {{ date('Y') }} Tolaki Learning Management System. <br>Secured by Enterprise Security.</p>
        </div>
    </div>

    <script>
        // Imutabilitas Fungsi Toggle Password
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

        // Imutabilitas Preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.style.opacity = '0';
            setTimeout(() => { preloader.style.visibility = 'hidden'; }, 600);
        });
    </script>
</body>
</html>