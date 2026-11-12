@props([
    'topViolators' => [],
])

@if(count($topViolators) > 0)
    <div class="p-3 sm:p-3.5 rounded-2xl border border-rose-200/70 dark:border-rose-950/60 bg-rose-50/30 dark:bg-slate-900/90 shadow-2xs space-y-2">
        <div class="flex items-center justify-between gap-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="size-5 rounded-lg bg-rose-600 text-white flex items-center justify-center font-bold text-[11px] shrink-0">!</span>
                <h3 class="font-bold text-rose-900 dark:text-rose-200">
                    Pemantauan Disiplin: Mahasantri Memerlukan Pendampingan
                </h3>
            </div>
            <span class="text-[10.5px] text-slate-400 hidden sm:inline">Berdasarkan akumulasi catatan pelanggaran disiplin</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 pt-0.5">
            @foreach($topViolators as $tv)
                <div class="p-2 sm:p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-xs flex items-center justify-between shadow-2xs hover:border-rose-300 transition">
                    <div class="truncate mr-2 min-w-0">
                        <div class="font-bold text-slate-900 dark:text-white truncate">{{ $tv->name }}</div>
                        <div class="text-[10.5px] text-slate-400 font-mono truncate">{{ $tv->nomor_induk }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-900/60">
                            {{ $tv->total_kasus }} Kasus
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
