@props(['currentMonthCarbon', 'prevMonth', 'nextMonth'])

<!-- Integrated Calendar Topbar (Kompak, Elegan, 1 Baris & Hemat Ruang) -->
<div class="px-3.5 py-2.5 sm:px-4 sm:py-2.5 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-2 sm:gap-3 bg-slate-50/70 dark:bg-slate-800/40">
    <!-- Month Navigation (< Desember 2026 >) -->
    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
        <a
            href="{{ route('kegiatan.index', ['view' => 'calendar', 'bulan' => $prevMonth->month, 'tahun' => $prevMonth->year]) }}"
            class="p-1 sm:p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700 transition cursor-pointer"
            title="Bulan Sebelumnya"
            aria-label="Bulan Sebelumnya"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">
            {{ $currentMonthCarbon->translatedFormat('F Y') }}
        </h3>

        <a
            href="{{ route('kegiatan.index', ['view' => 'calendar', 'bulan' => $nextMonth->month, 'tahun' => $nextMonth->year]) }}"
            class="p-1 sm:p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700 transition cursor-pointer"
            title="Bulan Berikutnya"
            aria-label="Bulan Berikutnya"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    <!-- Visual Color Legend (Ultra-Compact, 1 Baris di Semua Ukuran Layar) -->
    <div class="flex items-center gap-2 sm:gap-3.5 text-[10px] sm:text-[11px] font-semibold text-slate-600 dark:text-slate-400 shrink-0">
        <span class="inline-flex items-center gap-1">
            <span class="size-1.5 sm:size-2 rounded-full bg-emerald-500"></span>
            <span>Kelas<span class="hidden sm:inline">&nbsp;Akademik</span></span>
        </span>
        <span class="inline-flex items-center gap-1">
            <span class="size-1.5 sm:size-2 rounded-full bg-blue-500"></span>
            <span>Rutin<span class="hidden sm:inline">&nbsp;Asrama</span></span>
        </span>
        <span class="inline-flex items-center gap-1">
            <span class="size-1.5 sm:size-2 rounded-full bg-purple-500"></span>
            <span>Khusus<span class="hidden sm:inline">&nbsp;/ Insidental</span></span>
        </span>
    </div>
</div>
