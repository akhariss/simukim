<x-app-layout title="Sinkron & Optimasi Sistem">
    <div class="space-y-4 sm:space-y-5 max-w-7xl mx-auto">
        <!-- 1. Page Header -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Sistem'],
                ['label' => 'Sinkron & Optimasi']
            ]"
            title="Kelola Cache & Optimasi Sistem"
            description="Percepat kinerja aplikasi dengan membersihkan berkas sementara, menyegarkan tampilan, dan memperbarui data mahasantri kampus."
        />

        <!-- 2. Notifikasi Status -->
        @if (session('status'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-slate-800 border border-emerald-200/80 dark:border-slate-700 text-emerald-900 dark:text-emerald-200 text-xs sm:text-sm flex items-center gap-3 shadow-2xs">
                <div class="size-8 rounded-xl bg-emerald-100 dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <span class="font-semibold">{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-slate-800 border border-rose-200/80 dark:border-slate-700 text-rose-900 dark:text-rose-200 text-xs sm:text-sm flex items-center gap-3 shadow-2xs">
                <div class="size-8 rounded-xl bg-rose-100 dark:bg-slate-700 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </div>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- 3. KPI Cards: Status Kesehatan & Kinerja Sistem (Mudah Dipahami Non-IT) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <!-- KPI 1: Kecepatan Sistem -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kondisi Kinerja</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80">
                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Optimal
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Sangat Responsif</p>
                <p class="text-[11px] text-slate-400">Pangkalan data lokal termuat cepat tanpa kendala.</p>
            </div>

            <!-- KPI 2: Berkas Memori Sementara -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Berkas Sementara (Cache)</span>
                    <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400">{{ $cacheStats['count'] }} berkas</span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $cacheStats['size_formatted'] }}</p>
                <p class="text-[11px] text-slate-400">Penyimpanan memori ringan (<span class="font-mono text-[10px]">storage/framework/cache</span>).</p>
            </div>

            <!-- KPI 3: Data Mahasantri -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mahasantri Terdaftar</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $localStats['assigned_santri'] }} Kamar</span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ number_format($localStats['total_santri']) }} Santri</p>
                <p class="text-[11px] text-slate-400">{{ $localStats['lunas_santri'] }} lunas &bull; {{ $localStats['belum_lunas_santri'] }} belum bayar</p>
            </div>
        </div>

        <!-- 4. Matriks Komparasi Data Pangkalan Asrama vs Portal Kampus (SAHAJA) -->
        @include('admin.cache.partials.comparison-matrix', [
            'activePeriode' => $activePeriode,
            'localStats' => $localStats,
            'sahajaSummary' => $sahajaSummary,
            'lastSyncTime' => $lastSyncTime,
        ])

        <!-- 5. Dua Panel Aksi Ramah Pengguna -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- PANEL A: Percepat & Bersihkan Aplikasi -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <div class="size-9 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Percepat Aplikasi</h2>
                            <p class="text-[11px] text-slate-400">Pembersihan memori sementara rutin</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pt-1">
                        Gunakan fitur ini jika aplikasi terasa berat atau ada data yang baru diubah tetapi belum langsung muncul di layar. Tombol ini akan membersihkan memori sementara agar aplikasi kembali ringan dan segar.
                    </p>
                </div>

                <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <form method="POST" action="{{ route('admin.cache.clear') }}" data-confirm="Bersihkan seluruh berkas sementara dan optimalkan kecepatan aplikasi?">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Percepat Aplikasi Sekarang</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.cache.clear') }}">
                        @csrf
                        <input type="hidden" name="type" value="views">
                        <button type="submit" class="w-full py-2 px-3 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Segarkan Tampilan Halaman Saja</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- PANEL B: Sinkronisasi Data Mahasantri Kampus -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <div class="size-9 rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Sinkronisasi Data Kampus</h2>
                            <p class="text-[11px] text-slate-400">Pangkalan data akademik kampus (Sahaja)</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pt-1">
                        Tarik data mahasantri muqim dan penetapan kamar terbaru dari portal kampus Sahaja. Klik tombol di bawah ketika ada santri baru yang baru membayar atau mendaftar di sistem kampus.
                    </p>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-1 text-xs">
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span>Semester Target:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $activePeriode?->nama ?? 'Belum Ada Periode' }} ({{ $activePeriode?->kode ?? '-' }})</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span>Riwayat Sinkron:</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                @if($lastSyncTime)
                                    {{ $lastSyncTime }} ({{ $lastSyncCount }} santri)
                                @else
                                    Siap disinkronkan
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <form method="POST" action="{{ route('admin.cache.sahaja.sync') }}" data-confirm="Tarik dan perbarui data mahasantri dari portal kampus Sahaja untuk semester {{ $activePeriode?->nama }}?">
                        @csrf
                        <input type="hidden" name="kode_periode" value="{{ $activePeriode?->kode }}">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Tarik Data Mahasantri Terbaru</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 5. AREA KHUSUS PEMULIHAN DATABASE (Terisolasi Sesuai Arahan, Tidak Mengganggu Admin Umum) -->
        <div x-data="{ openDanger: false }" class="pt-4 border-t border-slate-200/80 dark:border-slate-800">
            <button
                type="button"
                @click="openDanger = !openDanger"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition cursor-pointer"
            >
                <svg class="size-3.5 transition-transform" :class="openDanger ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Pengaturan Lanjutan: Pemulihan Database (Khusus Developer / Darurat)</span>
            </button>

            <div x-show="openDanger" x-cloak class="mt-3">
                @include('admin.cache.partials.danger-zone')
            </div>
        </div>
    </div>
</x-app-layout>
