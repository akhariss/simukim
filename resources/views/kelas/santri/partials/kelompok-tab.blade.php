{{-- Tab 1: Informasi Kelompok & Daftar Rekan Mahasantri --}}
<div x-show="activeTab === 'kelompok'" class="space-y-3 sm:space-y-4">
    @if($kelompok)
        {{-- Banner Profil Kelompok & Pengampu --}}
        <div class="p-3.5 sm:p-5 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-50/70 via-white to-slate-50 dark:from-slate-800/80 dark:via-slate-900 dark:to-slate-900 border border-emerald-200/70 dark:border-slate-800 space-y-2.5 sm:space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-2.5">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white">{{ $kelompok->nama }}</h2>
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] sm:text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">Aktif</span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kapasitas: <strong>{{ $anggotaList->count() }}</strong> / {{ $kelompok->kapasitas }} Mahasantri Terdaftar
                    </p>
                </div>
            </div>

            {{-- Ringkasan Tutor Pengampu & Jadwal Kajian --}}
            @if($jadwalList->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-2.5 pt-1">
                    @foreach($jadwalList as $jdw)
                        <div class="p-2.5 sm:p-3 rounded-xl bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 space-y-0.5 sm:space-y-1 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md font-bold text-[9.5px] sm:text-[10px] bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40">{{ $jdw->hari }}</span>
                                <span class="font-mono text-[10px] sm:text-[10.5px] text-slate-500 font-semibold">{{ $jdw->jam }}</span>
                            </div>
                            <div class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $jdw->nama_kelas }}</div>
                            <div class="text-[10.5px] sm:text-[11px] text-slate-500 truncate">{{ $jdw->nama_sesi }} &bull; {{ $jdw->ruangan }}</div>
                            <div class="text-[10.5px] sm:text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 truncate">Tutor: {{ $jdw->tutor }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tabel Daftar Anggota Kelompok (Atomic Table Container) --}}
        <x-table-container>
            <x-slot:toolbar>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-2.5">
                    <div class="flex items-center justify-between sm:justify-start gap-2">
                        <div class="flex items-center gap-1.5">
                            <svg class="size-3.5 sm:size-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span class="text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Rekan Mahasantri</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] sm:text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300" x-text="filteredAnggota.length + ' Santri'"></span>
                    </div>
                    <x-search-input model="searchMember" placeholder="Cari rekan atau NIM..." class="w-full sm:w-60" />
                </div>
            </x-slot:toolbar>

            {{-- Desktop Table View --}}
            <x-slot:desktop>
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200/70 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">NIM / Nomor Induk</th>
                            <th class="px-4 py-3">Nama Lengkap Mahasantri</th>
                            <th class="px-4 py-3">Program Studi</th>
                            <th class="px-4 py-3">Zonasi Gender</th>
                            <th class="px-4 py-3">Kamar Asrama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(m, idx) in filteredAnggota" :key="m.id">
                            <tr :class="m.is_me ? 'bg-emerald-50/60 dark:bg-emerald-950/20 font-semibold' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40'" class="transition">
                                <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs" x-text="idx + 1"></td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-300" x-text="m.nim"></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 dark:text-white" x-text="m.name"></span>
                                        <span x-show="m.is_me" class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-emerald-600 text-white">Anda</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300" x-text="m.prodi"></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="m.gender === 'L' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-pink-50 text-pink-700 dark:bg-pink-950/40 dark:text-pink-300'"
                                          x-text="m.gender === 'L' ? 'Ikhwan' : 'Akhwat'"></span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono text-xs" x-text="m.kamar"></td>
                            </tr>
                        </template>
                        <tr x-show="filteredAnggota.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">Tidak ada rekan mahasantri yang cocok dengan pencarian.</td>
                        </tr>
                    </tbody>
                </table>
            </x-slot:desktop>

            {{-- Mobile Card View --}}
            <x-slot:mobile>
                <template x-for="(m, idx) in filteredAnggota" :key="m.id">
                    <div class="p-3 space-y-1" :class="m.is_me ? 'bg-emerald-50/40 dark:bg-emerald-950/10' : ''">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="font-mono text-[10px] text-slate-400" x-text="'#' + (idx + 1)"></span>
                                <span class="font-bold text-xs text-slate-900 dark:text-white truncate" x-text="m.name"></span>
                                <span x-show="m.is_me" class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-emerald-600 text-white shrink-0">Anda</span>
                            </div>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold shrink-0"
                                  :class="m.gender === 'L' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40' : 'bg-pink-50 text-pink-700 dark:bg-pink-950/40'"
                                  x-text="m.gender === 'L' ? 'Ikhwan' : 'Akhwat'"></span>
                        </div>
                        <div class="text-[10.5px] text-slate-500 font-mono" x-text="m.nim + ' • ' + m.prodi"></div>
                        <div class="text-[10px] text-slate-400 font-mono" x-text="'Kamar: ' + m.kamar"></div>
                    </div>
                </template>
                <div x-show="filteredAnggota.length === 0" class="p-6 text-center text-xs text-slate-400">Tidak ada rekan mahasantri yang cocok.</div>
            </x-slot:mobile>
        </x-table-container>
    @else
        <div class="p-8 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-400 text-xs">
            Anda belum dialokasikan ke dalam kelompok kajian manapun pada periode ini. Silakan hubungi admin atau mudabbir asrama.
        </div>
    @endif
</div>