{{-- Tabel Kajian & Tutor Pengampu --}}
<x-table-container>
    <x-slot:toolbar>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Kajian &amp; Tutor Pengampu</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih kajian untuk melihat detail, jadwal, dan mengelola tutor pengampu.</p>
            </div>
            <span class="text-[10px] sm:text-[11px] font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">
                {{ $masterKelasList->count() }} Kajian
            </span>
        </div>
    </x-slot:toolbar>

    <x-slot:desktop>
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                <tr>
                    <th class="px-4 py-2.5">Tipe Kajian</th>
                    <th class="px-4 py-2.5">Nama Kajian</th>
                    <th class="px-4 py-2.5">Jadwal Sesi</th>
                    <th class="px-4 py-2.5">Tutor Pengampu</th>
                    <th class="px-4 py-2.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                @forelse($masterKelasList as $kelasItem)
                    @php $pengampuItem = $kelompok->pengampu->firstWhere('kelas_id', $kelasItem->id); @endphp
                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/30 transition">
                        <td class="px-4 py-3">
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $kelasItem->tipe?->badgeClass() }}">
                                {{ $kelasItem->tipe?->shortLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $kelasItem->nama }}</td>
                        <td class="px-4 py-3">
                            <div class="space-y-0.5">
                                @foreach($kelasItem->jadwal as $j)
                                    <p class="text-[11px] text-slate-500">{{ $j->hari }} &bull; {{ substr($j->jam_mulai,0,5) }}&ndash;{{ substr($j->jam_selesai,0,5) }}
                                        @if($j->ruangan) &bull; {{ $j->ruangan }} @endif
                                    </p>
                                @endforeach
                                @if($kelasItem->jadwal->isEmpty())
                                    <span class="text-[11px] text-slate-400 italic">Belum ada jadwal</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($pengampuItem?->tutor)
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $pengampuItem->tutor->name }}</p>
                                    <p class="text-[10px] font-mono text-slate-400">{{ $pengampuItem->tutor->nomor_induk ?? '' }}</p>
                                </div>
                            @else
                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold italic">Belum Ditugaskan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('kelompok.kajian.show', [$kelompok, $kelasItem]) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 rounded-lg transition">
                                <span>Detail &amp; Tutor</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-xs text-slate-400">Belum ada mata kajian aktif. Tambahkan di menu Master Kajian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        <div class="p-3 space-y-2">
            @forelse($masterKelasList as $kelasItem)
                @php $pengampuItem = $kelompok->pengampu->firstWhere('kelas_id', $kelasItem->id); @endphp
                <div class="p-3 rounded-xl border border-slate-200/70 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $kelasItem->tipe?->badgeClass() }}">{{ $kelasItem->tipe?->shortLabel() }}</span>
                        <a href="{{ route('kelompok.kajian.show', [$kelompok, $kelasItem]) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            <span>Detail &amp; Tutor &rarr;</span>
                        </a>
                    </div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $kelasItem->nama }}</p>
                    <p class="text-[11px] text-slate-500">Tutor: <span class="font-semibold {{ $pengampuItem?->tutor ? 'text-slate-800 dark:text-slate-200' : 'text-amber-600' }}">{{ $pengampuItem?->tutor?->name ?? 'Belum Ditugaskan' }}</span></p>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Belum ada mata kajian aktif.</div>
            @endforelse
        </div>
    </x-slot:mobile>
</x-table-container>
