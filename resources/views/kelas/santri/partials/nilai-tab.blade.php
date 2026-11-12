{{-- Tab 3: Hasil Penilaian Seluruh Mata Kajian --}}
<div x-show="activeTab === 'nilai'" x-cloak x-data="{ expandedId: null }">
    <x-table-container>
        {{-- Header Bar --}}
        <x-slot:toolbar>
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <svg class="size-3.5 sm:size-4 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span class="text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider truncate">Hasil Capaian Nilai Akhir</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 truncate">Klik baris kajian untuk melihat rincian bobot dan nilai per komponen.</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[9.5px] sm:text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 shrink-0">
                    {{ $rekapNilaiList->count() }} Kajian
                </span>
            </div>
        </x-slot:toolbar>

        {{-- Desktop Table View --}}
        <x-slot:desktop>
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200/70 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">Mata Kajian</th>
                        <th class="px-4 py-3">Tutor Pengampu</th>
                        <th class="px-4 py-3 text-center">Nilai Angka</th>
                        <th class="px-4 py-3 text-center">Predikat</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($rekapNilaiList as $idx => $nil)
                        <tr
                            @click="expandedId = (expandedId === {{ $nil->kelas_id }} ? null : {{ $nil->kelas_id }})"
                            class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 cursor-pointer transition select-none"
                            :class="expandedId === {{ $nil->kelas_id }} ? 'bg-purple-50/30 dark:bg-purple-950/20' : ''"
                        >
                            <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $nil->nama_kelas }}</div>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">{{ $nil->tipe_kelas }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">{{ $nil->tutor_name }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($nil->is_graded)
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ number_format($nil->nilai_angka, 2) }}</span>
                                @else
                                    <span class="text-slate-400 font-mono italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($nil->is_graded)
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">{{ $nil->nilai_huruf ?: '-' }}</span>
                                @else
                                    <span class="text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($nil->is_graded)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Sudah Dinilai</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">Belum Dinilai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-purple-600 dark:text-purple-400 font-semibold inline-flex items-center gap-1">
                                    <span x-text="expandedId === {{ $nil->kelas_id }} ? 'Tutup' : 'Rincian'"></span>
                                    <svg class="size-3.5 transition-transform" :class="expandedId === {{ $nil->kelas_id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </td>
                        </tr>
                        {{-- Dropdown Rincian Komponen Penilaian (Desktop) --}}
                        <tr x-show="expandedId === {{ $nil->kelas_id }}" x-cloak class="bg-slate-50/60 dark:bg-slate-800/40">
                            <td colspan="7" class="px-6 py-4">
                                <div class="rounded-xl border border-purple-200/60 dark:border-purple-900/40 bg-white dark:bg-slate-900 p-4 space-y-3 shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-2">
                                            <span class="size-2 rounded-full bg-purple-600"></span>
                                            <span>Rincian Bobot &amp; Komponen Penilaian: {{ $nil->nama_kelas }}</span>
                                        </h4>
                                        @if($nil->catatan)
                                            <span class="text-[11px] text-slate-500 italic">Catatan: {{ $nil->catatan }}</span>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                        @forelse($nil->breakdown ?? [] as $b)
                                            <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 space-y-1">
                                                <div class="flex justify-between items-center text-[10.5px]">
                                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $b->nama }}</span>
                                                    <span class="font-mono text-purple-600 dark:text-purple-400 font-bold">{{ $b->bobot }}%</span>
                                                </div>
                                                <p class="font-mono font-bold text-sm {{ $b->nilai !== null ? 'text-slate-900 dark:text-white' : 'text-slate-400 italic' }}">
                                                    {{ $b->nilai !== null ? number_format($b->nilai, 1) : 'Belum Ada' }}
                                                </p>
                                                @if($b->nilai !== null)
                                                    <p class="text-[10px] text-slate-400 font-mono">Kontribusi: {{ number_format(($b->nilai * ($b->bobot / 100)), 1) }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="col-span-full text-xs text-slate-400 italic">Belum ada komponen penilaian.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada mata kajian yang terdaftar untuk kelompok Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot:desktop>

        {{-- Mobile Card List View --}}
        <x-slot:mobile>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($rekapNilaiList as $nil)
                    <div class="p-3.5 space-y-2.5">
                        <div
                            @click="expandedId = (expandedId === {{ $nil->kelas_id }} ? null : {{ $nil->kelas_id }})"
                            class="flex items-start justify-between gap-2 cursor-pointer"
                        >
                            <div class="min-w-0">
                                <div class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $nil->nama_kelas }}</div>
                                <div class="text-[10px] text-slate-500 font-medium">Tutor: {{ $nil->tutor_name }}</div>
                            </div>
                            <div class="text-right shrink-0 flex items-center gap-1.5">
                                @if($nil->is_graded)
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[11px] bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">{{ $nil->nilai_huruf ?: '-' }} ({{ number_format($nil->nilai_angka, 1) }})</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">Belum Dinilai</span>
                                @endif
                                <svg class="size-3.5 text-slate-400 transition-transform" :class="expandedId === {{ $nil->kelas_id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        {{-- Dropdown Rincian Komponen (Mobile) --}}
                        <div x-show="expandedId === {{ $nil->kelas_id }}" x-cloak class="p-3 rounded-xl bg-purple-50/50 dark:bg-slate-800/80 border border-purple-100 dark:border-slate-700 space-y-2">
                            <p class="text-[10.5px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300">Rincian Komponen Nilai:</p>
                            <div class="grid grid-cols-2 gap-2">
                                @forelse($nil->breakdown ?? [] as $b)
                                    <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-[11px]">
                                        <div class="flex justify-between text-slate-500 text-[10px]">
                                            <span>{{ $b->nama }}</span>
                                            <span class="font-bold text-purple-600">{{ $b->bobot }}%</span>
                                        </div>
                                        <p class="font-bold font-mono text-xs mt-0.5 {{ $b->nilai !== null ? 'text-slate-900 dark:text-white' : 'text-slate-400 italic' }}">
                                            {{ $b->nilai !== null ? number_format($b->nilai, 1) : '-' }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="col-span-2 text-xs text-slate-400">Tidak ada komponen nilai.</p>
                                @endforelse
                            </div>
                            @if($nil->catatan)
                                <p class="text-[10.5px] text-slate-500 italic pt-1 border-t border-purple-100 dark:border-slate-700">Catatan: {{ $nil->catatan }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada mata kajian terdaftar.</div>
                @endforelse
            </div>
        </x-slot:mobile>
    </x-table-container>
</div>
