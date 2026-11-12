<x-app-layout title="Kajian Diampu">
    <div class="space-y-4">
        {{-- Header Halaman --}}
        <x-page-header
            :breadcrumbs="[['label' => 'Akademik Kajian'], ['label' => 'Kajian Diampu']]"
            title="Mata Kajian Diampu"
            description="Daftar mata kajian dan kelompok halaqah yang diampu pada semester aktif."
        >
            <x-slot:actions>
                <a href="{{ route('kelas.laporan.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan Bulanan</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Jadwal Mengajar Tutor --}}
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-2.5">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                    <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Jadwal Mengajar Anda</span>
                </h3>
                <span class="text-[11px] font-semibold text-slate-500">{{ count($jadwalMengajar ?? []) }} Sesi Terdaftar</span>
            </div>

            {{-- Jadwal Desktop: Compact Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-3 py-2 w-36">Hari &amp; Waktu</th>
                            <th class="px-3 py-2">Mata Kajian</th>
                            <th class="px-3 py-2">Kelompok Halaqah</th>
                            <th class="px-3 py-2">Sesi Pertemuan</th>
                            <th class="px-3 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($jadwalMengajar ?? [] as $jm)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-3 py-2">
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 mr-1.5">{{ $jm->hari }}</span>
                                    <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $jm->jam_mulai }}-{{ $jm->jam_selesai }}</span>
                                </td>
                                <td class="px-3 py-2 font-bold text-slate-900 dark:text-white">{{ $jm->kelas_nama }}</td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $jm->kelompok_nama }}</td>
                                <td class="px-3 py-2 text-slate-400 font-mono text-[11px]">{{ $jm->nama_sesi }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('kelas.absensi.index', array_filter(['kelompok_id' => $jm->kelompok_id, 'kelas_id' => $jm->kelas_id, 'jadwal_id' => $jm->jadwal_id ?? null])) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline text-xs">Presensi &rarr;</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-4 text-center text-xs text-slate-400">Belum ada jadwal mengajar yang ditetapkan untuk semester ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Jadwal Mobile: Compact Clean Rows --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($jadwalMengajar ?? [] as $jm)
                    <div class="py-2.5 flex items-center justify-between gap-2 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">{{ $jm->hari }}</span>
                                <span class="font-mono text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ $jm->jam_mulai }}-{{ $jm->jam_selesai }}</span>
                            </div>
                            <p class="font-bold text-slate-900 dark:text-white truncate mt-0.5">{{ $jm->kelas_nama }}</p>
                            <p class="text-[10.5px] text-slate-500 truncate">{{ $jm->kelompok_nama }} &bull; {{ $jm->nama_sesi }}</p>
                        </div>
                        <a href="{{ route('kelas.absensi.index', array_filter(['kelompok_id' => $jm->kelompok_id, 'kelas_id' => $jm->kelas_id, 'jadwal_id' => $jm->jadwal_id ?? null])) }}" class="px-2.5 py-1 rounded-lg text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 shrink-0">Presensi &rarr;</a>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400">Belum ada jadwal mengajar.</div>
                @endforelse
            </div>
        </div>

        {{-- Daftar Mata Kajian Diampu --}}
        <x-table-container>
            <x-slot:toolbar>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Mata Kajian Diampu</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kelompok halaqah dan mata kajian aktif yang berada di bawah bimbingan Anda.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ count($kajianCards) }} Kajian</span>
                </div>
            </x-slot:toolbar>

            {{-- Desktop Table View --}}
            <x-slot:desktop>
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Mata Kajian &amp; Tipe</th>
                            <th class="px-4 py-3">Kelompok Halaqah</th>
                            <th class="px-4 py-3">Mahasantri</th>
                            <th class="px-4 py-3 text-center">Sesi Terlaksana</th>
                            <th class="px-4 py-3">Status Penilaian</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($kajianCards as $idx => $k)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-4 py-3 font-mono text-slate-400 text-center">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('tutor.kajian.show', ['kelompok' => $k->kelompok_id, 'kelas' => $k->kelas_id]) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        {{ $k->kelas_nama }}
                                    </a>
                                    <span class="block mt-0.5 text-[10px] font-semibold text-slate-500">
                                        {{ $k->kelas_tipe === 'masal' ? 'Kajian Masal' : 'Kajian Halaqah' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">{{ $k->kelompok_nama }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('tutor.kajian.show', ['kelompok' => $k->kelompok_id, 'kelas' => $k->kelas_id]) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $k->total_santri }} Santri &rarr;
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-800 dark:text-slate-200">{{ $k->total_sesi }} Sesi</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 font-semibold text-[11px] {{ $k->sudah_dinilai > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        <span>{{ $k->sudah_dinilai > 0 ? '● Terekam (' . $k->sudah_dinilai . ' Santri)' : '○ Belum Diisi' }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}" class="px-2.5 py-1 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs transition">Presensi</a>
                                        <a href="{{ route('kelas.nilai.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-lg transition">Nilai</a>
                                        <a href="{{ route('tutor.kajian.show', ['kelompok' => $k->kelompok_id, 'kelas' => $k->kelas_id]) }}" class="px-2 py-1 text-xs font-semibold text-blue-600 hover:underline">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada penetapan mata kajian untuk semester aktif saat ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-slot:desktop>

            {{-- Mobile Compact Card View (1 Row/Clean Card Per Class) --}}
            <x-slot:mobile>
                <div class="p-2.5 space-y-2">
                    @forelse($kajianCards as $idx => $k)
                        <div class="p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 truncate max-w-[170px]">{{ $k->kelompok_nama }}</span>
                                <span class="text-[10px] font-semibold text-slate-500 shrink-0">{{ $k->kelas_tipe === 'masal' ? 'Masal' : 'Halaqah' }}</span>
                            </div>
                            <div>
                                <a href="{{ route('tutor.kajian.show', ['kelompok' => $k->kelompok_id, 'kelas' => $k->kelas_id]) }}" class="font-bold text-xs text-slate-900 dark:text-white hover:text-blue-600 transition block">
                                    {{ $k->kelas_nama }}
                                </a>
                                <p class="text-[10.5px] text-slate-500 mt-0.5">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $k->total_santri }} Santri</span>
                                    &bull; <span class="font-mono">{{ $k->total_sesi }} Sesi</span>
                                    &bull; <span class="{{ $k->sudah_dinilai > 0 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $k->sudah_dinilai > 0 ? 'Nilai Terekam' : 'Belum Diisi' }}</span>
                                </p>
                            </div>
                            <div class="flex items-center justify-between gap-1.5 pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60 text-xs">
                                <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}" class="px-2.5 py-1 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs">Presensi Sesi</a>
                                <a href="{{ route('kelas.nilai.index', ['kelompok_id' => $k->kelompok_id, 'kelas_id' => $k->kelas_id]) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-200/80 dark:bg-slate-700 rounded-lg">Lembar Nilai</a>
                                <a href="{{ route('tutor.kajian.show', ['kelompok' => $k->kelompok_id, 'kelas' => $k->kelas_id]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Detail &rarr;</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">Belum ada mata kajian diampu.</div>
                    @endforelse
                </div>
            </x-slot:mobile>
        </x-table-container>
    </div>
</x-app-layout>
