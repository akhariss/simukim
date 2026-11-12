<x-table-container>
    <x-slot:desktop>
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                <tr>
                    <th class="px-4 py-3">Kelompok Halaqah</th>
                    <th class="px-4 py-3">Mata Kajian</th>
                    <th class="px-4 py-3">Jadwal Sesi Mengajar</th>
                    <th class="px-4 py-3 text-center">Santri Terdaftar</th>
                    <th class="px-4 py-3 text-center">Status Rekap Nilai</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                @forelse($myKajianList as $k)
                    <tr x-show="matchesItem('{{ addslashes($k->kelompok_nama) }}', '{{ addslashes($k->kelas_nama) }}', '{{ addslashes($k->jadwal_text) }}', '{{ addslashes($k->santri_keywords ?? '') }}', '{{ $k->periode_id }}')"
                        class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                            <div>{{ $k->kelompok_nama }}</div>
                            @if(!$isBinaan)
                                <span class="text-[10px] text-slate-400 font-normal">Semester {{ $k->periode_nama }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $k->kelas_nama }}</span>
                            <span class="ml-1 text-[10px] text-slate-400">({{ $k->kelas_tipe }})</span>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500">{{ $k->jadwal_text }}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-700 dark:text-slate-300">{{ $k->total_santri }} Santri</td>
                        <td class="px-4 py-3.5 text-center">
                            @if($k->sudah_dinilai > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    {{ $k->sudah_dinilai }} / {{ $k->total_santri }} Dinilai
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    Belum Dinilai
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('kelas.nilai.index', array_filter(['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id, 'mode' => request('mode')])) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-400 transition cursor-pointer">
                                <span>Kelola Nilai</span>
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada data kelompok kajian yang diampu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse($myKajianList as $k)
            <div x-show="matchesItem('{{ addslashes($k->kelompok_nama) }}', '{{ addslashes($k->kelas_nama) }}', '{{ addslashes($k->jadwal_text) }}', '{{ addslashes($k->santri_keywords ?? '') }}', '{{ $k->periode_id }}')"
                 class="p-3.5 space-y-2.5 border-b border-slate-100 dark:border-slate-800 last:border-b-0">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $k->kelompok_nama }}</h4>
                        <p class="text-[11px] text-slate-500">{{ $k->kelas_nama }} &bull; {{ $k->jadwal_text }}</p>
                        @if(!$isBinaan)
                            <span class="text-[10px] text-slate-400">Semester {{ $k->periode_nama }}</span>
                        @endif
                    </div>
                    @if($k->sudah_dinilai > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                            {{ $k->sudah_dinilai }}/{{ $k->total_santri }}
                        </span>
                    @else
                        <span class="text-[10px] text-slate-400">Belum Dinilai</span>
                    @endif
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                    <span class="text-slate-400 text-[11px]">{{ $k->total_santri }} Santri Terdaftar</span>
                    <a href="{{ route('kelas.nilai.index', array_filter(['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id, 'mode' => request('mode')])) }}" class="font-bold text-blue-600 hover:underline">Kelola Nilai &rarr;</a>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-slate-400">Belum ada data kelompok kajian yang diampu.</div>
        @endforelse
    </x-slot:mobile>
</x-table-container>

