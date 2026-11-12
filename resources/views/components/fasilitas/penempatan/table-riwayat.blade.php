{{-- Tabel Riwayat Mutasi & Keluar Asrama --}}
<div class="w-full">

    {{-- ── DESKTOP TABLE (md+) ─────────────────────────────────── --}}
    <div class="hidden md:block">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-700/80">
                    <th class="px-5 py-3 w-12 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">No</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Mahasantri</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Kamar</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Periode Tinggal</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penempatanList as $p)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors">

                        {{-- No --}}
                        <td class="px-5 py-3.5 text-center text-slate-400 dark:text-slate-500 font-mono text-xs font-semibold">
                            {{ $penempatanList->firstItem() + $loop->index }}
                        </td>

                        {{-- Mahasantri --}}
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ $p->santri->user->name ?? '-' }}
                            </div>
                            <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $p->santri->nomor_induk ?? '-' }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span>{{ $p->santri->prodi ?? 'Reguler' }}</span>
                            </div>
                        </td>

                        {{-- Kamar --}}
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-sm text-slate-800 dark:text-slate-200">
                                Kamar {{ $p->kamar->nomor_kamar ?? '-' }}
                            </span>
                            <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $p->kamar->lantai->gedung->nama ?? '' }}, Lt. {{ $p->kamar->lantai->nomor_lantai ?? '-' }}
                            </div>
                        </td>

                        {{-- Periode Tinggal --}}
                        <td class="px-5 py-3.5">
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $p->tanggal_masuk ? $p->tanggal_masuk->format('d M Y') : '-' }}
                                <span class="text-slate-400 font-normal"> s/d </span>
                                {{ $p->tanggal_keluar ? $p->tanggal_keluar->format('d M Y') : 'Sekarang' }}
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-3.5">
                            <x-status-badge :status="$p->status" />
                            @if(!empty($p->catatan))
                                <div class="mt-1 text-[11px] text-slate-400 max-w-xs truncate" title="{{ $p->catatan }}">
                                    {{ $p->catatan }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">
                            Belum ada riwayat mutasi atau keluar asrama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── MOBILE LIST (< md) — HARAM SCROLL HORIZONTAL ────────── --}}
    <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($penempatanList as $p)
            <div class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-[10px] text-slate-400 font-bold">#{{ $penempatanList->firstItem() + $loop->index }}</span>
                            <span class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $p->santri->user->name ?? '-' }}</span>
                        </div>
                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="font-mono font-semibold">{{ $p->santri->nomor_induk ?? '-' }}</span>
                            · Kamar {{ $p->kamar->nomor_kamar ?? '-' }}
                        </p>
                    </div>

                    <x-status-badge :status="$p->status" />
                </div>

                <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1.5 border-t border-slate-100 dark:border-slate-800">
                    🗓️ {{ $p->tanggal_masuk ? $p->tanggal_masuk->format('d M Y') : '-' }}
                    s/d {{ $p->tanggal_keluar ? $p->tanggal_keluar->format('d M Y') : 'Sekarang' }}
                    @if(!empty($p->catatan))
                        <div class="mt-0.5 text-slate-400 italic">"{{ Str::limit($p->catatan, 80) }}"</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-slate-400">
                Belum ada riwayat mutasi atau keluar asrama.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
        <div>
            Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200">{{ $penempatanList->firstItem() ?? 0 }}–{{ $penempatanList->lastItem() ?? 0 }}</span>
            dari <span class="font-bold text-slate-800 dark:text-slate-200">{{ $penempatanList->total() }}</span> riwayat
        </div>
        <div>{{ $penempatanList->links() }}</div>
    </div>
</div>
