<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Wilayah - Command Center</title>
    
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

        /* ── MODAL ── */
        #edit-modal {
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        #edit-modal.hidden {
            opacity: 0; visibility: hidden; pointer-events: none;
        }
        #edit-modal:not(.hidden) {
            opacity: 1; visibility: visible;
        }

        /* ── PAGINATION ── */
        .page-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 8px;
            border: 2px solid black; border-radius: 10px;
            font-size: 13px; font-weight: 800;
            background: white; color: black;
            box-shadow: 2px 2px 0 rgba(0,0,0,1);
            transition: all 0.15s;
        }
        html.dark .page-btn {
            background: #2d2460; color: #e9d5ff; border-color: #4C1D95;
            box-shadow: 2px 2px 0 #4C1D95;
        }
        .page-btn:hover:not(.page-active):not(:disabled) {
            background: #EDE9FE; color: #7C3AED;
            transform: translateY(-1px);
        }
        .page-btn.page-active {
            background: #7C3AED; color: #fff;
            border-color: black;
        }
        .page-btn:disabled { opacity: 0.4; cursor: not-allowed; box-shadow: none; }
    </style>
</head>
<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300 relative">

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>


    
    <div id="edit-modal" class="hidden fixed inset-0 z-[8000] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo-lg w-full max-w-md overflow-hidden">
            
            <div class="p-5 border-b-2 border-black dark:border-p-dark bg-p text-white flex items-center justify-between">
                <h3 class="text-lg font-black flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Wilayah
                </h3>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/40 rounded-lg border-2 border-white/30 transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            
            <form id="edit-form" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Identifier Kode Pos</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelopes-bulk absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                        <select id="edit-postal" name="kode_pos" required
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all appearance-none cursor-pointer">
                            <option value="">Pilih Kode Pos Sekolah...</option>
                            @foreach($allSchoolPostalCodes ?? [] as $school)
                                <option value="{{ $school->kode_pos }}">{{ $school->kode_pos }} - {{ $school->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Area Entitas (Kec/Desa)</label>
                    <div class="relative">
                        <i class="fa-solid fa-map-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                        <input id="edit-district" name="nama_kecamatan" type="text" placeholder="Contoh: Kec. Ueesi" required
                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Indeks Bahasa Dominan</label>
                    <div class="relative">
                        <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                        <select id="edit-language" name="kode_bahasa" required
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                            @foreach($languages ?? [] as $lang)
                                <option value="{{ $lang->kode }}">{{ $lang->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t-2 border-p-lt dark:border-p-dark flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-white dark:bg-[#1e1b4b] text-black dark:text-white border-2 border-black dark:border-p-dark rounded-xl shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all py-3 text-sm font-black uppercase tracking-wide">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-p hover:bg-p-dark text-white border-2 border-black rounded-xl shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all flex justify-center items-center gap-2 py-3 text-sm font-black uppercase tracking-wide">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
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
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-house text-lg"></i>
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
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center border-2 border-transparent">
                        <i class="fa-solid fa-map-location-dot"></i>
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
                        <p class="text-sm font-black text-black dark:text-white truncate">{{ Auth::user()->nama ?? 'Admin' }}</p>
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
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Geofencing & Kode Pos</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">Pemetaan teritorial linguistik sistem.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    
                    
                    <button id="theme-toggle" aria-label="Toggle Dark Mode" class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10 items-start">
                    
                    
                    <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden lg:sticky lg:top-0 fade-in fade-in-1">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-p text-white">
                            <h3 class="text-lg font-black flex items-center gap-2">
                                <i class="fa-solid fa-location-crosshairs"></i> Registrasi Wilayah
                            </h3>
                            <p class="text-[11px] font-semibold text-white/70 mt-1">Injeksi data teritorial untuk pemetaan dialek.</p>
                        </div>
                        
                        <div class="p-6">
                            <form action="{{ route('administrator.wilayah.store') }}" method="POST" class="space-y-5" id="region-form">
                                @csrf
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Identifier Kode Pos</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-envelopes-bulk absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <select name="kode_pos"
                                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all appearance-none cursor-pointer">
                                            <option value="">Pilih Kode Pos Sekolah...</option>
                                            @foreach($allSchoolPostalCodes ?? [] as $school)
                                                <option value="{{ $school->kode_pos }}" {{ old('kode_pos') == $school->kode_pos ? 'selected' : '' }}>
                                                    {{ $school->kode_pos }} - {{ $school->nama }}


                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <?php $__errorArgs = ['kode_pos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Area Entitas (Kec/Desa)</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-map-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <input name="nama_kecamatan" type="text" placeholder="Contoh: Kec. Ueesi"
                                               value="{{ old('nama_kecamatan') }}"
                                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                                    </div>
                                    <?php $__errorArgs = ['nama_kecamatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Indeks Bahasa Dominan</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <select name="kode_bahasa"
                                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                            @foreach($languages ?? [] as $lang)
                                                <option value="{{ $lang->kode }}">{{ $lang->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="pt-6 border-t-2 border-p-lt dark:border-p-dark mt-6">
                                    <button type="submit" id="execute-btn"
                                            class="w-full bg-p hover:bg-p-dark text-white border-2 border-black rounded-xl shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all flex justify-center items-center gap-2 py-3 text-sm font-black uppercase tracking-wide">
                                        <i class="fa-solid fa-satellite-dish"></i> Execute Pemetaan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-2 bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in fade-in-2">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-white dark:bg-[#2d2460] flex justify-between items-center">
                            <h2 class="text-lg font-black text-black dark:text-white">Log Wilayah Terdistribusi</h2>
                            <span class="bg-p-lt dark:bg-p-dark/50 text-p-dark dark:text-purple-200 text-[10px] font-black px-2.5 py-1 rounded-md border-2 border-black dark:border-p-dark shadow-neo-sm uppercase">
                                {{ $regions->total() }} Nodes
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto flex-1 font-body">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead>
                                    <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Postal Checksum</th>
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Area Registry</th>
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Engine Dialek</th>
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Mutate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                    @forelse($regions as $region)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                        <td class="px-6 py-5">
                                            <span class="font-mono text-sm font-black text-black bg-neo-yellow border-2 border-black px-2.5 py-1 rounded-md shadow-neo-sm">
                                                {{ $region->kode_pos }}


                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
    <div class="font-bold text-black dark:text-white flex flex-col gap-1 text-base">
        <div>
            <i class="fa-solid fa-map-pin text-p-mid text-sm mr-2"></i>
            {{ $region->nama_kecamatan }}
        </div>
        <div class="text-[10px] font-mono text-gray-500 dark:text-gray-400 mt-1">
            <i class="fa-solid fa-school text-xs mr-1"></i>
            {{ $region->sekolah->pluck('nama')->join(', ') ?: 'Belum ada sekolah terdaftar' }}
        </div>
    </div>
</td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black tracking-wider uppercase bg-p-lt dark:bg-p-dark/50 text-p-dark dark:text-purple-200 border-2 border-black dark:border-p-dark shadow-neo-sm">
                                                <i class="fa-solid fa-language"></i> {{ $region->kode_bahasa }}


                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                
                                                <button type="button"
                                                        onclick="openEditModal({{ $region->id }}, '{{ addslashes($region->kode_pos) }}', '{{ addslashes($region->nama_kecamatan) }}', '{{ addslashes($region->kode_bahasa) }}')"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-cyan transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none" title="Edit Region">
                                                    <i class="fa-solid fa-pen text-sm"></i>
                                                </button>
                                                
                                                <form id="delete-form-{{ $region->id }}" action="{{ route('administrator.wilayah.destroy', $region->id) }}" method="POST" class="inline-block" onsubmit="event.preventDefault(); openDeleteModal('delete-form-{{ $region->id }}', '{{ addslashes($region->nama_kecamatan) }}');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none" title="Drop Region">
                                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                                <i class="fa-solid fa-satellite-dish text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Peta wilayah kosong. Lakukan injeksi area baru.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        
                        @if($regions->hasPages())
                        <div class="px-6 py-4 border-t-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b]">
                            {{ $regions->links() }}
                        </div>
                        @endif

                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // ── PRELOADER ──
        window.addEventListener('DOMContentLoaded', () => {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 500);
        });

        // ── SIDEBAR ──
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

        // ── DARK MODE ──
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



        // ════════════════════════════════════════════
        // EDIT MODAL
        // ════════════════════════════════════════════
        function openEditModal(id, postalCode, districtName, languageCode) {
            document.getElementById('edit-postal').value   = postalCode;
            document.getElementById('edit-district').value = districtName;
            document.getElementById('edit-language').value = languageCode;

            // Set form action dynamically
            const baseUrl = '{{ rtrim(route("administrator.wilayah.index"), "/") }}';
            document.getElementById('edit-form').action = baseUrl + '/' + id;

            document.getElementById('edit-modal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
            document.body.classList.remove('modal-active');
        }

        // Close edit modal on backdrop click
        document.getElementById('edit-modal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeCustomAlert();
            }
        });

        // ════════════════════════════════════════════
                
        // CONFIRM DELETE (custom — bukan browser confirm)
        // ════════════════════════════════════════════

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
