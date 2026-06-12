<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konverter Engine (AI) - Command Center</title>
    
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
        
        /* ── PRELOADER ── */
        #preloader { position: fixed; inset: 0; background: #F5F3FF; z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.5s ease, visibility 0.5s ease; }
        html.dark #preloader { background: #1e1b4b; }
        .pre-logo { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.2rem; color: #0A0A0A; letter-spacing: -1px; display: flex; align-items: center; gap: 6px; animation: pre-pulse 1.2s ease-in-out infinite; }
        .pre-logo span { color: #7C3AED; }
        html.dark .pre-logo { color: #fff; }
        @keyframes pre-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ── NAVIGATION ACTIVE ── */
        .nav-active { background: #7C3AED !important; color: #fff !important; border-color: #0A0A0A !important; box-shadow: 2px 2px 0 #0A0A0A; }
        .nav-active i { color: #fff !important; }

        /* ── BACKGROUND PATTERN ── */
        .dot-grid-bg { background-image: radial-gradient(circle, #7C3AED18 1.5px, transparent 1.5px); background-size: 24px 24px; }

        /* ── RENDER STAGGERING ── */
        @keyframes fade-up { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fade-up 0.4s ease-out both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }

        /* ========== AI PULSE ========== */
        @keyframes ai-pulse {
            0% { box-shadow: 0 0 0 0 rgba(165, 243, 252, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(165, 243, 252, 0); }
            100% { box-shadow: 0 0 0 0 rgba(165, 243, 252, 0); }
        }
        .ai-active-glow { animation: ai-pulse 2s infinite; border-color: #06b6d4 !important; }

        /* ========== LOADING MODAL OVERLAY (REFACTORED) ========== */
        #loading-modal {
            position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center;
            background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
        }
        #loading-modal.active { display: flex; }

        .modal-card {
            background: #fff; border: 3px solid #000; border-radius: 24px;
            box-shadow: 8px 8px 0px 0px rgba(0,0,0,1); padding: 40px 48px;
            max-width: 420px; width: 90%; text-align: center; position: relative; overflow: hidden;
        }
        html.dark .modal-card { background: #2d2460; border-color: #4C1D95; color: #f1f5f9; box-shadow: 8px 8px 0px 0px #000; }

        .modal-card::before {
            content: ''; position: absolute; top: -30px; right: -30px; width: 100px; height: 100px;
            background: #FDE047; border: 3px solid #000; border-radius: 50%; opacity: 0.6;
        }
        html.dark .modal-card::before { border-color: #4C1D95; opacity: 0.3; }
        .modal-card::after {
            content: ''; position: absolute; bottom: -20px; left: -20px; width: 70px; height: 70px;
            background: #A5F3FC; border: 3px solid #000; border-radius: 50%; opacity: 0.5;
        }
        html.dark .modal-card::after { border-color: #4C1D95; opacity: 0.3; }

        /* ===== Animasi Orbital Loader ===== */
        .modal-loader { position: relative; width: 100px; height: 100px; margin: 0 auto 28px; }
        .modal-loader .orbit { position: absolute; inset: 0; border: 2px dashed rgba(0,0,0,0.15); border-radius: 50%; animation: orbit-spin 4s linear infinite; }
        html.dark .modal-loader .orbit { border-color: rgba(255,255,255,0.2); }
        .modal-loader .orb { position: absolute; width: 20px; height: 20px; border-radius: 6px; border: 2px solid #000; transform: rotate(45deg); }
        .modal-loader .orb-1 { top: -10px; left: calc(50% - 10px); background: #7C3AED; animation: pulse-orb 1.2s ease-in-out infinite; }
        .modal-loader .orb-2 { right: -10px; top: calc(50% - 10px); background: #A7F3D0; animation: pulse-orb 1.2s ease-in-out 0.3s infinite; }
        .modal-loader .orb-3 { bottom: -10px; left: calc(50% - 10px); background: #FFB8A3; animation: pulse-orb 1.2s ease-in-out 0.6s infinite; }
        .modal-loader .orb-4 { left: -10px; top: calc(50% - 10px); background: #A5F3FC; animation: pulse-orb 1.2s ease-in-out 0.9s infinite; }
        .modal-loader .center-dot { position: absolute; top: 50%; left: 50%; width: 16px; height: 16px; background: #FDE047; border: 2px solid #000; border-radius: 50%; transform: translate(-50%, -50%); animation: center-beat 1s ease-in-out infinite; }

        @keyframes orbit-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse-orb { 0%, 100% { transform: rotate(45deg) scale(1); } 50% { transform: rotate(45deg) scale(1.3); } }
        @keyframes center-beat { 0%, 100% { transform: translate(-50%, -50%) scale(1); background: #FDE047; } 50% { transform: translate(-50%, -50%) scale(1.4); background: #A5F3FC; } }

        /* Progress bar animasi */
        .modal-progress-track { width: 100%; height: 8px; background: #F5F3FF; border: 2px solid #000; border-radius: 99px; overflow: hidden; margin-top: 24px; }
        html.dark .modal-progress-track { background: #1e1b4b; border-color: #4C1D95; }
        .modal-progress-bar { height: 100%; width: 0%; border-radius: 99px; background: linear-gradient(90deg, #FDE047, #A5F3FC, #7C3AED, #A7F3D0); background-size: 300% 100%; animation: progress-fill 30s ease-out forwards, shimmer 2s linear infinite; }
        
        @keyframes progress-fill { 0% { width: 0%; } 10% { width: 20%; } 30% { width: 45%; } 60% { width: 70%; } 85% { width: 88%; } 100% { width: 92%; } }
        @keyframes shimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }

        .modal-status-text { font-size: 14px; font-weight: 700; color: #6b7280; margin-top: 10px; min-height: 22px; font-family: 'Plus Jakarta Sans', sans-serif; }
        html.dark .modal-status-text { color: #a78bfa; }

        .modal-warning { display: none; margin-top: 16px; padding: 10px 14px; background: #FDE047; border: 2px solid #000; border-radius: 12px; font-size: 12px; font-weight: 700; color: #000; box-shadow: 2px 2px 0 #000; }
        html.dark .modal-warning { border-color: #4C1D95; box-shadow: 2px 2px 0 #4C1D95; }
        .modal-warning.show { display: block; }

        /* ========== Tombol Download ========== */
        .btn-download-riwayat { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #A7F3D0; border: 2px solid #000; border-radius: 8px; box-shadow: 2px 2px 0 #000; font-size: 12px; font-weight: 800; color: #000; cursor: pointer; text-decoration: none; transition: all 0.15s ease; white-space: nowrap; }
        html.dark .btn-download-riwayat { border-color: #0A0A0A; }
        .btn-download-riwayat:hover { transform: translate(1px, 1px); box-shadow: 1px 1px 0 #000; background: #6ee7b7; }
        
        .btn-download-riwayat.disabled { background: #F5F3FF; color: #9ca3af; cursor: not-allowed; pointer-events: none; box-shadow: none; border-color: #d1d5db; }
        html.dark .btn-download-riwayat.disabled { background: #1e1b4b; border-color: #4C1D95; color: #6b7280; }

        /* ========== Tombol Hapus ========== */
        .btn-hapus { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; background: #FECDD3; border: 2px solid #000; border-radius: 8px; box-shadow: 2px 2px 0 #000; font-size: 11px; font-weight: 800; color: #000; cursor: pointer; transition: all 0.15s ease; white-space: nowrap; }
        .btn-hapus:hover { transform: translate(1px, 1px); box-shadow: 1px 1px 0 #000; background: #fda4af; }
        html.dark .btn-hapus { border-color: #0A0A0A; }

        /* ========== Pagination ========== */
        .pagination-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; px: 8px; background: #fff; border: 2px solid #000; border-radius: 8px; box-shadow: 2px 2px 0 #000; font-size: 12px; font-weight: 800; color: #000; cursor: pointer; transition: all 0.15s ease; padding: 0 8px; }
        .pagination-btn:hover:not(:disabled) { transform: translate(1px, 1px); box-shadow: 1px 1px 0 #000; background: #EDE9FE; }
        .pagination-btn.active { background: #7C3AED; color: #fff; border-color: #000; }
        .pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; box-shadow: none; }
        html.dark .pagination-btn { background: #2d2460; color: #e2e8f0; }
        html.dark .pagination-btn.active { background: #7C3AED; }

        /* ========== Error Format Toast ========== */
        #format-error-toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(120px);
            z-index: 99999; display: flex; align-items: center; gap: 10px;
            background: #FECDD3; border: 2.5px solid #000; border-radius: 14px;
            box-shadow: 5px 5px 0 #000; padding: 12px 20px;
            font-weight: 900; font-size: 14px; color: #000;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
        }
        #format-error-toast.show { transform: translateX(-50%) translateY(0); }

        /* ========== Custom Confirm Modal (Hapus) ========== */
        #confirm-modal {
            position: fixed; inset: 0; z-index: 999999; display: none;
            align-items: center; justify-content: center;
            background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
        }
        #confirm-modal.active { display: flex; }
        #confirm-modal .cm-card {
            background: #fff; border: 3px solid #000; border-radius: 20px;
            box-shadow: 8px 8px 0 #000; padding: 32px 36px; max-width: 380px; width: 90%;
            text-align: center; position: relative; overflow: hidden;
            animation: cm-pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        html.dark #confirm-modal .cm-card { background: #2d2460; border-color: #4C1D95; box-shadow: 8px 8px 0 #000; }
        @keyframes cm-pop { from { opacity: 0; transform: scale(0.88) translateY(16px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        #confirm-modal .cm-card::before {
            content: ''; position: absolute; top: -24px; right: -24px;
            width: 80px; height: 80px; background: #FECDD3; border: 3px solid #000;
            border-radius: 50%; opacity: 0.7; pointer-events: none;
        }
        html.dark #confirm-modal .cm-card::before { border-color: #4C1D95; opacity: 0.25; }
        #confirm-modal .cm-card::after {
            content: ''; position: absolute; bottom: -18px; left: -18px;
            width: 60px; height: 60px; background: #FDE047; border: 3px solid #000;
            border-radius: 50%; opacity: 0.5; pointer-events: none;
        }
        html.dark #confirm-modal .cm-card::after { border-color: #4C1D95; opacity: 0.2; }

        #confirm-modal .cm-icon-wrap {
            width: 64px; height: 64px; border-radius: 16px; border: 3px solid #000;
            background: #FECDD3; box-shadow: 4px 4px 0 #000;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px; position: relative; z-index: 1;
        }
        html.dark #confirm-modal .cm-icon-wrap { border-color: #4C1D95; box-shadow: 4px 4px 0 #000; }

        #confirm-modal .cm-title {
            font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 900;
            color: #000; letter-spacing: -0.5px; position: relative; z-index: 1; margin-bottom: 6px;
        }
        html.dark #confirm-modal .cm-title { color: #f1f5f9; }

        #confirm-modal .cm-nama_file {
            display: inline-block; max-width: 260px; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; background: #F5F3FF; border: 2px solid #000; border-radius: 8px;
            padding: 3px 10px; font-size: 11px; font-weight: 800; color: #7C3AED;
            font-family: 'Plus Jakarta Sans', sans-serif; position: relative; z-index: 1;
            margin-bottom: 6px;
        }
        html.dark #confirm-modal .cm-nama_file { background: #1e1b4b; border-color: #4C1D95; }

        #confirm-modal .cm-subtitle {
            font-size: 12px; font-weight: 700; color: #6b7280;
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: relative; z-index: 1; margin-bottom: 24px;
        }
        html.dark #confirm-modal .cm-subtitle { color: #a78bfa; }

        #confirm-modal .cm-actions { display: flex; gap: 10px; justify-content: center; position: relative; z-index: 1; }

        #confirm-modal .cm-btn-cancel {
            flex: 1; padding: 10px 0; background: #F5F3FF; border: 2.5px solid #000; border-radius: 12px;
            box-shadow: 3px 3px 0 #000; font-size: 13px; font-weight: 900; color: #000;
            cursor: pointer; font-family: 'Outfit', sans-serif; transition: all 0.15s ease;
        }
        html.dark #confirm-modal .cm-btn-cancel { background: #1e1b4b; color: #e2e8f0; border-color: #4C1D95; box-shadow: 3px 3px 0 #000; }
        #confirm-modal .cm-btn-cancel:hover { transform: translate(1px, 1px); box-shadow: 2px 2px 0 #000; background: #EDE9FE; }

        #confirm-modal .cm-btn-delete {
            flex: 1; padding: 10px 0; background: #FECDD3; border: 2.5px solid #000; border-radius: 12px;
            box-shadow: 3px 3px 0 #000; font-size: 13px; font-weight: 900; color: #000;
            cursor: pointer; font-family: 'Outfit', sans-serif; transition: all 0.15s ease;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        html.dark #confirm-modal .cm-btn-delete { border-color: #4C1D95; box-shadow: 3px 3px 0 #000; }
        #confirm-modal .cm-btn-delete:hover { transform: translate(1px, 1px); box-shadow: 2px 2px 0 #000; background: #fda4af; }
        #confirm-modal .cm-btn-delete:disabled { opacity: 0.6; cursor: not-allowed; pointer-events: none; }

        /* ========== Custom Alert Toast (sukses/error notif) ========== */
        #notif-toast {
            position: fixed; bottom: 28px; right: 28px;
            z-index: 999999; display: flex; align-items: center; gap: 12px;
            border: 2.5px solid #000; border-radius: 16px;
            box-shadow: 5px 5px 0 #000; padding: 14px 20px;
            font-weight: 900; font-size: 13px; color: #000;
            min-width: 260px; max-width: 340px;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-family: 'Outfit', sans-serif;
        }
        #notif-toast.show { transform: translateX(0); }
        #notif-toast.type-success { background: #A7F3D0; }
        #notif-toast.type-error   { background: #FECDD3; }
        #notif-toast .nt-icon { font-size: 20px; flex-shrink: 0; }
        #notif-toast .nt-body .nt-title { font-size: 13px; font-weight: 900; }
        #notif-toast .nt-body .nt-sub   { font-size: 11px; font-weight: 700; color: #374151; margin-top: 1px; }
    </style>
</head>
<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300 relative">

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>

    <div id="loading-modal" role="dialog" aria-modal="true" aria-label="Proses komputasi server berjalan">
        <div class="modal-card">
            <div class="modal-loader">
                <div class="orbit"></div>
                <div class="orb orb-1"></div>
                <div class="orb orb-2"></div>
                <div class="orb orb-3"></div>
                <div class="orb orb-4"></div>
                <div class="center-dot"></div>
            </div>
            <h2 id="modal-title" class="text-2xl font-black text-black dark:text-white mb-1 relative z-10 font-sans tracking-tight">Memproses Parameter</h2>
            <p id="modal-subtitle" class="text-xs font-bold text-gray-500 dark:text-purple-300/60 relative z-10 font-body uppercase tracking-widest">Pipeline terhubung ke server</p>

            <div class="modal-progress-track relative z-10">
                <div class="modal-progress-bar" id="modal-progress-bar"></div>
            </div>
            <p class="modal-status-text relative z-10" id="modal-status-text">Menginisialisasi core engine...</p>

            <div class="modal-warning relative z-10" id="modal-warning">
                <i class="fa-solid fa-triangle-exclamation mr-1 text-red-500"></i> Mode AI Terdeteksi.<br>Parsing dokumen dapat memakan waktu 10-60 detik.
            </div>

            <div class="mt-5 flex items-center justify-center gap-2 text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-purple-300/50 relative z-10 bg-gray-100 dark:bg-[#1e1b4b] px-3 py-1.5 rounded-md border-2 border-dashed border-gray-300 dark:border-p-dark">
                <i class="fa-solid fa-lock text-p-mid"></i> Sesi I/O Terkunci
            </div>
        </div>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden opacity-0 transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <!-- Toast: Format File Salah -->
    <div id="format-error-toast">
        <i class="fa-solid fa-circle-xmark text-red-600 text-xl"></i>
        <span>Format File Salah! Pastikan tipe file sesuai pipeline.</span>
    </div>

    <!-- Custom Confirm Modal: Hapus Log -->
    <div id="confirm-modal" role="dialog" aria-modal="true">
        <div class="cm-card">
            <div class="cm-icon-wrap">
                <i class="fa-solid fa-trash-can text-2xl text-red-500"></i>
            </div>
            <div class="cm-title">Hapus Log Ini?</div>
            <div class="cm-nama_file" id="cm-nama_file">—</div>
            <div class="cm-subtitle">Tindakan ini permanen dan tidak dapat dibatalkan.</div>
            <div class="cm-actions">
                <button class="cm-btn-cancel" id="cm-btn-cancel">
                    <i class="fa-solid fa-xmark mr-1"></i> Batal
                </button>
                <button class="cm-btn-delete" id="cm-btn-delete">
                    <i class="fa-solid fa-trash-can"></i> Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Notif Toast: Sukses / Error -->
    <div id="notif-toast">
        <i class="nt-icon fa-solid fa-check-circle"></i>
        <div class="nt-body">
            <div class="nt-title" id="nt-title">Berhasil</div>
            <div class="nt-sub"  id="nt-sub">—</div>
        </div>
    </div>

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
                <a href="{{ route('materi.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-book-open w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Materi Belajar</span>
                </a>
                
                <a href="#" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
                    <i class="fa-solid fa-file-export w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Kelola File</span>
                </a>

                <a href="{{ route('soal.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-clipboard-question w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Bank Soal</span>
                </a>

                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mt-6 mb-2 uppercase tracking-widest">Data Akademik</p>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-users w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Data Siswa</span>
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
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Konversi Data (Automasi AI)</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">Sistem ekstraksi dokumen statis ke database JSON.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" aria-label="Toggle Dark Mode" class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300 relative">
                
                <div id="alert-container" class="hidden mb-6 flex items-center gap-3 px-6 py-4 rounded-xl border-2 border-black shadow-neo-sm text-sm font-bold fade-in"></div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10 items-start">
                    
                    <div id="form-card" class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo overflow-hidden transition-all duration-300 fade-in fade-in-1 lg:sticky lg:top-0">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-white dark:bg-[#2d2460] flex justify-between items-start">
                            <div>
                                <h3 id="form-title" class="text-xl font-black text-black dark:text-white">Eksekusi Pipeline</h3>
                                <p id="form-subtitle" class="text-xs font-bold text-gray-500 dark:text-purple-300/60 mt-1 uppercase tracking-wider">Validasi & Konversi Format</p>
                            </div>
                            <div id="ai-badge" class="hidden bg-neo-cyan text-black px-2.5 py-1 rounded-md border-2 border-black shadow-neo-sm font-black text-[10px] uppercase flex items-center gap-1.5">
                                <i class="fa-solid fa-microchip"></i> Engine Active
                            </div>
                        </div>

                        <div class="p-6">
                            <form id="uploadForm" enctype="multipart/form-data" class="space-y-6 relative">
                                <div>
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Algoritma Parsing <span class="text-neo-red">*</span></label>
                                    <div id="folder-nama-container" class="mt-4">
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Nama Folder / Materi <span class="text-neo-red">*</span></label>
                                    <input type="text" name="folder_name" id="folder_name" placeholder="Contoh: matematika-bab-1" required
                                        class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all">
                                    <p class="text-[11px] font-bold text-gray-500 mt-1">Nama ini akan digunakan sebagai nama folder gambar dan file JSON.</p>
                                    <select name="type" id="file_type" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p dark:focus:border-p-mid focus:shadow-neo-p transition-all cursor-pointer appearance-none">
                                        <option value="materi">Standar: PDF Ekstraksi Teks Dasar</option>
                                    </select>
                                    <div id="page-range-inputs" class="grid grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Hal. Awal <span class="text-gray-400 normal-case">(opsional)</span></label>
                                            <input type="number" name="page_start" id="page_start" placeholder="Contoh: 1" min="1"
                                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Hal. Akhir <span class="text-gray-400 normal-case">(opsional)</span></label>
                                            <input type="number" name="page_end" id="page_end" placeholder="Contoh: 15" min="1"
                                                class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all">
                                        </div>
                                        <p class="col-span-2 text-[11px] font-bold text-gray-500 mt-[-8px]">Gunakan ini untuk memecah PDF per Bab. Biarkan kosong untuk proses semua halaman.</p>
                                    </div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Target File <span class="text-neo-red">*</span></label>
                                    <div id="drop-zone" class="border-2 border-dashed border-black dark:border-p-dark bg-p-xlt dark:bg-p-dark/20 rounded-xl p-8 text-center transition-all relative group shadow-neo-sm cursor-pointer hover:bg-p-lt dark:hover:bg-p-dark/40">
                                        <div class="flex flex-col items-center pointer-events-none">
                                            <div id="icon-container" class="w-14 h-14 bg-white border-2 border-black text-black rounded-full flex items-center justify-center mb-3 transition-all shadow-neo-sm group-hover:-translate-y-1">
                                                <i id="upload_icon" class="fa-solid fa-file-pdf text-xl text-red-500"></i>
                                            </div>
                                            <span id="file_name_display" class="text-sm font-black text-black dark:text-white mb-1 uppercase tracking-wide">Pilih Dokumen</span>
                                            <span id="file_rules" class="text-[11px] font-bold text-gray-500 dark:text-purple-300/60 font-mono">Accept: .pdf (Memisahkan Teks & Binary Image)</span>
                                        </div>
                                        <input type="file" name="upload_file" id="file_input" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" accept=".pdf" required>
                                    </div>
                                </div>

                                <div class="pt-2 mt-4">
                                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Gambar Sisipan AI (Maks 5) <span class="text-gray-400 normal-case">(Opsional)</span></label>
                                    <div class="border-2 border-dashed border-black dark:border-p-dark bg-neo-cyan/10 dark:bg-p-dark/20 rounded-xl p-6 text-center transition-all relative group shadow-neo-sm cursor-pointer hover:bg-neo-cyan/30 dark:hover:bg-p-dark/40">
                                        <div class="flex flex-col items-center pointer-events-none">
                                            <div class="w-12 h-12 bg-white border-2 border-black text-black rounded-full flex items-center justify-center mb-2 transition-all shadow-neo-sm group-hover:-translate-y-1">
                                                <i class="fa-solid fa-images text-lg text-blue-500"></i>
                                            </div>
                                            <span id="image_name_display" class="text-sm font-black text-black dark:text-white mb-1 uppercase tracking-wide">Pilih Gambar</span>
                                            <span class="text-[11px] font-bold text-gray-500 dark:text-purple-300/60 font-mono">Format: .jpg, .png (Pilih s/d 5 file sekaligus)</span>
                                        </div>
                                        <input type="file" name="teacher_images[]" id="teacher_images" class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" accept="image/*" multiple onchange="validateImages(this)">
                                    </div>
                                </div>

                                <script>
                                function validateImages(input) {
                                    if (input.files.length > 5) {
                                        Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Anda hanya dapat mengunggah maksimal 5 gambar.' });
                                        input.value = '';
                                        document.getElementById('image_name_display').innerText = 'Pilih Gambar';
                                        return;
                                    }
                                    document.getElementById('image_name_display').innerText = input.files.length > 0 ? input.files.length + ' Gambar Terpilih' : 'Pilih Gambar';
                                }
                                </script>

                                <div class="pt-6 border-t-2 border-p-lt dark:border-p-dark mt-6">
                                    <button type="submit" id="submit-btn" class="w-full bg-neo-yellow hover:bg-yellow-400 text-black border-2 border-black rounded-xl shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all flex justify-center items-center gap-2 py-3.5 text-sm font-black uppercase tracking-wide">
                                        <i class="fa-solid fa-file-lines"></i> Jalankan Native PDF Parser
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo overflow-hidden flex flex-col fade-in fade-in-2">
                        <div class="p-6 border-b-2 border-black dark:border-p-dark bg-white dark:bg-[#2d2460] flex justify-between items-center">
                            <h2 class="text-lg font-black text-black dark:text-white">Log Eksekusi Server</h2>
                            <span class="bg-p-lt dark:bg-p-dark/50 text-p-dark dark:text-purple-200 text-[10px] font-black px-2.5 py-1 rounded-md border-2 border-black dark:border-p-dark shadow-neo-sm uppercase">
                                History
                            </span>
                        </div>
                        <div class="overflow-x-auto flex-1 font-body no-scrollbar">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead class="sticky top-0 bg-p-xlt dark:bg-p-dark/40 z-10 shadow-[0_2px_0_0_#000] dark:shadow-[0_2px_0_0_#4C1D95]">
                                    <tr class="text-sm border-b-2 border-black dark:border-p-dark">
                                        <th class="px-5 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">File Header</th>
                                        <th class="px-4 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Size</th>
                                        <th class="px-4 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Kondisi</th>
                                        <th class="px-4 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Output</th>
                                        <th class="px-4 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="log-table-body" class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm font-medium">
                                    @forelse($histories ?? [] as $history)
                                    <tr class="log-row hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group" data-id="{{ $history->id }}">
                                        <td class="px-5 py-4">
                                            <div class="font-bold text-black dark:text-white truncate max-w-[180px] text-sm" title="{{ $history->nama_file_asli }}">{{ $history->nama_file_asli }}</div>
                                            <div class="text-[10px] font-bold text-gray-500 dark:text-purple-300/60 mt-1 uppercase tracking-wider flex items-center gap-1.5">
                                                @if(str_contains($history->tipe_konversi, 'ai'))
                                                    <span class="text-neo-cyan dark:text-cyan-400"><i class="fa-solid fa-robot"></i></span>
                                                @else
                                                    <i class="fa-solid fa-gear text-p-mid"></i>
                                                @endif
                                                {{ str_replace('_', ' → ', $history->tipe_konversi) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center font-mono font-bold text-black dark:text-white text-xs bg-p-xlt dark:bg-[#1e1b4b]">
                                            {{ number_format($history->ukuran_file_kb / 1024, 2) }} <span class="text-[10px] text-gray-500">MB</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @if($history->status == 'success')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-neo-green text-black border-2 border-black shadow-neo-sm">
                                                    <i class="fa-solid fa-check"></i> Valid
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-neo-red text-black border-2 border-black shadow-neo-sm" title="{{ $history->log_error }}">
                                                    <i class="fa-solid fa-xmark"></i> Galat
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @if($history->status == 'success' && $history->jalur_output_json && $history->jalur_output_json !== '-')
                                                <a href="{{ route('converter.download', ['path' => base64_encode($history->jalur_output_json)]) }}"
                                                   class="btn-download-riwayat text-[10px] uppercase tracking-wider" title="Unduh JSON">
                                                    <i class="fa-solid fa-download"></i> Get
                                                </a>
                                            @else
                                                <span class="btn-download-riwayat disabled text-[10px] uppercase tracking-wider" title="Blob tidak tersedia">
                                                    <i class="fa-solid fa-ban"></i> Null
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <button class="btn-hapus" onclick="hapusLog({{ $history->id }}, this)" title="Hapus log ini">
                                                <i class="fa-solid fa-trash-can"></i> Del
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="5" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                            <div class="inline-flex items-center justify-center w-14 h-14 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-xl text-p-mid mb-3">
                                                <i class="fa-solid fa-server text-xl"></i>
                                            </div>
                                            <p class="text-gray-500 dark:text-purple-300/50 font-bold text-xs uppercase tracking-widest">Log Eksekusi Bersih</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div id="pagination-container" class="px-5 py-4 border-t-2 border-p-lt dark:border-p-dark flex items-center justify-between gap-3 bg-white dark:bg-[#2d2460]">
                            <span id="pagination-info" class="text-[11px] font-bold text-gray-500 dark:text-purple-300/60 uppercase tracking-wider"></span>
                            <div id="pagination-buttons" class="flex items-center gap-1.5"></div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // System Preloader
        window.addEventListener('DOMContentLoaded', () => {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 500);
        });

        // DOM Referensi Cepat O(1)
        const typeSelect       = document.getElementById('file_type');
        const fileInput        = document.getElementById('file_input');
        const fileIcon         = document.getElementById('upload_icon');
        const fileRules        = document.getElementById('file_rules');
        const fileNameDisplay  = document.getElementById('file_name_display');
        const dropZone         = document.getElementById('drop-zone');
        const submitBtn        = document.getElementById('submit-btn');
        const aiBadge          = document.getElementById('ai-badge');
        const iconContainer    = document.getElementById('icon-container');

        // Modal Referensi
        const loadingModal     = document.getElementById('loading-modal');
        const modalTitle       = document.getElementById('modal-title');
        const modalSubtitle    = document.getElementById('modal-subtitle');
        const modalStatusText  = document.getElementById('modal-status-text');
        const modalWarning     = document.getElementById('modal-warning');
        const modalProgressBar = document.getElementById('modal-progress-bar');

        // State Controller Modal (Isolasi UI Block)
        function showLoadingModal(isAI = false) {
            modalProgressBar.style.animation = 'none';
            void modalProgressBar.offsetWidth;
            modalProgressBar.style.animation = '';

            if (isAI) {
                modalTitle.textContent    = 'Engine AI Aktif';
                modalSubtitle.textContent = 'Membaca pointer dan menstrukturkan array...';
                modalWarning.classList.add('show');
            } else {
                modalTitle.textContent    = 'Memproses Parameter';
                modalSubtitle.textContent = 'Pipeline terhubung ke server...';
                modalWarning.classList.remove('show');
            }

            loadingModal.classList.add('active');

            const statusMessages = isAI
                ? [
                    'Membangun socket ke OpenAI API...',
                    'Deep parsing dokumen PDF...',
                    'Memetakan relasional teks (Vision)...',
                    'Mengonversi struktur data...',
                    'Menyusun blob file JSON...',
                    'Finalisasi payload...',
                ]
                : [
                    'Menginisialisasi kernel parser...',
                    'Memvalidasi ekstensi file...',
                    'Memulai proses I/O streaming...',
                    'Menyimpan blob output...',
                    'Finalisasi checksum...',
                ];

            let idx = 0;
            modalStatusText.textContent = statusMessages[0];
            window._statusInterval = setInterval(() => {
                idx = (idx + 1) % statusMessages.length;
                modalStatusText.textContent = statusMessages[idx];
            }, 2500);
        }

        function hideLoadingModal() {
            loadingModal.classList.remove('active');
            if (window._statusInterval) clearInterval(window._statusInterval);
        }

        // Keamanan Modal Terhadap User Interruption
        loadingModal.addEventListener('keydown', e => e.preventDefault());
        loadingModal.addEventListener('click', e => {
            if (e.target === loadingModal) e.stopPropagation();
        });

        // Kontrol State Pipeline Dinamis
        typeSelect.addEventListener('change', function() {
            fileInput.value = "";
            fileNameDisplay.innerText = "PILIH DOKUMEN";
            fileNameDisplay.classList.remove('text-neo-cyan', 'dark:text-cyan-400');
            const pageRangeInputs = document.getElementById('page-range-inputs');

            // Resetting Base Styles
            dropZone.classList.remove('bg-neo-cyan', 'ai-active-glow', 'bg-opacity-20', 'dark:bg-cyan-900/30');
            iconContainer.classList.remove('text-neo-cyan');
            submitBtn.classList.remove('bg-neo-cyan', 'hover:bg-cyan-400', 'text-black');
            
            submitBtn.classList.add('bg-neo-yellow', 'hover:bg-yellow-400', 'text-black');
            aiBadge.classList.add('hidden');

            if (this.value === 'kamus') {
                pageRangeInputs.classList.add('hidden');
                fileInput.accept = ".xls,.xlsx,.csv";
                fileIcon.className = "fa-solid fa-file-excel text-xl text-green-600";
                fileRules.innerText = "Accept: .xls, .xlsx, .csv (Max: 512MB)";
                submitBtn.innerHTML = '<i class="fa-solid fa-bolt"></i> Inisiasi Konversi Deterministik';

            } else if (this.value === 'materi') {
                pageRangeInputs.classList.remove('hidden');
                fileInput.accept = ".pdf";
                fileIcon.className = "fa-solid fa-file-pdf text-xl text-red-500";
                fileRules.innerText = "Accept: .pdf (Memisahkan Teks & Binary Image)";
                submitBtn.innerHTML = '<i class="fa-solid fa-file-lines"></i> Jalankan Native PDF Parser';

            } else if (this.value === 'pdf_to_excel') {
                pageRangeInputs.classList.remove('hidden');
                fileInput.accept = ".pdf";
                fileIcon.className = "fa-solid fa-robot text-2xl";
                iconContainer.classList.add('text-neo-cyan');
                fileRules.innerHTML = "Accept: .pdf <br>AI Generative memetakan struktur kamus otonom.";
                dropZone.classList.add('bg-neo-cyan', 'bg-opacity-20', 'ai-active-glow', 'dark:bg-cyan-900/30');
                
                submitBtn.classList.remove('bg-neo-yellow', 'hover:bg-yellow-400');
                submitBtn.classList.add('bg-neo-cyan', 'hover:bg-cyan-400', 'text-black');
                submitBtn.innerHTML = '<i class="fa-solid fa-microchip"></i> Execute AI Extraction Engine';
                aiBadge.classList.remove('hidden');
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                if (!isValidFileFormat(file)) {
                    showFormatError();
                    this.value = '';
                    fileNameDisplay.innerText = 'PILIH DOKUMEN';
                    fileNameDisplay.classList.remove('text-neo-cyan', 'dark:text-cyan-400');
                    return;
                }
                fileNameDisplay.innerText = file.name;
                fileNameDisplay.classList.add('text-neo-cyan', 'dark:text-cyan-400');
            }
        });

        // ========================
        // DRAG & DROP VALIDASI FORMAT
        // ========================
        function getAcceptedExtensions() {
            const val = typeSelect.value;
            if (val === 'kamus') return ['xls', 'xlsx', 'csv'];
            return ['pdf'];
        }

        function isValidFileFormat(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            return getAcceptedExtensions().includes(ext);
        }

        function showFormatError() {
            const toast = document.getElementById('format-error-toast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        }

        const MAX_UPLOAD_BYTES = 500 * 1024 * 1024;

        function formatBytes(bytes) {
            return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
        }

        function showUploadSizeError(file) {
            const alertBox = document.getElementById('alert-container');
            alertBox.className = "mb-6 flex items-center gap-3 px-5 py-3 rounded-xl border-2 border-black shadow-neo-sm text-sm font-black bg-neo-red text-black uppercase tracking-wide fade-in";
            alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-xl"></i> <div>File terlalu besar.<br><span class="text-[10px] font-bold tracking-normal normal-case text-gray-700">Ukuran file ${formatBytes(file.size)}. Batas aman upload aplikasi adalah 500 MB.</span></div>`;
            alertBox.classList.remove('hidden');
        }

        function normalizeFetchError(rawResponse) {
            const plain = rawResponse.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            if (/POST Content-Length .* exceeds the limit/i.test(plain)) {
                return 'Ukuran upload melebihi batas post_max_size PHP. Restart service PHP dan Apache di FlyEnv setelah menaikkan post_max_size dan upload_max_filesize.';
            }
            return plain.slice(0, 500) || 'Server mengembalikan response non-JSON.';
        }

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-p', 'bg-p-lt');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-p', 'bg-p-lt');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-p', 'bg-p-lt');
            const files = e.dataTransfer.files;
            if (!files || files.length === 0) return;
            const file = files[0];
            if (!isValidFileFormat(file)) {
                showFormatError();
                return;
            }
            if (file.size > MAX_UPLOAD_BYTES) {
                showUploadSizeError(file);
                return;
            }
            // Assign ke file input
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileNameDisplay.innerText = file.name;
            fileNameDisplay.classList.add('text-neo-cyan', 'dark:text-cyan-400');
        });

        // ========================
        // VALIDASI TAMBAHAN SAAT SUBMIT (CEGAH FORMAT SALAH LOLOS)
        // ========================
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Cek format sekali lagi sebelum submit
            if (fileInput.files.length > 0 && !isValidFileFormat(fileInput.files[0])) {
                showFormatError();
                return;
            }
            if (fileInput.files.length > 0 && fileInput.files[0].size > MAX_UPLOAD_BYTES) {
                showUploadSizeError(fileInput.files[0]);
                return;
            }

            const form = this;
            const alertBox = document.getElementById('alert-container');
            const formData = new FormData(form);
            const isAI = typeSelect.value === 'pdf_to_excel';

            alertBox.classList.add('hidden');
            showLoadingModal(isAI);
            submitBtn.disabled = true;

            try {
                const response = await fetch("{{ route('converter.process') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });

                const rawResponse = await response.text();
                let result = {};
                try {
                    result = rawResponse ? JSON.parse(rawResponse) : {};
                } catch (parseError) {
                    throw new Error(normalizeFetchError(rawResponse));
                }

                hideLoadingModal();
                submitBtn.disabled = false;

                if (response.ok) {
                    alertBox.className = "mb-6 flex items-center gap-3 px-5 py-3 rounded-xl border-2 border-black shadow-neo-sm text-sm font-black bg-neo-green text-black uppercase tracking-wide fade-in";
                    alertBox.innerHTML = `<i class="fa-solid fa-check-circle text-xl"></i> <div>${result.message}<br><span class="text-[10px] font-bold tracking-normal capitalize text-gray-700">Mengeksekusi protokol unduhan...</span></div>`;
                    alertBox.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.href = result.download_url;
                        setTimeout(() => window.location.reload(), 2000);
                    }, 1000);

                } else {
                    let errors = result.errors ? result.errors.join('<br>') : 'Fatal Error pada Logic Engine.';
                    alertBox.className = "mb-6 flex items-center gap-3 px-5 py-3 rounded-xl border-2 border-black shadow-neo-sm text-sm font-black bg-neo-red text-black uppercase tracking-wide fade-in";
                    alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-xl"></i> <div>${errors}</div>`;
                    alertBox.classList.remove('hidden');
                }

            } catch (error) {
                hideLoadingModal();
                submitBtn.disabled = false;
                alertBox.className = "mb-6 flex items-center gap-3 px-5 py-3 rounded-xl border-2 border-black shadow-neo-sm text-sm font-black bg-neo-red text-black uppercase tracking-wide fade-in";
                alertBox.innerHTML = `<i class="fa-solid fa-server text-xl"></i> <div>Request gagal diproses.<br><span class="text-[10px] font-bold tracking-normal normal-case text-gray-700">${error.message || 'Koneksi timeout / terputus. Periksa log server dan batas waktu Apache/PHP.'}</span></div>`;
                alertBox.classList.remove('hidden');
            }
        });

        // ========================
        // PAGINATION LOGIC
        // ========================
        const ROWS_PER_PAGE = 5;
        let currentPage = 1;

        function getAllRows() {
            return Array.from(document.querySelectorAll('#log-table-body tr.log-row'));
        }

        function renderPagination() {
            const rows = getAllRows();
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));

            if (currentPage > totalPages) currentPage = totalPages;

            // Show/hide rows
            rows.forEach((row, i) => {
                const page = Math.floor(i / ROWS_PER_PAGE) + 1;
                row.style.display = (page === currentPage) ? '' : 'none';
            });

            // Update info
            const start = total === 0 ? 0 : (currentPage - 1) * ROWS_PER_PAGE + 1;
            const end   = Math.min(currentPage * ROWS_PER_PAGE, total);
            document.getElementById('pagination-info').textContent = total > 0
                ? `Menampilkan ${start}–${end} dari ${total} log`
                : 'Tidak ada log';

            // Render buttons
            const btnContainer = document.getElementById('pagination-buttons');
            btnContainer.innerHTML = '';

            if (totalPages <= 1) return;

            // Prev
            const prevBtn = document.createElement('button');
            prevBtn.className = 'pagination-btn';
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-[10px]"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => { currentPage--; renderPagination(); };
            btnContainer.appendChild(prevBtn);

            // Pages
            for (let p = 1; p <= totalPages; p++) {
                const btn = document.createElement('button');
                btn.className = 'pagination-btn' + (p === currentPage ? ' active' : '');
                btn.textContent = p;
                btn.onclick = ((pg) => () => { currentPage = pg; renderPagination(); })(p);
                btnContainer.appendChild(btn);
            }

            // Next
            const nextBtn = document.createElement('button');
            nextBtn.className = 'pagination-btn';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-[10px]"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => { currentPage++; renderPagination(); };
            btnContainer.appendChild(nextBtn);
        }

        // Init pagination on load
        document.addEventListener('DOMContentLoaded', () => { renderPagination(); });
        window.addEventListener('DOMContentLoaded', () => { renderPagination(); });

        // ========================
        // HAPUS LOG (AJAX DELETE) — Custom Modal
        // ========================
        let _hapusPendingId  = null;
        let _hapusPendingBtn = null;
        let _hapusPendingFilename = '';

        const confirmModal   = document.getElementById('confirm-modal');
        const cmFilename     = document.getElementById('cm-nama_file');
        const cmBtnCancel    = document.getElementById('cm-btn-cancel');
        const cmBtnDelete    = document.getElementById('cm-btn-delete');
        const notifToast     = document.getElementById('notif-toast');
        const ntIcon         = notifToast.querySelector('.nt-icon');
        const ntTitle        = document.getElementById('nt-title');
        const ntSub          = document.getElementById('nt-sub');

        function showNotif(type, title, sub) {
            notifToast.className = `show type-${type}`;
            ntIcon.className = `nt-icon fa-solid ${type === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-red-500'}`;
            ntTitle.textContent = title;
            ntSub.textContent   = sub;
            clearTimeout(notifToast._timer);
            notifToast._timer = setTimeout(() => { notifToast.classList.remove('show'); }, 3500);
        }

        function openConfirmModal(id, btn, nama_file) {
            _hapusPendingId  = id;
            _hapusPendingBtn = btn;
            _hapusPendingFilename = nama_file;
            cmFilename.textContent = nama_file || `Log #${id}`;
            cmBtnDelete.disabled = false;
            cmBtnDelete.innerHTML = '<i class="fa-solid fa-trash-can"></i> Ya, Hapus';
            confirmModal.classList.add('active');
        }

        function closeConfirmModal() {
            confirmModal.classList.remove('active');
            _hapusPendingId  = null;
            _hapusPendingBtn = null;
        }

        cmBtnCancel.addEventListener('click', closeConfirmModal);
        confirmModal.addEventListener('click', (e) => { if (e.target === confirmModal) closeConfirmModal(); });

        cmBtnDelete.addEventListener('click', async () => {
            if (!_hapusPendingId) return;

            const id  = _hapusPendingId;
            const btn = _hapusPendingBtn;

            cmBtnDelete.disabled = true;
            cmBtnDelete.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }

            try {
                const deleteUrl = `{{ rtrim(route('converter.log.destroy', ['id' => '__ID__']), '') }}`.replace('__ID__', id);
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                closeConfirmModal();

                if (response.ok) {
                    showNotif('success', 'Log Berhasil Dihapus', _hapusPendingFilename || `Log #${id}`);
                    const row = btn ? btn.closest('tr.log-row') : document.querySelector(`tr.log-row[data-id="${id}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(24px)';
                        setTimeout(() => {
                            row.remove();
                            if (getAllRows().length === 0) {
                                const tbody = document.getElementById('log-table-body');
                                tbody.innerHTML = `<tr id="empty-row"><td colspan="5" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]"><div class="inline-flex items-center justify-center w-14 h-14 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-xl text-p-mid mb-3"><i class="fa-solid fa-server text-xl"></i></div><p class="text-gray-500 dark:text-purple-300/50 font-bold text-xs uppercase tracking-widest">Log Eksekusi Bersih</p></td></tr>`;
                            }
                            renderPagination();
                        }, 300);
                    }
                } else {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Del'; }
                    showNotif('error', 'Gagal Menghapus', 'Server menolak permintaan. Coba lagi.');
                }
            } catch (err) {
                closeConfirmModal();
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Del'; }
                showNotif('error', 'Koneksi Error', 'Tidak dapat terhubung ke server.');
            }
        });

        function hapusLog(id, btn) {
            const row = btn.closest('tr.log-row');
            const nama_file = row ? row.querySelector('.font-bold.text-black')?.textContent?.trim() : `Log #${id}`;
            openConfirmModal(id, btn, nama_file);
        }

        // Dark Mode Logic
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

        // Overlay Sidebar Mobile
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
</body>
</html>
