<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Soal - Command Center</title>
    
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

        /* ── RADIO BUTTON FIX ── */
        .neo-radio {
            appearance: none;
            width: 1.25rem; height: 1.25rem;
            border: 2px solid #0A0A0A; border-radius: 50%;
            background-color: #fff;
            position: relative; outline: none; cursor: pointer;
            box-shadow: 2px 2px 0 0 #0A0A0A;
        }
        .neo-radio:checked { background-color: #A7F3D0; }
        .neo-radio:checked::after {
            content: ''; position: absolute;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 0.5rem; height: 0.5rem;
            background-color: #0A0A0A; border-radius: 50%;
        }
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
                
                <a href="#" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
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
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Bank Soal & Kuis Adaptif</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">Sistem manajemen *node* evaluasi kecerdasan buatan.</p>
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
                
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-5 bg-white dark:bg-[#2d2460] p-5 rounded-2xl shadow-neo border-2 border-black dark:border-p-dark fade-in fade-in-1">
                    <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-4 w-full xl:w-auto">
                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 dark:text-purple-300/50"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Query isi pertanyaan..." 
                                   class="w-full pl-11 pr-4 py-3 bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all placeholder:text-gray-400">
                        </div>
                        <select name="material_id" onchange="this.form.submit()" 
                                class="w-full sm:w-auto border-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b] rounded-xl py-3 px-4 pr-10 text-sm text-black dark:text-white font-bold outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                            <option value="all">Semua Pointer Materi</option>
                            @foreach($materials as $mat) 
                                <option value="{{ $mat->id }}" {{ request('material_id') == $mat->id ? 'selected' : '' }}>{{ Str::limit($mat->title_indo, 30) }}</option> 
                            @endforeach
                        </select>
                    </form>
                    
                    <button onclick="toggleModal('modal-add')" class="w-full xl:w-auto bg-neo-cyan hover:bg-cyan-300 text-black px-6 py-3 rounded-xl border-2 border-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all text-sm font-black flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> Inject Soal Evaluasi
                    </button>
                </div>

                <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in fade-in-2">
                    <div class="overflow-x-auto flex-1 font-body">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Pertanyaan (Base String)</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Pointer Materi</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Bobot / Engine</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Kunci Checksum</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Mutate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                @forelse($questions as $q)
                                <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                    <td class="px-6 py-5 max-w-md whitespace-normal">
                                        <div class="font-bold text-black dark:text-white line-clamp-2 leading-relaxed">{{ $q->question_text_indo }}</div>
                                        <div class="text-[10px] font-bold text-gray-500 dark:text-purple-300/60 mt-2 flex items-center gap-1.5 bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark px-2.5 py-1 w-max rounded-md uppercase tracking-wider">
                                            <i class="fa-solid fa-microchip text-p-mid"></i> Auto-translate Engine
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="inline-flex items-center gap-2 bg-p-xlt dark:bg-p-dark/50 text-black dark:text-white px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black dark:border-p-dark shadow-neo-sm">
                                            <i class="fa-solid fa-link text-p-mid"></i> {{ Str::limit($q->material->title_indo ?? 'Null Reference', 25) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex justify-center text-neo-yellow dark:text-yellow-400 text-sm gap-0.5 drop-shadow-sm" title="Algoritma Bobot: Lvl {{ $q->difficulty_weight }}">
                                            @for($i=1; $i<=5; $i++) 
                                                <i class="fa-{{ $i <= $q->difficulty_weight ? 'solid' : 'regular text-gray-300 dark:text-gray-600' }} fa-star"></i> 
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="mx-auto w-10 h-10 rounded-xl bg-neo-green border-2 border-black flex items-center justify-center text-black font-black uppercase text-lg shadow-neo-sm">
                                            {{ $q->correct_answer_key }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <form action="{{ route('questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Menghapus soal akan merusak dataset historis pengguna. Lanjutkan eksekusi?');"> 
                                            @csrf @method('DELETE') 
                                            <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none" title="Drop Question">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button> 
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                            <i class="fa-solid fa-clipboard-question text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Dataset evaluasi (Bank Soal) masih kosong.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($questions->hasPages())
                        <div class="px-6 py-4 border-t-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b]">
                            {{ $questions->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <div id="modal-add" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-add')"></div>
        
        <div class="modal-container bg-white dark:bg-[#2d2460] w-11/12 md:max-w-4xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 overflow-y-auto max-h-[90vh] transform transition-all scale-95 opacity-0" id="modal-content">
            
            <div class="pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-p flex justify-between items-center sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-pen-nib text-p text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight leading-none">Inject Soal Adaptif</h3>
                        <p class="text-[11px] font-semibold text-white/70 mt-1">Evaluasi terkalibrasi AI berdasarkan bobot kesulitan.</p>
                    </div>
                </div>
                <button onclick="toggleModal('modal-add')" aria-label="Close Modal" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            
            <div class="px-8 py-8 bg-white dark:bg-[#2d2460]">
                <form action="{{ route('questions.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Materi Induk (Pointer) <span class="text-neo-red">*</span></label>
                            <select name="material_id" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none" required>
                                <option value="">-- Pilih Referensi Node --</option>
                                @foreach($materials as $mat) 
                                    <option value="{{ $mat->id }}">{{ $mat->title_indo }}</option> 
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Bobot Kesulitan (AI Distribution) <span class="text-neo-red">*</span></label>
                            <select name="difficulty_weight" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                <option value="1">Lvl 1 - Sangat Dasar</option>
                                <option value="2">Lvl 2 - Dasar</option>
                                <option value="3" selected>Lvl 3 - Menengah (Default)</option>
                                <option value="4">Lvl 4 - Lanjutan</option>
                                <option value="5">Lvl 5 - Kompleksitas Tinggi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Formulasi Pertanyaan (String ID) <span class="text-neo-red">*</span></label>
                        <textarea name="question_text_indo" rows="4" 
                                  class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all resize-y" 
                                  placeholder="Input string instruksi pertanyaan dengan presisi..." required></textarea>
                    </div>

                    <div class="border-t-2 border-black dark:border-p-dark pt-8 mt-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-3">
                            <label class="block text-black dark:text-white text-lg font-black tracking-tight">Matriks Vektor Jawaban</label>
                            <span class="text-[11px] font-black uppercase text-black bg-neo-yellow border-2 border-black px-3 py-1.5 rounded-md shadow-neo-sm">
                                Tetapkan Kunci Checksum (Radio)
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @foreach(['a','b','c','d'] as $opt)
                            <label class="relative flex items-start gap-4 p-4 border-2 border-black dark:border-p-dark rounded-xl bg-white dark:bg-[#2d2460] transition-all cursor-pointer hover:-translate-y-0.5 shadow-neo-sm hover:shadow-neo group">
                                <div class="pt-2">
                                    <input type="radio" name="correct_answer_key" value="{{ $opt }}" class="neo-radio" required>
                                </div>
                                <div class="flex-1 w-full">
                                    <div class="font-black text-sm text-gray-400 dark:text-purple-300/50 mb-1 uppercase tracking-widest">Opsi {{ $opt }}</div>
                                    <textarea name="option_{{ $opt }}" rows="2" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-lg px-3 py-2 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all resize-none" placeholder="Input string untuk opsi..." required></textarea>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t-2 border-p-lt dark:border-p-dark mt-8">
                        <button type="button" onclick="toggleModal('modal-add')" class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark text-black dark:text-white rounded-xl text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">Abort</button>
                        <button type="submit" class="px-5 py-2.5 bg-neo-green border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all">Write to Database <i class="fa-solid fa-database ml-1"></i></button>
                    </div>
                </form>
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
    </script>
</body>
</html>