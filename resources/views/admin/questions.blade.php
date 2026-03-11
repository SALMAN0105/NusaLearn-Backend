<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Soal - Admin</title>
    
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

        .modal { transition: opacity 0.3s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }
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
                
                <a href="#" class="flex items-center gap-3 px-4 py-3.5 bg-neo-lavender text-black border-2 border-black shadow-neo-sm font-bold rounded-xl transition-all text-base">
                    <i class="fa-solid fa-clipboard-question w-6 text-center text-lg"></i> <span>Bank Soal (Kuis)</span>
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
                        <h1 class="text-3xl font-extrabold text-black dark:text-white tracking-tight hidden md:block">Bank Soal & Kuis Adaptif</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    @if(session('success'))
                        <div class="bg-neo-green border-2 border-black text-black px-5 py-2.5 rounded-xl flex items-center gap-3 shadow-neo-sm text-base font-bold" role="alert">
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
                
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-5 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-neo border-2 border-black">
                    <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-black dark:text-gray-300"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari isi pertanyaan..." class="w-full pl-12 pr-5 py-3.5 bg-gray-50 dark:bg-slate-700 border-2 border-black rounded-xl text-base font-bold text-black dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-600 transition-all shadow-neo-sm">
                        </div>
                        <select name="material_id" onchange="this.form.submit()" class="w-full sm:w-auto border-2 border-black bg-gray-50 dark:bg-slate-700 dark:text-white rounded-xl py-3.5 px-5 text-base text-black font-bold focus:outline-none transition-all cursor-pointer shadow-neo-sm appearance-none">
                            <option value="all">Semua Materi Induk</option>
                            @foreach($materials as $mat) 
                                <option value="{{ $mat->id }}" {{ request('material_id') == $mat->id ? 'selected' : '' }}>{{ Str::limit($mat->title_indo, 30) }}</option> 
                            @endforeach
                        </select>
                    </form>
                    
                    <button onclick="toggleModal('modal-add')" class="w-full lg:w-auto bg-neo-cyan hover:bg-cyan-300 text-black px-6 py-3.5 rounded-xl border-2 border-black shadow-neo hover:-translate-y-1 hover:shadow-neo-lg transition-all text-base font-extrabold flex items-center justify-center gap-3">
                        <i class="fa-solid fa-plus text-lg"></i> Buat Soal
                    </button>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-neo border-2 border-black overflow-hidden flex flex-col">
                    <div class="overflow-x-auto flex-1 font-inter">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-slate-700 text-gray-800 dark:text-gray-200 text-sm border-b-2 border-black">
                                    <th class="px-8 py-5 font-bold">Pertanyaan (Indonesia)</th>
                                    <th class="px-8 py-5 font-bold">Materi Terkait</th>
                                    <th class="px-8 py-5 font-bold text-center">Bobot Kesulitan</th>
                                    <th class="px-8 py-5 font-bold text-center">Kunci</th>
                                    <th class="px-8 py-5 font-bold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-gray-100 dark:divide-slate-700 text-base font-medium">
                                @forelse($questions as $q)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors group">
                                    <td class="px-8 py-6 max-w-md">
                                        <div class="font-bold text-black dark:text-white line-clamp-2 leading-relaxed">{{ $q->question_text_indo }}</div>
                                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1.5 bg-gray-100 dark:bg-slate-800 border-2 border-black dark:border-gray-500 px-3 py-1 w-max rounded-md"><i class="fa-solid fa-language"></i> Auto-translate di sisi klien</div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="inline-flex items-center gap-2 bg-neo-lavender dark:bg-slate-600 text-black dark:text-white px-3 py-1.5 rounded-lg text-sm font-bold border-2 border-black shadow-neo-sm">
                                            <i class="fa-solid fa-book-bookmark"></i> {{ Str::limit($q->material->title_indo, 25) }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="flex justify-center text-yellow-400 text-sm gap-1 drop-shadow-md border-black" title="Bobot: {{ $q->difficulty_weight }}">
                                            @for($i=1; $i<=5; $i++) 
                                                <i class="fa-{{ $i <= $q->difficulty_weight ? 'solid' : 'regular text-gray-300 dark:text-gray-600' }} fa-star"></i> 
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="mx-auto w-10 h-10 rounded-xl bg-neo-green border-2 border-black flex items-center justify-center text-black font-black uppercase text-lg shadow-neo-sm">
                                            {{ $q->correct_answer_key }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <form action="{{ route('questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini?');"> 
                                            @csrf @method('DELETE') 
                                            <button class="w-10 h-10 rounded-xl flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none">
                                                <i class="fa-solid fa-trash-can text-base"></i>
                                            </button> 
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-16 text-center bg-white dark:bg-slate-800">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 dark:bg-slate-700 border-2 border-dashed border-gray-400 dark:border-gray-500 rounded-full text-gray-400 dark:text-gray-500 mb-4">
                                            <i class="fa-solid fa-clipboard-question text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-gray-400 font-bold text-base">Belum ada bank soal tersedia.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($questions->hasPages())
                        <div class="px-8 py-5 border-t-2 border-black bg-gray-50 dark:bg-slate-700">
                            {{ $questions->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <div id="modal-add" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
        <div class="modal-overlay absolute w-full h-full bg-gray-900/40 backdrop-blur-sm" onclick="toggleModal('modal-add')"></div>
        
        <div class="modal-container bg-white dark:bg-slate-800 w-11/12 md:max-w-4xl mx-auto rounded-3xl border-2 border-black shadow-neo-lg z-50 overflow-y-auto max-h-[90vh] transform transition-all scale-95 opacity-0" id="modal-content">
            
            <div class="modal-content pt-7 pb-5 px-8 border-b-2 border-black bg-neo-lavender dark:bg-slate-700 flex justify-between items-center sticky top-0 z-20">
                <div>
                    <h3 class="text-2xl font-extrabold text-black dark:text-white">Buat Soal Pilihan Ganda</h3>
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">Soal akan disajikan secara adaptif berdasarkan AI.</p>
                </div>
                <div class="cursor-pointer z-50 w-10 h-10 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all" onclick="toggleModal('modal-add')">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </div>
            </div>
            
            <div class="px-8 py-8 bg-white dark:bg-slate-800">
                <form action="{{ route('questions.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-black dark:text-white text-base font-bold mb-3">Materi Induk <span class="text-red-500">*</span></label>
                            <select name="material_id" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none" required>
                                <option value="">-- Pilih Referensi Materi --</option>
                                @foreach($materials as $mat) 
                                    <option value="{{ $mat->id }}">{{ $mat->title_indo }}</option> 
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-black dark:text-white text-base font-bold mb-3">Bobot Kesulitan (Distribusi AI) <span class="text-red-500">*</span></label>
                            <select name="difficulty_weight" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none">
                                <option value="1">Level 1 - Sangat Mudah</option>
                                <option value="2">Level 2 - Mudah</option>
                                <option value="3" selected>Level 3 - Sedang (Rekomendasi)</option>
                                <option value="4">Level 4 - Sulit</option>
                                <option value="5">Level 5 - Sangat Sulit</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-black dark:text-white text-base font-bold mb-3">Pertanyaan (Bahasa Indonesia) <span class="text-red-500">*</span></label>
                        <textarea name="question_text_indo" rows="4" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm leading-relaxed resize-y" placeholder="Tulis instruksi atau soal di sini secara spesifik..." required></textarea>
                    </div>

                    <div class="border-t-2 border-black dark:border-gray-700 pt-8 mt-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-3">
                            <label class="block text-black dark:text-white text-xl font-extrabold">Matriks Pilihan Jawaban</label>
                            <span class="text-sm font-bold text-black bg-neo-yellow border-2 border-black px-4 py-2 rounded-xl shadow-neo-sm">Tandai Radio Button untuk Jawaban Benar</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @foreach(['a','b','c','d'] as $opt)
                            <label class="relative flex items-center gap-4 p-5 border-2 border-black rounded-xl bg-white dark:bg-slate-800 transition-all cursor-pointer hover:-translate-y-1 shadow-neo-sm hover:shadow-neo group">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 border-black bg-gray-50 dark:bg-slate-700 group-hover:bg-neo-green transition-colors">
                                    <input type="radio" name="correct_answer_key" value="{{ $opt }}" class="w-4 h-4 text-black border-none focus:ring-0 cursor-pointer accent-black" required>
                                </div>
                                <div class="flex-1 relative flex items-center gap-3">
                                    <span class="font-black text-xl text-black dark:text-white uppercase">{{ $opt }}.</span>
                                    <input type="text" name="option_{{ $opt }}" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-lg px-4 py-3 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all" placeholder="Tulis opsi jawaban..." required>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-8 border-t-2 border-black dark:border-gray-700 mt-8">
                        <button type="button" onclick="toggleModal('modal-add')" class="px-6 py-3.5 bg-white border-2 border-black text-black rounded-xl text-base font-extrabold shadow-neo-sm hover:-translate-y-1 hover:shadow-neo transition-all">Batal</button>
                        <button type="submit" class="px-6 py-3.5 bg-neo-green border-2 border-black text-black rounded-xl text-base font-extrabold shadow-neo-sm hover:-translate-y-1 hover:shadow-neo transition-all">Simpan Soal</button>
                    </div>
                </form>
            </div>
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

        // Kontrol Modal Tervalidasi
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const content = modal.querySelector('.modal-container');
            const body = document.querySelector('body');
            
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
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
                    body.classList.remove('modal-active'); 
                }, 300);
            }
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