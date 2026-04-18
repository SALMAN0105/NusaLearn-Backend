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
        #edit-modal, #custom-alert {
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        #edit-modal.hidden, #custom-alert.hidden {
            opacity: 0; visibility: hidden; pointer-events: none;
        }
        #edit-modal:not(.hidden), #custom-alert:not(.hidden) {
            opacity: 1; visibility: visible;
        }

        /* ── CUSTOM ALERT ANIMATION ── */
        @keyframes alert-pop {
            0%   { transform: scale(0.85) translateY(-20px); opacity: 0; }
            60%  { transform: scale(1.04) translateY(0); opacity: 1; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }
        .alert-box { animation: alert-pop 0.35s cubic-bezier(.34,1.56,.64,1) both; }

        /* ── DRAG OVER FILE INPUT ── */
        .drag-over {
            border-color: #ef4444 !important;
            background-color: #fee2e2 !important;
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

    {{-- ══════════════════════════════════════════════
         CUSTOM ALERT — FORMAT FILE SALAH
    ══════════════════════════════════════════════ --}}
    <div id="custom-alert" class="hidden fixed inset-0 z-[9000] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="alert-box bg-white dark:bg-[#2d2460] border-2 border-black dark:border-red-400 rounded-2xl shadow-neo-lg w-full max-w-sm p-6 relative">
            {{-- Icon --}}
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-neo-red border-2 border-black rounded-2xl shadow-neo-sm">
                <i class="fa-solid fa-file-circle-xmark text-3xl text-red-600"></i>
            </div>
            {{-- Title --}}
            <h3 class="text-center text-xl font-black text-black dark:text-white mb-1">Format File Salah</h3>
            {{-- Message --}}
            <p id="custom-alert-msg" class="text-center text-sm font-semibold text-gray-500 dark:text-purple-300/70 mb-6">
                File yang diunggah tidak sesuai format yang diizinkan.
            </p>
            {{-- Close Button --}}
            <button onclick="closeCustomAlert()" class="w-full bg-red-500 hover:bg-red-600 text-white border-2 border-black rounded-xl shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all py-3 text-sm font-black uppercase tracking-wide flex items-center justify-center gap-2">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         EDIT MODAL
    ══════════════════════════════════════════════ --}}
    <div id="edit-modal" class="hidden fixed inset-0 z-[8000] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo-lg w-full max-w-md overflow-hidden">
            {{-- Header --}}
            <div class="p-5 border-b-2 border-black dark:border-p-dark bg-p text-white flex items-center justify-between">
                <h3 class="text-lg font-black flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Wilayah
                </h3>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/40 rounded-lg border-2 border-white/30 transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            {{-- Form --}}
            <form id="edit-form" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Identifier Kode Pos</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelopes-bulk absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                        <input id="edit-postal" name="postal_code" type="text" placeholder="Contoh: 93572" required
                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Area Entitas (Kec/Desa)</label>
                    <div class="relative">
                        <i class="fa-solid fa-map-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                        <input id="edit-district" name="district_name" type="text" placeholder="Contoh: Kec. Ueesi" required
                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Indeks Bahasa Dominan</label>
                    <div class="relative">
                        <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                        <select id="edit-language" name="language_code" required
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                            @foreach($languages ?? [] as $lang)
                                <option value="{{ $lang->code }}">{{ $lang->name }}</option>
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
                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mb-2 uppercase tracking-widest">General</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-chart-pie w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Dashboard</span>
                </a>

                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mt-6 mb-2 uppercase tracking-widest">Konten</p>
                <a href="{{ route('materials.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-book-open w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Materi Belajar</span>
                </a>
                <a href="{{ route('converter.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-file-export w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Kelola File</span>
                </a>
                <a href="{{ route('questions.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-clipboard-question w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Bank Soal</span>
                </a>

                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mt-6 mb-2 uppercase tracking-widest">Master Data</p>
                <a href="{{ route('languages.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-language w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Bahasa Daerah</span>
                </a>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-users w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Data Siswa</span>
                </a>
                
                <a href="#" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
                    <i class="fa-solid fa-map-location-dot w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Wilayah</span>
                </a>
            </nav>

            <div class="border-t-2 border-black dark:border-p-dark p-4 bg-white dark:bg-[#2d2460]">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=7C3AED&color=fff&bold=true"
                         alt="Avatar Admin" class="w-10 h-10 rounded-full border-2 border-black dark:border-p-dark shadow-neo-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-black dark:text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
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
                    @if(session('success'))
                        <div class="hidden sm:flex bg-neo-green border-2 border-black text-black px-4 py-2 rounded-xl items-center gap-2 shadow-neo-sm text-sm font-bold fade-in" role="alert">
                            <i class="fa-solid fa-circle-check"></i> <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    <button id="theme-toggle" aria-label="Toggle Dark Mode" class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10 items-start">
                    
                    {{-- ── FORM REGISTRASI ── --}}
                    <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden lg:sticky lg:top-0 fade-in fade-in-1">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-p text-white">
                            <h3 class="text-lg font-black flex items-center gap-2">
                                <i class="fa-solid fa-location-crosshairs"></i> Registrasi Wilayah
                            </h3>
                            <p class="text-[11px] font-semibold text-white/70 mt-1">Injeksi data teritorial untuk pemetaan dialek.</p>
                        </div>
                        
                        <div class="p-6">
                            {{-- Zona Drop File (trigger custom alert jika format salah) --}}
                            <div id="drop-zone"
                                 class="mb-5 border-2 border-dashed border-p-mid dark:border-p-dark rounded-xl p-5 text-center transition-all cursor-default select-none"
                                 ondragover="handleDragOver(event)"
                                 ondragleave="handleDragLeave(event)"
                                 ondrop="handleDrop(event)">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-p-mid mb-2 block"></i>
                                <p class="text-xs font-bold text-gray-400 dark:text-purple-300/60">Drop file CSV/JSON untuk import batch</p>
                                <p class="text-[10px] font-semibold text-gray-300 dark:text-purple-300/40 mt-1">Format: .csv atau .json saja</p>
                                <input id="file-input" type="file" accept=".csv,.json" class="hidden" onchange="handleFileSelect(event)">
                                <button type="button" onclick="document.getElementById('file-input').click()"
                                        class="mt-3 px-4 py-1.5 bg-p-lt border-2 border-black dark:border-p-dark rounded-lg text-xs font-black text-p-dark dark:text-purple-200 shadow-neo-sm hover:bg-p hover:text-white transition-all">
                                    Pilih File
                                </button>
                                <p id="file-name-display" class="mt-2 text-xs font-bold text-p-dark dark:text-purple-200 hidden"></p>
                            </div>

                            <form action="{{ route('regions.store') }}" method="POST" class="space-y-5" id="region-form">
                                @csrf
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Identifier Kode Pos</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-envelopes-bulk absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <input name="postal_code" type="text" placeholder="Contoh: 93572" required
                                               value="{{ old('postal_code') }}"
                                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                                    </div>
                                    @error('postal_code')
                                        <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Area Entitas (Kec/Desa)</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-map-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <input name="district_name" type="text" placeholder="Contoh: Kec. Ueesi" required
                                               value="{{ old('district_name') }}"
                                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                                    </div>
                                    @error('district_name')
                                        <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Indeks Bahasa Dominan</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <select name="language_code" required
                                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                            @foreach($languages ?? [] as $lang)
                                                <option value="{{ $lang->code }}">{{ $lang->name }}</option>
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

                    {{-- ── TABEL WILAYAH ── --}}
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
                                                {{ $region->postal_code }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="font-bold text-black dark:text-white flex items-center gap-2 text-base">
                                                <i class="fa-solid fa-map-pin text-p-mid text-sm"></i>
                                                {{ $region->district_name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black tracking-wider uppercase bg-p-lt dark:bg-p-dark/50 text-p-dark dark:text-purple-200 border-2 border-black dark:border-p-dark shadow-neo-sm">
                                                <i class="fa-solid fa-language"></i> {{ $region->language_code }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                {{-- Edit Button --}}
                                                <button type="button"
                                                        onclick="openEditModal({{ $region->id }}, '{{ addslashes($region->postal_code) }}', '{{ addslashes($region->district_name) }}', '{{ addslashes($region->language_code) }}')"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-cyan transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none" title="Edit Region">
                                                    <i class="fa-solid fa-pen text-sm"></i>
                                                </button>
                                                {{-- Delete Button --}}
                                                <form action="{{ route('regions.destroy', $region->id) }}" method="POST" class="inline-block" onsubmit="return confirmDelete(event)">
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

                        {{-- ── PAGINATION ── --}}
                        @if($regions->hasPages())
                        <div class="p-5 border-t-2 border-black dark:border-p-dark bg-white dark:bg-[#2d2460] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs font-bold text-gray-400 dark:text-purple-300/60">
                                Menampilkan <span class="text-black dark:text-white font-black">{{ $regions->firstItem() }}–{{ $regions->lastItem() }}</span>
                                dari <span class="text-black dark:text-white font-black">{{ $regions->total() }}</span> node
                            </p>
                            <div class="flex items-center gap-1.5">
                                {{-- Prev --}}
                                @if($regions->onFirstPage())
                                    <button class="page-btn" disabled><i class="fa-solid fa-chevron-left text-xs"></i></button>
                                @else
                                    <a href="{{ $regions->previousPageUrl() }}" class="page-btn"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                                @endif

                                {{-- Page Numbers --}}
                                @foreach($regions->getUrlRange(max(1, $regions->currentPage() - 2), min($regions->lastPage(), $regions->currentPage() + 2)) as $page => $url)
                                    @if($page == $regions->currentPage())
                                        <span class="page-btn page-active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if($regions->hasMorePages())
                                    <a href="{{ $regions->nextPageUrl() }}" class="page-btn"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                                @else
                                    <button class="page-btn" disabled><i class="fa-solid fa-chevron-right text-xs"></i></button>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // ── PRELOADER ──
        window.addEventListener('load', () => {
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
        // CUSTOM ALERT
        // ════════════════════════════════════════════
        function showCustomAlert(message) {
            const alertEl = document.getElementById('custom-alert');
            const msgEl   = document.getElementById('custom-alert-msg');
            if (message) msgEl.textContent = message;
            alertEl.classList.remove('hidden');
            document.body.classList.add('modal-active');
        }
        function closeCustomAlert() {
            document.getElementById('custom-alert').classList.add('hidden');
            document.body.classList.remove('modal-active');
        }
        // Close on backdrop click
        document.getElementById('custom-alert').addEventListener('click', function(e) {
            if (e.target === this) closeCustomAlert();
        });

        // ════════════════════════════════════════════
        // DRAG & DROP — FORMAT VALIDATION
        // ════════════════════════════════════════════
        const ALLOWED_EXTS = ['.csv', '.json'];
        const dropZone = document.getElementById('drop-zone');

        function isAllowedFile(file) {
            const name = file.name.toLowerCase();
            return ALLOWED_EXTS.some(ext => name.endsWith(ext));
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('drag-over');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (!files || files.length === 0) return;

            const file = files[0];
            if (!isAllowedFile(file)) {
                showCustomAlert(`File "${file.name}" tidak didukung. Hanya format .csv atau .json yang diizinkan.`);
                return;
            }
            // If valid, show file name
            showFileName(file.name);
        }

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!isAllowedFile(file)) {
                showCustomAlert(`File "${file.name}" tidak didukung. Hanya format .csv atau .json yang diizinkan.`);
                e.target.value = '';
                return;
            }
            showFileName(file.name);
        }

        function showFileName(name) {
            const display = document.getElementById('file-name-display');
            display.textContent = '✓ ' + name;
            display.classList.remove('hidden');
        }

        // ════════════════════════════════════════════
        // EXECUTE BUTTON — cek jika ada file tidak valid
        // (misal user drop file sembarang lalu tekan execute)
        // ════════════════════════════════════════════
        document.getElementById('region-form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file-input');
            if (fileInput.files.length > 0 && !isAllowedFile(fileInput.files[0])) {
                e.preventDefault();
                showCustomAlert(`Format file "${fileInput.files[0].name}" salah. Gunakan .csv atau .json.`);
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
            const baseUrl = "{{ url('admin/regions') }}";
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
        let pendingDeleteForm = null;

        function confirmDelete(e) {
            e.preventDefault();
            pendingDeleteForm = e.target.closest('form');
            showDeleteConfirm();
            return false;
        }

        function showDeleteConfirm() {
            // Reuse custom alert as confirm dialog
            const alertEl = document.getElementById('custom-alert');
            const msgEl   = document.getElementById('custom-alert-msg');
            msgEl.textContent = 'Mencabut izin wilayah ini akan menghapus akses siswa di kode pos tersebut. Lanjutkan?';

            // Swap close button to confirm/cancel
            const closeBtn = alertEl.querySelector('button');
            closeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Batal';
            closeBtn.onclick = () => {
                pendingDeleteForm = null;
                resetAlertButton();
                closeCustomAlert();
            };

            // Add confirm button if not already
            let confirmBtn = document.getElementById('confirm-delete-btn');
            if (!confirmBtn) {
                confirmBtn = document.createElement('button');
                confirmBtn.id = 'confirm-delete-btn';
                confirmBtn.type = 'button';
                confirmBtn.className = 'w-full mt-2 bg-p hover:bg-p-dark text-white border-2 border-black rounded-xl shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all py-3 text-sm font-black uppercase tracking-wide flex items-center justify-center gap-2';
                confirmBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Ya, Hapus';
                closeBtn.parentNode.appendChild(confirmBtn);
            }
            confirmBtn.style.display = 'flex';
            confirmBtn.onclick = () => {
                if (pendingDeleteForm) pendingDeleteForm.submit();
                resetAlertButton();
                closeCustomAlert();
            };

            // Show alert with icon swap
            alertEl.querySelector('i.fa-file-circle-xmark').className = 'fa-solid fa-triangle-exclamation text-3xl text-amber-500';
            alertEl.querySelector('h3').textContent = 'Konfirmasi Hapus';
            alertEl.querySelector('.bg-neo-red').style.backgroundColor = '#FDE68A';
            alertEl.classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function resetAlertButton() {
            const alertEl = document.getElementById('custom-alert');
            const closeBtn = alertEl.querySelector('button:not(#confirm-delete-btn)');
            closeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Tutup';
            closeBtn.onclick = closeCustomAlert;
            const confirmBtn = document.getElementById('confirm-delete-btn');
            if (confirmBtn) confirmBtn.style.display = 'none';
            // Reset icon & title
            alertEl.querySelector('i').className = 'fa-solid fa-file-circle-xmark text-3xl text-red-600';
            alertEl.querySelector('h3').textContent = 'Format File Salah';
            alertEl.querySelector('div.flex.items-center.justify-center').style.backgroundColor = '';
        }
    </script>
</body>
</html>