<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - NusaLearn</title>
    
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body.modal-active { overflow: hidden !important; }

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

        .modal-wrap { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.75); backdrop-filter:blur(5px); }
        .modal-wrap.active { display:flex; }
        .modal-card { background:#fff; border:3px solid #000; border-radius:24px; box-shadow:8px 8px 0 #000; width:90%; max-width:550px; overflow:hidden; animation:em-pop 0.3s cubic-bezier(0.34,1.56,0.64,1) both; }
        html.dark .modal-card { background:#2d2460; border-color:#4C1D95; }
        @keyframes em-pop { from{opacity:0;transform:scale(0.9) translateY(20px)} to{opacity:1;transform:scale(1) translateY(0)} }

        @keyframes badge-ping {
            0% { transform: scale(1); opacity: 1; }
            75%, 100% { transform: scale(1.8); opacity: 0; }
        }
        .live-dot::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%;
            background: #22C55E; animation: badge-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fade-up 0.4s ease-out both; }
    </style>
</head>

<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300 relative">

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden opacity-0 transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <!-- ===================================================
         MODAL ADD
    =================================================== -->
    <div id="add-modal" class="modal-wrap" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="flex items-center justify-between px-7 py-5 bg-p border-b-2 border-black dark:border-p-dark">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-user-plus text-p text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white tracking-tight leading-none">Tambah Pengguna</h3>
                        <p class="text-[11px] font-semibold text-white/70 mt-0.5 font-body">Daftarkan akun pengguna baru ke sistem.</p>
                    </div>
                </div>
                <button onclick="closeAddModal()" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="px-7 py-6 bg-white dark:bg-[#2d2460] max-h-[80vh] overflow-y-auto no-scrollbar">
                <form action="{{ route('administrator.pengguna.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input name="nama" type="text" placeholder="Nama Lengkap" required
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Role Pengguna</label>
                            <select name="peran" id="add-peran-select" required
                                    class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                                <option value="">Pilih Role...</option>
                                <option value="administrator">Administrator</option>
                                <option value="admin">Guru / Admin</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Username</label>
                            <input name="nama_pengguna" type="text" placeholder="Username" required
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Email</label>
                            <input name="email" type="email" placeholder="Email (Opsional)"
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                        </div>
                    </div>

                    <!-- Conditional Field: Sekolah Asal -->
                    <div id="add-school-container" class="hidden">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Sekolah Asal</label>
                        <select name="asal_sekolah" id="add-school-select"
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                            <option value="">Pilih Sekolah...</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->nama }}">{{ $school->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Conditional Field: Kode Pos / Wilayah -->
                    <div id="add-postal-container" class="hidden">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Wilayah (Kode Pos)</label>
                        <select name="kode_pos" id="add-postal-select"
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                            <option value="">Pilih Wilayah...</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->kode_pos }}">
                                    {{ $region->kode_pos }} - {{ $region->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-wider mb-2">Password</label>
                            <div class="relative">
                                  <input type="password" id="add-password" name="kata_sandi" placeholder="Min. 8 karakter" required
                                      class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all pr-12">
                                  <button type="button" onclick="togglePasswordVisibility('add-password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black dark:hover:text-white transition-colors">
                                      <i class="fa-solid fa-eye"></i>
                                  </button>
                              </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-wider mb-2">Konfirmasi Password</label>
                            <div class="relative">
                                  <input type="password" id="add-password-conf" name="kata_sandi_confirmation" placeholder="Ulangi kata_sandi" required
                                      class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all pr-12">
                                  <button type="button" onclick="togglePasswordVisibility('add-password-conf', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black dark:hover:text-white transition-colors">
                                      <i class="fa-solid fa-eye"></i>
                                  </button>
                              </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t-2 border-p-lt dark:border-p-dark">
                        <button type="button" onclick="closeAddModal()" class="flex-1 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 bg-p border-2 border-black rounded-xl text-white text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================================================
         MODAL EDIT
    =================================================== -->
    <div id="edit-modal" class="modal-wrap" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="flex items-center justify-between px-7 py-5 bg-p border-b-2 border-black dark:border-p-dark">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-user-pen text-p text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white tracking-tight leading-none">Edit Pengguna</h3>
                        <p class="text-[11px] font-semibold text-white/70 mt-0.5 font-body">Perbarui data pengguna.</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="px-7 py-6 bg-white dark:bg-[#2d2460] max-h-[80vh] overflow-y-auto no-scrollbar">
                <form id="edit-form" method="POST" class="space-y-5">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input id="edit-nama" name="nama" type="text" placeholder="Nama Lengkap" required
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Role Pengguna</label>
                            <select name="peran" id="edit-peran-select" required
                                    class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                                <option value="administrator">Administrator</option>
                                <option value="admin">Guru / Admin</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Username</label>
                            <input id="edit-nama_pengguna" name="nama_pengguna" type="text" placeholder="Username" required
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Email</label>
                            <input id="edit-email" name="email" type="email" placeholder="Email (Opsional)"
                                   class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                        </div>
                    </div>

                    <!-- Conditional Field: Sekolah Asal -->
                    <div id="edit-school-container" class="hidden">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Sekolah Asal</label>
                        <select name="asal_sekolah" id="edit-school-select"
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                            <option value="">Pilih Sekolah...</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->nama }}">{{ $school->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Conditional Field: Kode Pos / Wilayah -->
                    <div id="edit-postal-container" class="hidden">
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Wilayah (Kode Pos)</label>
                        <select name="kode_pos" id="edit-postal-select"
                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all">
                            <option value="">Pilih Wilayah...</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->kode_pos }}">
                                    {{ $region->kode_pos }} - {{ $region->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-4 bg-p-lt dark:bg-[#241f5c] border-2 border-dashed border-black dark:border-p-dark rounded-xl">
                        <p class="text-[11px] font-bold text-p-dark dark:text-purple-300 mb-3"><i class="fa-solid fa-circle-info"></i> Biarkan kata_sandi kosong jika tidak ingin mengubahnya.</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-black dark:text-white uppercase tracking-wider mb-2">Password Baru</label>
                                <div class="relative">
                                  <input type="password" id="edit-password" name="kata_sandi" placeholder="Min. 8 karakter"
                                      class="w-full bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all pr-12">
                                  <button type="button" onclick="togglePasswordVisibility('edit-password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black dark:hover:text-white transition-colors">
                                      <i class="fa-solid fa-eye"></i>
                                  </button>
                              </div>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-black dark:text-white uppercase tracking-wider mb-2">Konfirmasi Password</label>
                                <div class="relative">
                                  <input type="password" id="edit-password-conf" name="kata_sandi_confirmation" placeholder="Ulangi kata_sandi"
                                      class="w-full bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl py-3 px-4 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all pr-12">
                                  <button type="button" onclick="togglePasswordVisibility('edit-password-conf', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black dark:hover:text-white transition-colors">
                                      <i class="fa-solid fa-eye"></i>
                                  </button>
                              </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t-2 border-p-lt dark:border-p-dark">
                        <button type="button" onclick="closeEditModal()" class="flex-1 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-black dark:text-white text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 bg-p border-2 border-black rounded-xl text-white text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg hover:bg-p-dark transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden p-2 md:p-4 gap-4">

        <!-- Sidebar -->
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
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                    </div>
                    <span>Wilayah Persebaran</span>
                </a>

                <a href="{{ route('administrator.pengguna.index') }}" class="{{ request()->routeIs('administrator.pengguna.*') || request()->routeIs('administrator.pengguna.index') ? 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group' : 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group' }}">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center border-2 border-transparent">
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

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-[#241f5c] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo">
            
            <header class="h-20 border-b-2 border-black dark:border-p-dark flex items-center justify-between px-6 lg:px-10 z-20 sticky top-0 bg-white dark:bg-[#241f5c] rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" aria-label="Toggle Sidebar"
                        class="md:hidden w-10 h-10 flex items-center justify-center bg-p-lt border-2 border-black text-p rounded-xl shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>

                    <div class="hidden md:flex items-center gap-3">
                        <div class="w-10 h-10 bg-p border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                            <i class="fa-solid fa-users text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Kelola Pengguna</h1>
                            <p class="text-sm text-gray-500 font-medium mt-1">Kelola data seluruh pengguna sistem NusaLearn.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    

                    @if(session('error'))
                    <div class="hidden sm:flex bg-neo-red border-2 border-black text-black font-bold rounded-xl px-4 py-2 items-center gap-2 shadow-neo-sm text-sm fade-in">
                        <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
                    </div>
                    @endif

                    <button id="theme-toggle" aria-label="Toggle Dark Mode"
                        class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>

                    <button onclick="openAddModal()" class="hidden sm:flex items-center gap-2 bg-p text-white border-2 border-black px-4 py-2.5 rounded-xl shadow-neo-sm hover:bg-p-dark transition-all font-bold">
                        <i class="fa-solid fa-plus"></i> Tambah Pengguna
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">
                
                <!-- Filter & Search Bar -->
                <div class="bg-white dark:bg-[#2d2460] rounded-2xl border-2 border-black dark:border-p-dark p-4 mb-6 shadow-neo fade-in">
                    <form action="{{ route('administrator.pengguna.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="w-full md:w-1/3 relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nama_pengguna, email..." 
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-black dark:border-p-dark dark:bg-[#1e1b4b] font-body text-sm font-semibold focus:outline-none focus:border-p transition-all shadow-neo-sm">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-gray-400"></i>
                        </div>

                        <div class="w-full md:w-auto flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                            <select name="peran" class="px-4 py-2.5 rounded-xl border-2 border-black dark:border-p-dark dark:bg-[#1e1b4b] font-body text-sm font-semibold focus:outline-none focus:border-p transition-all shadow-neo-sm">
                                <option value="">Semua Role</option>
                                <option value="administrator" {{ request('peran') == 'administrator' ? 'selected' : '' }}>Administrator</option>
                                <option value="admin" {{ request('peran') == 'admin' ? 'selected' : '' }}>Guru / Admin</option>
                                <option value="siswa" {{ request('peran') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            </select>

                            <button type="submit" class="flex items-center justify-center gap-2 bg-p text-white border-2 border-black px-5 py-2.5 rounded-xl shadow-neo-sm hover:bg-p-dark transition-all font-bold">
                                <i class="fa-solid fa-filter"></i> Filter & Cari
                            </button>

                            @if(request()->anyFilled(['search', 'peran']))
                            <a href="{{ route('administrator.pengguna.index') }}" class="flex items-center justify-center gap-2 bg-white text-black border-2 border-black px-5 py-2.5 rounded-xl shadow-neo-sm hover:bg-gray-100 transition-all font-bold text-center">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in">
                    <div class="overflow-x-auto flex-1 font-body">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Profil Pengguna</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Sekolah Asal</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Status</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                @forelse($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $avatarColor = 'bg-neo-yellow';
                                                if ($user->peran === 'administrator') $avatarColor = 'bg-neo-pink';
                                                elseif ($user->peran === 'admin') $avatarColor = 'bg-neo-cyan';
                                                elseif ($user->peran === 'siswa') $avatarColor = 'bg-neo-coral';
                                            @endphp
                                            <div class="w-10 h-10 rounded-xl {{ $avatarColor }} border-2 border-black dark:border-p-dark flex items-center justify-center text-black font-black text-lg shadow-neo-sm flex-shrink-0">
                                                {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-extrabold text-black dark:text-white text-base">
                                                    {{ $user->nama }}
                                                    @if($user->id === auth()->id())
                                                    <span class="ml-1 text-xs font-bold bg-p text-white px-2 py-0.5 rounded border border-black shadow-neo-p-sm">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="font-bold text-gray-500 text-xs">
                                                    {{ '@' . $user->nama_pengguna }} | {{ $user->email ?? 'Tidak ada email' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        @if($user->peran === 'administrator')
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-neo-pink text-black border-2 border-black text-xs font-black shadow-neo-sm">
                                                Administrator
                                            </span>
                                        @elseif($user->peran === 'admin')
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-neo-cyan text-black border-2 border-black text-xs font-black shadow-neo-sm">
                                                Guru
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-neo-coral text-black border-2 border-black text-xs font-black shadow-neo-sm">
                                                Siswa
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 max-w-[200px]">
                                        <div class="text-black dark:text-white font-bold truncate flex items-center gap-2 text-sm" title="{{ $user->asal_sekolah }}">
                                            <i class="fa-solid fa-school text-p-mid text-xs"></i> {{ $user->asal_sekolah ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($user->aktif)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-neo-green text-black border-2 border-black text-xs font-black shadow-neo-sm">
                                                    <div class="w-2 h-2 rounded-full bg-green-500 border border-black"></div>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-neo-red text-black border-2 border-black text-xs font-black shadow-neo-sm">
                                                    <div class="w-2 h-2 rounded-full bg-red-500 border border-black"></div>
                                                    Nonaktif
                                                </span>
                                            @endif

                                            @if($user->id !== auth()->id())
                                            <form action="{{ route('administrator.pengguna.toggle-active', $user->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-yellow transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none" 
                                                    title="{{ $user->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                                    @if($user->aktif)
                                                        <i class="fa-solid fa-ban text-xs text-red-600"></i>
                                                    @else
                                                        <i class="fa-solid fa-circle-check text-xs text-green-600"></i>
                                                    @endif
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->nama) }}', '{{ addslashes($user->peran) }}', '{{ addslashes($user->nama_pengguna) }}', '{{ addslashes($user->email ?? '') }}', '{{ addslashes($user->asal_sekolah ?? '') }}', '{{ addslashes($user->kode_pos ?? '') }}')"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center bg-p-lt border-2 border-black text-p hover:bg-p hover:text-white transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none"
                                                title="Edit Pengguna">
                                                <i class="fa-solid fa-pen text-sm"></i>
                                            </button>

                                            @if($user->id !== auth()->id())
                                            <form id="delete-form-{{ $user->id }}" action="{{ route('administrator.pengguna.destroy', $user->id) }}" method="POST" class="inline" onsubmit="event.preventDefault(); openDeleteModal('delete-form-{{ $user->id }}', '{{ addslashes($user->name) }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none"
                                                        title="Hapus Pengguna">
                                                    <i class="fa-solid fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                            <i class="fa-solid fa-users-slash text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Tidak ditemukan data pengguna.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                    <div class="px-6 py-4 border-t-2 border-black dark:border-p-dark bg-p-xlt dark:bg-p-dark/20 flex items-center justify-center font-body">
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>

                <!-- Add Button for Mobile -->
                <div class="mt-6 sm:hidden">
                    <button onclick="openAddModal()" class="w-full flex items-center justify-center gap-2 bg-p text-white border-2 border-black px-4 py-3 rounded-xl shadow-neo-sm hover:bg-p-dark transition-all font-bold">
                        <i class="fa-solid fa-plus"></i> Tambah Pengguna
                    </button>
                </div>
            </main>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 500);
            
            // Trigger dynamic fields logic on page load
            handleAddRoleFields();
        });

        // Add Modal Functions
        function openAddModal() {
            document.getElementById('add-modal').classList.add('active');
            document.body.classList.add('modal-active');
            handleAddRoleFields();
        }
        function closeAddModal() {
            document.getElementById('add-modal').classList.remove('active');
            document.body.classList.remove('modal-active');
        }

        // Edit Modal Functions
        function openEditModal(id, nama, peran, nama_pengguna, email, asal_sekolah, kode_pos) {
            const form = document.getElementById('edit-form');
            form.action = "{{ route('administrator.pengguna.update', 999) }}".replace('999', id);
            document.getElementById('edit-nama').value = nama;
            document.getElementById('edit-peran-select').value = peran;
            document.getElementById('edit-nama_pengguna').value = nama_pengguna;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-school-select').value = asal_sekolah;
            document.getElementById('edit-postal-select').value = kode_pos;
            
            document.getElementById('edit-modal').classList.add('active');
            document.body.classList.add('modal-active');
            
            handleEditRoleFields();
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.remove('active');
            document.body.classList.remove('modal-active');
        }

        // Dynamic Role Field Handlers
        const addRoleSelect = document.getElementById('add-peran-select');
        const addSchoolContainer = document.getElementById('add-school-container');
        const addSchoolSelect = document.getElementById('add-school-select');
        const addPostalContainer = document.getElementById('add-postal-container');
        const addPostalSelect = document.getElementById('add-postal-select');

        function handleAddRoleFields() {
            const val = addRoleSelect.value;
            if (val === 'administrator') {
                addSchoolContainer.classList.add('hidden');
                addSchoolSelect.removeAttribute('required');
                addSchoolSelect.value = "";

                addPostalContainer.classList.add('hidden');
                addPostalSelect.removeAttribute('required');
                addPostalSelect.value = "";
            } else if (val === 'admin') {
                addSchoolContainer.classList.remove('hidden');
                addSchoolSelect.setAttribute('required', 'required');

                addPostalContainer.classList.add('hidden');
                addPostalSelect.removeAttribute('required');
                addPostalSelect.value = "";
            } else if (val === 'siswa') {
                addSchoolContainer.classList.remove('hidden');
                addSchoolSelect.setAttribute('required', 'required');

                addPostalContainer.classList.remove('hidden');
                addPostalSelect.setAttribute('required', 'required');
            } else {
                addSchoolContainer.classList.add('hidden');
                addSchoolSelect.removeAttribute('required');
                addPostalContainer.classList.add('hidden');
                addPostalSelect.removeAttribute('required');
            }
        }

        addRoleSelect.addEventListener('change', handleAddRoleFields);

        const editRoleSelect = document.getElementById('edit-peran-select');
        const editSchoolContainer = document.getElementById('edit-school-container');
        const editSchoolSelect = document.getElementById('edit-school-select');
        const editPostalContainer = document.getElementById('edit-postal-container');
        const editPostalSelect = document.getElementById('edit-postal-select');

        function handleEditRoleFields() {
            const val = editRoleSelect.value;
            if (val === 'administrator') {
                editSchoolContainer.classList.add('hidden');
                editSchoolSelect.removeAttribute('required');

                editPostalContainer.classList.add('hidden');
                editPostalSelect.removeAttribute('required');
            } else if (val === 'admin') {
                editSchoolContainer.classList.remove('hidden');
                editSchoolSelect.setAttribute('required', 'required');

                editPostalContainer.classList.add('hidden');
                editPostalSelect.removeAttribute('required');
            } else if (val === 'siswa') {
                editSchoolContainer.classList.remove('hidden');
                editSchoolSelect.setAttribute('required', 'required');

                editPostalContainer.classList.remove('hidden');
                editPostalSelect.setAttribute('required', 'required');
            } else {
                editSchoolContainer.classList.add('hidden');
                editSchoolSelect.removeAttribute('required');
                editPostalContainer.classList.add('hidden');
                editPostalSelect.removeAttribute('required');
            }
        }

        editRoleSelect.addEventListener('change', handleEditRoleFields);

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

    @if($errors->any())
        neoSwal.fire({ icon: 'error', title: 'Oops...', text: '{{ $errors->first() }}' });
    @endif

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

</script>
    <x-delete-modal />
</body>
</html>
