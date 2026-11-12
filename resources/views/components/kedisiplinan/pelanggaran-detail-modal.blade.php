<div
    x-data="{
        open: false,
        pel: null,
        init() {
            window.addEventListener('open-detail-pelanggaran', (e) => {
                this.pel = e.detail;
                this.open = true;
            });
        }
    }"
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
                    class="relative w-full max-w-lg my-auto max-h-[calc(100vh-3rem)] sm:max-h-[calc(100vh-4rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col overflow-hidden z-10"
                >
                    <!-- 1. Sticky Header -->
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-slate-800 text-rose-600 dark:text-rose-400">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Detail Kasus Pelanggaran</h3>
                                <p class="text-[10.5px] text-slate-400" x-text="pel ? 'Kasus #' + pel.id + ' • ' + pel.tanggal : ''"></p>
                            </div>
                        </div>
                        <button type="button" @click="open = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- 2. Scrollable Body -->
                    <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3.5 text-xs">
                        <template x-if="pel">
                            <div class="space-y-3">
                                <!-- Card Info Mahasantri -->
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate text-xs" x-text="pel.nama"></div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span class="font-mono" x-text="pel.nim"></span>
                                            <span x-show="pel.gender">&bull; <span x-text="pel.gender"></span></span>
                                            <span x-show="pel.prodi">&bull; <span x-text="pel.prodi"></span></span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-[10.5px] font-semibold bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 shrink-0" x-text="pel.kamar"></span>
                                </div>

                                <!-- Grid Detail Pelanggaran -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        <span class="text-[10px] text-slate-400 block font-medium">Tingkat Pelanggaran</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 block truncate" x-text="pel.jenis"></span>
                                    </div>
                                    <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        <span class="text-[10px] text-slate-400 block font-medium">Tanggal Kejadian</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5 block" x-text="pel.tanggal"></span>
                                    </div>
                                    <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        <span class="text-[10px] text-slate-400 block font-medium">Status Pelaksanaan</span>
                                        <span class="inline-flex items-center gap-1 mt-0.5 font-bold" :class="pel.status === 'selesai' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                            <span class="size-1.5 rounded-full" :class="pel.status === 'selesai' ? 'bg-emerald-600' : 'bg-amber-600'"></span>
                                            <span x-text="pel.status === 'selesai' ? 'Selesai Dilaksanakan' : 'Belum Dilaksanakan'"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Sanksi / Hukuman -->
                                <div>
                                    <span class="text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 block mb-1">Tindakan / Sanksi yang Diberikan</span>
                                    <div class="p-2.5 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 text-amber-900 dark:text-amber-300 font-medium text-xs">
                                        <span x-text="pel.hukuman || 'Belum ada sanksi khusus yang ditentukan'"></span>
                                    </div>
                                </div>

                                <!-- Kronologi / Uraian -->
                                <div>
                                    <span class="text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 block mb-1">Kronologi / Catatan Kejadian</span>
                                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-300 leading-relaxed text-xs whitespace-pre-line" x-text="pel.deskripsi"></div>
                                </div>

                                <!-- Meta Info: Dicatat Oleh & Waktu Input -->
                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px] text-slate-400">
                                    <div>
                                        <span>Dicatat oleh: </span>
                                        <strong class="text-slate-700 dark:text-slate-300 font-medium" x-text="pel.input_by_name || 'Petugas Disiplin'"></strong>
                                    </div>
                                    <div x-text="pel.created_at"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- 3. Sticky Footer -->
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-end gap-2">
                        <button type="button" @click="open = false" class="px-4 py-1.5 sm:py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

