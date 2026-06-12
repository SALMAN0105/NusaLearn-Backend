<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fade-up 0.4s ease-out both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }

        /* Template badge colors */
        .badge-multiple_choice  { background: #A5F3FC; }
        .badge-drag_and_drop    { background: #F9A8D4; }
        .badge-matching_game    { background: #A7F3D0; }
        .badge-fill_blank       { background: #FDE047; }
        .badge-image_quiz       { background: #FFB8A3; }

        /* JSON Preview */
        .json-preview {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 0.8s linear infinite; }

        /* AI loading state */
        #btn-generate.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        /* ── FIX: Modal wrapper harus flex col agar tombol tidak terpotong ── */
        .modal-scroll-wrap {
            display: flex;
            flex-direction: column;
            max-height: 92vh;
        }
        .modal-scroll-body {
            flex: 1;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .modal-scroll-body::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-p-xlt text-black font-sans selection:bg-p-lt selection:text-p-dark dark:bg-[#1e1b4b] dark:text-gray-100 transition-colors duration-300 relative">

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <i class="fa-solid fa-sparkles text-xl ml-1"></i></div>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden opacity-0 transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <div class="flex h-screen overflow-hidden p-2 md:p-4 gap-4">

        {{-- ════ SIDEBAR ════ --}}
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
                <a href="{{ route('converter.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-file-export w-5 text-center flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                    <span>Kelola File</span>
                </a>
                <a href="#" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
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
                         alt="Avatar" class="w-10 h-10 rounded-full border-2 border-black dark:border-p-dark shadow-neo-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-black dark:text-white truncate">{{ Auth::user()->nama ?? 'Guru' }}</p>
                        <p class="text-xs text-gray-400 font-semibold truncate">Sistem Inti Laravel</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-9 h-9 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 hover:bg-neo-red hover:text-black rounded-lg transition-all shadow-neo-sm hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none flex-shrink-0">
                            <i class="fa-solid fa-power-off text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ════ MAIN CONTENT ════ --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-[#241f5c] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo">

            <header class="h-20 border-b-2 border-black dark:border-p-dark flex items-center justify-between px-6 lg:px-10 z-20 sticky top-0 bg-white dark:bg-[#241f5c] rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="md:hidden w-10 h-10 flex items-center justify-center bg-p-lt border-2 border-black text-p rounded-xl shadow-neo-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>
                    <div class="hidden md:block">
                        <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Bank Soal & Kuis Multimedia</h1>
                        <p class="text-xs font-semibold text-gray-400 dark:text-purple-300/60 mt-1">AI-generated interactive quiz dengan 5 template soal.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    
                    <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark text-p dark:text-purple-300 rounded-xl shadow-neo-sm hover:bg-p hover:text-white dark:hover:bg-p transition-all">
                        <i id="theme-toggle-icon" class="fa-solid fa-moon text-base"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-10 bg-p-xlt dark:bg-[#1e1b4b] dot-grid-bg transition-colors duration-300">

                {{-- ── Toolbar ── --}}
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-5 bg-white dark:bg-[#2d2460] p-5 rounded-2xl shadow-neo border-2 border-black dark:border-p-dark fade-in fade-in-1">
                    <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
                        <div class="relative w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pertanyaan..."
                                   class="w-full pl-11 pr-4 py-3 bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all placeholder:text-gray-400">
                        </div>
                        <select name="materi_id" onchange="this.form.submit()"
                                class="w-full sm:w-auto border-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b] rounded-xl py-3 px-4 text-sm text-black dark:text-white font-bold outline-none cursor-pointer appearance-none">
                            <option value="all">Semua Materi</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}" {{ request('materi_id') == $mat->id ? 'selected' : '' }}>{{ Str::limit($mat->judul, 30) }}</option>
                            @endforeach
                        </select>
                        <select name="tipe_template" onchange="this.form.submit()"
                                class="w-full sm:w-auto border-2 border-black dark:border-p-dark bg-p-xlt dark:bg-[#1e1b4b] rounded-xl py-3 px-4 text-sm text-black dark:text-white font-bold outline-none cursor-pointer appearance-none">
                            <option value="">Semua Template</option>
                            @foreach(\App\Models\Soal::ALL_TEMPLATES as $key => $label)
                                <option value="{{ $key }}" {{ request('tipe_template') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div class="flex gap-3 w-full xl:w-auto">
                        <button onclick="toggleModal('modal-add')" class="flex-1 xl:flex-none bg-neo-cyan hover:bg-cyan-300 text-black px-5 py-3 rounded-xl border-2 border-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all text-sm font-black flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen-nib"></i> Manual
                        </button>
                        {{-- <button onclick="toggleModal('modal-ai-generate')" class="flex-1 xl:flex-none bg-p hover:bg-p-dark text-white px-5 py-3 rounded-xl border-2 border-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all text-sm font-black flex items-center justify-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> AI Generate
                        </button> --}}
                    </div>
                </div>

                {{-- ── Tabel Soal ── --}}
                <div class="bg-white dark:bg-[#2d2460] rounded-2xl shadow-neo border-2 border-black dark:border-p-dark overflow-hidden flex flex-col fade-in fade-in-2">
                    <div class="overflow-x-auto flex-1 font-body">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-p-xlt dark:bg-p-dark/40 border-b-2 border-black dark:border-p-dark">
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Pertanyaan</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider">Materi</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Template</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Bobot</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-center">Aset</th>
                                    <th class="px-6 py-4 text-xs font-black text-p-dark dark:text-purple-300 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-p-lt dark:divide-p-dark/30 text-sm">
                                @forelse($questions as $q)
                                <tr class="hover:bg-gray-50 dark:hover:bg-p-dark/20 transition-colors group">
                                    <td class="px-6 py-5 max-w-xs whitespace-normal">
                                        <div class="font-bold text-black dark:text-white line-clamp-2 leading-relaxed">{{ $q->teks_soal }}</div>
                                        <div class="text-[10px] font-bold text-gray-500 dark:text-purple-300/60 mt-1 flex items-center gap-1.5">
                                            <i class="fa-solid fa-microchip text-p-mid"></i> Auto-translate Engine
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="inline-flex items-center gap-2 bg-p-xlt dark:bg-p-dark/50 text-black dark:text-white px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black dark:border-p-dark shadow-neo-sm">
                                            <i class="fa-solid fa-link text-p-mid"></i> {{ Str::limit($q->materi->judul ?? 'Null', 22) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @php $ttype = $q->tipe_template ?? 'multiple_choice'; @endphp
                                        <span class="badge-{{ $ttype }} inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-black border-2 border-black shadow-neo-sm uppercase tracking-wide">
                                            @switch($ttype)
                                                @case('drag_and_drop') <i class="fa-solid fa-hand-pointer"></i> @break
                                                @case('matching_game') <i class="fa-solid fa-shuffle"></i> @break
                                                @case('fill_blank')    <i class="fa-solid fa-pen-line"></i> @break
                                                @case('image_quiz')    <i class="fa-solid fa-image"></i> @break
                                                @default               <i class="fa-solid fa-list-check"></i>
                                            @endswitch
                                            {{ $q->template_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex justify-center text-neo-yellow dark:text-yellow-400 text-sm gap-0.5">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="fa-{{ $i <= $q->bobot_kesulitan ? 'solid' : 'regular text-gray-300 dark:text-gray-600' }} fa-star"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @php $assetCount = count($q->aset_diperlukan ?? []); @endphp
                                        @if($assetCount > 0)
                                            @php $missing = $q->getMissingAssets(); @endphp
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-black border-2 border-black shadow-neo-sm {{ empty($missing) ? 'bg-neo-green' : 'bg-neo-red' }}">
                                                <i class="fa-solid fa-{{ empty($missing) ? 'check' : 'triangle-exclamation' }}"></i>
                                                {{ $assetCount }} aset
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs font-bold">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="previewQuestionData({{ json_encode($q->data_soal ?? ['info' => 'Legacy format (no data_soal)']) }})"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-p-lt border-2 border-black text-p hover:bg-p hover:text-white transition-all shadow-neo-sm" title="Preview JSON">
                                                <i class="fa-solid fa-code text-sm"></i>
                                            </button>
                                            <button onclick="editQuestion({{ $q->id }}, '{{ $q->tipe_template ?? 'multiple_choice' }}', {{ json_encode($q->data_soal ?? ['teks_soal' => $q->teks_soal, 'options' => $q->opsi_json]) }}, {{ $q->materi_id }}, {{ $q->bobot_kesulitan }}, {{ $q->kelas }})"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-neo-yellow border-2 border-black text-black hover:bg-yellow-400 transition-all shadow-neo-sm" title="Edit Soal">
                                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                                            </button>
                                            <form id="delete-form-{{ $q->id }}" action="{{ route('soal.destroy', $q->id) }}" method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="button" onclick="confirmDelete('{{ $q->id }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border-2 border-black text-black hover:bg-neo-red transition-all shadow-neo-sm">
                                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center bg-white dark:bg-[#2d2460]">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-p-xlt dark:bg-p-dark/30 border-2 border-dashed border-p-mid rounded-2xl text-p-mid mb-4">
                                            <i class="fa-solid fa-clipboard-question text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 dark:text-purple-300/50 font-bold text-sm">Bank Soal masih kosong.</p>
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

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL A: INPUT MANUAL                                               --}}
    {{-- FIX: enctype multipart/form-data + modal-scroll-wrap structure      --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div id="modal-add" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-add')"></div>

        {{-- FIX #1: Ganti overflow-y-auto max-h di container luar dengan modal-scroll-wrap --}}
        <div class="modal-container modal-scroll-wrap bg-white dark:bg-[#2d2460] w-11/12 md:max-w-4xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 transform transition-all scale-95 opacity-0">

            {{-- Header — sticky di dalam scroll wrap, BUKAN di luar --}}
            <div class="flex-shrink-0 pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-neo-cyan rounded-t-3xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-pen-nib text-black text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-black tracking-tight leading-none">Buat Soal Manual</h3>
                        <p class="text-[11px] font-bold text-black/70 mt-1" id="manual-template-label">Pilih template untuk mulai</p>
                    </div>
                </div>
                <button onclick="toggleModal('modal-add')" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>


            <div class="modal-scroll-body px-8 py-8 bg-white dark:bg-[#2d2460] space-y-6">

                <div id="manual-step-pick">
                    <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Pilih Tipe Template</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                        $tplMap = [
                            'multiple_choice' => ['icon' => 'fa-list-check',   'color' => 'neo-cyan',   'label' => 'Pilihan Ganda'],
                            'drag_and_drop'   => ['icon' => 'fa-hand-pointer', 'color' => 'neo-pink',   'label' => 'Drag & Drop'],
                            'matching_game'   => ['icon' => 'fa-shuffle',      'color' => 'neo-green',  'label' => 'Pasangkan'],
                            'fill_blank'      => ['icon' => 'fa-pen-line',     'color' => 'neo-yellow', 'label' => 'Isi Kosong'],
                            'image_quiz'      => ['icon' => 'fa-image',        'color' => 'neo-coral',  'label' => 'Kuis Gambar'],
                        ];
                        @endphp
                        @foreach($tplMap as $tplKey => $tpl)
                        <button type="button"
                                onclick="selectManualTemplate('{{ $tplKey }}')"
                                data-tpl="{{ $tplKey }}"
                                class="manual-tpl-btn flex flex-col items-center gap-2 p-4 border-2 border-black dark:border-p-dark rounded-xl bg-{{ $tpl['color'] }} hover:-translate-y-0.5 shadow-neo-sm hover:shadow-neo transition-all text-black font-bold text-sm">
                            <i class="fa-solid {{ $tpl['icon'] }} text-2xl"></i>
                            {{ $tpl['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- STEP 2: Form per Template --}}
                <div id="manual-form-area" class="hidden">
                    {{-- FIX #3: Tambahkan enctype="multipart/form-data" agar upload file bisa jalan --}}
                    <form id="manual-quiz-form"
                          action="{{ route('questions.manual.store') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-6">
                        @csrf
                        <input type="hidden" name="tipe_template" id="manual-tpl-type">

                        {{-- Info umum --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Materi Induk <span class="text-red-500">*</span></label>
                                <select name="materi_id" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none" required>
                                    <option value="">-- Pilih Materi --</option>
                                    @foreach($materials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->judul }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Bobot Kesulitan</label>
                                <select name="bobot_kesulitan" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none">
                                    @for($i=1; $i<=5; $i++)
                                        <option value="{{ $i }}" {{ $i==3 ? 'selected':'' }}>Level {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                          <div>
                              <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kelas</label>
                              <select name="kelas" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none" required>
                                  <option value="1">Kelas 1</option>
                                  <option value="2">Kelas 2</option>
                                  <option value="3">Kelas 3</option>
                                  
                                  
                                  
                              </select>
                          </div>

                        </div>

                        {{-- FIX #4: teks_soal selalu ada di semua template --}}
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Teks Pertanyaan <span class="text-red-500">*</span></label>
                            <textarea name="teks_soal" id="main-question-text" rows="2"
                                      class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all resize-y"
                                      placeholder="Tulis pertanyaan utama..." required></textarea>
                        </div>

                        {{-- ════ PILIHAN GANDA ════ --}}
                        <div id="tpl-multiple_choice" class="tpl-section hidden space-y-4">
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-cyan/30">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Opsi Jawaban — Pilih Radio Yang Benar</p>
                                @foreach(['A','B','C','D'] as $idx => $label)
                                <div class="flex items-center gap-3 mb-3 p-3 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl">
                                    <span class="w-8 h-8 flex items-center justify-center bg-neo-cyan border-2 border-black rounded-lg font-black text-sm text-black flex-shrink-0">{{ $label }}</span>
                                    <input type="text" name="options[]"
                                           placeholder="Teks opsi {{ $label }}..."
                                           class="flex-1 bg-p-xlt dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2 text-sm font-bold text-black dark:text-white outline-none focus:border-p"
                                           {{ $idx < 2 ? 'required' : '' }}>
                                    <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                                        <input type="radio" name="correct_option" value="{{ $idx }}"
                                               {{ $idx === 0 ? 'checked' : '' }}
                                               class="w-5 h-5 accent-violet-600 cursor-pointer">
                                        <span class="text-xs font-black text-black dark:text-white whitespace-nowrap">Jawaban Benar</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ════ DRAG & DROP ════ --}}
                        <div id="tpl-drag_and_drop" class="tpl-section hidden space-y-4">
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-pink/30">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-1">Upload 4 Foto Item Drag</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4">Upload foto untuk setiap pilihan. Klik <strong>Tandai Benar</strong> pada foto yang merupakan jawaban benar.</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="dnd-photo-grid">
                                    @for($i = 0; $i < 4; $i++)
                                    <div class="flex flex-col items-center gap-2" id="dnd-card-{{ $i }}">
                                        <div class="w-full aspect-square border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-white dark:bg-[#1e1b4b] relative overflow-hidden cursor-pointer hover:border-p transition-colors flex items-center justify-center"
                                             id="dnd-box-{{ $i }}"
                                             onclick="document.getElementById('dnd-file-{{ $i }}').click()">
                                            <input type="file" name="dnd_images[]"
                                                   id="dnd-file-{{ $i }}"
                                                   accept="image/jpeg,image/png,image/webp"
                                                   class="hidden"
                                                   onchange="previewDnd(this, {{ $i }})">
                                            <img id="dnd-prev-{{ $i }}" class="w-full h-full object-cover hidden absolute inset-0" alt="Preview foto {{ $i+1 }}">
                                            <div id="dnd-placeholder-{{ $i }}" class="flex flex-col items-center gap-1 pointer-events-none">
                                                <i class="fa-solid fa-camera text-2xl text-gray-300 dark:text-gray-600"></i>
                                                <span class="text-[10px] font-bold text-gray-400">Foto {{ $i+1 }}</span>
                                            </div>
                                        </div>
                                        <button type="button" id="dnd-correct-btn-{{ $i }}"
                                                onclick="setDndCorrect({{ $i }})"
                                                class="w-full text-[10px] font-black py-1.5 px-2 border-2 border-black dark:border-p-dark rounded-lg bg-white dark:bg-[#1e1b4b] text-black dark:text-white hover:bg-neo-green transition-all shadow-neo-sm">
                                            Tandai Benar
                                        </button>
                                        <span id="dnd-badge-{{ $i }}" class="hidden text-[10px] font-black px-2 py-0.5 bg-neo-green border border-black rounded-full text-black">✓ Jawaban Benar</span>
                                    </div>
                                    @endfor
                                </div>
                                <input type="hidden" name="dnd_correct_index" id="dnd-correct-hidden" value="">
                            </div>
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-white dark:bg-[#1e1b4b]">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Label Zona (Target Drop)</p>
                                <div class="flex gap-3 mb-3">
                                    <input type="text" id="dnd-zone-input" placeholder="Contoh: Rumah Adat Tolaki"
                                           class="flex-1 bg-p-xlt dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl px-4 py-2 text-sm font-bold text-black dark:text-white outline-none focus:border-p">
                                    <button type="button" onclick="addDndZone()" class="bg-p text-white border-2 border-black rounded-xl px-4 py-2 text-sm font-black shadow-neo-sm hover:bg-p-dark transition-all">+ Zona</button>
                                </div>
                                <div id="dnd-zones-display" class="flex flex-wrap gap-2 min-h-[32px]">
                                    <span class="text-[11px] text-gray-400 italic">Belum ada zona ditambahkan</span>
                                </div>
                                <input type="hidden" name="dnd_zones" id="dnd-zones-hidden" value="[]">
                            </div>
                        </div>

                        {{-- ════ MATCHING GAME ════ --}}
                        <div id="tpl-matching_game" class="tpl-section hidden space-y-4">
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-green/30">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-1">Pasangan Soal</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4">Pasang item kiri dengan item kanan yang benar.</p>
                                <div id="match-pairs-container" class="space-y-3">
                                    @for($i = 1; $i <= 4; $i++)
                                    <div class="match-pair-row grid grid-cols-[1fr_auto_1fr] gap-3 items-center">
                                        <input type="text" name="pair_left[]"
                                               placeholder="Item Kiri #{{ $i }}"
                                               class="bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2.5 text-sm font-bold text-black dark:text-white outline-none focus:border-p"
                                               {{ $i <= 2 ? 'required' : '' }}>
                                        <span class="text-gray-400 dark:text-gray-500 text-sm font-bold text-center">↔</span>
                                        <input type="text" name="pair_right[]"
                                               placeholder="Item Kanan #{{ $i }}"
                                               class="bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2.5 text-sm font-bold text-black dark:text-white outline-none focus:border-p"
                                               {{ $i <= 2 ? 'required' : '' }}>
                                    </div>
                                    @endfor
                                </div>
                                <button type="button" onclick="addMatchPairManual()" class="mt-3 text-sm font-bold text-p dark:text-p-mid hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-plus text-xs"></i> Tambah pasangan
                                </button>
                            </div>
                        </div>

                        {{-- ════ FILL BLANK ════ --}}
                        <div id="tpl-fill_blank" class="tpl-section hidden space-y-4">
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-yellow/30">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-1">Kalimat dengan Blank</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-3">
                                    Gunakan <kode class="bg-white dark:bg-[#1e1b4b] px-1 py-0.5 rounded border border-gray-200">___</kode> (3 garis bawah) sebagai penanda kosong.
                                </p>
                                {{-- FIX #5: fill_sentence punya name="fill_sentence" TERPISAH dari teks_soal --}}
                                <textarea name="fill_sentence" id="fill-sentence-input" rows="2"
                                          placeholder="Tulis kalimat dengan ___ sebagai penanda blank... Contoh: Rumah adat Tolaki disebut ___"
                                          onchange="parseFillBlanks()" oninput="parseFillBlanks()"
                                          class="w-full bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p resize-y"></textarea>
                                <div id="fill-blanks-answers" class="mt-4 space-y-3 hidden">
                                    <p class="text-[10px] font-black text-black dark:text-white uppercase tracking-widest">Jawaban Benar Per Blank</p>
                                </div>
                            </div>
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-white dark:bg-[#1e1b4b]">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Bank Kata (Pilihan Siswa)</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-3">Masukkan jawaban benar + pengecoh untuk ditampilkan sebagai pilihan siswa.</p>
                                <div class="flex gap-3 mb-3">
                                    <input type="text" id="wb-text-input" placeholder="Ketik kata lalu klik Tambah..."
                                           class="flex-1 bg-p-xlt dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl px-4 py-2 text-sm font-bold text-black dark:text-white outline-none focus:border-p"
                                           onkeydown="if(event.key==='Enter'){event.preventDefault();addWordBankManual()}">
                                    <button type="button" onclick="addWordBankManual()" class="bg-p text-white border-2 border-black rounded-xl px-4 py-2 text-sm font-black shadow-neo-sm hover:bg-p-dark transition-all">+ Tambah</button>
                                </div>
                                <div id="wb-chips" class="flex flex-wrap gap-2 min-h-[32px]">
                                    <span class="text-[11px] text-gray-400 italic">Belum ada kata</span>
                                </div>
                                <input type="hidden" name="word_bank" id="wb-hidden" value="[]">
                            </div>
                        </div>

                        {{-- ════ IMAGE QUIZ ════ --}}
                        <div id="tpl-image_quiz" class="tpl-section hidden space-y-4">
                            <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-coral/30">
                                <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Gambar Utama</p>
                                <div class="flex gap-5 items-start">
                                    <div class="flex-shrink-0 w-40 h-40 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-white dark:bg-[#1e1b4b] relative overflow-hidden cursor-pointer hover:border-p transition-colors flex items-center justify-center"
                                         onclick="document.getElementById('imgq-file').click()">
                                        <input type="file" name="main_image" id="imgq-file"
                                               accept="image/jpeg,image/png,image/webp"
                                               class="hidden"
                                               onchange="previewImgQ(this)">
                                        <img id="imgq-preview" class="w-full h-full object-cover hidden absolute inset-0" alt="Preview gambar utama">
                                        <div id="imgq-placeholder" class="flex flex-col items-center gap-2">
                                            <i class="fa-solid fa-image text-3xl text-gray-300 dark:text-gray-600"></i>
                                            <span class="text-[11px] font-bold text-gray-400">Klik upload</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-3">Gambar akan ditampilkan penuh ke siswa. Tambahkan 4 opsi jawaban di bawah.</p>
                                        @foreach(['A','B','C','D'] as $idx => $label)
                                        <div class="flex items-center gap-3 p-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl">
                                            <span class="w-7 h-7 flex-shrink-0 flex items-center justify-center bg-neo-coral border-2 border-black rounded-lg font-black text-xs text-black">{{ $label }}</span>
                                            <input type="text" name="options[]"
                                                   placeholder="Opsi {{ $label }}..."
                                                   class="flex-1 bg-p-xlt dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-lg px-3 py-1.5 text-sm font-bold text-black dark:text-white outline-none focus:border-p"
                                                   {{ $idx < 2 ? 'required' : '' }}>
                                            <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                                                <input type="radio" name="correct_option" value="{{ $idx }}" {{ $idx === 0 ? 'checked' : '' }} class="w-4 h-4 accent-violet-600 cursor-pointer">
                                                <span class="text-[10px] font-black text-black dark:text-white whitespace-nowrap">Benar</span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Penjelasan (semua template) --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-black text-black dark:text-white uppercase tracking-widest">Penjelasan Jawaban (Opsional)</label>
                                <button type="button" onclick="generateManualExplanation()" class="text-xs font-bold text-p hover:text-p-dark flex items-center gap-1 border-2 border-black px-2 py-1 rounded-lg bg-neo-cyan shadow-neo-sm hover:translate-y-px transition-all">
                                    <i class="fa-solid fa-robot"></i> Buat dengan AI
                                </button>
                            </div>
                            <textarea name="explanation" id="manual-explanation-text" rows="2"
                                      class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all resize-y"
                                      placeholder="Tulis penjelasan manual, atau klik tombol 'Buat dengan AI' di atas..."></textarea>
                        </div>

                        {{-- FIX #6: Tombol simpan ada di DALAM form, DALAM scroll body --}}
                        {{-- Tidak ada padding bottom tambahan yg menyebabkan terpotong --}}
                        <div class="flex justify-between items-center pt-4 border-t-2 border-p-lt dark:border-p-dark pb-2">
                            <button type="button" onclick="backToTemplatePick()"
                                    class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark text-black dark:text-white rounded-xl text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                                <i class="fa-solid fa-arrow-left mr-1"></i> Ganti Template
                            </button>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-neo-green border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Soal
                            </button>
                        </div>

                    </form>
                </div>
            </div>{{-- end modal-scroll-body --}}
        </div>{{-- end modal-container --}}
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL B: AI GENERATE                                                --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div id="modal-ai-generate" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="closeAiModal()"></div>
        <div class="modal-container modal-scroll-wrap bg-white dark:bg-[#2d2460] w-11/12 md:max-w-3xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 transform transition-all scale-95 opacity-0">

            {{-- Header --}}
            <div class="flex-shrink-0 pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-gradient-to-r from-p to-p-mid rounded-t-3xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-wand-magic-sparkles text-p text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight leading-none">AI Quiz Generator</h3>
                        <p class="text-[11px] font-semibold text-white/70 mt-1" id="ai-modal-step-label">Langkah 1 dari 2: Konfigurasi Template</p>
                    </div>
                </div>
                <button onclick="closeAiModal()" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="modal-scroll-body">

                {{-- Step 1: Konfigurasi --}}
                <div id="ai-step-1" class="px-8 py-8 bg-white dark:bg-[#2d2460] space-y-6">
                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Pilih Template Soal</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="template-picker">
                            @php
                            $templateIcons = [
                                'multiple_choice' => ['icon'=>'fa-list-check',   'color'=>'neo-cyan',   'label'=>'Pilihan Ganda'],
                                'drag_and_drop'   => ['icon'=>'fa-hand-pointer', 'color'=>'neo-pink',   'label'=>'Drag & Drop'],
                                'matching_game'   => ['icon'=>'fa-shuffle',      'color'=>'neo-green',  'label'=>'Pasangkan'],
                                'fill_blank'      => ['icon'=>'fa-pen-line',     'color'=>'neo-yellow', 'label'=>'Isi Kosong'],
                                'image_quiz'      => ['icon'=>'fa-image',        'color'=>'neo-coral',  'label'=>'Kuis Gambar'],
                            ];
                            @endphp
                            @foreach($templateIcons as $tKey => $tInfo)
                            <button type="button" onclick="selectTemplate('{{ $tKey }}')"
                                    data-template="{{ $tKey }}"
                                    class="template-btn flex flex-col items-center gap-2 p-4 border-2 border-black dark:border-p-dark rounded-xl bg-{{ $tInfo['color'] }} hover:-translate-y-0.5 shadow-neo-sm hover:shadow-neo transition-all text-black font-bold text-sm">
                                <i class="fa-solid {{ $tInfo['icon'] }} text-2xl"></i>
                                {{ $tInfo['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Materi Induk <span class="text-red-500">*</span></label>
                            <select id="ai-material-id" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none">
                                <option value="">-- Pilih Materi --</option>
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Tingkat Kesulitan</label>
                            <select id="ai-difficulty" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none">
                                @for($i=1;$i<=5;$i++)<option value="{{ $i }}" {{ $i==3 ? 'selected':'' }}>Level {{ $i }}</option>@endfor
                            </select>
                        </div>
                    </div>

                    <div id="ai-error" class="hidden bg-neo-red border-2 border-black rounded-xl px-4 py-3 text-sm font-bold text-black">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        <span id="ai-error-text"></span>
                    </div>

                    <div class="flex justify-end pb-2">
                        <button id="btn-generate" onclick="triggerGenerate()"
                                class="bg-p hover:bg-p-dark text-white px-8 py-3 rounded-xl border-2 border-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all text-sm font-black flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" id="btn-generate-icon"></i>
                            <span id="btn-generate-text">Generate Soal</span>
                        </button>
                    </div>
                </div>

                {{-- Step 2: Preview + Konfirmasi --}}
                <div id="ai-step-2" class="hidden px-8 py-8 bg-white dark:bg-[#2d2460] space-y-6">
                    <div id="missing-assets-warning" class="hidden bg-neo-yellow border-2 border-black rounded-xl px-4 py-3">
                        <p class="text-sm font-black text-black mb-1"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Peringatan Aset</p>
                        <p class="text-xs font-semibold text-black" id="missing-assets-list"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4" id="preview-meta">
                        <div class="bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl p-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-purple-300/50 mb-1">Template</div>
                            <div class="font-black text-black dark:text-white text-sm" id="preview-template-label">—</div>
                        </div>
                        <div class="bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl p-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-purple-300/50 mb-1">Aset Dibutuhkan</div>
                            <div class="font-black text-black dark:text-white text-sm" id="preview-assets-count">—</div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-black text-black dark:text-white uppercase tracking-widest">Preview JSON Output</label>
                            <button onclick="copyJson()" class="text-xs font-bold text-p dark:text-p-mid flex items-center gap-1 hover:underline">
                                <i class="fa-solid fa-copy"></i> Salin
                            </button>
                        </div>
                        <div class="bg-[#1e1b4b] rounded-xl border-2 border-black p-4 overflow-auto max-h-64">
                            <pre class="json-preview text-neo-green" id="json-preview-content"></pre>
                        </div>
                    </div>

                    <form action="{{ route('soal.store') }}" method="POST" id="confirm-save-form" class="space-y-4">
                        @csrf
                        <input type="hidden" name="tipe_template"      id="hidden-template-type">
                        <input type="hidden" name="materi_id"        id="hidden-material-id">
                        <input type="hidden" name="bobot_kesulitan"  id="hidden-difficulty">
                          <input type="hidden" name="kelas" id="hidden-kelas">
                        <input type="hidden" name="teks_soal" id="hidden-question-text">
                        <input type="hidden" name="question_data_json" id="hidden-question-data">

                        <div id="force-save-container" class="hidden flex items-center gap-3 p-3 bg-neo-red border-2 border-black rounded-xl">
                            <input type="checkbox" name="force_save" id="force-save-cb" value="1" class="w-5 h-5 border-2 border-black rounded cursor-pointer">
                            <label for="force-save-cb" class="text-sm font-bold text-black cursor-pointer">Simpan paksa meskipun ada aset yang hilang</label>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t-2 border-p-lt dark:border-p-dark pb-2">
                            <button type="button" onclick="backToStep1()" class="px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark text-black dark:text-white rounded-xl text-sm font-black shadow-neo-sm hover:-translate-y-0.5 hover:shadow-neo transition-all">
                                <i class="fa-solid fa-arrow-left mr-1"></i> Regenerate
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-neo-green border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 hover:shadow-neo-lg transition-all">
                                <i class="fa-solid fa-check mr-1"></i> Konfirmasi & Simpan
                            </button>
                        </div>
                    </form>
                </div>

            </div>{{-- end modal-scroll-body --}}
        </div>{{-- end modal-container --}}
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL C: JSON Preview Read-only                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL D: EDIT SOAL                                                --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div id="modal-edit" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-edit')"></div>
        <div class="modal-container modal-scroll-wrap bg-white dark:bg-[#2d2460] w-11/12 md:max-w-4xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 transform transition-all scale-95 opacity-0">
            <div class="flex-shrink-0 pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-neo-yellow rounded-t-3xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-pen-to-square text-black text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-black tracking-tight leading-none">Edit Soal</h3>
                        <p class="text-[11px] font-bold text-black/70 mt-1" id="edit-template-label">Template: Pilihan Ganda</p>
                    </div>
                </div>
                <button onclick="toggleModal('modal-edit')" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="modal-scroll-body px-8 py-8 bg-white dark:bg-[#2d2460] space-y-6">
                <form id="edit-quiz-form" action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf @method('PUT')
                    <input type="hidden" name="tipe_template" id="edit-tpl-type">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Materi Induk</label>
                            <select name="materi_id" id="edit-material-id" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none" required>
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Bobot Kesulitan</label>
                            <select name="bobot_kesulitan" id="edit-difficulty" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none">
                                @for($i=1; $i<=5; $i++)
                                    <option value="{{ $i }}">Level {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                          <div>
                              <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Kelas</label>
                              <select id="edit-kelas" name="kelas" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all cursor-pointer appearance-none" required>
                                  <option value="1">Kelas 1</option>
                                  <option value="2">Kelas 2</option>
                                  <option value="3">Kelas 3</option>
                                  
                                  
                                  
                              </select>
                          </div>

                    </div>
                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Teks Pertanyaan <span class="text-red-500">*</span></label>
                        <textarea name="teks_soal" id="edit-main-question-text" rows="2" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all resize-y" required></textarea>
                    </div>

                    {{-- Section templates (Multiple choice, etc) --}}
                    <div id="edit-tpl-multiple_choice" class="edit-tpl-section hidden space-y-4">
                        <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-cyan/30">
                            <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-3">Opsi Jawaban</p>
                            @foreach(['A','B','C','D'] as $idx => $label)
                            <div class="flex items-center gap-3 mb-3 p-3 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl">
                                <span class="w-8 h-8 flex items-center justify-center bg-neo-cyan border-2 border-black rounded-lg font-black text-sm text-black flex-shrink-0">{{ $label }}</span>
                                <input type="text" name="options[]" id="edit-opt-{{ $idx }}" class="flex-1 bg-p-xlt dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2 text-sm font-bold text-black dark:text-white outline-none focus:border-p">
                                <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                                    <input type="radio" name="correct_option" value="{{ $idx }}" id="edit-correct-{{ $idx }}" class="w-5 h-5 accent-violet-600 cursor-pointer">
                                    <span class="text-xs font-black text-black dark:text-white whitespace-nowrap">Benar</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Drag & Drop --}}
                    <div id="edit-tpl-drag_and_drop" class="edit-tpl-section hidden space-y-4">
                        <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-pink/30">
                            <p class="text-xs font-black text-black dark:text-white uppercase tracking-widest mb-1">Update Foto Item (Opsional)</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @for($i = 0; $i < 4; $i++)
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-full aspect-square border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-white dark:bg-[#1e1b4b] relative overflow-hidden cursor-pointer flex items-center justify-center" onclick="document.getElementById('edit-dnd-file-{{ $i }}').click()">
                                        <input type="file" name="dnd_images[]" id="edit-dnd-file-{{ $i }}" accept="image/*" class="hidden" onchange="previewEditDnd(this, {{ $i }})">
                                        <img id="edit-dnd-prev-{{ $i }}" class="w-full h-full object-cover hidden absolute inset-0">
                                        <div id="edit-dnd-ph-{{ $i }}" class="flex flex-col items-center gap-1">
                                            <i class="fa-solid fa-camera text-xl text-gray-300"></i>
                                        </div>
                                    </div>
                                    <button type="button" id="edit-dnd-btn-{{ $i }}" onclick="setEditDndCorrect({{ $i }})" class="w-full text-[10px] font-black py-1 px-2 border-2 border-black rounded-lg bg-white shadow-neo-sm">Benar</button>
                                </div>
                                @endfor
                            </div>
                            <input type="hidden" name="dnd_correct_index" id="edit-dnd-correct-hidden">
                            <input type="hidden" name="dnd_zones" id="edit-dnd-zones-hidden">
                        </div>
                    </div>

                    {{-- Pasangkan --}}
                    <div id="edit-tpl-matching_game" class="edit-tpl-section hidden space-y-4">
                        <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-green/30">
                            <div id="edit-match-pairs-container" class="space-y-3"></div>
                            <button type="button" onclick="addEditMatchPair()" class="mt-3 text-sm font-bold text-p hover:underline">+ Tambah Pasangan</button>
                        </div>
                    </div>

                    {{-- Isi Kosong --}}
                    <div id="edit-tpl-fill_blank" class="edit-tpl-section hidden space-y-4">
                        <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-yellow/30">
                            <textarea name="fill_sentence" id="edit-fill-sentence-input" rows="2" oninput="parseEditFillBlanks()" class="w-full bg-white border-2 border-black rounded-xl px-4 py-3 text-sm font-bold outline-none"></textarea>
                            <div id="edit-fill-blanks-answers" class="mt-4 space-y-3 hidden"></div>
                        </div>
                        <input type="hidden" name="word_bank" id="edit-wb-hidden">
                    </div>

                    {{-- Kuis Gambar --}}
                    <div id="edit-tpl-image_quiz" class="edit-tpl-section hidden space-y-4">
                        <div class="p-4 border-2 border-dashed border-black dark:border-p-dark rounded-xl bg-neo-coral/30">
                            <div class="flex gap-5 items-start">
                                <div class="w-40 h-40 border-2 border-dashed border-black rounded-xl bg-white relative overflow-hidden cursor-pointer flex items-center justify-center" onclick="document.getElementById('edit-imgq-file').click()">
                                    <input type="file" name="main_image" id="edit-imgq-file" accept="image/*" class="hidden" onchange="previewEditImgQ(this)">
                                    <img id="edit-imgq-preview" class="w-full h-full object-cover hidden absolute inset-0">
                                    <div id="edit-imgq-ph" class="text-gray-300"><i class="fa-solid fa-image text-3xl"></i></div>
                                </div>
                                <div class="flex-1 space-y-2">
                                    @foreach(['A','B','C','D'] as $idx => $label)
                                    <div class="flex items-center gap-3 p-2 bg-white border-2 border-black rounded-xl">
                                        <input type="text" name="options[]" id="edit-imgq-opt-{{ $idx }}" class="flex-1 bg-transparent text-sm font-bold outline-none">
                                        <input type="radio" name="correct_option" value="{{ $idx }}" id="edit-imgq-correct-{{ $idx }}" class="w-4 h-4">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black dark:text-white uppercase tracking-widest mb-2">Penjelasan Jawaban</label>
                        <textarea name="explanation" id="edit-explanation-text" rows="2" class="w-full bg-p-xlt dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-4 py-3 text-sm font-bold text-black dark:text-white outline-none focus:border-p transition-all resize-y"></textarea>
                    </div>

                    <div class="flex justify-end pt-4 border-t-2 border-p-lt">
                        <button type="submit" class="px-8 py-3 bg-neo-green border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL E: KONFIRMASI HAPUS (CUSTOM THEME)                             --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <!-- Modal Preview JSON -->
    <div id="modal-json-view" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-json-view')"></div>
        <div class="modal-container bg-white dark:bg-[#2d2460] w-11/12 md:max-w-2xl mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-50 transform transition-all scale-95 opacity-0 flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between p-6 border-b-2 border-p-lt dark:border-p-dark">
                <div>
                    <h3 class="text-xl font-black text-black dark:text-white uppercase tracking-tight">Preview Data Soal (JSON)</h3>
                    <p class="text-xs font-bold text-gray-500 dark:text-purple-300 mt-1">Struktur mentah dari soal ini.</p>
                </div>
                <button onclick="toggleModal('modal-json-view')" class="w-10 h-10 flex items-center justify-center bg-neo-red border-2 border-black rounded-full text-black hover:-translate-y-1 hover:shadow-neo transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1 bg-gray-50 dark:bg-gray-900 rounded-b-3xl">
                <pre id="json-view-content" class="text-xs font-mono text-gray-800 dark:text-green-400 whitespace-pre-wrap break-all"></pre>
            </div>
        </div>
    </div>

    <div id="modal-delete" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[110]" aria-hidden="true">
        <div class="absolute w-full h-full bg-black/60 backdrop-blur-sm" onclick="toggleModal('modal-delete')"></div>
        <div class="modal-container bg-white dark:bg-[#2d2460] w-11/12 md:max-w-md mx-auto rounded-3xl border-2 border-black dark:border-p-dark shadow-neo-lg z-[120] transform transition-all scale-95 opacity-0">
            <div class="flex-shrink-0 pt-6 pb-5 px-8 border-b-2 border-black dark:border-p-dark bg-neo-red rounded-t-3xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center shadow-neo-sm">
                        <i class="fa-solid fa-trash-can text-black text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-black tracking-tight leading-none">Hapus Soal?</h3>
                    </div>
                </div>
                <button onclick="toggleModal('modal-delete')" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-black text-black rounded-full shadow-neo-sm hover:bg-neo-red transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <p class="text-sm font-bold text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan dan data akan hilang permanen.</p>
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button onclick="toggleModal('modal-delete')" class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark text-black dark:text-white rounded-xl text-sm font-black shadow-neo-sm hover:translate-y-px transition-all">
                        Batal
                    </button>
                    <button id="confirm-delete-btn" class="w-full sm:w-auto px-6 py-2.5 bg-neo-red border-2 border-black text-black rounded-xl text-sm font-black shadow-neo hover:-translate-y-0.5 transition-all">
                        Ya, Hapus Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- SCRIPTS                                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <script>
    // ── Preloader ──────────────────────────────────────────────────────────
    window.addEventListener('DOMContentLoaded', () => {
        const pre = document.getElementById('preloader');
        pre.style.opacity = '0';
        setTimeout(() => { pre.style.visibility = 'hidden'; }, 500);
    });

    // ── Sidebar ────────────────────────────────────────────────────────────
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

    // ── Modal generic ──────────────────────────────────────────────────────
    function toggleModal(modalID) {
        const modal   = document.getElementById(modalID);
        const content = modal.querySelector('.modal-container');
        const body    = document.querySelector('body');
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

    // ── Dark mode ──────────────────────────────────────────────────────────
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

    // ══════════════════════════════════════════════════════════════════════
    // MODAL MANUAL — State & Logic
    // ══════════════════════════════════════════════════════════════════════
    let _dndCorrectIdx = -1;
    let _dndZones      = [];
    let _wordBanks     = [];

    function selectManualTemplate(tplKey) {
        document.getElementById('manual-tpl-type').value = tplKey;

        document.querySelectorAll('.manual-tpl-btn').forEach(btn => {
            const active = btn.dataset.tpl === tplKey;
            btn.classList.toggle('ring-4', active);
            btn.classList.toggle('ring-black', active);
        });

        const labels = {
            'multiple_choice': 'Pilihan Ganda',
            'drag_and_drop':   'Drag & Drop (4 Foto)',
            'matching_game':   'Pasangkan',
            'fill_blank':      'Isi Kosong',
            'image_quiz':      'Kuis Gambar',
        };
        document.getElementById('manual-template-label').textContent = 'Template: ' + (labels[tplKey] || tplKey);

        document.getElementById('manual-step-pick').classList.add('hidden');
        document.getElementById('manual-form-area').classList.remove('hidden');

        // Textarea utama selalu wajib diisi
        const mainQ = document.getElementById('main-question-text');
        mainQ.required = true;

        // [PATCH] Isolasi Domain Validasi: 
        // Sembunyikan semua section dan DISABLE input di dalamnya agar tidak memblokir submit
        document.querySelectorAll('.tpl-section').forEach(s => {
            s.classList.add('hidden');
            s.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = true;
            });
        });

        // Tampilkan hanya section yang dipilih dan ENABLE kembali inputnya
        const section = document.getElementById('tpl-' + tplKey);
        if (section) {
            section.classList.remove('hidden');
            section.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = false;
            });
        }

        // Reset scroll ke atas
        const scrollBody = document.querySelector('#modal-add .modal-scroll-body');
        if (scrollBody) scrollBody.scrollTop = 0;
    }

    function backToTemplatePick() {
        document.getElementById('manual-step-pick').classList.remove('hidden');
        document.getElementById('manual-form-area').classList.add('hidden');
        document.querySelectorAll('.tpl-section').forEach(s => s.classList.add('hidden'));
        document.getElementById('manual-template-label').textContent = 'Pilih template untuk mulai';
        // Reset state
        _dndCorrectIdx = -1;
        _dndZones      = [];
        _wordBanks     = [];
        // Reset form
        document.getElementById('manual-quiz-form').reset();
        document.getElementById('dnd-correct-hidden').value = '';
        document.getElementById('dnd-zones-hidden').value   = '[]';
        document.getElementById('wb-hidden').value          = '[]';
        document.getElementById('dnd-zones-display').innerHTML  = '<span class="text-[11px] text-gray-400 italic">Belum ada zona ditambahkan</span>';
        document.getElementById('wb-chips').innerHTML           = '<span class="text-[11px] text-gray-400 italic">Belum ada kata</span>';
        document.getElementById('fill-blanks-answers').classList.add('hidden');
        // Reset DnD previews
        for (let i = 0; i < 4; i++) {
            const prev = document.getElementById('dnd-prev-' + i);
            const ph   = document.getElementById('dnd-placeholder-' + i);
            const badge = document.getElementById('dnd-badge-' + i);
            const btn   = document.getElementById('dnd-correct-btn-' + i);
            if (prev)  { prev.src = ''; prev.classList.add('hidden'); }
            if (ph)    ph.classList.remove('hidden');
            if (badge) badge.classList.add('hidden');
            if (btn)   btn.classList.remove('bg-neo-green');
        }
        // Reset image quiz preview
        const imgqPrev = document.getElementById('imgq-preview');
        const imgqPh   = document.getElementById('imgq-placeholder');
        if (imgqPrev) { imgqPrev.src = ''; imgqPrev.classList.add('hidden'); }
        if (imgqPh)   imgqPh.classList.remove('hidden');
    }

    // ── Drag & Drop ──────────────────────────────────────────────────────
    function previewDnd(input, idx) {
        const file = input.files[0];
        if (!file) return;
        const prev   = document.getElementById('dnd-prev-' + idx);
        const ph     = document.getElementById('dnd-placeholder-' + idx);
        const reader = new FileReader();
        reader.onload = e => {
            prev.src = e.target.result;
            prev.classList.remove('hidden');
            ph.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function setDndCorrect(idx) {
        _dndCorrectIdx = idx;
        for (let i = 0; i < 4; i++) {
            document.getElementById('dnd-badge-' + i).classList.add('hidden');
            document.getElementById('dnd-correct-btn-' + i).classList.remove('bg-neo-green');
            document.getElementById('dnd-box-' + i).style.outline = '';
        }
        document.getElementById('dnd-badge-' + idx).classList.remove('hidden');
        document.getElementById('dnd-correct-btn-' + idx).classList.add('bg-neo-green');
        document.getElementById('dnd-box-' + idx).style.outline = '3px solid #16a34a';
        document.getElementById('dnd-correct-hidden').value = idx;
    }

    function addDndZone() {
        const input = document.getElementById('dnd-zone-input');
        const val   = input.value.trim();
        if (!val) return;
        _dndZones.push(val);
        input.value = '';
        renderDndZones();
        document.getElementById('dnd-zones-hidden').value = JSON.stringify(_dndZones);
    }

    function removeDndZone(i) {
        _dndZones.splice(i, 1);
        renderDndZones();
        document.getElementById('dnd-zones-hidden').value = JSON.stringify(_dndZones);
    }

    function renderDndZones() {
        const el = document.getElementById('dnd-zones-display');
        if (_dndZones.length === 0) {
            el.innerHTML = '<span class="text-[11px] text-gray-400 italic">Belum ada zona</span>';
            return;
        }
        el.innerHTML = _dndZones.map((z, i) => `
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-neo-pink border-2 border-black rounded-full text-xs font-black text-black">
                ${z} <button type="button" onclick="removeDndZone(${i})" class="text-red-600 hover:text-red-800 font-black">×</button>
            </span>`).join('');
    }

    // ── Matching Game ────────────────────────────────────────────────────
    function addMatchPairManual() {
        const container = document.getElementById('match-pairs-container');
        const n = container.querySelectorAll('.match-pair-row').length + 1;
        const div = document.createElement('div');
        div.className = 'match-pair-row grid grid-cols-[1fr_auto_1fr_auto] gap-3 items-center';
        div.innerHTML = `
            <input type="text" name="pair_left[]" placeholder="Item Kiri #${n}"
                   class="bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2.5 text-sm font-bold text-black dark:text-white outline-none focus:border-p">
            <span class="text-gray-400 text-sm font-bold text-center">↔</span>
            <input type="text" name="pair_right[]" placeholder="Item Kanan #${n}"
                   class="bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2.5 text-sm font-bold text-black dark:text-white outline-none focus:border-p">
            <button type="button" onclick="this.closest('.match-pair-row').remove()"
                    class="w-8 h-8 flex items-center justify-center bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark text-black dark:text-white rounded-lg hover:bg-neo-red transition-all text-sm flex-shrink-0">
                <i class="fa-solid fa-trash-can"></i>
            </button>`;
        container.appendChild(div);
    }

    // ── Fill Blank ───────────────────────────────────────────────────────
    function parseFillBlanks() {
        const sentence  = document.getElementById('fill-sentence-input').value;
        const blanks    = (sentence.match(/___/g) || []).length;
        const container = document.getElementById('fill-blanks-answers');

        if (blanks === 0) {
            container.classList.add('hidden');
            // Hapus input jawaban yang lama
            const oldInputs = container.querySelectorAll('.blank-answer-row');
            oldInputs.forEach(el => el.remove());
            return;
        }

        container.classList.remove('hidden');

        // Update jumlah input jawaban (tambah/hapus sesuai jumlah blank)
        const existing = container.querySelectorAll('.blank-answer-row');
        // Hapus semua dulu, rebuild
        existing.forEach(el => el.remove());

        for (let i = 0; i < blanks; i++) {
            const div = document.createElement('div');
            div.className = 'blank-answer-row flex items-center gap-3 p-3 bg-white dark:bg-[#1e1b4b] border-2 border-black dark:border-p-dark rounded-xl';
            div.innerHTML = `
                <span class="w-7 h-7 flex-shrink-0 flex items-center justify-center bg-neo-yellow border-2 border-black rounded-lg font-black text-xs text-black">${i + 1}</span>
                <input type="text" name="fill_correct_answers[]"
                       placeholder="Jawaban benar untuk blank ke-${i + 1}..."
                       required
                       class="flex-1 bg-p-xlt dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl px-3 py-2 text-sm font-bold text-black dark:text-white outline-none focus:border-p">`;
            container.appendChild(div);
        }
    }

    function addWordBankManual() {
        const input = document.getElementById('wb-text-input');
        const val   = input.value.trim();
        if (!val) return;
        _wordBanks.push(val);
        input.value = '';
        renderWordBanks();
        document.getElementById('wb-hidden').value = JSON.stringify(_wordBanks);
    }

    function removeWordBank(i) {
        _wordBanks.splice(i, 1);
        renderWordBanks();
        document.getElementById('wb-hidden').value = JSON.stringify(_wordBanks);
    }

    function renderWordBanks() {
        const el = document.getElementById('wb-chips');
        if (_wordBanks.length === 0) {
            el.innerHTML = '<span class="text-[11px] text-gray-400 italic">Belum ada kata</span>';
            return;
        }
        el.innerHTML = _wordBanks.map((w, i) => `
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-p-lt dark:bg-p-dark/40 border-2 border-black dark:border-p-dark rounded-full text-xs font-black text-p dark:text-purple-300">
                ${w} <button type="button" onclick="removeWordBank(${i})" class="text-red-500 hover:text-red-700 font-black">×</button>
            </span>`).join('');
    }

    // ── Image Quiz ───────────────────────────────────────────────────────
    function previewImgQ(input) {
        const file = input.files[0];
        if (!file) return;
        const prev   = document.getElementById('imgq-preview');
        const ph     = document.getElementById('imgq-placeholder');
        const reader = new FileReader();
        reader.onload = e => {
            prev.src = e.target.result;
            prev.classList.remove('hidden');
            ph.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    // ══════════════════════════════════════════════════════════════════════
    // AI GENERATE LOGIC
    // ══════════════════════════════════════════════════════════════════════
    let selectedTemplate = null;
    let generatedData    = null;

    function selectTemplate(templateKey) {
        selectedTemplate = templateKey;
        document.querySelectorAll('.template-btn').forEach(btn => {
            const active = btn.dataset.template === templateKey;
            btn.classList.toggle('ring-4',         active);
            btn.classList.toggle('ring-black',     active);
            btn.classList.toggle('-translate-y-1', active);
        });
    }

    function closeAiModal() {
        toggleModal('modal-ai-generate');
        setTimeout(() => { backToStep1(); }, 350);
    }

    function backToStep1() {
        document.getElementById('ai-step-1').classList.remove('hidden');
        document.getElementById('ai-step-2').classList.add('hidden');
        document.getElementById('ai-modal-step-label').textContent = 'Langkah 1 dari 2: Konfigurasi Template';
        document.getElementById('ai-error').classList.add('hidden');
        generatedData = null;
    }

    async function triggerGenerate() {
        if (!selectedTemplate) { showAiError('Pilih template soal terlebih dahulu.'); return; }
        const materialId = document.getElementById('ai-material-id').value;
        if (!materialId)   { showAiError('Pilih materi induk terlebih dahulu.'); return; }

        const difficulty = document.getElementById('ai-difficulty').value;
        const btn        = document.getElementById('btn-generate');
        const btnIcon    = document.getElementById('btn-generate-icon');
        const btnText    = document.getElementById('btn-generate-text');

        btn.classList.add('loading');
        btnIcon.className    = 'fa-solid fa-spinner spinner';
        btnText.textContent  = 'AI sedang bekerja...';
        document.getElementById('ai-error').classList.add('hidden');

        try {
            const res = await fetch('{{ route("questions.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ materi_id: materialId, tipe_template: selectedTemplate, difficulty }),
            });

            const data = await res.json();

            if (!res.ok || data.status === 'error') {
                showAiError(data.message || 'Terjadi kesalahan tidak terduga.');
                return;
            }

            generatedData = data;
            showPreview(data, materialId, difficulty);

        } catch (err) {
            showAiError('Gagal terhubung ke server: ' + err.message);
        } finally {
            btn.classList.remove('loading');
            btnIcon.className   = 'fa-solid fa-wand-magic-sparkles';
            btnText.textContent = 'Generate Soal';
        }
    }

    function showPreview(data, materialId, difficulty) {
        const templateLabels = {
            'multiple_choice': 'Pilihan Ganda',
            'drag_and_drop':   'Drag & Drop',
            'matching_game':   'Pasangkan',
            'fill_blank':      'Isi Kosong',
            'image_quiz':      'Kuis Gambar',
        };

        document.getElementById('ai-modal-step-label').textContent = 'Langkah 2 dari 2: Preview & Konfirmasi';
        document.getElementById('preview-template-label').textContent = templateLabels[selectedTemplate] || selectedTemplate;
        document.getElementById('preview-assets-count').textContent   = data.aset_diperlukan.length + ' file';

        if (data.missing_assets && data.missing_assets.length > 0) {
            document.getElementById('missing-assets-list').textContent = 'File hilang: ' + data.missing_assets.join(', ');
            document.getElementById('missing-assets-warning').classList.remove('hidden');
            document.getElementById('force-save-container').classList.remove('hidden');
        } else {
            document.getElementById('missing-assets-warning').classList.add('hidden');
            document.getElementById('force-save-container').classList.add('hidden');
        }

        document.getElementById('json-preview-content').textContent = JSON.stringify(data.data_soal, null, 2);

        const qd = data.data_soal;
        document.getElementById('hidden-template-type').value  = selectedTemplate;
        document.getElementById('hidden-material-id').value    = materialId;
        document.getElementById('hidden-difficulty').value     = difficulty;
        document.getElementById('hidden-question-text').value  = qd.teks_soal || '';
        document.getElementById('hidden-question-data').value  = JSON.stringify(qd);

        document.getElementById('ai-step-1').classList.add('hidden');
        document.getElementById('ai-step-2').classList.remove('hidden');
    }

    function showAiError(msg) {
        document.getElementById('ai-error-text').textContent = msg;
        document.getElementById('ai-error').classList.remove('hidden');
    }

    function copyJson() {
        const text = document.getElementById('json-preview-content').textContent;
        navigator.clipboard.writeText(text).then(() => showCustomAlert('JSON disalin!'));
    }

    // ── Read-only preview dari tabel ────────────────────────────────────
    function previewQuestionData(data) {
        document.getElementById('json-view-content').textContent = JSON.stringify(data, null, 2);
        toggleModal('modal-json-view');
    }

    async function generateManualExplanation() {
        const questionText = document.getElementById('main-question-text').value;
        const textArea = document.getElementById('manual-explanation-text');
        
        if (!questionText.trim()) {
            showCustomAlert("Tulis Teks Pertanyaan utamanya terlebih dahulu sebelum menggunakan AI.");
            return;
        }

        textArea.value = "AI sedang berpikir...";
        textArea.disabled = true;

        try {
            const res = await fetch('{{ route("questions.generate-explanation") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question: questionText })
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                textArea.value = data.explanation;
            } else {
                textArea.value = "";
                showCustomAlert("Gagal: " + data.message);
            }
        } catch (e) {
            textArea.value = "";
            showCustomAlert("Kesalahan jaringan.");
        } finally {
            textArea.disabled = false;
        }
    }
    // ── Edit Question Logic ──────────────────────────────────────────────
    function editQuestion(id, templateType, data, materialId, difficultyWeight, kelasId) {
        if(document.getElementById('edit-kelas')) document.getElementById('edit-kelas').value = kelasId;
        const modal = document.getElementById('modal-edit');
        const form = document.getElementById('edit-quiz-form');
        form.action = `{{ url('admin/soal') }}/${id}`;
        
        document.getElementById('edit-tpl-type').value = templateType;
        document.getElementById('edit-material-id').value = materialId;
        document.getElementById('edit-difficulty').value = difficultyWeight;
        document.getElementById('edit-main-question-text').value = data.teks_soal || data.question_text || '';
        document.getElementById('edit-explanation-text').value = data.explanation || '';
        
        const labels = {
            'multiple_choice': 'Pilihan Ganda',
            'drag_and_drop':   'Drag & Drop',
            'matching_game':   'Pasangkan',
            'fill_blank':      'Isi Kosong',
            'image_quiz':      'Kuis Gambar',
        };
        document.getElementById('edit-template-label').textContent = 'Template: ' + (labels[templateType] || templateType);

        // Hide all sections first
        document.querySelectorAll('.edit-tpl-section').forEach(s => {
            s.classList.add('hidden');
            s.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
        });

        const section = document.getElementById('edit-tpl-' + templateType);
        if (section) {
            section.classList.remove('hidden');
            section.querySelectorAll('input, textarea, select').forEach(el => el.disabled = false);
        }

        // Populate template-specific fields
        if (templateType === 'multiple_choice') {
            const options = data.options || [];
            options.forEach((opt, i) => {
                const input = document.getElementById('edit-opt-' + i);
                const radio = document.getElementById('edit-correct-' + i);
                if (input) input.value = opt.text || '';
                if (radio) radio.checked = opt.benar || (data.kunci_jawaban === opt.id);
            });
        } else if (templateType === 'drag_and_drop') {
            const items = data.items || [];
            document.getElementById('edit-dnd-correct-hidden').value = items.findIndex(item => item.correct_zone) || 0;
            items.forEach((item, i) => {
                const prev = document.getElementById('edit-dnd-prev-' + i);
                const ph = document.getElementById('edit-dnd-ph-' + i);
                if (item.image_asset) {
                    prev.src = '/storage/quiz-assets/' + item.image_asset;
                    prev.classList.remove('hidden');
                    ph.classList.add('hidden');
                }
                if (item.correct_zone) setEditDndCorrect(i);
            });
            document.getElementById('edit-dnd-zones-hidden').value = JSON.stringify(data.zones || []);
        } else if (templateType === 'matching_game') {
            const pairs = data.pairs || [];
            const container = document.getElementById('edit-match-pairs-container');
            container.innerHTML = '';
            pairs.forEach((p, i) => {
                const div = document.createElement('div');
                div.className = 'grid grid-cols-[1fr_auto_1fr_auto] gap-3 items-center';
                div.innerHTML = `
                    <input type="text" name="pair_left[]" value="${p.left.text}" class="bg-white border-2 border-black rounded-xl px-3 py-2 text-sm font-bold">
                    <span class="dark:text-white">↔</span>
                    <input type="text" name="pair_right[]" value="${p.right.text}" class="bg-white border-2 border-black rounded-xl px-3 py-2 text-sm font-bold">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500">×</button>`;
                container.appendChild(div);
            });
        } else if (templateType === 'fill_blank') {
            document.getElementById('edit-fill-sentence-input').value = data.teks_soal || data.question_text || '';
            parseEditFillBlanks(data.correct_answers || []);
            document.getElementById('edit-wb-hidden').value = JSON.stringify(data.word_bank || []);
        } else if (templateType === 'image_quiz') {
            if (data.main_image) {
                const prev = document.getElementById('edit-imgq-preview');
                prev.src = '/storage/quiz-assets/' + data.main_image;
                prev.classList.remove('hidden');
                document.getElementById('edit-imgq-ph').classList.add('hidden');
            }
            (data.options || []).forEach((opt, i) => {
                const input = document.getElementById('edit-imgq-opt-' + i);
                const radio = document.getElementById('edit-imgq-correct-' + i);
                if (input) input.value = opt.text || '';
                if (radio) radio.checked = opt.benar || (data.kunci_jawaban === opt.id);
            });
        }

        toggleModal('modal-edit');
    }

    function previewEditDnd(input, idx) {
        const file = input.files[0];
        if (!file) return;
        const prev = document.getElementById('edit-dnd-prev-' + idx);
        const ph = document.getElementById('edit-dnd-ph-' + idx);
        const reader = new FileReader();
        reader.onload = e => {
            prev.src = e.target.result;
            prev.classList.remove('hidden');
            ph.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function setEditDndCorrect(idx) {
        document.getElementById('edit-dnd-correct-hidden').value = idx;
        for (let i = 0; i < 4; i++) {
            const btn = document.getElementById('edit-dnd-btn-' + i);
            if (btn) btn.classList.toggle('bg-neo-green', i === idx);
        }
    }

    function addEditMatchPair() {
        const container = document.getElementById('edit-match-pairs-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-[1fr_auto_1fr_auto] gap-3 items-center';
        div.innerHTML = `
            <input type="text" name="pair_left[]" placeholder="Kiri" class="bg-white border-2 border-black rounded-xl px-3 py-2 text-sm font-bold">
            <span class="dark:text-white">↔</span>
            <input type="text" name="pair_right[]" placeholder="Kanan" class="bg-white border-2 border-black rounded-xl px-3 py-2 text-sm font-bold">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500">×</button>`;
        container.appendChild(div);
    }

    function parseEditFillBlanks(answers = []) {
        const sentence = document.getElementById('edit-fill-sentence-input').value;
        const blanks = (sentence.match(/___/g) || []).length;
        const container = document.getElementById('edit-fill-blanks-answers');
        container.innerHTML = '';
        if (blanks > 0) {
            container.classList.remove('hidden');
            for (let i = 0; i < blanks; i++) {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 p-2 bg-white border-2 border-black rounded-xl';
                div.innerHTML = `
                    <span class="font-black text-xs">${i+1}</span>
                    <input type="text" name="fill_correct_answers[]" value="${answers[i] || ''}" placeholder="Jawaban..." class="flex-1 text-sm outline-none">`;
                container.appendChild(div);
            }
        } else {
            container.classList.add('hidden');
        }
    }

    function previewEditImgQ(input) {
        const file = input.files[0];
        if (!file) return;
        const prev = document.getElementById('edit-imgq-preview');
        const ph = document.getElementById('edit-imgq-ph');
        const reader = new FileReader();
        reader.onload = e => {
            prev.src = e.target.result;
            prev.classList.remove('hidden');
            ph.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    let deleteId = null;
    function confirmDelete(id) {
        deleteId = id;
        toggleModal('modal-delete');
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', () => {
        if (deleteId) {
            document.getElementById('delete-form-' + deleteId).submit();
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
</script>
</body>
</html>



