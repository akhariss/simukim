<x-table-container>
    <x-slot:desktop>
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                <tr>
                    <th class="px-4 py-3 w-10 text-center">No</th>
                    <th class="px-4 py-3 min-w-[180px]">Mahasantri</th>
                    <th class="px-4 py-3 min-w-[160px]">Kelompok Halaqah</th>
                    <th class="px-4 py-3 min-w-[260px]">Nilai Per Mata Kajian</th>
                    <th class="px-4 py-3 text-center min-w-[120px]">Rata-rata Nilai</th>
                    <th class="px-4 py-3 text-center min-w-[120px]">Status Penilaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                @forelse($santriRaporList as $idx => $s)
                    <tr x-show="matchesSantri('{{ addslashes($s->santri_nama) }}', '{{ addslashes($s->nomor_induk) }}', '{{ addslashes($s->kelompok_nama) }}', '{{ addslashes($s->prodi) }}', '{{ $s->periode_id }}')"
                        class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-900 dark:text-white truncate">{{ $s->santri_nama }}</p>
                            <p class="text-[10.5px] font-mono text-slate-400">{{ $s->nomor_induk }} &bull; {{ $s->prodi }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $s->kelompok_nama }}</span>
                            <span class="block text-[10px] text-slate-400">Semester {{ $s->periode_nama }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($s->courses as $c)
                                    <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] border {{ $c['has_grade'] ? 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700' : 'bg-slate-50/40 dark:bg-slate-900/40 border-dashed border-slate-200 dark:border-slate-800' }}">
                                        <span class="text-slate-600 dark:text-slate-300 font-medium">{{ $c['kelas_nama'] }}:</span>
                                        @if($c['has_grade'])
                                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($c['nilai_angka'], 1) }}</span>
                                            <span class="px-1 py-0.2 rounded text-[9.5px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">{{ $c['nilai_huruf'] }}</span>
                                        @else
                                            <span class="text-slate-400 italic text-[10px]">Belum dinilai</span>
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-slate-400 text-[11px] italic">Tidak ada kajian</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s->rata_rata !== null)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                    <span class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ number_format($s->rata_rata, 1) }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">{{ $s->predikat }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 font-mono text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s->graded_count === $s->total_kajian && $s->total_kajian > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    {{ $s->graded_count }}/{{ $s->total_kajian }} Selesai
                                </span>
                            @elseif($s->graded_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">
                                    {{ $s->graded_count }}/{{ $s->total_kajian }} Sebagian
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                    Belum Dinilai
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada mahasantri yang terdaftar dalam kelompok halaqah.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse($santriRaporList as $s)
            <div x-show="matchesSantri('{{ addslashes($s->santri_nama) }}', '{{ addslashes($s->nomor_induk) }}', '{{ addslashes($s->kelompok_nama) }}', '{{ addslashes($s->prodi) }}', '{{ $s->periode_id }}')"
                 class="p-3.5 space-y-3 border-b border-slate-100 dark:border-slate-800 last:border-b-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $s->santri_nama }}</h4>
                        <p class="text-[11px] font-mono text-slate-400">{{ $s->nomor_induk }} &bull; {{ $s->prodi }}</p>
                        <p class="text-[10.5px] text-slate-500 mt-0.5">{{ $s->kelompok_nama }} ({{ $s->periode_nama }})</p>
                    </div>
                    @if($s->rata_rata !== null)
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                            <span>{{ number_format($s->rata_rata, 1) }}</span>
                            <span class="px-1 rounded bg-blue-200/60 dark:bg-blue-800 text-[9.5px]">{{ $s->predikat }}</span>
                        </div>
                    @else
                        <span class="text-[10px] text-slate-400">Belum Dinilai</span>
                    @endif
                </div>

                <div class="space-y-1.5 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai Mata Kajian:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @forelse($s->courses as $c)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[140px]">{{ $c['kelas_nama'] }}</span>
                                @if($c['has_grade'])
                                    <div class="flex items-center gap-1 font-mono">
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($c['nilai_angka'], 1) }}</span>
                                        <span class="px-1 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">{{ $c['nilai_huruf'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum dinilai</span>
                                @endif
                            </div>
                        @empty
                            <span class="text-slate-400 text-xs italic">Tidak ada kajian</span>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-between items-center text-[11px] pt-1 text-slate-400">
                    <span>Progres: {{ $s->graded_count }}/{{ $s->total_kajian }} Selesai</span>
                    <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $s->kelompok_nama }}</span>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-slate-400">Belum ada data mahasantri.</div>
        @endforelse
    </x-slot:mobile>
</x-table-container>
