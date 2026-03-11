<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Wilayah - Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Outfit', 'sans-serif'],
                        inter: ['Inter', 'sans-serif']
                    },
                    colors: {
                        emerald: { 50: '#ecfdf5', 100: '#d1fae5', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' },
                        neo: { 
                            bg: '#F8F9FA', 
                            lavender: '#E2D9F3', 
                            green: '#A7F3D0', 
                            cyan: '#A5F3FC', 
                            red: '#FECDD3', 
                            yellow: '#FDE047', 
                            coral: '#FFB8A3', 
                            pink: '#F9A8D4' 
                        }
                    },
                    boxShadow: {
                        'neo': '4px 4px 0px 0px rgba(0, 0, 0, 1)',
                        'neo-sm': '2px 2px 0px 0px rgba(0, 0, 0, 1)',
                        'neo-hover': '1px 1px 0px 0px rgba(0, 0, 0, 1)',
                        'neo-lg': '6px 6px 0px 0px rgba(0, 0, 0, 1)',
                    }
                }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* --- PRELOADER ANIMATION --- */
        #preloader {
            position: fixed; inset: 0; background-color: #F8F9FA;
            z-index: 9999; display: flex; justify-content: center; align-items: center;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        html.dark #preloader { background-color: #0f172a; }

        .diamond-loader { position: relative; width: 64px; height: 64px; animation: spin-container 2s infinite cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .diamond { position: absolute; width: 24px; height: 24px; border-radius: 8px; border: 2px solid #000; transform: rotate(45deg); animation: assemble 2s infinite ease-in-out; }
        .diamond:nth-child(1) { top: 4px; left: 4px; background: #E2D9F3; --tx: -20px; --ty: -20px; }
        .diamond:nth-child(2) { top: 4px; right: 4px; background: #A7F3D0; --tx: 20px; --ty: -20px; }
        .diamond:nth-child(3) { bottom: 4px; left: 4px; background: #FFB8A3; --tx: -20px; --ty: 20px; }
        .diamond:nth-child(4) { bottom: 4px; right: 4px; background: #A5F3FC; --tx: 20px; --ty: 20px; }

        @keyframes assemble {
            0% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; }
            40%, 60% { transform: translate(0, 0) rotate(45deg) scale(1); opacity: 1; }
            100% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; }
        }
        @keyframes spin-container {
            0%, 30% { transform: rotate(0deg); }
            70%, 100% { transform: rotate(180deg); }
        }
    </style>
</head>
<body class="bg-neo-bg text-black font-sans antialiased selection:bg-neo-lavender selection:text-black dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300">

    <div id="preloader">
        <div class="diamond-loader">
            <div class="diamond"></div><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden p-2 md:p-4 gap-4">

        <aside id="sidebar" class="bg-white dark:bg-slate-800 w-64 md:w-[280px] flex-shrink-0 border-2 border-black rounded-2xl hidden md:flex flex-col transition-all duration-300 fixed md:relative z-40 h-full overflow-hidden shadow-neo">
            <div class="h-24 flex items-center px-6 border-b-2 border-black bg-white dark:bg-slate-800">
                <div class="flex items-center gap-4">
                    <div class="bg-neo-lavender text-black p-3 rounded-xl border-2 border-black shadow-neo-sm font-bold flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-lg"></i>
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight text-black dark:text-white">NusaLearn</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto py-6 px-5 space-y-1 bg-white dark:bg-slate-800">
                <p class="px-3 text-sm font-bold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">General</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-chart-pie w-6 text-center text-lg transition-colors"></i> <span>Dashboard</span>
                </a>
                
                <p class="px-3 text-sm font-bold text-gray-500 dark:text-gray-400 mt-8 mb-3 uppercase tracking-wider">Manajemen Konten</p>
                <a href="{{ route('materials.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-layer-group w-6 text-center text-lg transition-colors"></i> <span>Materi Belajar</span>
                </a>
                <a href="{{ route('questions.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-clipboard-question w-6 text-center text-lg transition-colors"></i> <span>Bank Soal (Kuis)</span>
                </a>

                <p class="px-3 text-sm font-bold text-gray-500 dark:text-gray-400 mt-8 mb-3 uppercase tracking-wider">Master Data</p>
                <a href="{{ route('languages.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-language w-6 text-center text-lg transition-colors"></i> <span>Bahasa Daerah</span>
                </a>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-users w-6 text-center text-lg transition-colors"></i> <span>Data Siswa</span>
                </a>
                
                <a href="#" class="flex items-center gap-3 px-4 py-3.5 bg-neo-lavender text-black border-2 border-black shadow-neo-sm font-bold rounded-xl transition-all text-base">
                    <i class="fa-solid fa-map-location-dot w-6 text-center text-lg"></i> <span>Wilayah (Kode Pos)</span>
                </a>
            </div>

            <div class="border-t-2 border-black p-6 bg-white dark:bg-slate-800">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=E2D9F3&color=000&bold=true" alt="Admin" class="w-12 h-12 rounded-full border-2 border-black shadow-neo-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-black dark:text-white truncate">Administrator</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium truncate">Sistem Inti</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo">
            
            <header class="h-24 border-b-2 border-black flex items-center justify-between px-8 lg:px-12 z-20 sticky top-0 bg-white dark:bg-slate-800 rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="md:hidden w-12 h-12 flex items-center justify-center bg-neo-lavender border-2 border-black text-black rounded-xl shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-3xl font-extrabold text-black dark:text-white tracking-tight hidden md:block">Peta Wilayah & Kode Pos</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    @if(session('success'))
                        <div class="bg-neo-green border-2 border-black text-black px-5 py-2.5 rounded-xl flex items-center gap-3 shadow-neo-sm text-base font-bold" role="alert">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="bg-neo-red border-2 border-black text-black px-5 py-2.5 rounded-xl flex items-center gap-3 shadow-neo-sm text-base font-bold" role="alert">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif
                    
                    <button id="theme-toggle" type="button" class="w-12 h-12 flex items-center justify-center bg-white border-2 border-black text-black rounded-xl shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all" aria-label="Toggle Dark Mode">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-xl"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8 lg:p-12 bg-neo-bg dark:bg-slate-900 transition-colors duration-300">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    
                    <div class="bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo p-8 lg:sticky lg:top-0">
                        <div class="border-b-2 border-black dark:border-gray-700 pb-5 mb-6">
                            <h3 class="text-2xl font-extrabold text-black dark:text-white">Petakan Wilayah Baru</h3>
                            <p class="text-sm font-bold text-gray-600 dark:text-gray-400 mt-1">Registrasi area untuk klasifikasi bahasa.</p>
                        </div>
                        
                        <form action="{{ route('regions.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-black dark:text-white text-base font-bold mb-3">Kode Pos</label>
                                <input name="postal_code" type="text" placeholder="Contoh: 93572" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm font-mono" required>
                            </div>
                            
                            <div>
                                <label class="block text-black dark:text-white text-base font-bold mb-3">Nama Kecamatan/Desa</label>
                                <input name="district_name" type="text" placeholder="Contoh: Kec. Ueesi" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm" required>
                            </div>
                            
                            <div>
                                <label class="block text-black dark:text-white text-base font-bold mb-3">Bahasa Dominan</label>
                                <select name="language_code" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none">
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang->code }}">{{ $lang->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="pt-6">
                                <button type="submit" class="w-full bg-neo-cyan hover:bg-cyan-300 text-black border-2 border-black font-extrabold py-4 px-5 rounded-xl shadow-neo hover:-translate-y-1 hover:shadow-neo-lg transition-all flex justify-center items-center gap-3 text-lg">
                                    <i class="fa-solid fa-map-pin text-xl"></i> Simpan Pemetaan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo overflow-hidden flex flex-col">
                        <div class="p-6 border-b-2 border-black bg-white dark:bg-slate-800">
                            <h2 class="text-xl font-extrabold text-black dark:text-white">Daftar Wilayah Terdaftar</h2>
                        </div>
                        <div class="overflow-x-auto flex-1 font-inter">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-slate-700 text-gray-800 dark:text-gray-200 text-sm border-b-2 border-black">
                                        <th class="px-8 py-5 font-bold">Kode Pos</th>
                                        <th class="px-8 py-5 font-bold">Area / Wilayah</th>
                                        <th class="px-8 py-5 font-bold">Dialek Bahasa</th>
                                        <th class="px-8 py-5 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-2 divide-gray-100 dark:divide-slate-700 text-base font-medium">
                                    @forelse($regions as $region)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors group">
                                        <td class="px-8 py-5">
                                            <span class="font-mono font-bold text-black bg-neo-yellow border-2 border-black px-3 py-1.5 rounded-lg shadow-neo-sm">{{ $region->postal_code }}</span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="font-extrabold text-black dark:text-white text-lg">{{ $region->district_name }}</div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-bold bg-neo-lavender text-black border-2 border-black shadow-neo-sm uppercase">
                                                <i class="fa-solid fa-language"></i> {{ $region->language_code }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <form action="{{ route('regions.destroy', $region->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data wilayah ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none" title="Hapus Wilayah">
                                                    <i class="fa-solid fa-trash-can text-base"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-16 text-center bg-white dark:bg-slate-800">
                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 dark:bg-slate-700 border-2 border-dashed border-gray-400 dark:border-gray-500 rounded-full text-gray-400 dark:text-gray-500 mb-4">
                                                <i class="fa-solid fa-map-location-dot text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 font-bold text-base">Belum ada pemetaan wilayah yang terdaftar.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // Transisi Preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.style.opacity = '0';
            setTimeout(() => { preloader.style.visibility = 'hidden'; }, 600);
        });

        // Kontrol Sidebar
        function toggleSidebar() { 
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('absolute'); 
            sidebar.classList.toggle('h-full');
            sidebar.classList.toggle('w-[280px]');
            sidebar.classList.toggle('z-50');
        }

        // Dark Mode Logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleIcon = document.getElementById('theme-toggle-icon');

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
            themeToggleBtn.classList.replace('bg-white', 'bg-neo-lavender');
        } else {
            document.documentElement.classList.remove('dark');
        }

        themeToggleBtn.addEventListener('click', function() {
            const isDark = document.documentElement.classList.toggle('dark');
            if (isDark) {
                localStorage.theme = 'dark';
                themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
                themeToggleBtn.classList.replace('bg-white', 'bg-neo-lavender');
            } else {
                localStorage.theme = 'light';
                themeToggleIcon.classList.replace('fa-sun', 'fa-moon');
                themeToggleBtn.classList.replace('bg-neo-lavender', 'bg-white');
            }
        });
    </script>
</body>
</html>