@props([
    'dialogId' => 'export-dialog',
    'title' => 'Ekspor Data',
    'action' => '#',
    'activePeriode' => null,
])

<div
    x-data="{
        open: false,
        scope: 'bulan',
        bulan: '{{ date('Y-m') }}'
    }"
    @open-export-{{ $dialogId }}.window="open = true"
    @keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Full Viewport Backdrop -->
            <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="open = false"></div>

            <!-- Centering Wrapper (Safe Scrolling with my-auto) -->
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 py-6 sm:py-8">
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="relative w-full max-w-md my-auto max-h-[calc(100vh-3rem)] sm:max-h-[calc(100vh-4rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col overflow-hidden z-10"
                >
                    <!-- 1. Sticky Header -->
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                                <p class="text-[10.5px] text-slate-400 mt-0.5">Pilih rentang data dan format dokumen</p>
                            </div>
                        </div>
                        <button type="button" @click="open = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Form Container -->
                    <form action="{{ $action }}" method="GET" target="_blank" @submit="setTimeout(() => open = false, 500)" class="flex flex-col flex-1 min-h-0">
                        <!-- 2. Scrollable Body -->
                        <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3.5 text-xs">
                            <!-- Rentang Waktu -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                                    Rentang Waktu Laporan *
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer bg-slate-50/70 dark:bg-slate-800/50 hover:bg-slate-100 transition text-xs" :class="scope === 'bulan' ? 'ring-2 ring-emerald-500/20 border-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/20' : ''">
                                        <input type="radio" name="scope" value="bulan" x-model="scope" class="text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-xs">Bulanan</span>
                                            <span class="text-[10px] text-slate-400">Bulan tertentu</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer bg-slate-50/70 dark:bg-slate-800/50 hover:bg-slate-100 transition text-xs" :class="scope === 'periode' ? 'ring-2 ring-emerald-500/20 border-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/20' : ''">
                                        <input type="radio" name="scope" value="periode" x-model="scope" class="text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-xs">Satu Periode</span>
                                            <span class="text-[10px] text-slate-400 truncate max-w-[90px] block">{{ $activePeriode?->nama ?? 'Aktif' }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Pilihan Bulan (Tampil jika Bulanan) -->
                            <div x-show="scope === 'bulan'" x-transition class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                    Pilih Bulan Laporan *
                                </label>
                                <input
                                    type="month"
                                    name="bulan"
                                    x-model="bulan"
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                >
                            </div>
                        </div>

                        <!-- 3. Sticky Footer: Pilihan Format (Default: PDF Resmi) -->
                        <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 grid grid-cols-2 gap-2">
                            <button
                                type="submit"
                                name="format"
                                value="pdf"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Unduh PDF</span>
                            </button>
                            <button
                                type="submit"
                                name="format"
                                value="excel"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition cursor-pointer"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Unduh Excel</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
