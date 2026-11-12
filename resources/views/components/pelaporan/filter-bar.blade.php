@props([
    'activePeriode' => null,
    'periodeList' => collect(),
])

<div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3.5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2.5">
            <div class="size-7 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            </div>
            <div>
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">
                    Parameter &amp; Ruang Lingkup Laporan
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Tentukan periode akademik dan cakupan waktu unduhan berkas.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 self-start sm:self-auto">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                <span>Periode Aktif: {{ $activePeriode->nama ?? 'Tahun Ini' }}</span>
            </span>
        </div>
    </div>

    <!-- Kontrol Filter Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 text-xs">
        <!-- Dropdown Periode Akademik -->
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Periode Akademik</label>
            <select x-model="periodeId" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                @foreach($periodeList as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }} {{ $p->is_active ? '(Aktif)' : '' }}</option>
                @endforeach
            </select>
        </div>

        <!-- Pilihan Rentang Waktu (1 Periode vs Bulanan) -->
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Ruang Lingkup Waktu</label>
            <div class="h-[34px] p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 flex items-center">
                <button
                    type="button"
                    @click="rentang = 'periode'"
                    :class="rentang === 'periode' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-slate-200'"
                    class="flex-1 h-full rounded-lg text-xs transition cursor-pointer flex items-center justify-center"
                >
                    1 Periode
                </button>
                <button
                    type="button"
                    @click="rentang = 'bulanan'"
                    :class="rentang === 'bulanan' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-slate-200'"
                    class="flex-1 h-full rounded-lg text-xs transition cursor-pointer flex items-center justify-center"
                >
                    Bulanan
                </button>
            </div>
        </div>

        <!-- Input Bulan (Aktif jika rentang = bulanan) -->
        <div x-show="rentang === 'bulanan'" x-transition>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Pilih Bulan</label>
            <input
                type="month"
                x-model="bulan"
                class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
            >
        </div>

        <!-- Filter Kategori Modul -->
        <div :class="rentang === 'bulanan' ? '' : 'sm:col-span-2'">
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Kategori Tampilan</label>
            <select x-model="kategori" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                <option value="semua">Semua Modul &amp; Fitur</option>
                <option value="fasilitas">Fasilitas &amp; Hunian Asrama</option>
                <option value="akademik">Akademik &amp; Nilai Halaqah</option>
                <option value="kedisiplinan">Kedisiplinan &amp; Perizinan</option>
            </select>
        </div>
    </div>
</div>
