{{-- resources/views/admin/assets/index.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Library Aset — NusaLearn Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .shadow-neo    { box-shadow: 3px 3px 0 0 #000; }
        .shadow-neo-sm { box-shadow: 2px 2px 0 0 #000; }
        .dot-grid-bg   { background-image: radial-gradient(circle, #c4b5fd 1px, transparent 1px); background-size: 20px 20px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar  { -ms-overflow-style: none; scrollbar-width: none; }
        .nav-active    { @apply bg-p-lt dark:bg-p-dark/40 text-p dark:text-white border-black dark:border-p-dark; }
        .asset-card:hover .asset-overlay { opacity: 1; }
        .asset-overlay { opacity: 0; transition: opacity 0.2s; }
    </style>
</head>
<body class="h-full bg-gray-100 dark:bg-[#13103a] transition-colors duration-300">


    <div class="flex h-screen overflow-hidden p-2 md:p-4 gap-4">

        {{-- ============================= SIDEBAR ============================= --}}
        <aside class="bg-white dark:bg-[#2d2460] w-[260px] flex-shrink-0 border-2 border-black dark:border-p-dark rounded-2xl flex flex-col transition-transform duration-300 fixed md:relative z-40 h-[calc(100vh-1rem)] md:h-full overflow-hidden shadow-neo -translate-x-full md:translate-x-0" id="sidebar">
            <div class="h-20 flex items-center px-6 border-b-2 border-black dark:border-p-dark">
                <div class="flex items-center gap-3">
                    <div class="bg-violet-600 text-white w-10 h-10 rounded-xl border-2 border-black shadow-neo-sm flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <span class="text-xl font-black tracking-tight text-black dark:text-white">Nusa<span class="text-violet-600">Learn</span></span>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto no-scrollbar py-5 px-4 space-y-1">
                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mb-2 uppercase tracking-widest">General</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-p-dark/40 hover:text-violet-600 border-2 border-transparent hover:border-black rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i><span>Dashboard</span>
                </a>
                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mt-6 mb-2 uppercase tracking-widest">Konten</p>
                <a href="{{ route('materials.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-p-dark/40 hover:text-violet-600 border-2 border-transparent hover:border-black rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-book-open w-5 text-center"></i><span>Materi Belajar</span>
                </a>
                <a href="{{ route('questions.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-p-dark/40 hover:text-violet-600 border-2 border-transparent hover:border-black rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-clipboard-question w-5 text-center"></i><span>Bank Soal</span>
                </a>
                <a href="{{ route('assets.index') }}" class="nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group">
                    <i class="fa-solid fa-images w-5 text-center"></i><span>Library Aset</span>
                </a>
                <p class="px-3 text-[11px] font-black text-gray-400 dark:text-purple-300/50 mt-6 mb-2 uppercase tracking-widest">Master Data</p>
                <a href="{{ route('languages.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-p-dark/40 hover:text-violet-600 border-2 border-transparent hover:border-black rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-language w-5 text-center"></i><span>Bahasa Daerah</span>
                </a>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-p-dark/40 hover:text-violet-600 border-2 border-transparent hover:border-black rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-users w-5 text-center"></i><span>Data Siswa</span>
                </a>
                <a href="{{ route('regions.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-p-dark/40 hover:text-violet-600 border-2 border-transparent hover:border-black rounded-xl transition-all font-semibold text-sm group">
                    <i class="fa-solid fa-map-location-dot w-5 text-center"></i><span>Wilayah</span>
                </a>
            </nav>
            <div class="border-t-2 border-black dark:border-p-dark p-4">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=7C3AED&color=fff&bold=true"
                         alt="Avatar" class="w-10 h-10 rounded-full border-2 border-black shadow-neo-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-black dark:text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400 font-semibold">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ============================= MAIN CONTENT ============================= --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-white dark:bg-[#241f5c] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo">

            {{-- Header --}}
            <header class="h-20 border-b-2 border-black dark:border-p-dark flex items-center justify-between px-6 lg:px-10 z-20 sticky top-0 bg-white dark:bg-[#241f5c] rounded-t-2xl">
                <div>
                    <h1 class="text-xl font-black text-black dark:text-white tracking-tight">Library Aset Multimedia</h1>
                    <p class="text-xs font-semibold text-gray-400 mt-1">Content-Addressable Storage — sumber gambar, audio, dan animasi untuk kuis interaktif</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('assets.manifest') }}" class="hidden lg:flex items-center gap-2 px-4 py-2 border-2 border-black dark:border-p-dark rounded-xl font-black text-xs text-black dark:text-white hover:bg-violet-50 transition-all shadow-neo-sm">
                        <i class="fa-solid fa-list-check text-violet-600"></i> Manifest AI
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto no-scrollbar p-6 lg:p-8 bg-violet-50 dark:bg-[#1e1b4b] dot-grid-bg">

                {{-- Flash messages --}}
                @if(session('success'))
                <div class="mb-5 flex items-center gap-3 px-5 py-4 rounded-xl border-2 border-black bg-green-400 shadow-neo-sm text-sm font-bold text-black">
                    <i class="fa-solid fa-check-circle text-lg"></i> {{ session('success') }}
                </div>
                @endif
                @if($errors->any())
                <div class="mb-5 flex items-center gap-3 px-5 py-4 rounded-xl border-2 border-black bg-red-400 shadow-neo-sm text-sm font-bold text-black">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i> {{ $errors->first() }}
                </div>
                @endif

                {{-- Stats Bar --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    @foreach([
                        ['Total Aset',  $stats['total'] ?? 0,                        'fa-images',     'bg-violet-600 text-white'],
                        ['Gambar',      $stats['by_type']['image'] ?? 0,              'fa-image',      'bg-yellow-400 text-black'],
                        ['Audio',       $stats['by_type']['audio'] ?? 0,             'fa-music',      'bg-green-400 text-black'],
                        ['Animasi',     $stats['by_type']['application'] ?? 0,       'fa-film',       'bg-blue-400 text-black'],
                    ] as [$label, $count, $icon, $style])
                    <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl p-4 shadow-neo-sm flex items-center gap-4">
                        <div class="{{ $style }} w-10 h-10 rounded-lg border-2 border-black flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-black dark:text-white">{{ $count }}</div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $label }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Filter Bar --}}
                <form method="GET" class="flex flex-wrap gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / tag..."
                        class="border-2 border-black dark:border-p-dark rounded-xl px-4 py-2 text-sm font-medium bg-white dark:bg-[#2d2460] text-black dark:text-white">
                    <select name="source" class="border-2 border-black dark:border-p-dark rounded-xl px-3 py-2 text-sm font-medium bg-white dark:bg-[#2d2460] text-black dark:text-white">
                        <option value="">Semua Sumber</option>
                        @foreach(['pixabay','pexels','freesound','lottiefiles','iconify','manual'] as $src)
                        <option value="{{ $src }}" @selected(request('source') === $src)>{{ ucfirst($src) }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="border-2 border-black dark:border-p-dark rounded-xl px-3 py-2 text-sm font-medium bg-white dark:bg-[#2d2460] text-black dark:text-white">
                        <option value="">Semua Tipe</option>
                        <option value="image" @selected(request('type') === 'image')>Gambar</option>
                        <option value="audio" @selected(request('type') === 'audio')>Audio</option>
                        <option value="application" @selected(request('type') === 'application')>Animasi</option>
                    </select>
                    <select name="category" class="border-2 border-black dark:border-p-dark rounded-xl px-3 py-2 text-sm font-medium bg-white dark:bg-[#2d2460] text-black dark:text-white">
                        <option value="">Semua Kategori</option>
                        @foreach(['literasi','numerasi','budaya','umum'] as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-violet-600 border-2 border-black text-white rounded-xl px-5 py-2 font-black text-sm shadow-neo-sm hover:shadow-neo transition-all">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                </form>

                {{-- Asset Grid --}}
                @if($assets->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-20 h-20 bg-white dark:bg-[#2d2460] border-2 border-dashed border-violet-400 rounded-2xl flex items-center justify-center mb-4">
                        <i class="fa-solid fa-images text-3xl text-violet-400"></i>
                    </div>
                    <p class="font-black text-gray-500 dark:text-purple-300/50 text-lg">Library kosong</p>
                    <p class="text-sm text-gray-400 mt-2">Klik "Cari dari API" atau "Upload" untuk mulai menambahkan aset.</p>
                </div>
                @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($assets as $asset)
                    <div class="asset-card bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl overflow-hidden shadow-neo-sm hover:shadow-neo hover:-translate-y-0.5 transition-all relative group">
                        {{-- Preview --}}
                        <div class="aspect-square bg-gray-100 dark:bg-[#1e1b4b] flex items-center justify-center overflow-hidden relative">
                            @if($asset->isImage())
                                <img src="{{ $asset->getUrl() }}" alt="{{ $asset->original_name }}" class="w-full h-full object-cover">
                            @elseif($asset->isAudio())
                                <i class="fa-solid fa-music text-3xl text-blue-400"></i>
                            @elseif($asset->isLottie())
                                <i class="fa-solid fa-film text-3xl text-violet-400"></i>
                            @else
                                <i class="fa-solid fa-file text-3xl text-gray-400"></i>
                            @endif

                            {{-- Overlay actions --}}
                            <div class="asset-overlay absolute inset-0 bg-black/60 flex flex-col items-center justify-center gap-2">
                                <form action="{{ route('assets.toggle', $asset) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="text-xs font-black px-3 py-1.5 rounded-lg border-2 border-white text-white hover:bg-white hover:text-black transition-all">
                                        {{ $asset->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Hapus aset ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-black px-3 py-1.5 rounded-lg border-2 border-red-400 text-red-400 hover:bg-red-400 hover:text-white transition-all">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="p-2">
                            <p class="text-[10px] font-black text-black dark:text-white truncate" title="{{ $asset->original_name }}">{{ $asset->original_name }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md border border-current
                                    {{ $asset->source_api === 'pixabay' ? 'text-yellow-600' : '' }}
                                    {{ $asset->source_api === 'pexels' ? 'text-green-600' : '' }}
                                    {{ $asset->source_api === 'freesound' ? 'text-blue-600' : '' }}
                                    {{ $asset->source_api === 'lottiefiles' ? 'text-violet-600' : '' }}
                                    {{ in_array($asset->source_api, ['iconify', 'manual']) ? 'text-gray-500' : '' }}
                                ">{{ $asset->source_api }}</span>
                                <span class="text-[9px] text-gray-400 font-semibold">{{ $asset->size_kb }}KB</span>
                            </div>
                            @if(!$asset->is_active)
                            <div class="mt-1 text-[9px] font-black text-red-500 uppercase">⚠ Nonaktif</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $assets->links() }}
                </div>
                @endif

            </main>
        </div>
    </div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let selectedApi = 'pixabay';
</script>

</body>
</html>