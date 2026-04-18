<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Bahasa - Command Center</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
                        'neo':      '4px 4px 0px 0px rgba(0,0,0,1)',
                        'neo-sm':   '2px 2px 0px 0px rgba(0,0,0,1)',
                        'neo-lg':   '6px 6px 0px 0px rgba(0,0,0,1)',
                        'neo-p':    '4px 4px 0px 0px #4C1D95',
                        'neo-p-sm': '2px 2px 0px 0px #4C1D95',
                    }
                }
            }
        }
    </script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        #preloader { position: fixed; inset: 0; background: #F5F3FF; z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.5s ease, visibility 0.5s ease; }
        html.dark #preloader { background: #1e1b4b; }
        .pre-logo { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.2rem; color: #0A0A0A; letter-spacing: -1px; display: flex; align-items: center; gap: 6px; animation: pre-pulse 1.2s ease-in-out infinite; }
        .pre-logo span { color: #7C3AED; }
        html.dark .pre-logo { color: #fff; }
        @keyframes pre-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        .nav-active { background: #7C3AED !important; color: #fff !important; border-color: #0A0A0A !important; box-shadow: 2px 2px 0 #0A0A0A; }
        .nav-active i { color: #fff !important; }

        .dot-grid-bg { background-image: radial-gradient(circle, #7C3AED18 1.5px, transparent 1.5px); background-size: 24px 24px; }

        @keyframes fade-up { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
        .fade-in   { animation: fade-up 0.4s ease-out both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }

        /* ── PAGINATION ── */
        .pg-btn { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; background:#fff; border:2px solid #000; border-radius:8px; box-shadow:2px 2px 0 #000; font-size:12px; font-weight:800; color:#000; cursor:pointer; transition:all 0.15s ease; font-family:'Outfit',sans-serif; }
        .pg-btn:hover:not(:disabled) { transform:translate(1px,1px); box-shadow:1px 1px 0 #000; background:#EDE9FE; }
        .pg-btn.active { background:#7C3AED; color:#fff; }
        .pg-btn:disabled { opacity:0.35; cursor:not-allowed; pointer-events:none; box-shadow:none; }
        html.dark .pg-btn { background:#2d2460; color:#e2e8f0; border-color:#4C1D95; }
        html.dark .pg-btn.active { background:#7C3AED; color:#fff; }

        /* ── EDIT MODAL ── */
        #edit-modal { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.75); backdrop-filter:blur(5px); }
        #edit-modal.active { display:flex; }
        .em-card { background:#fff; border:3px solid #000; border-radius:24px; box-shadow:8px 8px 0 #000; width:90%; max-width:500px; overflow:hidden; animation:em-pop 0.3s cubic-bezier(0.34,1.56,0.64,1) both; }
        html.dark .em-card { background:#2d2460; border-color:#4C1D95; }
        @keyframes em-pop { from{opacity:0;transform:scale(0.9) translateY(20px)} to{opacity:1;transform:scale(1) translateY(0)} }

        /* ── DELETE CONFIRM MODAL ── */
        #delete-modal { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.75); backdrop-filter:blur(5px); }
        #delete-modal.active { display:flex; }
        .dm-card {
            background:#fff; border:3px solid #000; border-radius:24px;
            box-shadow:8px 8px 0 #000; width:90%; max-width:420px; overflow:hidden;
            animation:em-pop 0.3s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        html.dark .dm-card { background:#2d2460; border-color:#4C1D95; }

        /* ── DROP ZONE ── */
        .dz-wrap { transition:all 0.2s ease; }
        .dz-wrap.drag-over { background:#EDE9FE !important; border-color:#7C3AED !important; border-style:solid !important; }
        html.dark .dz-wrap.drag-over { background:rgba(76,29,149,0.3) !important; }

        /* ── FORMAT ERROR TOAST ── */
        #format-error-toast {
            position:fixed; bottom:28px; left:50%;
            transform:translateX(-50%) translateY(120px);
            z-index:999999; display:flex; align-items:center; gap:10px;
            background:#FECDD3; border:2.5px solid #000; border-radius:14px;
            box-shadow:5px 5px 0 #000; padding:13px 22px;
            font-weight:900; font-size:14px; color:#000;
            transition:transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
            white-space:nowrap; font-family:'Outfit',sans-serif;
        }
        #format-error-toast.show { transform:translateX(-50%) translateY(0); }
    </style>
</head>
<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300 relative">

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden opacity-0 transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <!-- ── FORMAT ERROR TOAST ── -->
    <div id="format-error-toast">
        <i class="fa-solid fa-circle-xmark text-red-500 text-xl"></i>
        <span>Format File Salah! Hanya file <strong>.json</strong> yang diterima.</span>
    </div>

    <!-- ===================================================
         EDIT MODAL
    =================================================== -->
    <div id="edit-modal" role="dialog" aria-modal="true">
        <div class="em-card">
            <!-- Header -->
            <div class="flex items-center justify-between px-7 py-5 bg-p border-b-2 border-black dark:border-p-dark">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-pen-to-square text-p text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white tracking-tight leading-none">Edit Bahasa</h3>
                        <p class="text-[11px] font-semibold text-white/70 mt-0.5">Perbarui data node bahasa.</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="px-7 py-6 bg-white dark:bg-[#2d2460]">
                <form id="edit-form" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Nama Entitas</label>
                        <div class="relative">
                            <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                            <input id="edit-name" name="name" type="text" placeholder="Contoh: Tolaki Mekongga"
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kode Akses</label>
                        <div class="relative">
                            <i class="fa-solid fa-code absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                            <input id="edit-code" name="code" type="text" placeholder="Contoh: tolakimekongga"
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono lowercase tracking-wider text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400" required>
                        </div>
                    </div>

                    <!-- Drop Zone Dataset Edit (opsional) -->
                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">
                            Ganti Dataset <span class="normal-case font-bold text-gray-400 tracking-normal">(opsional)</span>
                        </label>
                        <div id="edit-dz" class="dz-wrap p-4 bg-p-xlt dark:bg-p-dark/20 border-2 border-dashed border-black dark:border-p-dark rounded-xl relative cursor-pointer hover:bg-p-lt dark:hover:bg-p-dark/40 transition-all">
                            <div class="flex items-center gap-3 pointer-events-none">
                                <div id="edit-dz-icon" class="w-9 h-9 bg-white border-2 border-black rounded-lg flex items-center justify-center shadow-neo-sm flex-shrink-0">
                                    <i class="fa-solid fa-file-code text-p-mid"></i>
                                </div>
                                <div>
                                    <p id="edit-file-name" class="text-sm font-bold text-black dark:text-white">Pilih atau drop file .json</p>
                                    <p class="text-[11px] text-gray-400 font-bold">Biarkan kosong jika tidak ingin ganti dataset</p>
                                </div>
                            </div>
                            <input id="edit-dataset" name="dataset" type="file" accept=".json"
                                   class="opacity-0 absolute inset-0 w-full h-full cursor-pointer">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2 border-t-2 border-p-lt dark:border-p-dark">
                        <button type="button" onclick="closeEditModal()" class="flex-1 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                            <i class="fa-solid fa-xmark mr-1"></i> Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 bg-p border-2 border-black rounded-xl text-white text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================================================
         DELETE CONFIRM MODAL
    =================================================== -->
    <div id="delete-modal" role="dialog" aria-modal="true">
        <div class="dm-card">
            <!-- Header -->
            <div class="px-7 py-5 bg-neo-red border-b-2 border-black flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-black tracking-tight leading-none">Konfirmasi Hapus</h3>
                        <p class="text-[11px] font-semibold text-black/60 mt-0.5">Aksi ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <button onclick="closeDeleteModal()" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-white/80 transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <!-- Body -->
            <div class="px-7 py-6 bg-white dark:bg-[#2d2460]">
                <!-- Icon -->
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-neo-red border-2 border-black rounded-2xl shadow-neo flex items-center justify-center">
                        <i class="fa-solid fa-trash-can text-2xl text-red-600"></i>
                    </div>
                </div>
                <!-- Message -->
                <p class="text-center text-sm font-bold text-black dark:text-white mb-1">
                    Yakin ingin menghapus node ini?
                </p>
                <p id="delete-modal-target" class="text-center text-xs font-black text-p dark:text-purple-300 mb-5 truncate px-4"></p>
                <p class="text-center text-[11px] font-semibold text-gray-400 dark:text-purple-300/60 mb-6">
                    Penghapusan node ini bersifat <span class="text-red-500 font-black">ireversibel</span>. Semua data terkait akan ikut terhapus.
                </p>
                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="flex-1 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                        <i class="fa-solid fa-xmark mr-1"></i> Batal
                    </button>
                    <button type="button" id="delete-confirm-btn"
                            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 border-2 border-black rounded-xl text-white text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all">
                        <i class="fa-solid fa-trash-can mr-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                <a href="#" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
                    <i class="fa-solid fa-language w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Bahasa Daerah</span>
                </a>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-users w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
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

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-[#241f5c] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo">
            
            <header class="h-20 border-b-2 border-black dark:border-p-dark flex items-center justify-between px-6 lg:px-10 z-20 sticky top-0 bg-white dark:bg-[#241f5c] rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" aria-label="Toggle Sidebar" class="md:hidden w-10 h-10 flex items-center justify-center bg-p-lt border-2 border-black text-p rounded-xl shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>
                    <div class="hidden md:block">
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Registry Bahasa</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">Kelola dataset bahasa dan dialek regional.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if(session('success'))
                    <div class="hidden sm:flex bg-neo-green border-2 border-black text-black font-bold rounded-xl px-4 py-2 items-center gap-2 shadow-neo-sm text-sm fade-in">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="hidden sm:flex bg-neo-red border-2 border-black text-black font-bold rounded-xl px-4 py-2 items-center gap-2 shadow-neo-sm text-sm fade-in">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
                    </div>
                    @endif
                    <button id="theme-toggle" aria-label="Toggle Dark Mode" class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 lg:gap-10 items-start">
                    
                    <!-- ===== FORM TAMBAH ===== -->
                    <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden relative fade-in fade-in-1">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-p text-white">
                            <h3 class="text-lg font-black flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle"></i> Inject Database
                            </h3>
                            <p class="text-[11px] font-semibold text-white/70 mt-1">Registrasi *node* bahasa baru ke sistem.</p>
                        </div>

                        <div class="p-6 bg-white dark:bg-[#2d2460]">
                            <form action="{{ route('languages.store') }}" method="POST" enctype="multipart/form-data" id="store-form">
                                @csrf
                                <div class="mb-5">
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Nama Entitas</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-language absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <input name="name" type="text" placeholder="Contoh: Tolaki Mekongga"
                                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold text-black dark:text-white outline-none transition-all placeholder:text-gray-400 focus:border-p dark:focus:border-p-mid focus:shadow-neo-p" required>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kode Akses</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-code absolute left-4 top-1/2 -translate-y-1/2 text-p-mid text-sm pointer-events-none"></i>
                                        <input name="code" type="text" placeholder="Contoh: tolakimekongga"
                                               class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 pr-4 pl-11 text-sm font-bold font-mono lowercase tracking-wider text-black dark:text-white outline-none transition-all placeholder:text-gray-400 focus:border-p dark:focus:border-p-mid focus:shadow-neo-p" required>
                                    </div>
                                </div>

                                <!-- Drop Zone Dataset -->
                                <div class="mb-8">
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Upload Dataset (.json)</label>
                                    <div id="store-dz" class="dz-wrap p-5 bg-neo-cyan/20 dark:bg-p-dark/30 border-2 border-dashed border-black dark:border-p-dark rounded-xl relative cursor-pointer hover:bg-p-lt dark:hover:bg-p-dark/50 transition-all">
                                        <div class="flex flex-col items-center gap-2 pointer-events-none text-center">
                                            <div id="store-dz-icon" class="w-12 h-12 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                                                <i class="fa-solid fa-file-code text-xl text-p-mid"></i>
                                            </div>
                                            <p id="store-file-name" class="text-sm font-black text-black dark:text-white uppercase tracking-wide">Pilih atau Drop File</p>
                                            <p class="text-[11px] font-bold text-gray-500 dark:text-purple-300/50 flex items-center gap-1">
                                                <i class="fa-solid fa-triangle-exclamation text-neo-yellow"></i> Strict JSON format only.
                                            </p>
                                        </div>
                                        <input id="store-dataset" name="dataset" type="file" accept=".json"
                                               class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" required>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end gap-3 pt-5 border-t-2 border-p-lt dark:border-p-dark">
                                    <button type="reset" onclick="resetStoreDz()" class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">Clear</button>
                                    <button type="submit" class="px-5 py-2.5 bg-p border-2 border-black rounded-xl text-white text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all">Execute <i class="fa-solid fa-paper-plane ml-1"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ===== TABEL BAHASA ===== -->
                    <div class="xl:col-span-2 bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in fade-in-2">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-white dark:bg-[#2d2460] flex justify-between items-center">
                            <h2 class="text-lg font-black text-black dark:text-white">Active Database Nodes</h2>
                            <span class="bg-p-lt dark:bg-p-dark/50 text-p-dark dark:text-purple-200 text-[10px] font-black px-2.5 py-1 rounded-md border-2 border-black dark:border-p-dark shadow-neo-sm uppercase">
                                {{ count($languages ?? []) }} Entries
                            </span>
                        </div>

                        <div class="overflow-x-auto flex-1 font-body">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead>
                                    <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Nama Bahasa</th>
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Identifier</th>
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Health</th>
                                        <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Mutate</th>
                                    </tr>
                                </thead>
                                <tbody id="lang-table-body" class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                    @foreach($languages as $lang)
                                    <tr class="lang-row hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-black dark:text-white flex items-center gap-2">
                                                <i class="fa-solid fa-folder-tree text-p-mid text-sm"></i>
                                                {{ $lang->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-block text-black dark:text-white font-mono text-[11px] font-bold tracking-wider uppercase bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-md px-2.5 py-1 shadow-neo-sm">
                                                {{ $lang->code }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase bg-neo-green text-black border-2 border-black shadow-neo-sm">
                                                <i class="fa-solid fa-check"></i> Online
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Edit -->
                                                <button type="button"
                                                    onclick="openEditModal({{ $lang->id }}, '{{ addslashes($lang->name) }}', '{{ $lang->code }}')"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-yellow transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none"
                                                    title="Edit Node">
                                                    <i class="fa-solid fa-pen text-sm"></i>
                                                </button>
                                                <!-- Delete — pakai custom modal, bukan confirm() -->
                                                <form id="delete-form-{{ $lang->id }}"
                                                      action="{{ route('languages.destroy', $lang->id) }}"
                                                      method="POST" class="inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="button"
                                                        onclick="openDeleteModal({{ $lang->id }}, '{{ addslashes($lang->name) }}')"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none"
                                                        title="Drop Node">
                                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            @if($languages->isEmpty())
                            <div class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                    <i class="fa-solid fa-database text-2xl"></i>
                                </div>
                                <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Database bahasa regional kosong.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Pagination Footer -->
                        <div id="pg-container" class="px-6 py-4 border-t-2 border-p-lt dark:border-p-dark flex items-center justify-between gap-3 bg-white dark:bg-[#2d2460]">
                            <span id="pg-info" class="text-[11px] font-bold text-gray-500 dark:text-purple-300/60 uppercase tracking-wider"></span>
                            <div id="pg-buttons" class="flex items-center gap-1.5"></div>
                        </div>
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
            setTimeout(() => pre.style.visibility = 'hidden', 500);
        });

        // ── DARK MODE ──
        const themeBtn  = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-toggle-icon');
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
        themeBtn.addEventListener('click', () => {
            const dark = document.documentElement.classList.toggle('dark');
            localStorage.theme = dark ? 'dark' : 'light';
            themeIcon.classList.replace(dark ? 'fa-moon' : 'fa-sun', dark ? 'fa-sun' : 'fa-moon');
        });

        // ── SIDEBAR ──
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('sidebar-overlay');
            if (sb.classList.contains('-translate-x-full')) {
                sb.classList.remove('-translate-x-full');
                ov.classList.remove('hidden');
                setTimeout(() => ov.classList.remove('opacity-0'), 10);
            } else {
                sb.classList.add('-translate-x-full');
                ov.classList.add('opacity-0');
                setTimeout(() => ov.classList.add('hidden'), 300);
            }
        }

        // =============================================
        // FORMAT ERROR TOAST
        // =============================================
        function showFormatError() {
            const t = document.getElementById('format-error-toast');
            t.classList.add('show');
            clearTimeout(t._timer);
            t._timer = setTimeout(() => t.classList.remove('show'), 3800);
        }

        function isJson(file) {
            return file && file.name.toLowerCase().endsWith('.json');
        }

        // =============================================
        // DROP ZONE — STORE
        // =============================================
        const storeDz       = document.getElementById('store-dz');
        const storeInput    = document.getElementById('store-dataset');
        const storeFileName = document.getElementById('store-file-name');
        const storeDzIcon   = document.getElementById('store-dz-icon');

        function resetStoreDz() {
            storeFileName.textContent = 'Pilih atau Drop File';
            storeFileName.classList.remove('text-p', 'dark:text-cyan-400');
            storeDzIcon.innerHTML = '<i class="fa-solid fa-file-code text-xl text-p-mid"></i>';
        }

        function applyStoreFile(file) {
            if (!isJson(file)) { showFormatError(); return false; }
            storeFileName.textContent = file.name;
            storeFileName.classList.add('text-p');
            storeDzIcon.innerHTML = '<i class="fa-solid fa-circle-check text-xl text-emerald-500"></i>';
            return true;
        }

        storeInput.addEventListener('change', function () {
            if (this.files.length) {
                if (!applyStoreFile(this.files[0])) { this.value = ''; resetStoreDz(); }
            }
        });
        storeDz.addEventListener('dragover',  e => { e.preventDefault(); storeDz.classList.add('drag-over'); });
        storeDz.addEventListener('dragleave', () => storeDz.classList.remove('drag-over'));
        storeDz.addEventListener('drop', e => {
            e.preventDefault(); storeDz.classList.remove('drag-over');
            const f = e.dataTransfer.files[0];
            if (!f || !applyStoreFile(f)) return;
            const dt = new DataTransfer(); dt.items.add(f);
            storeInput.files = dt.files;
        });

        document.getElementById('store-form').addEventListener('submit', e => {
            if (storeInput.files.length && !isJson(storeInput.files[0])) {
                e.preventDefault();
                showFormatError();
            }
        });

        // =============================================
        // DROP ZONE — EDIT MODAL
        // =============================================
        const editDz       = document.getElementById('edit-dz');
        const editInput    = document.getElementById('edit-dataset');
        const editFileName = document.getElementById('edit-file-name');
        const editDzIcon   = document.getElementById('edit-dz-icon');

        function resetEditDz() {
            editFileName.textContent = 'Pilih atau drop file .json';
            editFileName.classList.remove('text-p');
            editDzIcon.innerHTML = '<i class="fa-solid fa-file-code text-p-mid"></i>';
        }

        function applyEditFile(file) {
            if (!isJson(file)) { showFormatError(); return false; }
            editFileName.textContent = file.name;
            editFileName.classList.add('text-p');
            editDzIcon.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-500"></i>';
            return true;
        }

        editInput.addEventListener('change', function () {
            if (this.files.length) {
                if (!applyEditFile(this.files[0])) { this.value = ''; resetEditDz(); }
            }
        });
        editDz.addEventListener('dragover',  e => { e.preventDefault(); editDz.classList.add('drag-over'); });
        editDz.addEventListener('dragleave', () => editDz.classList.remove('drag-over'));
        editDz.addEventListener('drop', e => {
            e.preventDefault(); editDz.classList.remove('drag-over');
            const f = e.dataTransfer.files[0];
            if (!f || !applyEditFile(f)) return;
            const dt = new DataTransfer(); dt.items.add(f);
            editInput.files = dt.files;
        });

        document.getElementById('edit-form').addEventListener('submit', e => {
            if (editInput.files.length && !isJson(editInput.files[0])) {
                e.preventDefault();
                showFormatError();
            }
        });

        // =============================================
        // EDIT MODAL
        // =============================================
        function openEditModal(id, name, code) {
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-code').value = code;
            resetEditDz();
            editInput.value = '';
            const base = '{{ rtrim(url("admin/languages"), "/") }}';
            document.getElementById('edit-form').action = `${base}/${id}`;
            document.getElementById('edit-modal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.remove('active');
        }

        document.getElementById('edit-modal').addEventListener('click', e => {
            if (e.target === document.getElementById('edit-modal')) closeEditModal();
        });

        // =============================================
        // DELETE CONFIRM MODAL
        // =============================================
        let _pendingDeleteFormId = null;

        function openDeleteModal(id, name) {
            _pendingDeleteFormId = id;
            document.getElementById('delete-modal-target').textContent = name;
            document.getElementById('delete-modal').classList.add('active');
        }

        function closeDeleteModal() {
            _pendingDeleteFormId = null;
            document.getElementById('delete-modal').classList.remove('active');
        }

        document.getElementById('delete-confirm-btn').addEventListener('click', () => {
            if (_pendingDeleteFormId !== null) {
                document.getElementById('delete-form-' + _pendingDeleteFormId).submit();
            }
        });

        document.getElementById('delete-modal').addEventListener('click', e => {
            if (e.target === document.getElementById('delete-modal')) closeDeleteModal();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { closeEditModal(); closeDeleteModal(); }
        });

        // =============================================
        // PAGINATION
        // =============================================
        const ROWS_PER_PAGE = 6;
        let currentPage = 1;

        function getRows() {
            return Array.from(document.querySelectorAll('#lang-table-body tr.lang-row'));
        }

        function renderPagination() {
            const rows  = getRows();
            const total = rows.length;
            const pages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
            if (currentPage > pages) currentPage = pages;

            rows.forEach((r, i) => {
                r.style.display = (Math.floor(i / ROWS_PER_PAGE) + 1 === currentPage) ? '' : 'none';
            });

            const s = total === 0 ? 0 : (currentPage - 1) * ROWS_PER_PAGE + 1;
            const e = Math.min(currentPage * ROWS_PER_PAGE, total);
            document.getElementById('pg-info').textContent = total > 0
                ? `Menampilkan ${s}–${e} dari ${total} entri`
                : 'Tidak ada data';

            const wrap = document.getElementById('pg-buttons');
            wrap.innerHTML = '';
            if (pages <= 1) return;

            const mkBtn = (html, disabled, active, onClick) => {
                const b = document.createElement('button');
                b.className = 'pg-btn' + (active ? ' active' : '');
                b.innerHTML = html; b.disabled = disabled;
                b.onclick = onClick;
                return b;
            };

            wrap.appendChild(mkBtn('<i class="fa-solid fa-chevron-left text-[10px]"></i>', currentPage === 1, false, () => { currentPage--; renderPagination(); }));
            for (let p = 1; p <= pages; p++) {
                wrap.appendChild(mkBtn(p, false, p === currentPage, ((pg) => () => { currentPage = pg; renderPagination(); })(p)));
            }
            wrap.appendChild(mkBtn('<i class="fa-solid fa-chevron-right text-[10px]"></i>', currentPage === pages, false, () => { currentPage++; renderPagination(); }));
        }

        document.addEventListener('DOMContentLoaded', renderPagination);
        window.addEventListener('load', renderPagination);
    </script>
</body>
</html>