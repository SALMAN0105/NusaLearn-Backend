{{-- resources/views/admin/assets/manifest.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manifest Aset AI — NusaLearn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .shadow-neo { box-shadow: 3px 3px 0 0 #000; }
        .shadow-neo-sm { box-shadow: 2px 2px 0 0 #000; }
    </style>
</head>
<body class="bg-violet-50 dark:bg-[#13103a] min-h-screen p-6 lg:p-10">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('assets.index') }}" class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-xl px-4 py-2 font-black text-sm text-black dark:text-white shadow-neo-sm hover:shadow-neo transition-all">
                <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
            </a>
            <div>
                <h1 class="text-2xl font-black text-black dark:text-white">Manifest AI</h1>
                <p class="text-sm text-gray-500 dark:text-purple-300/60">Dibuat: {{ $manifest['generated_at'] }}</p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-[#2d2460] border-2 border-black rounded-xl p-4 shadow-neo-sm text-center">
                <div class="text-3xl font-black text-black dark:text-white">{{ $manifest['stats']['total'] }}</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-1">Total Aset Aktif</div>
            </div>
            @foreach($manifest['stats']['by_type'] ?? [] as $type => $count)
            <div class="bg-white dark:bg-[#2d2460] border-2 border-black rounded-xl p-4 shadow-neo-sm text-center">
                <div class="text-3xl font-black text-black dark:text-white">{{ $count }}</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-1">{{ ucfirst($type) }}</div>
            </div>
            @endforeach
        </div>

        {{-- Prompt Text Preview --}}
        <div class="bg-white dark:bg-[#2d2460] border-2 border-black dark:border-p-dark rounded-2xl shadow-neo overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b-2 border-black dark:border-p-dark">
                <div>
                    <h2 class="font-black text-black dark:text-white">Teks Manifest untuk Prompt AI</h2>
                    <p class="text-xs text-gray-400 mt-1">Ini yang dikirim ke LLM saat membuat soal. AI hanya boleh pakai nama_file di bawah ini.</p>
                </div>
                <button onclick="copyManifest()" class="bg-yellow-400 border-2 border-black rounded-xl px-4 py-2 font-black text-xs text-black shadow-neo-sm hover:shadow-neo transition-all">
                    <i class="fa-solid fa-copy mr-1"></i> Salin
                </button>
            </div>
            <pre id="manifest-text" class="p-5 text-xs font-mono text-gray-700 dark:text-gray-300 overflow-x-auto whitespace-pre-wrap leading-relaxed max-h-[500px] overflow-y-auto">{{ $promptText }}</pre>
        </div>
    </div>

<script>
function copyManifest() {
    navigator.clipboard.writeText(document.getElementById('manifest-text').textContent)
        .then(() => showCustomAlert('Manifest disalin ke clipboard!'));
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
