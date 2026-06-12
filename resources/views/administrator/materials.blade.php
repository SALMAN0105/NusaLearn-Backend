<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Belajar - Command Center</title>
    
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
                        /* STANDARDIZED COLOR REGISTRY */
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

        /* ── BACKGROUND PATTERN ── */
        .dot-grid-bg {
            background-image: radial-gradient(circle, #7C3AED18 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        /* ── RENDER STAGGERING ── */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fade-up 0.4s ease-out both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }
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
                    <button onclick="toggleSidebar()" aria-label="Toggle Sidebar" class="md:hidden w-10 h-10 flex items-center justify-center bg-p-lt border-2 border-black text-p rounded-xl shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>
                    
                    <div class="hidden md:block">
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Materi Pembelajaran</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">Kelola index data dan konfigurasi modul belajar.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    
                    
                    
                    <button id="theme-toggle" aria-label="Toggle Dark Mode" class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">
                
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-5 bg-white dark:bg-[#2d2460] p-5 rounded-2xl shadow-neo border-2 border-black dark:border-p-dark fade-in fade-in-1">
                    <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-4 w-full xl:w-auto">
                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 dark:text-purple-300/50"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Query judul materi..." 
                                   class="w-full pl-11 pr-4 py-3 bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                        </div>
                        <select name="language_scope" onchange="this.form.submit()" 
                                class="w-full sm:w-auto border-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b] rounded-xl py-3 px-4 pr-10 text-sm text-black dark:text-white font-bold outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                            <option value="">Indonesia Filter (Semua)</option>
                            @foreach($languages ?? [] as $lang) 
                                <option value="{{ $lang->kode }}" {{ request('language_scope') == $lang->kode ? 'selected' : '' }}>{{ $lang->nama }}</option> 
                            @endforeach
                        </select>
                    </form>
                    
                    <button onclick="toggleModal('modal-add')" class="w-full xl:w-auto bg-p text-white px-6 py-3 rounded-xl border-2 border-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all text-sm font-black flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> tambah Materi Baru
                    </button>
                </div>

                <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in fade-in-2">
                    <div class="overflow-x-auto flex-1 font-body">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Materi Base (ID)</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Kategori</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Scope</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">level</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Kelas</th>`n                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Status AI</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Mutate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                @forelse($materials ?? [] as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            @if(isset($item->image_url) && $item->image_url)
                                                <img src="{{ asset('storage/' . $item->image_url) }}" class="w-12 h-12 rounded-xl object-cover border-2 border-black shadow-neo-sm">
                                            @else
                                                <div class="w-12 h-12 rounded-xl bg-p border-2 border-black flex items-center justify-center text-white font-black text-xl shadow-neo-sm uppercase">
                                                    {{ substr($item->judul ?? 'M', 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-extrabold text-black dark:text-white text-base">{{ $item->judul ?? 'Untitled Block' }}</div>
                                                <div class="text-[11px] font-bold text-gray-400 dark:text-purple-300/60 mt-0.5 font-mono uppercase">Pointer: #{{ $item->id ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-md text-[11px] font-black uppercase tracking-wider bg-p-xlt dark:bg-[#1e1b4b] text-black dark:text-white border-2 border-black dark:border-p-dark shadow-neo-sm">
                                            {{ $item->kategori ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[11px] font-black tracking-wider uppercase text-black bg-neo-yellow border-2 border-black px-2.5 py-1 rounded-md shadow-neo-sm">
                                            {{ ($item->kode_bahasa ?? 'global') == 'global' ? 'Indonesia' : $item->kode_bahasa }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center text-neo-yellow dark:text-yellow-400 text-sm gap-0.5 drop-shadow-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa-{{ $i <= ($item->tingkat_kesulitan ?? 1) ? 'solid' : 'regular text-gray-300 dark:text-gray-600' }} fa-star"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        @if(($item->status_ai ?? 'pending') == 'ready')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase bg-neo-green text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-check"></i> Ready</span>
                                        @elseif(($item->status_ai ?? 'pending') == 'processing')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase bg-neo-yellow text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-gear fa-spin"></i> Processing</span>
                                        @elseif(($item->status_ai ?? 'pending') == 'failed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase bg-neo-red text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-xmark"></i> Failed</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase bg-white text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-pause"></i> Standby</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="editMaterial({{ json_encode($item) }})" class="w-8 h-8 rounded-lg flex items-center justify-center bg-neo-yellow border-2 border-black text-black hover:bg-yellow-400 transition-all shadow-neo-sm" title="Edit Materi">
                                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                                            </button>
                                            
                                            <form id="delete-form-{{ $item->id }}" action="{{ route('administrator.materi.destroy', $item->id ?? 0) }}" method="POST" class="inline" onsubmit="event.preventDefault(); openDeleteModal('delete-form-{{ $item->id }}', '{{ addslashes($item->judul) }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm" title="Drop Material">
                                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                            <i class="fa-solid fa-folder-open text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Indeks materi kosong. Lakukan Tambah data.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                            <div class="px-6 py-4 border-t-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b]">
                                {{ $materials->links() }}
                            </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

        <!-- MODAL TAMBAH MATERI -->
    <div id="modal-add" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-add')"></div>
        <div class="modal-container bg-white dark:bg-[#2d2460] w-11/12 md:max-w-4xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 overflow-y-auto max-h-[90vh] transform transition-all scale-95 opacity-0">
            <div class="pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-p flex justify-between items-center sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-plus text-black text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight leading-none">Materi Baru</h3>
                        <p class="text-[11px] font-semibold text-white/80 mt-1">Buat materi baru dan unggah payload JSON.</p>
                    </div>
                </div>
                <button type="button" onclick="toggleModal('modal-add')" aria-label="Close Modal" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="px-8 py-8 bg-white dark:bg-[#2d2460]">
                <form action="{{ route('administrator.materi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Identifier (Judul Materi)</label>
                        <input name="judul" type="text" placeholder="Contoh: Logika Algoritma Dasar" required class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kategori Engine</label>
                            <select name="kategori" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="literasi">Literasi</option>
                                <option value="numerasi">Numerasi</option>
                                <option value="budaya">Budaya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Level Skalabilitas</label>
                            <select name="tingkat_kesulitan" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="1">Lvl. 1 (Sangat Dasar)</option><option value="2">Lvl. 2 (Dasar)</option><option value="3">Lvl. 3 (Menengah)</option><option value="4">Lvl. 4 (Kompleks)</option><option value="5">Lvl. 5 (Sangat Kompleks)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Target Bahasa</label>
                            <div class="relative">
                                <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                <select name="kode_bahasa" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none" required>
                                    <option value="global">Indonesia (Umum)</option>
                                    @foreach($languages ?? [] as $lang) 
                                        <option value="{{ $lang->kode }}">{{ $lang->nama }}</option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kelas Target</label>
                            <select name="kelas" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="1">Kelas 1</option><option value="2">Kelas 2</option><option value="3">Kelas 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-2">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Upload Payload (.json) - <span class="text-p lowercase font-bold">Wajib</span></label>
                        <div class="border-2 border-dashed border-black dark:border-p-dark bg-neo-cyan/20 dark:bg-p-dark/20 rounded-xl p-8 text-center hover:bg-neo-cyan/40 dark:hover:bg-p-dark/40 transition-colors relative group">
                            <div class="flex flex-col items-center justify-center pointer-events-none">
                                <div class="w-12 h-12 bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl flex items-center justify-center text-xl text-p dark:text-purple-400 mb-3 group-hover:-translate-y-1 transition-transform shadow-neo-sm">
                                    <i class="fa-solid fa-file-code"></i>
                                </div>
                                <span class="text-sm font-black text-black dark:text-white mb-1 uppercase tracking-wide">Pilih File JSON</span>
                                <span id="add_file_json_name" class="text-[11px] font-bold text-p dark:text-purple-400 font-mono">Payload berisi konten materi statis</span>
                            </div>
                            <input type="file" id="add_file_json" name="json_file" accept=".json" required class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" onchange="document.getElementById('add_file_json_name').textContent = this.files.length > 0 ? 'File: ' + this.files[0].name : 'Payload berisi konten materi statis'">
                        </div>
                    </div>
                    <div class="pt-2">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Aset Visual (Thumbnail)</label>
                        <input type="file" name="image" accept="image/*" class="block w-full text-xs font-bold text-gray-500 dark:text-purple-300/70 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-2 file:border-black file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-white file:text-black hover:file:bg-gray-100 file:transition-all file:cursor-pointer cursor-pointer border-2 border-black dark:border-p-dark rounded-xl bg-p-xlt dark:bg-[#1e1b4b] outline-none">
                    </div>
                    <div class="flex justify-end gap-3 pt-6 border-t-2 border-p-lt dark:border-p-dark mt-6">
                        <button type="button" onclick="toggleModal('modal-add')" class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-p border-2 border-black text-white rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all">Unggah Materi <i class="fa-solid fa-cloud-arrow-up ml-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<div id="modal-edit" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-edit')"></div>
        
        <div class="modal-container bg-white dark:bg-[#2d2460] w-11/12 md:max-w-3xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 overflow-y-auto max-h-[90vh] transform transition-all scale-95 opacity-0" id="edit-modal-content">
            
            <div class="pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-neo-yellow flex justify-between items-center sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-pen-to-square text-black text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-black tracking-tight leading-none">Edit Materi</h3>
                        <p class="text-[11px] font-semibold text-black/70 mt-1">Perbarui parameter materi atau ganti *payload*.</p>
                    </div>
                </div>
                <button onclick="toggleModal('modal-edit')" aria-label="Close Modal" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="px-8 py-8 bg-white dark:bg-[#2d2460]">
                <form id="edit-material-form" action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Identifier (Judul Materi)</label>
                        <input id="edit_title_indo" name="judul" type="text" placeholder="Contoh: Logika Algoritma Dasar" required
                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kategori Engine</label>
                            <select id="edit_category" name="kategori" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="literasi">Literasi</option>
                                <option value="numerasi">Numerasi</option>
                                <option value="budaya">Budaya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Level Skalabilitas</label>
                            <select id="edit_level_difficulty" name="tingkat_kesulitan" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="1">Lvl. 1 (Sangat Dasar)</option>
                                <option value="2">Lvl. 2 (Dasar)</option>
                                <option value="3">Lvl. 3 (Menengah)</option>
                                <option value="4">Lvl. 4 (Kompleks)</option>
                                <option value="5">Lvl. 5 (Sangat Kompleks)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Target Bahasa</label>
                            <select id="edit_language_code" name="kode_bahasa" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="global">Indonesia (Umum)</option>
                                @foreach($languages ?? [] as $lang) 
                                    <option value="{{ $lang->kode }}">{{ $lang->nama }}</option> 
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kelas Target</label>
                            <select id="edit_kelas" name="kelas" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="1">Kelas 1</option>
                                <option value="2">Kelas 2</option>
                                <option value="3">Kelas 3</option>
                                
                                
                                
                            </select>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Update Payload (.json) - <span class="text-p lowercase font-bold">Opsional</span></label>
                        <div class="border-2 border-dashed border-black dark:border-p-dark bg-neo-cyan/20 dark:bg-p-dark/20 rounded-xl p-8 text-center hover:bg-neo-cyan/40 dark:hover:bg-p-dark/40 transition-colors relative group">
                            <div class="flex flex-col items-center justify-center pointer-events-none">
                                <div class="w-12 h-12 bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl flex items-center justify-center text-xl text-p dark:text-purple-400 mb-3 group-hover:-translate-y-1 transition-transform shadow-neo-sm">
                                    <i class="fa-solid fa-file-code"></i>
                                </div>
                                <span class="text-sm font-black text-black dark:text-white mb-1 uppercase tracking-wide">Ganti File JSON</span>
                                <span id="edit_file_json_name" class="text-[11px] font-bold text-p dark:text-purple-400 font-mono">Biarkan kosong jika tidak ingin mengubah konten</span>
                            </div>
                            <input type="file" id="edit_file_json" name="json_file" accept=".json" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" onchange="document.getElementById('edit_file_json_name').textContent = this.files.length > 0 ? 'File: ' + this.files[0].name : 'Biarkan kosong jika tidak ingin mengubah konten'">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Aset Visual (Thumbnail)</label>
                        <div id="edit-image-preview-container" class="mb-4 hidden">
                            <img id="edit-image-preview" src="" class="w-32 h-32 object-cover rounded-xl border-2 border-black shadow-neo-sm">
                        </div>
                        <input type="file" name="image" accept="image/*" 
                               class="block w-full text-xs font-bold text-gray-500 dark:text-purple-300/70 
                               file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-2 file:border-black file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-white file:text-black hover:file:bg-gray-100 file:transition-all file:cursor-pointer cursor-pointer border-2 border-black dark:border-p-dark rounded-xl bg-p-xlt dark:bg-[#1e1b4b] outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t-2 border-p-lt dark:border-p-dark mt-6">
                        <button type="button" onclick="toggleModal('modal-edit')" class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-neo-yellow border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-yellow-400 transition-all">Simpan Perubahan <i class="fa-solid fa-save ml-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-delete" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[110]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-delete')"></div>
        <div class="modal-container bg-white dark:bg-[#2d2460] w-11/12 md:max-w-md mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-[120] transform transition-all scale-95 opacity-0">
            <div class="flex-shrink-0 pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-neo-red rounded-t-3xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-trash-can text-black text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-black tracking-tight leading-none">Hapus Materi?</h3>
                    </div>
                </div>
                <button onclick="toggleModal('modal-delete')" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <p class="text-sm font-bold text-gray-600 dark:text-gray-300">Peringatan: Penghapusan akan menghancurkan data relasional kuis. Apakah Anda yakin ingin melanjutkan?</p>
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button onclick="toggleModal('modal-delete')" class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark text-black dark:text-white rounded-xl text-sm font-black shadow-neo-sm hover:translate-y-px transition-all">
                        Batal
                    </button>
                    <button id="confirm-delete-btn" class="w-full sm:w-auto px-6 py-2.5 bg-neo-red border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 transition-all">
                        Ya, Hapus Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preloader Logic O(1)
        window.addEventListener('load', () => {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 500);
        });

        // Sidebar Execution Logic (Optimized for Mobile)
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

        // Modal Execution Logic (State Machine)
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const content = modal.querySelector('.modal-container');
            const body = document.querySelector('body');
            
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.setAttribute('aria-hidden', 'false');
                body.classList.add('modal-active');
                setTimeout(() => { 
                    content.classList.remove('scale-95', 'opacity-0'); 
                    content.classList.add('scale-100', 'opacity-100'); 
                }, 10);
            } else {
                content.classList.remove('scale-100', 'opacity-100'); 
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => { 
                    modal.classList.add('opacity-0', 'pointer-events-none'); 
                    modal.setAttribute('aria-hidden', 'true');
                    body.classList.remove('modal-active'); 
                }, 300);
            }
        }

        // System Environment (Dark Mode) Compiler
        const themeToggleBtn = document.getElementById('theme-toggle');
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

        // ── Material Management Logic ──────────────────────────────────────────
        function editMaterial(material) {
            const form = document.getElementById('edit-material-form');
            form.action = `{{ url('admin/administrator/materi') }}/${material.id}`;
            
            document.getElementById('edit_title_indo').value = material.judul;
            document.getElementById('edit_category').value = material.kategori;
            document.getElementById('edit_level_difficulty').value = material.tingkat_kesulitan;
            document.getElementById('edit_language_code').value = material.kode_bahasa;
            if(document.getElementById('edit_kelas')) document.getElementById('edit_kelas').value = material.kelas;
            
            const previewContainer = document.getElementById('edit-image-preview-container');
            const previewImg = document.getElementById('edit-image-preview');
            
            if (material.image_url) {
                previewImg.src = `/storage/${material.image_url}`;
                previewContainer.classList.remove('hidden');
            } else {
                previewContainer.classList.add('hidden');
            }
            
            toggleModal('modal-edit');
        }

        let deleteId = null;
        function confirmDelete(id) {
            deleteId = id;
            toggleModal('modal-delete');
        }

        document.getElementById('confirm-delete-btn').addEventListener('click', () => {
            if (deleteId) {
                document.getElementById('delete-form-' + deleteId).submit();
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

    @if($errors->any())
        neoSwal.fire({ icon: 'error', title: 'Oops...', text: '{{ $errors->first() }}' });
    @endif
</script>
    <x-delete-modal />
</body>
</html>





