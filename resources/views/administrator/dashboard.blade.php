<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - NusaLearn</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans:  ['Outfit', 'sans-serif'],
                        body:  ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        /* DIRATAKAN UNTUK KOMPILASI ABSOLUT, MENGHINDARI BUG 'DEFAULT' */
                        'p':         '#7C3AED',
                        'p-mid':     '#8B5CF6',
                        'p-lt':      '#EDE9FE',
                        'p-dark':    '#4C1D95',
                        'p-xlt':     '#F5F3FF',
                        
                        'neo-bg':    '#F5F3FF',
                        'neo-green': '#A7F3D0',
                        'neo-cyan':  '#A5F3FC',
                        'neo-red':   '#FECDD3',
                        'neo-yellow':'#FDE047',
                        'neo-coral': '#FFB8A3',
                        'neo-pink':  '#F9A8D4',
                    },
                    boxShadow: {
                        'neo':       '4px 4px 0px 0px rgba(0,0,0,1)',
                        'neo-sm':    '2px 2px 0px 0px rgba(0,0,0,1)',
                        'neo-lg':    '6px 6px 0px 0px rgba(0,0,0,1)',
                        'neo-p':     '4px 4px 0px 0px #4C1D95',
                        'neo-p-sm':  '2px 2px 0px 0px #4C1D95',
                    }
                }
            }
        }
    </script>

    <style>
        /* ── SYSTEM SCROLLBAR ── */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body.modal-active { overflow: hidden !important; }

        /* ── PRELOADER ── */
        #preloader {
            position: fixed; inset: 0; background: #F5F3FF; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        html.dark #preloader { background: #1e1b4b; }
        .pre-logo { 
            font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.2rem; 
            color: #0A0A0A; letter-spacing: -1px; display: flex; align-items: center; gap: 6px; 
            animation: pre-pulse 1.2s ease-in-out infinite; 
        }
        .pre-logo span { color: #7C3AED; }
        html.dark .pre-logo { color: #fff; }
        @keyframes pre-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ── NAVIGATION ACTIVE ── */
        .nav-active {
            background: #7C3AED !important; color: #fff !important;
            border-color: #0A0A0A !important; box-shadow: 2px 2px 0 #0A0A0A;
        }
        .nav-active i { color: #fff !important; }

        /* ── INTERACTION UTILITIES ── */
        .stat-card, .qa-card { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .stat-card:hover, .qa-card:hover { transform: translate(-3px, -3px); box-shadow: 6px 6px 0 #0A0A0A; }

        /* ── BACKGROUND PATTERN ── */
        .dot-grid-bg {
            background-image: radial-gradient(circle, #7C3AED18 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        /* ── BADGE ANIMATION ── */
        @keyframes badge-ping {
            0% { transform: scale(1); opacity: 1; }
            75%, 100% { transform: scale(1.8); opacity: 0; }
        }
        .live-dot::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%;
            background: #22C55E; animation: badge-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* ── RENDER STAGGERING ── */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fade-up 0.4s ease-out both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }
        .fade-in-3 { animation-delay: 0.15s; }
    </style>
</head>

<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300 relative">

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden opacity-0 transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <div class="flex h-screen overflow-hidden p-2 md:p-4 gap-4">

        <aside id="sidebar"
            class="bg-white dark:bg-[#2d2460] w-[260px] flex-shrink-0 border-2 border-black dark:border-p-dark rounded-2xl flex flex-col transition-transform duration-300 fixed md:relative z-40 h-[calc(100vh-1rem)] md:h-full overflow-hidden shadow-neo -translate-x-full md:translate-x-0">
            
            <div class="h-20 flex items-center px-6 border-b-2 border-black dark:border-p-dark bg-white dark:bg-[#2d2460]">
                <div class="flex items-center gap-3">
                    <div class="bg-p text-white w-10 h-10 rounded-xl border-2 border-black dark:border-p-dark shadow-neo-sm flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <span class="text-xl font-black tracking-tight text-black dark:text-white">
                        Nusa<span class="text-p">Learn</span>
                    </span>
                </div>
                <button onclick="toggleSidebar()" class="md:hidden ml-auto text-gray-500 hover:text-black dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto no-scrollbar py-5 px-4 space-y-1 bg-white dark:bg-[#2d2460]">
                <a href="{{ route('administrator.dashboard') }}" class="{{ request()->routeIs('administrator.dashboard') || request()->routeIs('administrator.dashboard') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center border-2 border-transparent">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <span>Dashboard Admin</span>
                </a>

                <a href="{{ route('administrator.sekolah.index') }}" class="{{ request()->routeIs('administrator.sekolah.*') || request()->routeIs('administrator.sekolah.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-school text-lg"></i>
                    </div>
                    <span>Kelola Sekolah</span>
                </a>

                <a href="{{ route('administrator.guru.index') }}" class="{{ request()->routeIs('administrator.guru.*') || request()->routeIs('administrator.guru.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-chalkboard-user text-lg"></i>
                    </div>
                    <span>Kelola Guru</span>
                </a>

                                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-6 mb-1 px-3 hidden md:block">KONTEN GLOBAL</span>

                <a href="{{ route('administrator.materi.index') }}" class="{{ request()->routeIs('administrator.materi.*') || request()->routeIs('administrator.materi.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-book-open text-lg"></i>
                    </div>
                    <span>Materi Global</span>
                </a>

                <a href="{{ route('administrator.soal.index') }}" class="{{ request()->routeIs('administrator.soal.*') || request()->routeIs('administrator.soal.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-database text-lg"></i>
                    </div>
                    <span>Bank Soal Global</span>
                </a>

                <a href="{{ route('administrator.converter.index') }}" class="{{ request()->routeIs('administrator.converter.*') || request()->routeIs('administrator.converter.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-file-export text-lg"></i>
                    </div>
                    <span>Konversi File</span>
                </a>

                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-6 mb-1 px-3 hidden md:block">MASTER DATA</span>

                <a href="{{ route('administrator.bahasa.index') }}" class="{{ request()->routeIs('administrator.bahasa.*') || request()->routeIs('administrator.bahasa.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-language text-lg"></i>
                    </div>
                    <span>Bahasa Daerah</span>
                </a>

                <a href="{{ route('administrator.wilayah.index') }}" class="{{ request()->routeIs('administrator.wilayah.*') || request()->routeIs('administrator.wilayah.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                    </div>
                    <span>Wilayah Persebaran</span>
                </a>

                <a href="{{ route('administrator.pengguna.index') }}" class="{{ request()->routeIs('administrator.pengguna.*') || request()->routeIs('administrator.pengguna.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                    <span>Kelola Pengguna</span>
                </a>
            </nav>

            <div class="border-t-2 border-black dark:border-p-dark p-4 bg-white dark:bg-[#2d2460]">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->nama ?? 'Admin' }}&background=7C3AED&color=fff&bold=true"
                         alt="Avatar Admin" class="w-10 h-10 rounded-full border-2 border-black dark:border-p-dark shadow-neo-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-black dark:text-white truncate">{{ Auth::user()->nama ?? 'Guru' }}</p>
                        <p class="text-xs text-gray-400 font-semibold truncate">Sistem Inti Laravel</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" aria-label="Logout"
                            class="w-9 h-9 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 hover:bg-neo-red hover:text-black dark:hover:bg-red-500/30 rounded-lg transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none flex-shrink-0">
                            <i class="fa-solid fa-power-off text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-[#241f5c] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo">
            
            <header class="h-20 border-b-2 border-black dark:border-p-dark flex items-center justify-between px-6 lg:px-10 z-20 sticky top-0 bg-white dark:bg-[#241f5c] rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" aria-label="Toggle Sidebar"
                        class="md:hidden w-10 h-10 flex items-center justify-center bg-p-lt border-2 border-black text-p rounded-xl shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>

                    <div class="hidden md:flex items-center gap-3">
                        <div class="w-10 h-10 bg-p border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                            <i class="fa-solid fa-server text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Dashboard Administrator</h1>
                            <p class="text-sm text-gray-500 font-medium mt-1">Kelola sekolah, guru, dan master data NusaLearn.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    

                    <button id="theme-toggle" aria-label="Toggle Dark Mode"
                        class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 bg-p-xlt dark:bg-p-dark/30 border-2 border-black dark:border-p-dark px-4 py-2.5 rounded-xl shadow-neo-sm">
                        <span class="relative flex h-2.5 w-2.5 live-dot">
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500 border border-black"></span>
                        </span>
                        <span class="text-xs font-black text-black dark:text-purple-200 uppercase tracking-wide">System Live</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">
                <div class="flex flex-col xl:flex-row gap-8 lg:gap-10">

                    <div class="flex-1 flex flex-col min-w-0 gap-8">
                        
                        <section class="fade-in fade-in-1">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                
                                <div class="stat-card bg-white dark:bg-[#1a1640] border-2 border-black dark:border-p-dark rounded-2xl p-5 shadow-neo flex items-start justify-between">
                                    <div>
                                        <h3 class="text-gray-500 dark:text-gray-400 font-bold text-sm mb-1 uppercase tracking-wider">Total Sekolah</h3>
                                        <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ $schoolCount ?? 0 }}</div>
                                        <div class="text-xs font-bold text-gray-500 flex items-center gap-1">
                                            <span>Total Institusi Terdaftar</span>
                                        </div>
                                    </div>
                                    <div class="w-14 h-14 bg-neo-yellow border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm transform rotate-3">
                                        <i class="fa-solid fa-school text-2xl text-black"></i>
                                    </div>
                                </div>

                                <div class="stat-card bg-white dark:bg-[#1a1640] border-2 border-black dark:border-p-dark rounded-2xl p-5 shadow-neo flex items-start justify-between">
                                    <div>
                                        <h3 class="text-gray-500 dark:text-gray-400 font-bold text-sm mb-1 uppercase tracking-wider">Total Guru</h3>
                                        <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ $teacherCount ?? 0 }}</div>
                                        <div class="text-xs font-bold text-gray-500 flex items-center gap-1">
                                            <span>Guru Aktif</span>
                                        </div>
                                    </div>
                                    <div class="w-14 h-14 bg-neo-green border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm transform -rotate-3">
                                        <i class="fa-solid fa-chalkboard-user text-2xl text-black"></i>
                                    </div>
                                </div>

                                <div class="stat-card sm:col-span-2 lg:col-span-1 bg-white dark:bg-[#2d2460] p-6 border-2 border-black dark:border-p-dark rounded-2xl shadow-neo flex flex-col justify-between min-h-[160px] relative overflow-hidden group">
                                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-neo-yellow/40 rounded-full border-2 border-black/10 opacity-70 group-hover:scale-110 transition-transform"></div>
                                    <div class="flex justify-between items-start relative z-10">
                                        <div class="w-10 h-10 bg-neo-yellow border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                                            <i class="fa-solid fa-location-dot text-black text-sm"></i>
                                        </div>
                                        <span class="bg-neo-yellow text-black text-[11px] font-black border-2 border-black px-2.5 py-1 rounded-md shadow-neo-sm flex items-center gap-1 uppercase">
                                            <i class="fa-solid fa-map"></i> Region
                                        </span>
                                    </div>
                                    <div class="relative z-10">
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2 mb-1 px-3 hidden md:block">ADMINISTRATOR PANEL</span> Wilayah</p>
                                        <h3 class="text-5xl font-black text-black dark:text-white tracking-tight">{{ $stats['total_wilayah'] ?? 1 }}</h3>
                                    </div>
                                </div>

                            </div>
                        </section>

                        <section class="fade-in fade-in-2">
                            <div class="flex justify-between items-center mb-5">
                                <h2 class="text-base font-black text-black dark:text-white uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-1.5 h-5 bg-p rounded-full inline-block"></span> Log Aktivitas
                                </h2>
                            </div>

<div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo overflow-hidden">
                                <div class="overflow-x-auto font-body">
                                    <table class="w-full text-left border-collapse whitespace-nowrap">
                                        <thead>
                                            <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                                <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Identitas Node</th>
                                                <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Pointer Materi</th>
                                                <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Status Checksum</th>
                                                <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                            @forelse($recentProgress ?? [] as $progress)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <img src="https://ui-avatars.com/api/?name={{ $progress->pengguna->nama ?? 'S' }}&background=7C3AED&color=fff&bold=true&size=32"
                                                             class="w-9 h-9 rounded-full border-2 border-black shadow-neo-sm flex-shrink-0" alt="User">
                                                        <div>
                                                            <div class="font-bold text-black dark:text-white">{{ $progress->pengguna->nama ?? 'Siswa Anonymous' }}</div>
                                                            <div class="text-[11px] font-semibold text-gray-400 dark:text-purple-300/60 uppercase">{{ $progress->pengguna->asal_sekolah ?? 'Origin Unknown' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ Str::limit($progress->soal->materi->judul ?? 'Referenced Block Removed', 32) }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($progress->benar)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] uppercase font-black bg-neo-green text-black border-2 border-black shadow-neo-sm">
                                                        <i class="fa-solid fa-check"></i> Valid
                                                    </span>
                                                    @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] uppercase font-black bg-neo-red text-black border-2 border-black shadow-neo-sm">
                                                        <i class="fa-solid fa-xmark"></i> Invalid
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-xs text-gray-400 dark:text-purple-300/60 font-semibold font-mono">
                                                    {{ $progress->dibuat_pada->diffForHumans() }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-16 text-center">
                                                    <div class="w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl flex items-center justify-center mx-auto mb-4">
                                                        <i class="fa-solid fa-ghost text-p-mid text-2xl"></i>
                                                    </div>
                                                    <p class="font-bold text-gray-500 dark:text-purple-300/50">Tidak ada log aktivitas terdeteksi pada server.</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>

                        </section>

                    </div>

                    <div class="w-full xl:w-[340px] shrink-0 flex flex-col gap-8 fade-in fade-in-3">
                        
                        <div>
                            <h3 class="text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-p"></i> Quick Executables
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                
                                <a href="{{ route('administrator.sekolah.index') }}" class="qa-card bg-neo-yellow border-2 border-black rounded-2xl p-5 shadow-neo flex flex-col justify-between aspect-square group">
                                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm group-hover:-rotate-12 transition-transform">
                                        <i class="fa-solid fa-school text-black"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-black text-sm leading-tight uppercase">Kelola Sekolah</p>
                                        <p class="text-[11px] font-bold text-black/60 mt-1">Registrasi Institusi</p>
                                    </div>
                                </a>

                                <a href="{{ route('administrator.bahasa.index') }}" class="qa-card bg-p border-2 border-black rounded-2xl p-5 shadow-neo flex flex-col justify-between aspect-square text-left group">
                                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm group-hover:rotate-12 transition-transform">
                                        <i class="fa-solid fa-language text-p"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-white text-sm leading-tight uppercase">Add Bahasa</p>
                                        <p class="text-[11px] font-bold text-white/70 mt-1">Register dialect</p>
                                    </div>
                                </a>

                                <a href="{{ route('administrator.guru.index') }}" class="qa-card col-span-2 bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl p-5 shadow-neo flex items-center justify-between group">
                                    <div>
                                        <p class="font-black text-black dark:text-white text-sm leading-tight uppercase">Kelola Guru</p>
                                        <p class="text-[11px] font-bold text-gray-500 dark:text-purple-300/60 mt-1 font-mono">Verifikasi & Akses</p>
                                    </div>
                                    <div class="w-12 h-12 bg-neo-cyan border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm group-hover:scale-110 transition-transform flex-shrink-0">
                                        <i class="fa-solid fa-chalkboard-user text-black text-lg"></i>
                                    </div>
                                </a>

                            </div>
                        </div>

                        <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-database text-p"></i> Registry Bahasa
                                </h3>
                                <span class="bg-p text-white text-[10px] font-black px-2 py-0.5 rounded-md border-2 border-black shadow-neo-sm">
                                    {{ count($activeLanguages ?? []) }} Node
                                </span>
                            </div>
                            <div class="space-y-3">
                                @forelse($activeLanguages ?? [['nama'=>'Tolaki Konawe', 'kode'=>'tk-1']] as $lang)
                                <div class="flex items-center justify-between bg-p-xlt dark:bg-p-dark/30 border-2 border-black dark:border-p-dark px-4 py-3 rounded-xl hover:-translate-x-1 transition-transform shadow-neo-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-black border-2 border-p-mid rounded-lg flex items-center justify-center shadow-neo-sm">
                                            <i class="fa-solid fa-kode text-white text-[10px]"></i>
                                        </div>
                                        <span class="font-bold text-black dark:text-white text-sm">{{ is_array($lang) ? $lang['nama'] : $lang->nama }}</span>
                                    </div>
                                    <span class="font-black text-[10px] text-p-dark dark:text-purple-200 bg-p-lt dark:bg-p-dark/50 px-2 py-1 rounded-md border-2 border-black dark:border-p-dark uppercase">
                                        {{ is_array($lang) ? $lang['kode'] : $lang->kode }}
                                    </span>
                                </div>
                                @empty
                                <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/50 bg-p-xlt dark:bg-p-dark/20 p-4 border-2 border-dashed border-p-mid dark:border-p-dark rounded-xl text-center">
                                    Registry kosong.
                                </p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="modal-language" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-language')"></div>

        <div id="modal-content-lang"
             class="bg-white dark:bg-[#2d2460] w-11/12 md:max-w-md mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 overflow-hidden transform transition-all scale-95 opacity-0">
            
            <div class="pt-6 pb-5 px-7 border-b-2 border-black dark:border-p-dark bg-p flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-language text-p text-sm"></i>
                    </div>
                    <h3 class="text-xl font-black text-white tracking-tight">Register Bahasa</h3>
                </div>
                <button onclick="toggleModal('modal-language')" aria-label="Close Modal"
                    class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="px-7 py-7 bg-white dark:bg-[#2d2460]">
                <form action="{{ route('administrator.bahasa.store') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Nama Dialek</label>
                        <div class="relative">
                            <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                            <input name="nama" type="text" placeholder="Cth: Bahasa Bugis"
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none transition-all placeholder:text-gray-400 focus:border-p dark:focus:border-p-mid focus:shadow-neo-p"
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-7">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">System Code</label>
                        <div class="relative">
                            <i class="fa-solid fa-kode absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                            <input name="kode" type="text" placeholder="Cth: bugis"
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono lowercase tracking-wider text-black dark:text-white outline-none transition-all placeholder:text-gray-400 focus:border-p dark:focus:border-p-mid focus:shadow-neo-p"
                                   required>
                        </div>
                        <p class="text-[11px] font-bold text-gray-500 dark:text-purple-300/50 mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-p-mid"></i> Karakter alfabet kecil, tanpa spasi.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-5 border-t-2 border-p-lt dark:border-p-dark">
                        <button type="button" onclick="toggleModal('modal-language')"
                            class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                            Abort
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-p border-2 border-black rounded-xl text-white text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all">
                            <i class="fa-solid fa-floppy-disk mr-1.5"></i> Eksekusi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preloader Logic
        window.addEventListener('DOMContentLoaded', () => {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 500);
        });

        // Sidebar Execution Logic
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            const isClosed = sidebar.classList.contains('-translate-x-full');
            
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        // Modal Execution Logic
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const modalContent = document.getElementById('modal-content-lang');
            const body = document.querySelector('body');

            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.setAttribute('aria-hidden', 'false');
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
                    modal.setAttribute('aria-hidden', 'true');
                    body.classList.remove('modal-active');
                }, 300);
            }
        }

        // System Environment (Dark Mode) Compiler
        const themeToggleBtn  = document.getElementById('theme-toggle');
        const themeToggleIcon = document.getElementById('theme-toggle-icon');

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            if (isDark) {
                localStorage.theme = 'dark';
                themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                localStorage.theme = 'light';
                themeToggleIcon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    </script>
<!-- SweetAlert2 Neo-Brutalism -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const neoSwal = Swal.mixin({
        customClass: {
            popup: 'border-2 border-black rounded-2xl shadow-neo-lg bg-white text-black',
            title: 'font-black uppercase tracking-tight text-xl',
            confirmButton: 'bg-neo-green border-2 border-black text-black font-black uppercase rounded-lg px-6 py-2 shadow-neo-sm hover:-translate-y-1 hover:shadow-neo transition-all',
            htmlContainer: 'font-bold text-sm text-gray-700'
        },
        buttonsStyling: false
    });

    window.showCustomAlert = function(msg) {
        neoSwal.fire({ icon: 'warning', title: 'Perhatian!', text: msg });
    };

    @if(session('success'))
        neoSwal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}' });
    @endif

    @if(isset($errors) && $errors->any())
        neoSwal.fire({ icon: 'error', title: 'Oops...', text: '{{ $errors->first() }}' });
    @endif
</script>
    <x-delete-modal />
</body>
</html>



