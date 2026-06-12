<!-- ===================================================
     DELETE CONFIRM MODAL
=================================================== -->
<style>
    #delete-modal { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.75); backdrop-filter:blur(5px); }
    #delete-modal.active { display:flex; }
    .dm-card {
        background:#fff; border:3px solid #000; border-radius:24px;
        box-shadow:8px 8px 0 #000; width:90%; max-width:420px; overflow:hidden;
        animation:em-pop 0.3s cubic-bezier(0.34,1.56,0.64,1) both;
    }
    @keyframes em-pop { 0% { transform:scale(0.9); opacity:0; } 100% { transform:scale(1); opacity:1; } }
</style>

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

<script>
    let _pendingDeleteFormId = null;

    function openDeleteModal(formId, itemName) {
        _pendingDeleteFormId = formId;
        const targetElement = document.getElementById('delete-modal-target');
        if (targetElement) {
            targetElement.textContent = itemName;
        }
        document.getElementById('delete-modal').classList.add('active');
    }

    function closeDeleteModal() {
        _pendingDeleteFormId = null;
        document.getElementById('delete-modal').classList.remove('active');
    }

    document.getElementById('delete-confirm-btn').addEventListener('click', () => {
        if (_pendingDeleteFormId !== null) {
            const form = document.getElementById(_pendingDeleteFormId);
            if(form) {
                form.submit();
            } else {
                console.error("Form with ID " + _pendingDeleteFormId + " not found!");
            }
        }
    });

    document.getElementById('delete-modal').addEventListener('click', e => {
        if (e.target === document.getElementById('delete-modal')) closeDeleteModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
