<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Belajar - Admin</title>
    
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
                <a href="#" class="flex items-center gap-3 px-4 py-3.5 bg-neo-lavender text-black border-2 border-black shadow-neo-sm font-bold rounded-xl transition-all text-base">
                    <i class="fa-solid fa-book-open w-6 text-center text-lg"></i> <span>Materi Belajar</span>
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
                        <h1 class="text-3xl font-extrabold text-black dark:text-white tracking-tight hidden md:block">Materi Pembelajaran</h1>
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
                
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-5 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-neo border-2 border-black">
                    <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                        <div class="relative w-full sm:w-72">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-black dark:text-gray-300"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul materi..." class="w-full pl-12 pr-5 py-3.5 bg-gray-50 dark:bg-slate-700 border-2 border-black rounded-xl text-base font-bold text-black dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-600 transition-all shadow-neo-sm">
                        </div>
                        <select name="language_scope" onchange="this.form.submit()" class="w-full sm:w-auto border-2 border-black bg-gray-50 dark:bg-slate-700 dark:text-white rounded-xl py-3.5 px-5 text-base text-black font-bold focus:outline-none transition-all cursor-pointer shadow-neo-sm appearance-none">
                            <option value="">Semua Bahasa</option>
                            @foreach($languages as $lang) 
                                <option value="{{ $lang->code }}" {{ request('language_scope') == $lang->code ? 'selected' : '' }}>{{ $lang->name }}</option> 
                            @endforeach
                        </select>
                    </form>
                    
                    <button onclick="toggleModal('modal-add')" class="w-full lg:w-auto bg-neo-yellow hover:bg-yellow-400 text-black px-6 py-3.5 rounded-xl border-2 border-black shadow-neo hover:-translate-y-1 hover:shadow-neo-lg transition-all text-base font-extrabold flex items-center justify-center gap-3">
                        <i class="fa-solid fa-plus text-lg"></i> Tambah Materi
                    </button>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-neo border-2 border-black overflow-hidden flex flex-col">
                    <div class="overflow-x-auto flex-1 font-inter">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-slate-700 text-gray-800 dark:text-gray-200 text-sm border-b-2 border-black">
                                    <th class="px-8 py-5 font-bold">Judul Materi (Indonesia)</th>
                                    <th class="px-8 py-5 font-bold">Kategori</th>
                                    <th class="px-8 py-5 font-bold">Scope Bahasa</th>
                                    <th class="px-8 py-5 font-bold text-center">Level</th>
                                    <th class="px-8 py-5 font-bold text-center">Status AI</th>
                                    <th class="px-8 py-5 font-bold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-gray-100 dark:divide-slate-700 text-base font-medium">
                                @forelse($materials as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-5">
                                            @if($item->image_url)
                                                <img src="{{ asset('storage/' . $item->image_url) }}" class="w-14 h-14 rounded-xl object-cover border-2 border-black shadow-neo-sm">
                                            @else
                                                <div class="w-14 h-14 rounded-xl bg-neo-lavender border-2 border-black flex items-center justify-center text-black font-black text-2xl shadow-neo-sm">{{ substr($item->title_indo, 0, 1) }}</div>
                                            @endif
                                            <div>
                                                <div class="font-extrabold text-black dark:text-white text-lg">{{ $item->title_indo }}</div>
                                                <div class="text-sm font-bold text-gray-500 dark:text-gray-400 mt-0.5 font-mono">ID: {{ $item->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 capitalize">
                                        <span class="px-4 py-1.5 rounded-lg text-sm font-bold bg-neo-lavender text-black border-2 border-black shadow-neo-sm">{{ $item->category }}</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-sm font-bold text-black bg-white border-2 border-black px-3 py-1.5 rounded-lg shadow-neo-sm">{{ $item->language_code == 'global' ? 'Global' : $item->language_code }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex justify-center text-yellow-400 text-sm gap-1 drop-shadow-md border-black">
                                            @for($i = 1; $i <= 3; $i++)
                                                <i class="fa-{{ $i <= $item->level_difficulty ? 'solid' : 'regular text-gray-300 dark:text-gray-600' }} fa-star"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    
                                    <td class="px-8 py-5 text-center">
                                        @if($item->ai_status == 'ready')
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-bold bg-neo-green text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-check"></i> Ready</span>
                                        @elseif($item->ai_status == 'processing')
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-bold bg-neo-yellow text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-hourglass-half"></i> Processing</span>
                                        @elseif($item->ai_status == 'failed')
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-bold bg-neo-red text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-xmark"></i> Failed</span>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-bold bg-white text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-pause"></i> Pending</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-8 py-5 text-right">
                                        <form action="{{ route('materials.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none">
                                                <i class="fa-solid fa-trash-can text-base"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center bg-white dark:bg-slate-800">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 dark:bg-slate-700 border-2 border-dashed border-gray-400 dark:border-gray-500 rounded-full text-gray-400 dark:text-gray-500 mb-4">
                                            <i class="fa-solid fa-folder-open text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-gray-400 font-bold text-base">Belum ada materi pembelajaran.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="modal-add" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
        <div class="modal-overlay absolute w-full h-full bg-gray-900/40 backdrop-blur-sm" onclick="toggleModal('modal-add')"></div>
        
        <div class="modal-container bg-white dark:bg-slate-800 w-11/12 md:max-w-3xl mx-auto rounded-3xl border-2 border-black shadow-neo-lg z-50 overflow-y-auto max-h-[90vh] transform transition-all scale-95 opacity-0" id="modal-content">
            
            <div class="modal-content pt-7 pb-5 px-8 border-b-2 border-black bg-neo-lavender dark:bg-slate-700 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h3 class="text-2xl font-extrabold text-black dark:text-white">Tambah Materi Baru</h3>
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">Isi detail dan unggah dataset pembelajaran (JSON).</p>
                </div>
                <div class="cursor-pointer z-50 w-10 h-10 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all" onclick="toggleModal('modal-add')">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </div>
            </div>

            <div class="px-8 py-8 bg-white dark:bg-slate-800">
                <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-black dark:text-white text-base font-bold mb-3">Judul Materi (Bahasa Indonesia)</label>
                        <input name="title_indo" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm" type="text" placeholder="Contoh: Mengenal Hewan" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-black dark:text-white text-base font-bold mb-3">Kategori</label>
                            <select name="category" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none">
                                <option value="literasi">Literasi</option>
                                <option value="numerasi">Numerasi</option>
                                <option value="budaya">Budaya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-black dark:text-white text-base font-bold mb-3">Level</label>
                            <select name="level_difficulty" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none">
                                <option value="1">Level 1 (Mudah)</option>
                                <option value="2">Level 2 (Sedang)</option>
                                <option value="3">Level 3 (Sulit)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-black dark:text-white text-base font-bold mb-3">Scope Bahasa</label>
                            <select name="language_code" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none">
                                <option value="global">Global (Semua)</option>
                                @foreach($languages as $lang) 
                                    <option value="{{ $lang->code }}">{{ $lang->name }}</option> 
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-4">
                        <label class="block text-black dark:text-white text-base font-bold mb-3">Upload File Materi (.json)</label>
                        <div class="border-2 border-dashed border-black bg-neo-cyan/30 rounded-xl p-10 text-center hover:bg-neo-cyan/60 transition-colors relative group shadow-neo-sm">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-white border-2 border-black text-black rounded-full flex items-center justify-center mb-4 group-hover:scale-110 group-hover:-translate-y-1 group-hover:shadow-neo transition-all">
                                    <i class="fa-solid fa-file-code text-2xl"></i>
                                </div>
                                <span class="text-lg font-extrabold text-black dark:text-white mb-1">Klik untuk upload file JSON</span>
                                <span class="text-sm font-bold text-gray-600 dark:text-gray-300">Format: .json (Maks 2MB)</span>
                            </div>
                            <input type="file" name="json_file" accept=".json" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" required>
                        </div>
                        <p class="text-sm font-bold text-gray-500 dark:text-gray-400 mt-3 flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> File JSON akan di-parse otomatis oleh sistem AI.</p>
                    </div>

                    <div class="pt-4">
                        <label class="block text-black dark:text-white text-base font-bold mb-3">Gambar Cover (Opsional)</label>
                        <input type="file" name="image" accept="image/*" class="block w-full text-base font-bold text-gray-500 dark:text-gray-400 file:mr-4 file:py-3 file:px-5 file:rounded-xl file:border-2 file:border-black file:text-base file:font-bold file:bg-white file:text-black hover:file:bg-gray-100 transition-all border-2 border-black rounded-xl cursor-pointer bg-gray-50 dark:bg-slate-900 shadow-neo-sm">
                    </div>

                    <div class="flex justify-end gap-4 pt-8 border-t-2 border-black dark:border-gray-700 mt-8">
                        <button type="button" onclick="toggleModal('modal-add')" class="px-6 py-3.5 bg-white border-2 border-black text-black rounded-xl text-base font-extrabold shadow-neo-sm hover:-translate-y-1 hover:shadow-neo transition-all">Batal</button>
                        <button type="submit" class="px-6 py-3.5 bg-neo-yellow border-2 border-black text-black rounded-xl text-base font-extrabold shadow-neo-sm hover:-translate-y-1 hover:shadow-neo transition-all">Simpan Materi</button>
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
            setTimeout(() => {
                preloader.style.visibility = 'hidden';
            }, 600);
        });

        // Toggle Sidebar O(1)
        function toggleSidebar() { 
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('absolute'); 
            sidebar.classList.toggle('h-full');
            sidebar.classList.toggle('w-[280px]');
            sidebar.classList.toggle('z-50');
        }

        // Toggle Modal O(1)
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