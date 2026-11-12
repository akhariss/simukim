<div
    x-data="{
        open: false,
        izin: null,
        init() {
            window.addEventListener('open-detail-izin', (e) => {
                this.izin = e.detail;
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
                            <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Detail Surat Izin Santri</h3>
                                <p class="text-[10.5px] text-slate-400" x-text="izin ? 'Nomor Berkas #' + izin.id : ''"></p>
                            </div>
                        </div>
                        <button type="button" @click="open = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- 2. Scrollable Body -->
                    <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3.5 text-xs">
                        <template x-if="izin">
                            <div class="space-y-3">
                                <!-- Card Info Mahasantri -->
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate text-xs" x-text="izin.nama"></div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span class="font-mono" x-text="izin.nim"></span>
                                            <span>&bull;</span>
                                            <span x-text="izin.gender"></span>
                                            <span x-show="izin.prodi">&bull; <span x-text="izin.prodi"></span></span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-[10.5px] font-semibold bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 shrink-0" x-text="izin.kamar"></span>
                                </div>

                                <!-- Grid Detail Perizinan -->
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        <span class="text-[10px] text-slate-400 block font-medium">Kategori Izin</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 block truncate" x-text="izin.kategori"></span>
                                    </div>
                                    <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        <span class="text-[10px] text-slate-400 block font-medium">Status Berkas</span>
                                        <span class="inline-flex items-center gap-1 mt-0.5 font-bold" :class="{
                                            'text-blue-600 dark:text-blue-400': izin.status === 'berlaku',
                                            'text-emerald-600 dark:text-emerald-400': izin.status === 'selesai',
                                            'text-rose-600 dark:text-rose-400': izin.status === 'dibatalkan'
                                        }">
                                            <span class="size-1.5 rounded-full" :class="{
                                                'bg-blue-600': izin.status === 'berlaku',
                                                'bg-emerald-600': izin.status === 'selesai',
                                                'bg-rose-600': izin.status === 'dibatalkan'
                                            }"></span>
                                            <span x-text="izin.status === 'berlaku' ? 'Sedang Berlaku' : (izin.status === 'selesai' ? 'Telah Kembali' : 'Dibatalkan')"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Rentang Tanggal & Durasi -->
                                <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block font-medium">Jadwal Keluar s/d Kembali</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5 block" x-text="izin.tanggal_mulai + ' — ' + izin.tanggal_selesai"></span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-blue-300" x-text="izin.durasi + ' Hari'"></span>
                                </div>

                                <!-- Alasan Keperluan -->
                                <div>
                                    <span class="text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 block mb-1">Alasan / Keperluan Izin</span>
                                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-300 leading-relaxed text-xs whitespace-pre-line" x-text="izin.alasan"></div>
                                </div>

                                <!-- Meta Info: Dicatat Oleh & Waktu Input -->
                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px] text-slate-400">
                                    <div>
                                        <span>Dicatat oleh: </span>
                                        <strong class="text-slate-700 dark:text-slate-300 font-medium" x-text="izin.input_by_name || 'Admin Asrama'"></strong>
                                    </div>
                                    <div x-text="izin.created_at"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- 3. Sticky Footer -->
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-between gap-2">
                        <template x-if="izin && izin.cetak_url">
                            <a :href="izin.cetak_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 transition">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak Surat</span>
                            </a>
                        </template>
                        <button type="button" @click="open = false" class="ml-auto px-4 py-1.5 sm:py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

