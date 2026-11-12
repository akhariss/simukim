{{-- Daftar Mata Kajian Diampu untuk Presensi --}}
<x-table-container>
    <x-slot:toolbar>
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Daftar Kelas &amp; Halaqah Binaan</span>
            <span class="text-[11px] text-slate-400 font-medium">{{ count($myKajianList ?? []) }} Kelas Terdaftar</span>
        </div>
    </x-slot:toolbar>

    <x-slot:desktop>
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                <tr>
                    <th class="px-4 py-3 w-12 text-center">No</th>
                    <th class="px-4 py-3">Mata Kajian &amp; Tipe</th>
                    <th class="px-4 py-3">Kelompok Halaqah</th>
                    <th class="px-4 py-3 text-center w-32">Mahasantri</th>
                    <th class="px-4 py-3 text-center w-36">Sesi Terlaksana</th>
                    <th class="px-4 py-3 text-right w-44">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($myKajianList ?? [] as $index => $k)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <td class="px-4 py-3 text-center font-mono text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $k->kelas_nama }}</span>
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium {{ $k->kelas_tipe === 'masal' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }} mt-0.5">
                                {{ $k->kelas_tipe === 'masal' ? 'Kajian Masal' : 'Kajian Halaqah' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                {{ $k->kelompok_nama }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $k->total_santri }}</span>
                            <span class="text-slate-400 text-[11px]"> Santri</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ $k->total_sesi }}</span>
                            <span class="text-slate-400 text-[11px]"> Pertemuan</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a
                                    href="{{ route('kelas.absensi.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition"
                                >
                                    <span>Presensi</span>
                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a
                                    href="{{ route('kelas.absensi.rekap', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition"
                                    title="Rekap Presensi"
                                >
                                    Rekap
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                            Belum ada penetapan kelas kajian yang diampu untuk periode semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($myKajianList ?? [] as $index => $k)
                <div class="p-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                {{ $k->kelompok_nama }}
                            </span>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white mt-1">{{ $k->kelas_nama }}</h4>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $k->kelas_tipe === 'masal' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                            {{ $k->kelas_tipe === 'masal' ? 'Masal' : 'Halaqah' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <span>{{ $k->total_santri }} Santri &bull; {{ $k->total_sesi }} Sesi Terlaksana</span>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}" class="px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-blue-600">Presensi &rarr;</a>
                            <a href="{{ route('kelas.absensi.rekap', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}" class="px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">Rekap</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">
                    Belum ada penetapan kelas kajian yang diampu untuk periode semester ini.
                </div>
            @endforelse
        </div>
    </x-slot:mobile>
</x-table-container>
