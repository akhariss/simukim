{{-- Daftar Mahasantri Pengikut Kajian (Desktop Table + Mobile Cards) --}}
<div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Santri Pengikut Kajian</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Seluruh santri di rombel ini otomatis terdaftar pada kajian ini.</p>
        </div>
        <span class="text-xs font-bold font-mono px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
            {{ $kelompok->anggota->count() }} Santri
        </span>
    </div>

    {{-- 1. Desktop Table View (>= 768px) --}}
    <div class="hidden md:block overflow-hidden rounded-xl border border-slate-200/70 dark:border-slate-800">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/70 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                <tr>
                    <th class="py-2.5 px-3.5">No</th>
                    <th class="py-2.5 px-3.5">Mahasantri</th>
                    <th class="py-2.5 px-3.5">Program Studi</th>
                    <th class="py-2.5 px-3.5">Kamar Asrama</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($kelompok->anggota as $idx => $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <td class="py-2.5 px-3.5 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3.5">
                            <div class="flex items-center gap-2">
                                <span class="size-5 rounded text-[9px] font-black flex items-center justify-center shrink-0 {{ $item->santri->gender === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                    {{ $item->santri->gender ?? 'L' }}
                                </span>
                                <div>
                                    <strong class="text-slate-800 dark:text-slate-100 block">{{ $item->santri->user->name ?? '-' }}</strong>
                                    <span class="font-mono text-[10px] text-slate-400">{{ $item->santri->nomor_induk }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 px-3.5 text-slate-600 dark:text-slate-300">{{ $item->santri->prodi ?? '-' }}</td>
                        <td class="py-2.5 px-3.5 text-slate-600 dark:text-slate-300">
                            @if($item->santri->penempatanAktif?->kamar)
                                <span class="inline-flex items-center gap-1 font-medium text-emerald-700 dark:text-emerald-300">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ $item->santri->penempatanAktif->kamar->nama_kamar }}</span>
                                </span>
                            @else
                                <span class="text-[11px] font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200/60 dark:border-amber-900/40">
                                    Belum Ada Kamar
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-xs text-slate-400">
                            Belum ada santri yang terdaftar di kelompok ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 2. Mobile Stacked Cards (< 768px - Anti Horizontal Scroll) --}}
    <div class="block md:hidden space-y-2">
        @forelse($kelompok->anggota as $item)
            <div class="p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="size-5 rounded text-[9px] font-black flex items-center justify-center shrink-0 {{ $item->santri->gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $item->santri->gender ?? 'L' }}
                        </span>
                        <div class="min-w-0">
                            <strong class="text-xs text-slate-800 dark:text-slate-100 block truncate">{{ $item->santri->user->name ?? '-' }}</strong>
                            <span class="font-mono text-[10px] text-slate-400 block">{{ $item->santri->nomor_induk }}</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Prodi:</span>
                        <span class="text-slate-700 dark:text-slate-300 font-medium truncate block">{{ $item->santri->prodi ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block">Kamar:</span>
                        <span class="font-medium truncate block {{ $item->santri->penempatanAktif?->kamar ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $item->santri->penempatanAktif?->kamar?->nama_kamar ?? 'Belum Ada' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-4 text-center text-xs text-slate-400">
                Belum ada santri yang terdaftar di kelompok ini.
            </div>
        @endforelse
    </div>
</div>

