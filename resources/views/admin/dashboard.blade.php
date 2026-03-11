<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - Soft Neobrutalism (Upscaled)</title>
    
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
        
        .modal { transition: opacity 0.3s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }

        #preloader { position: fixed; inset: 0; background-color: #F8F9FA; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.6s ease, visibility 0.6s ease; }
        html.dark #preloader { background-color: #0f172a; } 

        .diamond-loader { position: relative; width: 64px; height: 64px; animation: spin-container 2s infinite cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .diamond { position: absolute; width: 24px; height: 24px; border-radius: 8px; border: 2px solid #000; transform: rotate(45deg); animation: assemble 2s infinite ease-in-out; }
        .diamond:nth-child(1) { top: 4px; left: 4px; background: #E2D9F3; --tx: -20px; --ty: -20px; }
        .diamond:nth-child(2) { top: 4px; right: 4px; background: #A7F3D0; --tx: 20px; --ty: -20px; }
        .diamond:nth-child(3) { bottom: 4px; left: 4px; background: #FFB8A3; --tx: -20px; --ty: 20px; }
        .diamond:nth-child(4) { bottom: 4px; right: 4px; background: #A5F3FC; --tx: 20px; --ty: 20px; }

        @keyframes assemble { 0% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; } 40%, 60% { transform: translate(0, 0) rotate(45deg) scale(1); opacity: 1; } 100% { transform: translate(var(--tx), var(--ty)) rotate(45deg) scale(0); opacity: 0; } }
        @keyframes spin-container { 0%, 30% { transform: rotate(0deg); } 70%, 100% { transform: rotate(180deg); } }
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
                <a href="#" class="flex items-center gap-3 px-4 py-3.5 bg-neo-lavender text-black border-2 border-black shadow-neo-sm font-bold rounded-xl transition-all text-base">
                    <i class="fa-solid fa-chart-pie w-6 text-center text-lg"></i> <span>Dashboard</span>
                </a>
                
                <p class="px-3 text-sm font-bold text-gray-500 dark:text-gray-400 mt-8 mb-3 uppercase tracking-wider">Manajemen Konten</p>
                <a href="{{ route('materials.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-book-open w-6 text-center text-lg transition-colors"></i> <span>Materi Belajar</span>
                </a>
                <a href="{{ route('converter.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-file-export w-6 text-center text-lg transition-colors"></i> <span>Kelola File (Konverter)</span>
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
                <a href="{{ route('regions.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-map-location-dot w-6 text-center text-lg transition-colors"></i> <span>Wilayah (Kode Pos)</span>
                </a>
            </div>

            <div class="border-t-2 border-black p-6 bg-white dark:bg-slate-800">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=E2D9F3&color=000&bold=true" alt="Admin" class="w-12 h-12 rounded-full border-2 border-black shadow-neo-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-black dark:text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium truncate">Sistem Inti</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red rounded-lg transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo">
            
            <header class="h-24 border-b-2 border-black flex items-center justify-between px-8 lg:px-12 z-20 sticky top-0 bg-white dark:bg-slate-800 rounded-t-2xl">
                <button onclick="toggleSidebar()" class="md:hidden w-12 h-12 flex items-center justify-center bg-neo-lavender border-2 border-black text-black rounded-xl shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="hidden md:block">
                    <h1 class="text-3xl font-extrabold text-black dark:text-white tracking-tight">Dashboard Admin</h1>
                </div>
                <div class="flex items-center gap-5">
                    @if(session('success'))
                        <div class="bg-neo-green border-2 border-black text-black font-bold rounded-xl px-5 py-3 flex items-center gap-3 shadow-neo-sm text-base" role="alert">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    <button id="theme-toggle" type="button" class="w-12 h-12 flex items-center justify-center bg-white border-2 border-black text-black rounded-xl shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all" aria-label="Toggle Dark Mode">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-xl"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8 lg:p-12 bg-neo-bg dark:bg-slate-900 transition-colors duration-300">
                <div class="flex flex-col xl:flex-row gap-8 lg:gap-10">
                    
                    <div class="flex-1 flex flex-col min-w-0 gap-10">
                        <section>
                            <h2 class="text-xl font-extrabold text-black dark:text-white mb-5">Statistik Sistem</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                                <div class="bg-white dark:bg-slate-800 p-6 border-2 border-black rounded-2xl shadow-neo flex flex-col justify-between min-h-[160px]">
                                    <div class="flex justify-between items-start mb-4">
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-300">Total siswa</p>
                                        <i class="fa-solid fa-ellipsis-vertical text-gray-400 text-lg cursor-pointer"></i>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <h3 class="text-5xl font-black text-black dark:text-white">{{ number_format($stats['total_siswa'] ?? 3) }}</h3>
                                        <div class="flex items-center gap-1.5 text-sm font-bold text-black bg-neo-green border-2 border-black px-3 py-1.5 rounded-lg shadow-neo-sm">
                                            <span>Real-time</span> <i class="fa-solid fa-arrow-trend-up"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-slate-800 p-6 border-2 border-black rounded-2xl shadow-neo flex flex-col justify-between min-h-[160px]">
                                    <div class="flex justify-between items-start mb-4">
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-300">Materi rilis</p>
                                        <i class="fa-solid fa-ellipsis-vertical text-gray-400 text-lg cursor-pointer"></i>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <h3 class="text-5xl font-black text-black dark:text-white">{{ $stats['total_materi'] ?? 1 }}</h3>
                                        <div class="flex items-center gap-1.5 text-sm font-bold text-black bg-neo-cyan border-2 border-black px-3 py-1.5 rounded-lg shadow-neo-sm">
                                            <span>Modul</span> <i class="fa-solid fa-book"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-slate-800 p-6 border-2 border-black rounded-2xl shadow-neo flex flex-col justify-between min-h-[160px]">
                                    <div class="flex justify-between items-start mb-4">
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-300">Cakupan wilayah</p>
                                        <i class="fa-solid fa-ellipsis-vertical text-gray-400 text-lg cursor-pointer"></i>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <h3 class="text-5xl font-black text-black dark:text-white">{{ $stats['total_wilayah'] ?? 1 }}</h3>
                                        <div class="flex items-center gap-1.5 text-sm font-bold text-black bg-neo-red border-2 border-black px-3 py-1.5 rounded-lg shadow-neo-sm">
                                            <span>Kode Pos</span> <i class="fa-solid fa-map-pin"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section>
                            <div class="flex justify-between items-end mb-5">
                                <h2 class="text-xl font-extrabold text-black dark:text-white">Aktivitas Terbaru</h2>
                                <button class="text-base font-bold text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white border-b-2 border-transparent hover:border-black dark:hover:border-white transition-all">view all</button>
                            </div>

                            <div class="bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo overflow-hidden flex flex-col">
                                <div class="overflow-x-auto flex-1 font-inter">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50 dark:bg-slate-700 text-gray-800 dark:text-gray-200 text-sm border-b-2 border-black">
                                                <th class="px-8 py-5 font-bold">Nama Siswa</th>
                                                <th class="px-8 py-5 font-bold">Materi / Soal</th>
                                                <th class="px-8 py-5 font-bold">Hasil</th>
                                                <th class="px-8 py-5 font-bold">Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y-2 divide-gray-100 dark:divide-slate-700 text-base font-medium">
                                            @forelse($recentProgress ?? [] as $progress)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                                    <td class="px-8 py-5">
                                                        <div class="font-bold text-black dark:text-white text-lg">
                                                            {{ $progress->user->name ?? 'Siswa Tidak Dikenal' }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $progress->user->school_origin ?? '-' }}</div>
                                                    </td>
                                                    <td class="px-8 py-5 text-gray-800 dark:text-gray-300 font-semibold">
                                                        {{ Str::limit($progress->question->material->title_indo ?? 'Soal dihapus', 30) }}
                                                    </td>
                                                    <td class="px-8 py-5">
                                                        @if($progress->is_correct)
                                                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl text-sm font-bold bg-neo-green text-black border-2 border-black shadow-neo-sm">
                                                                Benar
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl text-sm font-bold bg-neo-red text-black border-2 border-black shadow-neo-sm">
                                                                Salah
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-sm">
                                                        {{ $progress->created_at->diffForHumans() }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="px-8 py-16 text-center bg-white dark:bg-slate-800">
                                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 dark:bg-slate-700 border-2 border-dashed border-gray-400 dark:border-gray-500 rounded-full text-gray-400 dark:text-gray-500 mb-4">
                                                            <i class="fa-solid fa-inbox text-2xl"></i>
                                                        </div>
                                                        <p class="text-gray-500 dark:text-gray-400 font-bold text-base">Belum ada aktivitas siswa hari ini.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="w-full xl:w-[380px] shrink-0 flex flex-col gap-8">
                        <div class="flex justify-between items-end xl:mb-2">
                            <h2 class="text-xl font-extrabold text-black dark:text-white">Pengaturan Cepat</h2>
                        </div>

                        <div class="bg-white dark:bg-slate-800 border-2 border-black rounded-2xl p-7 shadow-neo">
                            <h3 class="text-sm font-extrabold text-gray-500 dark:text-gray-400 mb-5 uppercase tracking-wider">Bahasa Sistem</h3>
                            <div class="space-y-4">
                                @forelse($activeLanguages ?? [['name'=>'Tolaki Konawe', 'code'=>'tlk']] as $lang)
                                    <div class="flex items-center justify-between bg-neo-bg dark:bg-slate-700 border-2 border-black px-5 py-4 rounded-xl shadow-neo-sm hover:-translate-y-0.5 transition-transform">
                                        <span class="font-bold text-black dark:text-white text-base">{{ is_array($lang) ? $lang['name'] : $lang->name }}</span>
                                        <span class="font-bold text-sm text-black bg-neo-lavender px-3 py-1.5 rounded-lg border-2 border-black">{{ is_array($lang) ? $lang['code'] : $lang->code }}</span>
                                    </div>
                                @empty
                                    <p class="text-base font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-700 p-5 border-2 border-dashed border-gray-300 dark:border-slate-500 rounded-xl text-center">Belum ada bahasa.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <a href="{{ route('materials.index') }}" class="bg-neo-yellow border-2 border-black rounded-2xl p-6 shadow-neo hover:-translate-y-1.5 hover:shadow-neo-lg transition-all flex flex-col justify-between aspect-square group">
                                <div class="w-14 h-14 bg-white border-2 border-black rounded-full flex items-center justify-center shadow-neo-sm group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-plus text-black text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-extrabold text-black text-lg leading-tight mb-1">Tambah Materi</p>
                                    <p class="text-sm font-medium text-gray-800">Buat modul</p>
                                </div>
                            </a>

                            <button onclick="toggleModal('modal-language')" class="bg-neo-cyan border-2 border-black rounded-2xl p-6 shadow-neo hover:-translate-y-1.5 hover:shadow-neo-lg transition-all text-left flex flex-col justify-between aspect-square group">
                                <div class="w-14 h-14 bg-white border-2 border-black rounded-full flex items-center justify-center shadow-neo-sm group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-language text-black text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-extrabold text-black text-lg leading-tight mb-1">Tambah Bahasa</p>
                                    <p class="text-sm font-medium text-gray-800">Registrasi dialek</p>
                                </div>
                            </button>

                            <a href="{{ route('converter.index') }}" class="col-span-2 bg-neo-green border-2 border-black rounded-2xl p-6 shadow-neo hover:-translate-y-1.5 hover:shadow-neo-lg transition-all flex items-center justify-between group">
                                <div>
                                    <p class="font-extrabold text-black text-xl leading-tight mb-1">Kelola File (Konversi)</p>
                                    <p class="text-sm font-bold text-gray-800">Konversi PDF/Excel ke JSON otomatis</p>
                                </div>
                                <div class="w-14 h-14 bg-white border-2 border-black rounded-full flex items-center justify-center shadow-neo-sm group-hover:scale-110 transition-transform shrink-0">
                                    <i class="fa-solid fa-file-export text-black text-xl"></i>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <div id="modal-language" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
        <div class="modal-overlay absolute w-full h-full bg-gray-900/40 backdrop-blur-sm" onclick="toggleModal('modal-language')"></div>
        
        <div class="modal-container bg-white dark:bg-slate-800 w-11/12 md:max-w-lg mx-auto rounded-3xl border-2 border-black shadow-neo-lg z-50 overflow-hidden transform transition-all scale-95 opacity-0" id="modal-content-lang">
            <div class="pt-7 pb-5 px-8 border-b-2 border-black bg-neo-lavender dark:bg-slate-700 flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-extrabold text-black dark:text-white">Tambah Bahasa</h3>
                </div>
                <div class="cursor-pointer z-50 w-10 h-10 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all" onclick="toggleModal('modal-language')">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </div>
            </div>

            <div class="px-8 py-8 bg-white dark:bg-slate-800">
                <form action="{{ route('languages.store') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-black dark:text-white text-base font-bold mb-3">Nama Bahasa</label>
                        <input name="name" type="text" placeholder="Contoh: Bahasa Bugis" class="w-full border-2 border-black rounded-xl px-5 py-4 text-base font-bold focus:outline-none focus:bg-gray-50 dark:focus:bg-slate-700 dark:bg-slate-900 dark:text-white transition-all shadow-neo-sm" required>
                    </div>
                    <div class="mb-10">
                        <label class="block text-black dark:text-white text-base font-bold mb-3">Kode Sistem</label>
                        <input name="code" type="text" placeholder="Contoh: bugis" class="w-full border-2 border-black rounded-xl px-5 py-4 text-base font-bold font-inter focus:outline-none focus:bg-gray-50 dark:focus:bg-slate-700 dark:bg-slate-900 dark:text-white transition-all shadow-neo-sm" required>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 mt-2"><i class="fa-solid fa-circle-info mr-1"></i> Huruf kecil, tanpa spasi.</p>
                    </div>
                    
                    <div class="flex justify-end gap-4 pt-4 border-t-2 border-gray-100 dark:border-slate-700">
                        <button type="button" onclick="toggleModal('modal-language')" class="px-6 py-3 bg-white border-2 border-black rounded-xl text-black text-base font-bold shadow-neo-sm hover:-translate-y-1 transition-all">Batal</button>
                        <button type="submit" class="px-6 py-3 bg-neo-yellow border-2 border-black rounded-xl text-black text-base font-bold shadow-neo-sm hover:-translate-y-1 transition-all">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.style.opacity = '0';
            setTimeout(() => { preloader.style.visibility = 'hidden'; }, 600); 
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('absolute'); 
            sidebar.classList.toggle('h-full');
            sidebar.classList.toggle('z-50');
        }

        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const modalContent = document.getElementById('modal-content-lang');
            const body = document.querySelector('body');
            
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                body.classList.add('modal-active');
                setTimeout(() => {
                    modalContent.classList.remove('scale-95', 'opacity-0');
                    modalContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    body.classList.remove('modal-active');
                }, 300);
            }
        }

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