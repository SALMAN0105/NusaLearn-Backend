<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register Administrator - Tolaki Learning</title>
    
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

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
        
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center relative overflow-x-hidden font-sans antialiased selection:bg-emerald-200 selection:text-emerald-900 py-6 sm:py-12">

    <div id="preloader">
        <div class="diamond-loader">
            <div class="diamond"></div><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div>
        </div>
    </div>

    <div class="absolute inset-0 w-full h-full bg-slate-50 overflow-hidden z-0 pointer-events-none fixed">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        <div class="absolute top-0 -left-4 w-72 h-72 sm:w-96 sm:h-96 bg-emerald-300 rounded-full mix-blend-multiply filter blur-[80px] sm:blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 sm:w-96 sm:h-96 bg-teal-300 rounded-full mix-blend-multiply filter blur-[80px] sm:blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-10 sm:left-20 w-72 h-72 sm:w-96 sm:h-96 bg-green-300 rounded-full mix-blend-multiply filter blur-[80px] sm:blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="w-full max-w-2xl relative z-10 px-4 sm:px-6 flex flex-col items-center my-auto">
        
        <div class="text-center mb-5 sm:mb-6 animate-fade-in-up">
            <div class="w-14 h-14 sm:w-16 sm:h-16 mx-auto bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl shadow-lg flex items-center justify-center mb-3 sm:mb-4 transform rotate-12 hover:rotate-0 transition-transform duration-500">
                <i class="fa-solid fa-graduation-cap text-white text-2xl sm:text-3xl -rotate-12 hover:rotate-0 transition-transform duration-500"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Tolaki<span class="text-emerald-600">App</span></h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1 font-medium">Registrasi Panel Administrator</p>
        </div>

        <div class="w-full bg-white/85 backdrop-blur-xl rounded-2xl sm:rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50 p-6 sm:p-8 md:p-10 animate-fade-in-up overflow-y-auto max-h-[88vh] no-scrollbar" style="animation-delay: 0.1s;">
            
            @if ($errors->any())
                <div class="mb-5 sm:mb-6 bg-red-50 border border-red-100 text-red-600 px-4 sm:px-5 py-3 sm:py-4 rounded-xl flex items-start gap-3 shadow-sm animate-fade-in-up" role="alert">
                    <i class="fa-solid fa-triangle-exclamation mt-1 text-sm"></i>
                    <div>
                        <strong class="block font-bold text-xs sm:text-sm mb-1">Registrasi Gagal!</strong>
                        <ul class="list-disc list-inside text-[11px] sm:text-xs space-y-1 opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.register.post') }}" class="space-y-4 sm:space-y-5">
                @csrf

                <div class="bg-emerald-50/50 border border-emerald-100 p-3.5 sm:p-4 rounded-xl mb-3 sm:mb-4 shadow-sm">
                    <label class="block text-emerald-800 text-[12px] sm:text-[13px] font-bold mb-2 ml-1" for="access_code">
                        Kode Akses Pendaftaran <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-emerald-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="fa-solid fa-shield-halved text-xs sm:text-sm"></i>
                        </div>
                        <input class="w-full bg-white border border-emerald-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 shadow-sm uppercase font-mono tracking-wider placeholder:font-sans placeholder:tracking-normal placeholder:normal-case" 
                               id="access_code" type="text" name="access_code" required placeholder="Masukkan Token Akses">
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-emerald-600/70 mt-2 ml-1 font-medium leading-tight"><i class="fa-solid fa-circle-info"></i> Registrasi ini terbatas. Masukkan kode akses institusi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-slate-700 text-[12px] sm:text-[13px] font-bold mb-1.5 sm:mb-2 ml-1" for="name">Nama Lengkap Admin</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-id-card text-xs sm:text-sm"></i></div>
                            <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Budi Santoso">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-700 text-[12px] sm:text-[13px] font-bold mb-1.5 sm:mb-2 ml-1" for="username">Username Akses</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-user-shield text-xs sm:text-sm"></i></div>
                            <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" id="username" type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: admin_smk1">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-slate-700 text-[12px] sm:text-[13px] font-bold mb-1.5 sm:mb-2 ml-1" for="school_origin">Asal Sekolah / Instansi</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-school text-xs sm:text-sm"></i></div>
                            <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" id="school_origin" type="text" name="school_origin" value="{{ old('school_origin') }}" required placeholder="Contoh: SMKN 1 Kendari">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-700 text-[12px] sm:text-[13px] font-bold mb-1.5 sm:mb-2 ml-1" for="email">Alamat Email Valid</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-solid fa-envelope text-xs sm:text-sm"></i></div>
                            <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="admin@sekolah.sch.id">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:gap-5 pt-2 sm:pt-3 border-t border-gray-100/50">
                    <p class="text-[11px] sm:text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg font-medium border border-amber-100 leading-relaxed"><i class="fa-solid fa-circle-exclamation mr-1"></i> Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol khusus (@, #, dsb).</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label class="block text-slate-700 text-[12px] sm:text-[13px] font-bold mb-1.5 sm:mb-2 ml-1" for="password">Kata Sandi Baru</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                    <i class="fa-solid fa-key text-xs sm:text-sm"></i>
                                </div>
                                <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-12 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" 
                                       id="password" type="password" name="password" required placeholder="••••••••">
                                <button type="button" onclick="togglePassword('password', 'eye-icon-pass')" class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none">
                                    <i id="eye-icon-pass" class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-700 text-[12px] sm:text-[13px] font-bold mb-1.5 sm:mb-2 ml-1" for="password_confirmation">Konfirmasi Kata Sandi</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                                    <i class="fa-solid fa-lock text-xs sm:text-sm"></i>
                                </div>
                                <input class="w-full bg-slate-50 border border-gray-200 rounded-xl py-2.5 sm:py-3 pl-10 sm:pl-11 pr-12 text-slate-800 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white shadow-sm" 
                                       id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••">
                                <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-conf')" class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none">
                                    <i id="eye-icon-conf" class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 sm:pt-6">
                    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 sm:py-3.5 px-4 rounded-xl shadow-[0_4px_14px_0_rgba(16,185,129,0.39)] hover:shadow-[0_6px_20px_rgba(16,185,129,0.23)] hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base" type="submit">
                        <i class="fa-solid fa-user-plus"></i> Selesaikan Registrasi
                    </button>
                    
                    <div class="text-center mt-4 sm:mt-5">
                        <a href="{{ route('login') }}" class="text-xs sm:text-[13px] text-emerald-600 hover:text-emerald-800 font-bold transition-colors inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-left"></i> Sudah memiliki akun? Login di sini
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-5 sm:mt-6 text-[10px] sm:text-xs font-medium text-slate-400 animate-fade-in-up" style="animation-delay: 0.2s;">
            <p>&copy; {{ date('Y') }} Tolaki Learning Management System. <br class="sm:hidden">Secured by Enterprise Security.</p>
        </div>
    </div>

    <script>
        // Logika Toggle Password Visibility (O(1) DOM Manipulation)
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