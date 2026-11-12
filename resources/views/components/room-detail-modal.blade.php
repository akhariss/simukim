<!-- Komponen Atomic: Modal Interaktif Detail Kamar & Daftar Penghuni -->
<div
    x-data="{
        open: false,
        loading: false,
        room: null,
        errorMessage: '',
        lastRoomId: null,
        fetchRoom(id) {
            if (!id) return;
            this.lastRoomId = id;
            this.loading = true;
            this.open = true;
            this.room = null;
            this.errorMessage = '';
            const targetUrl = '{{ route('fasilitas.monitoring.room', ['kamar' => '__ID__']) }}'.replace('__ID__', encodeURIComponent(id));
            fetch(targetUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data && data.nomor_kamar) {
                    this.room = data;
                } else {
                    this.errorMessage = 'Format data kamar tidak sesuai.';
                }
                this.loading = false;
            })
            .catch(err => {
                this.loading = false;
                this.room = null;
                this.errorMessage = err.message || 'Gagal memuat detail kamar.';
            });
        }
    }"
    @open-room-modal.window="fetchRoom($event.detail.roomId)"
    @open-room-detail.window="fetchRoom($event.detail.roomId)"
    @keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            data-modal-portal="true"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
    <!-- Backdrop Gelap Lembut -->
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 bg-slate-900/60 transition-opacity"
        @click="open = false"
    ></div>

    <!-- Modal Dialog Box -->
    <div class="flex min-h-full items-center justify-center p-3 sm:p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-lg max-h-[90vh] sm:max-h-[85vh] bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800 p-5 sm:p-6 flex flex-col overflow-hidden"
        >
            <!-- Loading Indicator -->
            <div x-show="loading" class="py-14 flex flex-col items-center justify-center space-y-3">
                <svg class="animate-spin size-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Memuat data kamar & penghuni...</span>
            </div>

            <!-- Error State: Jika data gagal dimuat, modal tidak lagi blank putih! -->
            <div x-show="!loading && !room" class="py-8 flex flex-col items-center justify-center text-center space-y-3">
                <div class="size-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">Gagal Memuat Detail Kamar</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs" x-text="errorMessage || 'Data kamar tidak dapat diakses atau koneksi terputus.'"></p>
                <div class="flex items-center gap-2 pt-2">
                    <button
                        type="button"
                        @click="fetchRoom(lastRoomId)"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition cursor-pointer"
                    >
                        Coba Lagi
                    </button>
                    <button
                        type="button"
                        @click="open = false"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>

            <!-- Content Modal -->
            <div x-show="!loading && room" class="flex flex-col flex-1 min-h-0 space-y-4 overflow-hidden">
                <!-- Header Modal: Info Kamar & Lokasi -->
                <div class="shrink-0 flex items-start justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight" x-text="'Kamar ' + (room ? room.nomor_kamar : '')"></h3>
                            
                            <span
                                class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
                                :class="room && room.gender === 'Ikhwan' ? 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700' : 'bg-pink-50 text-pink-800 border-pink-200 dark:bg-slate-800 dark:text-pink-300 dark:border-slate-700'"
                                x-text="room ? room.gender : ''"
                            ></span>

                            <template x-if="room && room.tipe_ruang === 'Mudabbir'">
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700">
                                    Mudabbir
                                </span>
                            </template>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                            <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span x-text="room ? (room.gedung + ' • Lantai ' + room.lantai) : ''"></span>
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="open = false"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition cursor-pointer"
                        title="Tutup Popup"
                    >
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Body: 3 Metrik & Daftar Mahasantri Penghuni -->
                <div class="flex-1 overflow-y-auto min-h-0 space-y-4 pr-1">
                    <!-- 3 Kartu Metrik Kapasitas & Slot -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kapasitas</div>
                            <div class="text-sm font-black text-slate-800 dark:text-slate-200 mt-0.5" x-text="room ? (room.kapasitas_maks + ' Bed') : ''"></div>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Terisi</div>
                            <div class="text-sm font-black text-blue-600 dark:text-blue-400 mt-0.5" x-text="room ? (room.terisi + ' Santri') : ''"></div>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Slot Kosong</div>
                            <div
                                class="text-sm font-black mt-0.5"
                                :class="room && (room.sisa_bed || room.sisa) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'"
                                x-text="room ? ((room.sisa_bed !== undefined ? room.sisa_bed : (room.sisa || 0)) + ' Bed') : ''"
                            ></div>
                        </div>
                    </div>

                    <!-- Bagian Daftar Mahasantri Penghuni -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Mahasantri Penghuni Kamar
                            </h4>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full" x-text="room ? (room.penghuni.length + ' orang aktif') : ''"></span>
                        </div>

                        <!-- Jika Tidak Ada Santri -->
                        <template x-if="room && room.penghuni.length === 0">
                            <div class="py-8 px-4 text-center rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-dashed border-slate-200 dark:border-slate-700/80">
                                <div class="size-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Kamar belum memiliki penghuni</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Dapat di-assign melalui menu Penempatan Mahasantri.</p>
                            </div>
                        </template>

                        <!-- List Kartu Santri yang Lega & Rapi -->
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <template x-for="(santri, idx) in (room ? room.penghuni : [])" :key="santri.id">
                                <div class="p-3 rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-800/60 flex items-center justify-between gap-3 shadow-2xs hover:border-slate-300 dark:hover:border-slate-700 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="size-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            <span x-text="santri.name ? santri.name.charAt(0).toUpperCase() : '?'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="santri.name"></div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="santri.nomor_induk"></span>
                                                <span>•</span>
                                                <span class="truncate" x-text="santri.prodi || 'Reguler'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-slate-700">
                                            Aktif
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-1" x-text="'Sejak ' + santri.tanggal_masuk"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Aksi: Spacing Lega & Seimbang -->
                <div class="shrink-0 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3">
                    <a
                        :href="'{{ route('fasilitas.penempatan.index') }}?kamar_id=' + (room ? room.id : '')"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition"
                    >
                        <span>Kelola di Penempatan</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <button
                        type="button"
                        @click="open = false"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
</div>
