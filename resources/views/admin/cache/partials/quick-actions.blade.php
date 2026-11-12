<!-- Quick Action Panels -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
    <!-- Action Card 1: Data Cache -->
    <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs flex flex-col justify-between space-y-3">
        <div>
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                    💾
                </div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Data Cache Aplikasi</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Mengosongkan memori cache data query seperti statistik periode, ringkasan kamar, dan struktur dropdown gedung.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.cache.clear') }}" data-confirm="Kosongkan data cache aplikasi?">
            @csrf
            <input type="hidden" name="type" value="data">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-slate-800 dark:text-blue-300 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                <span>Hapus Data Cache</span>
            </button>
        </form>
    </div>

    <!-- Action Card 2: View Templates -->
    <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs flex flex-col justify-between space-y-3">
        <div>
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-xl bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                    📑
                </div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Cache Blade Views</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Menghapus seluruh file kompilasi Blade view PHP di disk. Laravel akan merekompilasi template secara otomatis.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.cache.clear') }}" data-confirm="Hapus seluruh kompilasi Blade view?">
            @csrf
            <input type="hidden" name="type" value="views">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-slate-800 dark:text-indigo-300 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                <span>Hapus Cache Views</span>
            </button>
        </form>
    </div>

    <!-- Action Card 3: Prune Expired Files -->
    <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs flex flex-col justify-between space-y-3">
        <div>
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-xl bg-emerald-50 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                    🧹
                </div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Pangkas Berkas Basi (Prune)</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Membersihkan hanya berkas cache yang masa berlakunya sudah expired dari disk storage tanpa menghapus cache aktif.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.cache.clear') }}" data-confirm="Jalankan pemangkasan file cache kedaluwarsa?">
            @csrf
            <input type="hidden" name="type" value="prune">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-slate-800 dark:text-emerald-300 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                <span>Pangkas File Basi (Prune)</span>
            </button>
        </form>
    </div>
</div>
