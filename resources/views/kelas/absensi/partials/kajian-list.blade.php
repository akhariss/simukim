{{-- Daftar Mata Kajian Diampu untuk Presensi --}}
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($myKajianList ?? [] as $k)
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between hover:border-blue-500/50 transition">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10.5px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                {{ $k->kelompok_nama }}
                            </span>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white mt-1">{{ $k->kelas_nama }}</h3>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $k->kelas_tipe === 'masal' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                            {{ $k->kelas_tipe === 'masal' ? 'Kajian Masal' : 'Kajian Halaqah' }}
                        </span>
                    </div>

                    <div class="py-2.5 border-y border-slate-100 dark:border-slate-800/80 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                        <div class="flex justify-between items-center">
                            <span>Mahasantri Terdaftar:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $k->total_santri }} Santri</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Sesi Terlaksana:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ $k->total_sesi }} Sesi</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-3">
                    <a
                        href="{{ route('kelas.absensi.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}"
                        class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Presensi &rarr;</span>
                    </a>
                    <a
                        href="{{ route('kelas.absensi.rekap', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}"
                        class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Rekap</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 text-xs text-slate-400">
                Belum ada penetapan kelas kajian yang diampu untuk periode semester ini.
            </div>
        @endforelse
    </div>
</div>

