<section class="relative pt-10 pb-16 sm:pt-16 sm:pb-20 overflow-hidden">
    <!-- Atmospheric Luxury Background (Subtle Grid + Ambient Glow) -->
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-30 pointer-events-none"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 -z-10 w-full max-w-4xl h-80 bg-gradient-to-b from-blue-500/10 via-indigo-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        
        <!-- Live Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-semibold mb-5 border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 shadow-2xs">
            <span class="size-2 rounded-full bg-blue-600 animate-pulse"></span>
            <span>Platform Resmi Ma'had Al-Jami'ah &bull; UIN Siber Syekh Nurjati Cirebon</span>
        </div>

        <!-- Main Headline (Balanced Typography, Zero Cutoff) -->
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tight text-slate-950 dark:text-white leading-[1.18] sm:leading-[1.15]">
            Satu Sistem Terpadu. <br>
            <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent">
                Seluruh Ekosistem Mahasantri.
            </span>
        </h1>

        <!-- Crisp Professional Subtitle -->
        <p class="mt-3.5 text-xs sm:text-sm lg:text-[15px] max-w-2xl mx-auto leading-relaxed text-slate-600 dark:text-slate-400 font-normal">
            <strong class="font-semibold text-slate-900 dark:text-white">SIMUKIM</strong> (Sistem Informasi Mahasantri Mukim) adalah platform operasional terpadu yang menghubungkan tata kelola hunian asrama, halaqah akademik, absensi harian, kedisiplinan perizinan, hingga penerbitan laporan eksekutif dalam satu kendali digital modern.
        </p>

        <!-- CTA Buttons -->
        <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    data-no-spa
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>Masuk ke Dashboard SIMUKIM</span>
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    data-no-spa
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-white bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500 shadow-xs transition-all text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>Akses Portal SIMUKIM</span>
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            @endauth
            <a
                href="#fitur"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 border border-slate-200/90 dark:border-slate-700 shadow-2xs transition-all text-xs sm:text-sm flex items-center justify-center gap-1.5 cursor-pointer"
            >
                <span>Pelajari Modul Sistem</span>
                <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </a>
        </div>

        <!-- Autoplay Animated Feature Console (Otomatis ganti slide secara berkala) -->
        <div
            id="showcase"
            x-data="{
                activeSlide: 0,
                totalSlides: 4,
                timer: null,
                isPaused: false,
                init() {
                    this.startAutoplay();
                },
                startAutoplay() {
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (!this.isPaused) {
                            this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                        }
                    }, 4000);
                },
                selectSlide(idx) {
                    this.activeSlide = idx;
                    this.startAutoplay();
                }
            }"
            @mouseenter="isPaused = true"
            @mouseleave="isPaused = false"
            class="mt-10 text-left"
        >
            <div class="rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 p-2.5 sm:p-3.5 shadow-lg shadow-slate-200/40 dark:shadow-none">
                
                <!-- Chrome Header with Tabs & Animated Progress Indicator -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100 dark:border-slate-800 px-2">
                    <div class="flex items-center gap-1.5 py-1">
                        <span class="size-2 rounded-full bg-rose-500/80"></span>
                        <span class="size-2 rounded-full bg-amber-500/80"></span>
                        <span class="size-2 rounded-full bg-emerald-500/80"></span>
                    </div>

                    <!-- Autoplay Tab Chips -->
                    <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                        <template x-for="(tab, i) in ['1. Hunian & Kamar', '2. Kajian & Nilai', '3. Presensi & Izin', '4. Buku Laporan']" :key="i">
                            <button
                                type="button"
                                @click="selectSlide(i)"
                                :class="activeSlide === i ? 'bg-blue-600 text-white shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium'"
                                class="px-2.5 py-1 rounded-lg text-[11px] transition whitespace-nowrap cursor-pointer flex items-center gap-1.5"
                            >
                                <span class="size-1.5 rounded-full" :class="activeSlide === i ? 'bg-white animate-pulse' : 'bg-transparent'"></span>
                                <span x-text="tab"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Showcase Animated Container -->
                <div class="p-3 sm:p-5 min-h-[200px] bg-slate-50/80 dark:bg-slate-950/70 rounded-xl mt-2 border border-slate-100 dark:border-slate-800/60">
                    
                    <!-- Slide 0: Hunian & Kamar -->
                    <div x-show="activeSlide === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Tata Kelola Hunian &amp; Alokasi Kamar</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Monitoring kapasitas dan ketersediaan ranjang gedung asrama secara otomatis.</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60 shrink-0">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                <span>192 Kamar Terdata &bull; 94% Terisi</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Gedung Jadid</div>
                                <div class="text-sm font-black text-slate-900 dark:text-white mt-0.5">4 Lantai &bull; 96 Unit</div>
                                <div class="text-[10.5px] text-blue-600 dark:text-blue-400 mt-1">Hunian Mahasantri Putra</div>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Gedung Qadim</div>
                                <div class="text-sm font-black text-slate-900 dark:text-white mt-0.5">4 Lantai &bull; 96 Unit</div>
                                <div class="text-[10.5px] text-emerald-600 dark:text-emerald-400 mt-1">Hunian Mahasantri Putri</div>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Proteksi Alokasi</div>
                                <div class="text-sm font-black text-slate-900 dark:text-white mt-0.5">Pessimistic Lock</div>
                                <div class="text-[10.5px] text-slate-500 mt-1">Mencegah Overbooking</div>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Status Mukim</div>
                                <div class="text-sm font-black text-slate-900 dark:text-white mt-0.5">Terverifikasi</div>
                                <div class="text-[10.5px] text-indigo-600 dark:text-indigo-400 mt-1">Sinkron Pangkalan Kampus</div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 1: Kajian & Nilai -->
                    <div x-show="activeSlide === 1" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Kurikulum Halaqah &amp; Transkrip Nilai</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pencatatan evaluasi harian, ujian lisan, huruf mutu (A-E), dan kelulusan santri.</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10.5px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/60 shrink-0">
                                <span>Kurikulum Terstruktur</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-slate-900 dark:text-white">Halaqah Qur'ani</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Tahsin tajwid, makharijul huruf, dan setoran tahfidz bertahap.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-slate-900 dark:text-white">Kajian Turots (Kitab)</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Kajian Fiqih, Akhlak, dan Aqidah bersanad bersama dosen pengampu.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-slate-900 dark:text-white">Transkrip Otomatis</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Penghitungan nilai akhir dan rekapitulasi kelulusan siap ekspor.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2: Disiplin & Izin -->
                    <div x-show="activeSlide === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Kedisiplinan &amp; Perizinan Digital</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Alur tertib izin keluar/pulang bertingkat dan perekaman presensi kegiatan.</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10.5px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60 shrink-0">
                                <span>Approval Bertingkat</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-slate-900 dark:text-white">Izin Pulang &amp; Keluar Digital</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pengajuan mandiri oleh mahasantri dengan verifikasi pengasuh.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-slate-900 dark:text-white">Presensi Shalat 5 Waktu</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Perekaman kehadiran shalat fardhu berjamaah dan taklim keasramaan.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-slate-900 dark:text-white">Buku Ketertiban Santri</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pencatatan poin pembinaan karakter yang adil dan transparan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3: Buku Laporan -->
                    <div x-show="activeSlide === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Pusat Pelaporan Terpadu Eksekutif</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Konsolidasi 5 modul dalam format dokumen resmi cetak dan Excel multi-sheet.</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10.5px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800/60 shrink-0">
                                <span>Dokumen Resmi Pimpinan</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-blue-600 dark:text-blue-400">Buku PDF Terpadu (A4 Portrait)</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Dokumen terpadu lengkap dengan lembar pengesahan Mudir Ma'had.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                                <div class="font-bold text-emerald-600 dark:text-emerald-400">Excel Multi-Sheet Terpadu</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Data analitik 5 sheet (Okupansi, Nilai, Kehadiran, Pelanggaran, Izin) siap olah.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</section>
