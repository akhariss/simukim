{{-- Inline Section: Detail Agenda & Jadwal Harian Terpilih di Bawah Kalender --}}
<div class="p-3.5 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
    <!-- Section Header (Proporsional & Anti-Pecah di Mobile) -->
    <div class="flex items-center justify-between gap-2 pb-2.5 sm:pb-3 border-b border-slate-100 dark:border-slate-800">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="size-2 rounded-full bg-blue-600 shrink-0"></span>
                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedDateTitle"></h3>
                <template x-if="selectedDayIsToday">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold badge-info shrink-0">
                        Hari Ini
                    </span>
                </template>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEvents.length"></span> agenda &amp; jadwal kelas
            </p>
        </div>

        <!-- Helper Tip (Tampil di Desktop, Disembunyikan di Mobile agar Tidak Memakan Ruang) -->
        <p class="text-[11px] text-slate-400 hidden sm:flex items-center gap-1.5 shrink-0">
            <svg class="size-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Klik tanggal lain pada kalender untuk melihat jadwalnya</span>
        </p>
    </div>

    <!-- List of Events for the Selected Date -->
    <div class="space-y-2.5 sm:space-y-3">
        <template x-for="(ev, index) in selectedEvents" :key="ev.id || index">
            <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800/70 space-y-2.5 transition hover:border-slate-300 dark:hover:border-slate-700">
                <!-- Event Header & Badges -->
                <div class="flex items-start justify-between gap-2">
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="size-2 rounded-full shrink-0"
                                :class="ev.is_kelas ? 'bg-emerald-500' : (ev.jenis_code === 'rutin' ? 'bg-blue-500' : 'bg-purple-500')"
                            ></span>
                            <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white truncate" x-text="ev.nama"></h4>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 text-[10px] font-semibold">
                            <!-- Category / Class Type Badge -->
                            <template x-if="ev.is_kelas">
                                <span
                                    class="px-2 py-0.5 rounded-md font-bold border"
                                    :class="ev.tipe_badge || 'badge-success'"
                                    x-text="ev.tipe_label || ev.jenis_label || 'Kelas Akademik'"
                                ></span>
                            </template>
                            <template x-if="!ev.is_kelas">
                                <span
                                    class="px-2 py-0.5 rounded-md font-bold border"
                                    :class="ev.jenis_code === 'rutin' ? 'badge-info' : 'badge-tutor'"
                                    x-text="ev.jenis_label || 'Kegiatan'"
                                ></span>
                            </template>

                            <!-- Time Badge -->
                            <template x-if="ev.jam_mulai">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60 font-mono">
                                    <svg class="size-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span><span x-text="ev.jam_mulai"></span> - <span x-text="ev.jam_selesai || 'Selesai'"></span> WIB</span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Single Class Direct Link -->
                    <template x-if="ev.url && !ev.is_grouped">
                        <a
                            :href="ev.url"
                            class="inline-flex items-center gap-1 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-xl text-[11px] sm:text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-white hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-slate-700 border border-emerald-200 dark:border-slate-700 transition shrink-0"
                        >
                            <span>Detail</span>
                            <span class="hidden sm:inline">Kelas</span>
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </template>
                </div>

                <!-- Tipe, Lokasi, Tutor & Deskripsi -->
                <div class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 space-y-1 pt-1 border-t border-slate-200/50 dark:border-slate-700/50">
                    <template x-if="ev.is_kelas && ev.tipe_label">
                        <p class="flex items-center gap-1.5">
                            <svg class="size-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span class="text-slate-400 font-medium">Tipe:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100" x-text="ev.tipe_label"></span>
                        </p>
                    </template>
                    <template x-if="ev.lokasi">
                        <p class="flex items-center gap-1.5">
                            <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-slate-400 font-medium">Lokasi:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="ev.lokasi"></span>
                        </p>
                    </template>
                    <template x-if="ev.tutor_name && !ev.is_grouped">
                        <p class="flex items-center gap-1.5">
                            <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-slate-400 font-medium">Tutor:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="ev.tutor_name"></span>
                        </p>
                    </template>
                    <template x-if="ev.deskripsi">
                        <p class="text-slate-500 dark:text-slate-400 italic text-[10.5px] pt-0.5" x-text="ev.deskripsi"></p>
                    </template>
                </div>

                <!-- Grouped Parallel Classes Section (Jika Grup Serentak) -->
                <template x-if="ev.is_grouped && ev.sub_items && ev.sub_items.length > 0">
                    <div class="mt-2 p-2.5 sm:p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span class="flex items-center gap-1.5">
                                <svg class="size-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span>Rincian Kelompok Halaqah Serentak:</span>
                            </span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono font-bold" x-text="ev.sub_items.length + ' Grup Aktif'"></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="g in ev.sub_items" :key="g.id">
                                <div class="p-2.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-slate-900 dark:text-white truncate" x-text="g.nama"></p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Tutor: <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="g.tutor_name || 'Belum Ditunjuk'"></span>
                                        </p>
                                    </div>
                                    <a
                                        :href="g.url"
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white hover:bg-blue-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-slate-700 transition shrink-0"
                                    >
                                        Detail
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Empty State if no events on selected date -->
        <template x-if="selectedEvents.length === 0">
            <div class="py-6 sm:py-8 text-center text-xs text-slate-400 space-y-1">
                <div class="size-9 sm:size-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-1.5">
                    <svg class="size-4 sm:size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="font-semibold text-slate-600 dark:text-slate-300">Tidak ada agenda kegiatan atau jadwal kelas pada tanggal ini.</p>
                <p class="text-[11px] text-slate-400">Silakan klik tanggal lain yang memiliki tanda agenda pada kalender di atas.</p>
            </div>
        </template>
    </div>
</div>
