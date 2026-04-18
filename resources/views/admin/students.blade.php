<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Command Center</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        body: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        'p':          '#7C3AED',
                        'p-mid':      '#8B5CF6',
                        'p-lt':       '#EDE9FE',
                        'p-dark':     '#4C1D95',
                        'p-xlt':      '#F5F3FF',
                        'neo-bg':     '#F5F3FF',
                        'neo-green':  '#A7F3D0',
                        'neo-cyan':   '#A5F3FC',
                        'neo-red':    '#FECDD3',
                        'neo-yellow': '#FDE047',
                        'neo-coral':  '#FFB8A3',
                        'neo-pink':   '#F9A8D4',
                    },
                    boxShadow: {
                        'neo':    '4px 4px 0px 0px rgba(0,0,0,1)',
                        'neo-sm': '2px 2px 0px 0px rgba(0,0,0,1)',
                        'neo-lg': '6px 6px 0px 0px rgba(0,0,0,1)',
                        'neo-p':  '4px 4px 0px 0px #4C1D95',
                        'neo-p-sm':'2px 2px 0px 0px #4C1D95',
                    }
                }
            }
        }
    </script>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

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

        .nav-active {
            background: #7C3AED !important; color: #fff !important;
            border-color: #0A0A0A !important; box-shadow: 2px 2px 0 #0A0A0A;
        }
        .nav-active i { color: #fff !important; }

        .dot-grid-bg {
            background-image: radial-gradient(circle, #7C3AED18 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in   { animation: fade-up 0.4s ease-out both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }

        /* ═══════ MODAL — fixed z-index & pointer-events ═══════ */
        /* Wrapper: hidden by default, pointer-events none so nothing is blocked */
        #modal-wrapper {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            pointer-events: none; /* ← KEY: tidak blokir klik saat tersembunyi */
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #modal-wrapper.active {
            pointer-events: auto; /* ← aktifkan saat modal terbuka */
            opacity: 1;
        }

        /* Backdrop di dalam wrapper */
        #modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            cursor: pointer;
        }

        /* Panel modal */
        #modal-content {
            position: relative;
            z-index: 1;
            max-width: 780px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s ease;
        }
        #modal-wrapper.active #modal-content {
            transform: translateY(0) scale(1);
        }
        #modal-content::-webkit-scrollbar { width: 6px; }
        #modal-content::-webkit-scrollbar-track { background: transparent; }
        #modal-content::-webkit-scrollbar-thumb { background: #7C3AED; border-radius: 10px; }

        /* Stat cards */
        .stat-card {
            border: 2px solid black;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 3px 3px 0 rgba(0,0,0,1);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            transform: translate(-1px,-1px);
            box-shadow: 4px 4px 0 rgba(0,0,0,1);
        }

        .chart-wrapper { position: relative; height: 220px; }

        .accuracy-bar-track {
            height: 10px; background: #EDE9FE;
            border-radius: 999px; border: 2px solid black; overflow: hidden;
        }
        .accuracy-bar-fill {
            height: 100%; border-radius: 999px; transition: width 1s ease;
        }

        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position:  400px 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, #EDE9FE 25%, #F5F3FF 50%, #EDE9FE 75%);
            background-size: 400px 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300">

    <!-- PRELOADER -->
    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>

    <!-- SIDEBAR OVERLAY (mobile) -->
    <div id="sidebar-overlay"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden opacity-0 transition-opacity duration-300"
         onclick="toggleSidebar()"></div>

    <!-- ═══════════════════════ PROGRESS MODAL ═══════════════════════ -->
    <div id="modal-wrapper">
        <!-- Backdrop: klik untuk tutup -->
        <div id="modal-backdrop" onclick="closeProgressModal()"></div>

        <!-- Panel -->
        <div id="modal-content" class="bg-white dark:bg-[#2d2460] rounded-2xl border-2 border-black dark:border-p-dark shadow-neo-lg">

            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b-2 border-black dark:border-p-dark sticky top-0 bg-white dark:bg-[#2d2460] rounded-t-2xl z-10">
                <div class="flex items-center gap-4">
                    <div id="modal-avatar"
                         class="w-12 h-12 rounded-xl bg-p border-2 border-black flex items-center justify-center text-white font-black text-xl shadow-neo-sm">
                        ?
                    </div>
                    <div>
                        <h2 id="modal-student-name" class="text-lg font-black text-black dark:text-white">Memuat Data...</h2>
                        <p  id="modal-student-meta" class="text-xs text-gray-400 dark:text-purple-300/60 font-semibold mt-0.5"></p>
                    </div>
                </div>
                <button onclick="closeProgressModal()"
                        class="w-10 h-10 flex items-center justify-center bg-neo-red border-2 border-black rounded-xl shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                    <i class="fa-solid fa-xmark text-black text-lg"></i>
                </button>
            </div>

            <!-- Loading shimmer -->
            <div id="modal-loading" class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div class="shimmer h-20"></div>
                    <div class="shimmer h-20"></div>
                    <div class="shimmer h-20"></div>
                </div>
                <div class="shimmer h-56"></div>
                <div class="shimmer h-32"></div>
            </div>

            <!-- Body (tampil setelah data ready) -->
            <div id="modal-body" class="p-6 space-y-6 hidden">

                <!-- Stat Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="stat-card bg-p-xlt dark:bg-[#1e1b4b]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-purple-300/60 mb-1">Total Jawaban</p>
                        <p id="stat-total" class="text-3xl font-black text-p dark:text-p-mid">—</p>
                        <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mt-1">soal dikerjakan</p>
                    </div>
                    <div class="stat-card bg-neo-green/40 dark:bg-[#1e1b4b]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-purple-300/60 mb-1">Jawaban Benar</p>
                        <p id="stat-correct" class="text-3xl font-black text-green-600 dark:text-neo-green">—</p>
                        <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mt-1">benar</p>
                    </div>
                    <div class="stat-card bg-neo-red/40 dark:bg-[#1e1b4b]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-purple-300/60 mb-1">Jawaban Salah</p>
                        <p id="stat-wrong" class="text-3xl font-black text-red-500 dark:text-neo-red">—</p>
                        <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mt-1">salah</p>
                    </div>
                    <div class="stat-card bg-neo-yellow/40 dark:bg-[#1e1b4b]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-purple-300/60 mb-1">Rata-rata Waktu</p>
                        <p id="stat-avgtime" class="text-3xl font-black text-yellow-600 dark:text-neo-yellow">—</p>
                        <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mt-1">detik / soal</p>
                    </div>
                </div>

                <!-- Accuracy Bar -->
                <div class="bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl p-5 shadow-neo-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-bullseye text-p"></i>
                            <span class="text-sm font-black text-black dark:text-white uppercase tracking-wider">Akurasi Keseluruhan</span>
                        </div>
                        <span id="stat-accuracy" class="text-xl font-black text-p dark:text-p-mid">—%</span>
                    </div>
                    <div class="accuracy-bar-track">
                        <div id="accuracy-fill" class="accuracy-bar-fill bg-p" style="width:0%"></div>
                    </div>
                    <p id="accuracy-label" class="text-[11px] font-bold text-gray-400 dark:text-purple-300/60 mt-2"></p>
                </div>

                <!-- Bar Chart -->
                <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl overflow-hidden shadow-neo-sm">
                    <div class="px-5 py-4 border-b-2 border-black dark:border-p-dark bg-p-xlt dark:bg-p-dark/40 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-p"></i>
                            <span class="text-sm font-black text-black dark:text-white uppercase tracking-wider">Progres Belajar (30 Hari Terakhir)</span>
                        </div>
                        <div class="flex items-center gap-3 text-[10px] font-bold uppercase tracking-wider">
                            <span class="flex items-center gap-1">
                                <span class="inline-block w-3 h-3 rounded-sm bg-neo-green border border-black"></span>Benar
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="inline-block w-3 h-3 rounded-sm bg-neo-red border border-black"></span>Salah
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <div id="no-chart-data" class="hidden text-center py-12">
                            <i class="fa-solid fa-chart-bar text-4xl text-gray-300 dark:text-p-dark mb-3"></i>
                            <p class="text-sm font-bold text-gray-400 dark:text-purple-300/50">Belum ada data aktivitas belajar</p>
                        </div>
                        <div class="chart-wrapper" id="chart-container">
                            <canvas id="progress-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Weekly Table -->
                <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl overflow-hidden shadow-neo-sm">
                    <div class="px-5 py-4 border-b-2 border-black dark:border-p-dark bg-p-xlt dark:bg-p-dark/40">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar-week text-p"></i>
                            <span class="text-sm font-black text-black dark:text-white uppercase tracking-wider">Ringkasan Performa Mingguan</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm font-body">
                            <thead>
                                <tr class="bg-p-xlt dark:bg-[#1e1b4b] border-b-2 border-black dark:border-p-dark text-[11px] uppercase font-black text-p-dark dark:text-purple-300 tracking-wider">
                                    <th class="px-5 py-3 text-left">Minggu ke-</th>
                                    <th class="px-5 py-3 text-center">Total Soal</th>
                                    <th class="px-5 py-3 text-center">Benar</th>
                                    <th class="px-5 py-3 text-center">Salah</th>
                                    <th class="px-5 py-3 text-center">Akurasi</th>
                                    <th class="px-5 py-3 text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody id="weekly-table-body" class="divide-y-2 divide-p-lt dark:divide-p-dark/30 font-bold">
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-400 dark:text-purple-300/50 text-xs font-bold italic">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /modal-body -->
        </div><!-- /modal-content -->
    </div><!-- /modal-wrapper -->

    <!-- ═══════════════════════ MAIN LAYOUT ═══════════════════════ -->
    <div class="flex h-screen overflow-hidden p-2 md:p-4 gap-4">

        <!-- SIDEBAR -->
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
                <a href="#" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
                    <i class="fa-solid fa-users w-5 text-center flex-shrink-0"></i>
                    <span>Data Siswa</span>
                </a>
                <a href="{{ route('regions.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-map-location-dot w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Wilayah</span>
                </a>
            </nav>

            <div class="border-t-2 border-black dark:border-p-dark p-4 bg-white dark:bg-[#2d2460]">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=7C3AED&color=fff&bold=true"
                         alt="Avatar" class="w-10 h-10 rounded-full border-2 border-black dark:border-p-dark shadow-neo-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-black dark:text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                        <p class="text-xs text-gray-400 font-semibold truncate">Sistem Inti Laravel</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-9 h-9 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 hover:bg-neo-red hover:text-black rounded-lg transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none flex-shrink-0">
                            <i class="fa-solid fa-power-off text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN PANEL -->
        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-[#241f5c] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo">

            <!-- Header -->
            <header class="h-20 border-b-2 border-black dark:border-p-dark flex items-center justify-between px-6 lg:px-10 z-20 sticky top-0 bg-white dark:bg-[#241f5c] rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()"
                            class="md:hidden w-10 h-10 flex items-center justify-center bg-p-lt border-2 border-black text-p rounded-xl shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>
                    <div class="hidden md:block">
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Direktori Siswa</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">Sistem manajemen *node* pengguna aplikasi.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if(session('success'))
                        <div class="hidden sm:flex bg-neo-green border-2 border-black text-black px-4 py-2 rounded-xl items-center gap-2 shadow-neo-sm text-sm font-bold fade-in" role="alert">
                            <i class="fa-solid fa-circle-check"></i> <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    <button id="theme-toggle"
                            class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">

                <!-- Toolbar -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-5 bg-white dark:bg-[#2d2460] p-6 rounded-2xl shadow-neo border-2 border-black dark:border-p-dark fade-in fade-in-1">
                    <div class="w-full md:w-1/2">
                        <h2 class="text-lg font-black text-black dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-users-viewfinder text-p"></i> Manajemen Pengguna
                        </h2>
                        <p class="text-[11px] font-bold text-gray-500 dark:text-purple-300/60 mt-1 uppercase tracking-wider">Monitor status entitas siswa terdaftar</p>
                    </div>
                    <form action="" method="GET" class="w-full md:w-auto flex-1 max-w-md relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 dark:text-purple-300/50">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Query nama / username..."
                               class="w-full pl-11 pr-4 py-3 bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in fade-in-2">
                    <div class="overflow-x-auto flex-1 font-body">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Profil Entitas</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Username Registry</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Origin Base (Sekolah)</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Postal Node</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Timestamp Integrasi</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Mutate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                @forelse($students ?? [] as $student)
                                @php
                                    $sName   = e(addslashes($student->name ?? 'Unknown'));
                                    $sUser   = e(addslashes($student->username ?? ''));
                                    $sSchool = e(addslashes($student->school_origin ?? 'Belum Terkalibrasi'));
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-p border-2 border-black dark:border-p-dark flex items-center justify-center text-white font-black text-lg shadow-neo-sm">
                                                {{ strtoupper(substr($student->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="font-extrabold text-black dark:text-white text-base">
                                                {{ $student->name ?? 'Unknown Entity' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="font-mono text-xs font-bold text-black dark:text-white bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark px-3 py-1.5 rounded-lg shadow-neo-sm tracking-wider">
                                            {{ '@' . ($student->username ?? 'null') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 max-w-[200px]">
                                        <div class="text-black dark:text-white font-bold truncate flex items-center gap-2 text-sm" title="{{ $student->school_origin }}">
                                            <i class="fa-solid fa-school text-p-mid text-xs"></i>
                                            {{ $student->school_origin ?? 'Belum Terkalibrasi' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @if(isset($student->postal_code) && $student->postal_code)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-black tracking-wider uppercase bg-neo-yellow text-black border-2 border-black dark:border-p-dark shadow-neo-sm">
                                                <i class="fa-solid fa-map-pin"></i> {{ $student->postal_code }}
                                            </span>
                                        @else
                                            <span class="text-[11px] font-black uppercase text-gray-400 dark:text-purple-300/50 italic border-2 border-dashed border-gray-300 dark:border-p-dark px-2 py-1 rounded-md">Data Kosong</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center text-gray-600 dark:text-gray-300 font-bold text-xs font-mono">
                                        {{ $student->created_at ? $student->created_at->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- ✅ Tombol View Progres --}}
                                            <button
                                                type="button"
                                                onclick="openProgressModal({{ $student->id }}, '{{ $sName }}', '{{ $sUser }}', '{{ $sSchool }}')"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center bg-p-lt border-2 border-black text-p hover:bg-p hover:text-white transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none"
                                                title="Lihat Progres Belajar">
                                                <i class="fa-solid fa-chart-simple text-sm"></i>
                                            </button>

                                            {{-- Tombol Delete --}}
                                            <form action="{{ route('students.destroy', $student->id ?? 0) }}" method="POST"
                                                  onsubmit="return confirm('Peringatan: Mencabut entitas siswa akan menghapus seluruh relasi progres pembelajaran. Lanjutkan?');">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none"
                                                        title="Hapus Siswa">
                                                    <i class="fa-solid fa-user-minus text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                            <i class="fa-solid fa-users-slash text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Tidak ada *node* siswa yang terdeteksi dalam *database*.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($students) && method_exists($students, 'links') && $students->hasPages())
                        <div class="px-6 py-4 border-t-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b]">
                            {{ $students->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- ═══════════════════════ SCRIPTS ═══════════════════════ -->
    <script>
        /* ── Preloader ── */
        window.addEventListener('load', () => {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => pre.style.visibility = 'hidden', 500);
        });

        /* ── Sidebar ── */
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

        /* ── Dark Mode ── */
        const themeBtn  = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-toggle-icon');
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
        themeBtn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.theme = isDark ? 'dark' : 'light';
            themeIcon.classList.replace(isDark ? 'fa-moon' : 'fa-sun', isDark ? 'fa-sun' : 'fa-moon');
        });

        /* ══════════════════════════════════════════
           PROGRESS MODAL
        ══════════════════════════════════════════ */
        let progressChartInstance = null;

        function openProgressModal(studentId, name, username, school) {
            /* Reset tampilan */
            document.getElementById('modal-loading').classList.remove('hidden');
            document.getElementById('modal-body').classList.add('hidden');

            /* Isi header */
            document.getElementById('modal-avatar').textContent       = name.charAt(0).toUpperCase();
            document.getElementById('modal-student-name').textContent = name;
            document.getElementById('modal-student-meta').textContent = '@' + username + ' · ' + school;

            /* Hancurkan chart lama */
            if (progressChartInstance) {
                progressChartInstance.destroy();
                progressChartInstance = null;
            }

            /* Tampilkan modal */
            document.getElementById('modal-wrapper').classList.add('active');
            document.body.style.overflow = 'hidden';

            /* Fetch data progres */
            fetch('/admin/students/' + studentId + '/progress', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => renderProgressModal(data))
            .catch(err  => {
                console.error('Fetch error:', err);
                renderProgressModal(null);
            });
        }

        function closeProgressModal() {
            document.getElementById('modal-wrapper').classList.remove('active');
            document.body.style.overflow = '';
        }

        /* Tutup dengan Escape */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeProgressModal();
        });

        /* ── Render isi modal ── */
        function renderProgressModal(data) {
            document.getElementById('modal-loading').classList.add('hidden');
            document.getElementById('modal-body').classList.remove('hidden');

            const total      = data?.total            ?? 0;
            const correct    = data?.correct           ?? 0;
            const wrong      = data?.wrong             ?? 0;
            const avgTime    = data?.avg_time_seconds  ?? 0;
            const accuracy   = total > 0 ? Math.round((correct / total) * 100) : 0;
            const dailyData  = data?.daily             ?? [];
            const weeklyData = data?.weekly            ?? [];

            /* Stat cards */
            document.getElementById('stat-total').textContent   = total;
            document.getElementById('stat-correct').textContent = correct;
            document.getElementById('stat-wrong').textContent   = wrong;
            document.getElementById('stat-avgtime').textContent = avgTime > 0 ? Number(avgTime).toFixed(1) : '0';

            /* Accuracy bar */
            document.getElementById('stat-accuracy').textContent = accuracy + '%';
            const fillEl = document.getElementById('accuracy-fill');
            fillEl.style.width = accuracy + '%';

            if (accuracy >= 80) {
                fillEl.style.background = '#10b981';
                document.getElementById('accuracy-label').textContent = '🌟 Performa Luar Biasa! Siswa sangat menguasai materi.';
            } else if (accuracy >= 60) {
                fillEl.style.background = '#7C3AED';
                document.getElementById('accuracy-label').textContent = '📈 Progres Baik. Terus tingkatkan latihan soal.';
            } else if (accuracy >= 40) {
                fillEl.style.background = '#f59e0b';
                document.getElementById('accuracy-label').textContent = '⚠️ Perlu Perhatian. Dorong siswa untuk lebih aktif belajar.';
            } else if (total > 0) {
                fillEl.style.background = '#ef4444';
                document.getElementById('accuracy-label').textContent = '🔴 Performa Rendah. Evaluasi pemahaman materi siswa.';
            } else {
                fillEl.style.background = '#7C3AED';
                document.getElementById('accuracy-label').textContent = 'Belum ada aktivitas pengerjaan soal.';
            }

            /* Bar chart */
            if (dailyData.length === 0) {
                document.getElementById('chart-container').classList.add('hidden');
                document.getElementById('no-chart-data').classList.remove('hidden');
            } else {
                document.getElementById('chart-container').classList.remove('hidden');
                document.getElementById('no-chart-data').classList.add('hidden');

                const isDark  = document.documentElement.classList.contains('dark');
                const ctx     = document.getElementById('progress-chart').getContext('2d');
                progressChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: dailyData.map(d => d.date),
                        datasets: [
                            {
                                label: 'Benar',
                                data:  dailyData.map(d => d.correct),
                                backgroundColor: '#A7F3D080',
                                borderColor: '#000',
                                borderWidth: 2,
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Salah',
                                data:  dailyData.map(d => d.wrong),
                                backgroundColor: '#FECDD380',
                                borderColor: '#000',
                                borderWidth: 2,
                                borderRadius: 6,
                                borderSkipped: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? '#2d2460' : '#fff',
                                borderColor: '#000',
                                borderWidth: 2,
                                titleColor: isDark ? '#fff' : '#0A0A0A',
                                bodyColor:  isDark ? '#c4b5fd' : '#374151',
                                titleFont:  { family: 'Outfit', weight: '800', size: 13 },
                                bodyFont:   { family: 'Outfit', weight: '600', size: 12 },
                                padding: 12,
                                callbacks: {
                                    title: items => '📅 ' + items[0].label,
                                    label: item  => {
                                        const icon = item.datasetIndex === 0 ? '✅' : '❌';
                                        return ' ' + icon + ' ' + item.dataset.label + ': ' + item.raw + ' soal';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: isDark ? '#a78bfa' : '#6b7280', font: { family: 'Outfit', weight: '700', size: 11 }, maxRotation: 45 },
                                border: { color: '#000', width: 2 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: isDark ? '#ffffff12' : '#7C3AED12' },
                                ticks: { color: isDark ? '#a78bfa' : '#6b7280', font: { family: 'Outfit', weight: '700', size: 11 }, precision: 0 },
                                border: { color: '#000', width: 2 }
                            }
                        }
                    }
                });
            }

            /* Weekly table */
            const tbody = document.getElementById('weekly-table-body');
            if (weeklyData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-400 dark:text-purple-300/50 text-xs font-bold italic">Belum ada data mingguan</td></tr>';
            } else {
                let prevAcc = null;
                tbody.innerHTML = weeklyData.map((week, idx) => {
                    const weekTotal = week.correct + week.wrong;
                    const weekAcc   = weekTotal > 0 ? Math.round((week.correct / weekTotal) * 100) : 0;

                    let trend = '<span class="text-gray-400">—</span>';
                    if (prevAcc !== null) {
                        if      (weekAcc > prevAcc) trend = '<span class="text-green-500 font-black">▲ Naik</span>';
                        else if (weekAcc < prevAcc) trend = '<span class="text-red-500 font-black">▼ Turun</span>';
                        else                        trend = '<span class="text-yellow-500 font-black">= Stabil</span>';
                    }
                    prevAcc = weekAcc;

                    const accBg = weekAcc >= 80 ? 'bg-green-100 text-green-700'
                                : weekAcc >= 60 ? 'bg-purple-100 text-purple-700'
                                : weekAcc >= 40 ? 'bg-yellow-100 text-yellow-700'
                                : weekTotal > 0 ? 'bg-red-100 text-red-700'
                                :                 'bg-gray-100 text-gray-400';

                    return `<tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors text-sm">
                        <td class="px-5 py-3 font-black text-black dark:text-white">
                            Minggu ${idx + 1}
                            <span class="ml-2 text-[10px] text-gray-400 font-semibold">${week.week_label ?? ''}</span>
                        </td>
                        <td class="px-5 py-3 text-center font-bold text-black dark:text-white">${weekTotal}</td>
                        <td class="px-5 py-3 text-center font-bold text-green-600">${week.correct}</td>
                        <td class="px-5 py-3 text-center font-bold text-red-500">${week.wrong}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-block px-2 py-1 rounded-md text-[11px] font-black border-2 border-black shadow-neo-sm ${accBg}">
                                ${weekAcc}%
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center text-xs">${trend}</td>
                    </tr>`;
                }).join('');
            }
        }
    </script>
</body>
</html>