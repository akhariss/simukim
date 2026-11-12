<div x-show="kategori === 'semua'" class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-l-blue-600 dark:border-l-blue-500 shadow-2xs">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10.5px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800/60">
                    <svg class="size-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Laporan Eksekutif Terpadu Pimpinan</span>
                </span>
                <span class="text-[11px] text-slate-400 hidden sm:inline">• Konsolidasi Seluruh Modul</span>
            </div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">
                Buku Laporan Kinerja &amp; Tata Kelola Ma'had Al-Jami'ah
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Dokumen konsolidasi resmi seluruh pilar operasional (Hunian Asrama, Capaian Pembelajaran, Nilai, Presensi, Pelanggaran, dan Perizinan) ber-KOP Kemenag lengkap lembar pengesahan Mudir Ma'had untuk konsumsi Rektorat.
            </p>
        </div>

        <div class="shrink-0 flex flex-col sm:flex-row gap-2 pt-2 lg:pt-0">
            <!-- Unduh Semua (PDF) via Link Asli -->
            <a
                :href="'{{ route('pelaporan.export') }}?tipe=master&format=pdf&periode_id=' + (periodeId || '') + '&rentang=' + rentang + '&bulan=' + (bulan || '')"
                target="_blank"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl font-semibold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
            >
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Buku PDF Terpadu</span>
            </a>

            <!-- Unduh Semua (Excel) via Link Asli -->
            <a
                :href="'{{ route('pelaporan.export') }}?tipe=master&format=excel&periode_id=' + (periodeId || '') + '&rentang=' + rentang + '&bulan=' + (bulan || '')"
                target="_blank"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl font-semibold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition cursor-pointer"
            >
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Excel Multi-Sheet</span>
            </a>
        </div>
    </div>
</div>
