<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola File & Konversi - Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'], inter: ['Inter', 'sans-serif'] },
                    colors: {
                        neo: { bg: '#F8F9FA', lavender: '#E2D9F3', green: '#A7F3D0', cyan: '#A5F3FC', red: '#FECDD3', yellow: '#FDE047', coral: '#FFB8A3', pink: '#F9A8D4' }
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

        /* Loader specific untuk form konversi */
        .processing-overlay { display: none; }
        .is-processing .processing-overlay { display: flex; }
        .is-processing button[type="submit"] { display: none; }
    </style>
</head>
<body class="bg-neo-bg text-black font-sans antialiased selection:bg-neo-lavender selection:text-black dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300">

    <div id="preloader"><div class="diamond-loader"><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div><div class="diamond"></div></div></div>

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
                    <i class="fa-solid fa-file-export w-6 text-center text-lg"></i> <span>Kelola File (Konverter)</span>
                </a>
                <a href="{{ route('questions.index') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-black dark:hover:text-white border-2 border-transparent hover:border-black hover:shadow-neo-sm rounded-xl transition-all group font-semibold text-base">
                    <i class="fa-solid fa-clipboard-question w-6 text-center text-lg transition-colors"></i> <span>Bank Soal (Kuis)</span>
                </a>
            </div>
            
            <div class="border-t-2 border-black p-6 bg-white dark:bg-slate-800">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=E2D9F3&color=000&bold=true" class="w-12 h-12 rounded-full border-2 border-black shadow-neo-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-black dark:text-white truncate">Administrator</p>
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
                        <h1 class="text-3xl font-extrabold text-black dark:text-white tracking-tight hidden md:block">Konversi Dokumen (Automasi AI)</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" type="button" class="w-12 h-12 flex items-center justify-center bg-white border-2 border-black text-black rounded-xl shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-xl"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8 lg:p-12 bg-neo-bg dark:bg-slate-900 transition-colors duration-300 relative">
                
                <div id="alert-container" class="hidden mb-6 flex items-center gap-3 px-6 py-4 rounded-xl border-2 border-black shadow-neo-sm text-base font-bold"></div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
                    
                    <div class="bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo p-8">
                        <div class="border-b-2 border-black dark:border-gray-700 pb-5 mb-6">
                            <h3 class="text-2xl font-extrabold text-black dark:text-white">Upload File Konten</h3>
                            <p class="text-sm font-bold text-gray-600 dark:text-gray-400 mt-1">Sistem akan memvalidasi dan mengonversi format ke JSON (Max 500MB).</p>
                        </div>

                        <form id="uploadForm" enctype="multipart/form-data" class="space-y-6 relative">
                            <div>
                                <label class="block text-black dark:text-white text-base font-bold mb-3">Tipe Konten <span class="text-red-500">*</span></label>
                                <select name="type" id="file_type" class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-black rounded-xl px-5 py-4 text-base font-bold dark:text-white focus:outline-none focus:bg-white dark:focus:bg-slate-700 transition-all shadow-neo-sm cursor-pointer appearance-none">
                                    <option value="kamus">Kamus (Membutuhkan file .xls / .csv)</option>
                                    <option value="materi">Materi (Membutuhkan file .pdf)</option>
                                </select>
                            </div>

                            <div class="pt-2">
                                <label class="block text-black dark:text-white text-base font-bold mb-3">Pilih File Data <span class="text-red-500">*</span></label>
                                <div class="border-2 border-dashed border-black bg-neo-cyan/20 rounded-xl p-10 text-center hover:bg-neo-cyan/40 transition-colors relative group shadow-neo-sm">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-white border-2 border-black text-black rounded-full flex items-center justify-center mb-4 group-hover:scale-110 group-hover:-translate-y-1 transition-all">
                                            <i id="upload_icon" class="fa-solid fa-file-excel text-2xl"></i>
                                        </div>
                                        <span id="file_name_display" class="text-lg font-extrabold text-black dark:text-white mb-1">Klik untuk memilih file</span>
                                        <span id="file_rules" class="text-sm font-bold text-gray-600 dark:text-gray-300">Format: Excel/CSV (Maks 500MB)</span>
                                    </div>
                                    <input type="file" name="upload_file" id="file_input" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" accept=".xls,.xlsx,.csv" required>
                                </div>
                            </div>

                            <div class="pt-6">
                                <button type="submit" class="w-full bg-neo-yellow hover:bg-yellow-400 text-black border-2 border-black font-extrabold py-4 px-5 rounded-xl shadow-neo hover:-translate-y-1 hover:shadow-neo-lg transition-all flex justify-center items-center gap-3 text-lg">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Mulai Konversi & Download
                                </button>
                                
                                <div class="processing-overlay w-full bg-gray-100 dark:bg-slate-700 border-2 border-black font-extrabold py-4 px-5 rounded-xl shadow-neo flex justify-center items-center gap-3 text-lg text-black dark:text-white">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i> Sedang Membaca File & Ekstrak Data...
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-slate-800 border-2 border-black rounded-2xl shadow-neo overflow-hidden flex flex-col">
                        <div class="p-6 border-b-2 border-black bg-white dark:bg-slate-800 flex justify-between items-center">
                            <h2 class="text-xl font-extrabold text-black dark:text-white">Riwayat Eksekusi Server</h2>
                        </div>
                        <div class="overflow-x-auto flex-1 font-inter max-h-[500px]">
                            <table class="w-full text-left border-collapse">
                                <thead class="sticky top-0 bg-gray-50 dark:bg-slate-700 z-10 shadow-[0_2px_0_0_#000]">
                                    <tr class="text-gray-800 dark:text-gray-200 text-sm border-b-2 border-black">
                                        <th class="px-6 py-4 font-bold">Nama File</th>
                                        <th class="px-6 py-4 font-bold">Ukuran</th>
                                        <th class="px-6 py-4 font-bold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-2 divide-gray-100 dark:divide-slate-700 text-sm font-medium" id="history_table_body">
                                    @forelse($histories ?? [] as $history)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-black dark:text-white truncate max-w-[200px]" title="{{ $history->original_filename }}">{{ $history->original_filename }}</div>
                                            <div class="text-[11px] text-gray-500 mt-1 uppercase tracking-wide font-bold">{{ str_replace('_', ' to ', $history->conversion_type) }}</div>
                                        </td>
                                        <td class="px-6 py-4 font-mono font-bold text-black dark:text-white">
                                            {{ number_format($history->file_size_kb / 1024, 2) }} MB
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($history->status == 'success')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-neo-green text-black border-2 border-black shadow-neo-sm"><i class="fa-solid fa-check"></i> Sukses</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-neo-red text-black border-2 border-black shadow-neo-sm" title="{{ $history->error_log }}"><i class="fa-solid fa-xmark"></i> Gagal</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-bold">Belum ada aktivitas konversi.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // Transisi Preloader
        window.addEventListener('load', function() {
            document.getElementById('preloader').style.opacity = '0';
            setTimeout(() => { document.getElementById('preloader').style.visibility = 'hidden'; }, 600);
        });

        // Dynamic File Input UI
        const typeSelect = document.getElementById('file_type');
        const fileInput = document.getElementById('file_input');
        const fileIcon = document.getElementById('upload_icon');
        const fileRules = document.getElementById('file_rules');
        const fileNameDisplay = document.getElementById('file_name_display');

        typeSelect.addEventListener('change', function() {
            if(this.value === 'kamus') {
                fileInput.accept = ".xls,.xlsx,.csv";
                fileIcon.className = "fa-solid fa-file-excel text-2xl";
                fileRules.innerText = "Format: Excel/CSV (Maks 500MB)";
            } else {
                fileInput.accept = ".pdf";
                fileIcon.className = "fa-solid fa-file-pdf text-2xl text-neo-red";
                fileRules.innerText = "Format: PDF (Maks 500MB). Gambar akan di-ekstrak.";
            }
            fileInput.value = "";
            fileNameDisplay.innerText = "Klik untuk memilih file";
        });

        fileInput.addEventListener('change', function() {
            if(this.files.length > 0) {
                fileNameDisplay.innerText = this.files[0].name;
            }
        });

        // AJAX Form Submission Logika Activity Diagram
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = this;
            const alertBox = document.getElementById('alert-container');
            const formData = new FormData(form);
            
            // Masuk state processing
            form.classList.add('is-processing');
            alertBox.classList.add('hidden');
            
            try {
                const response = await fetch("{{ route('converter.process') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                
                const result = await response.json();
                
                if(response.ok) {
                    // Valid == Ya -> Notifikasi Sukses
                    alertBox.className = "mb-6 flex items-center gap-3 px-6 py-4 rounded-xl border-2 border-black shadow-neo-sm text-base font-bold bg-neo-green text-black";
                    alertBox.innerHTML = `<i class="fa-solid fa-check-circle text-xl"></i> ${result.message}. Mengunduh...`;
                    alertBox.classList.remove('hidden');
                    
                    // Otomatis Download File
                    setTimeout(() => {
                        window.location.href = result.download_url;
                        setTimeout(() => window.location.reload(), 2000); // Refresh history
                    }, 1500);

                } else {
                    // Valid == Tidak -> Tampilkan Error Validasi
                    let errors = result.errors ? result.errors.join('<br>') : 'Terjadi kesalahan sistem.';
                    alertBox.className = "mb-6 flex items-center gap-3 px-6 py-4 rounded-xl border-2 border-black shadow-neo-sm text-base font-bold bg-neo-red text-black";
                    alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-xl"></i> <div>${errors}</div>`;
                    alertBox.classList.remove('hidden');
                    form.classList.remove('is-processing'); // Kembali ke "Upload Ulang File"
                }
            } catch (error) {
                alertBox.className = "mb-6 flex items-center gap-3 px-6 py-4 rounded-xl border-2 border-black shadow-neo-sm text-base font-bold bg-neo-red text-black";
                alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-xl"></i> Terjadi kesalahan koneksi server. Pastikan limit 500MB diizinkan oleh php.ini`;
                alertBox.classList.remove('hidden');
                form.classList.remove('is-processing');
            }
        });

        // Dark Mode Logic Identik
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
        function toggleSidebar() { 
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden'); sidebar.classList.toggle('absolute'); 
            sidebar.classList.toggle('h-full'); sidebar.classList.toggle('w-[280px]');
            sidebar.classList.toggle('z-50');
        }
    </script>
</body>
</html>