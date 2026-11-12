{{-- Tab 2: Riwayat Presensi Mahasantri dengan Filter Kajian --}}
<div x-show="activeTab === 'presensi'" x-cloak>
    <x-table-container>
        {{-- Header Bar & Filter Kajian --}}
        <x-slot:toolbar>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-2.5">
                <div class="flex items-center justify-between sm:justify-start gap-2">
                    <div class="flex items-center gap-1.5">
                        <svg class="size-3.5 sm:size-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span class="text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Log Riwayat Presensi</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] sm:text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300" x-text="filteredAbsensi.length + ' Rekaman'"></span>
                </div>

                {{-- Filter per Mata Kajian --}}
                <div class="flex items-center justify-between sm:justify-end gap-2">
                    <span class="text-[10.5px] sm:text-[11px] font-semibold text-slate-500 shrink-0">Filter Kajian:</span>
                    <select x-model="filterKajian" class="flex-1 sm:flex-initial text-[11px] sm:text-xs rounded-lg sm:rounded-xl px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="all">Semua Mata Kajian</option>
                        @foreach($daftarKajian as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot:toolbar>

        {{-- Desktop Table View --}}
        <x-slot:desktop>
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200/70 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">Tanggal Pertemuan</th>
                        <th class="px-4 py-3">Mata Kajian</th>
                        <th class="px-4 py-3">Kelompok</th>
                        <th class="px-4 py-3 text-center">Status Kehadiran</th>
                        <th class="px-4 py-3">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template x-for="(a, idx) in filteredAbsensi" :key="a.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs" x-text="idx + 1"></td>
                            <td class="px-4 py-3 font-mono font-medium text-slate-700 dark:text-slate-300" x-text="a.tanggal"></td>
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white" x-text="a.nama_kelas"></td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400" x-text="a.kelompok_nama"></td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10.5px] font-bold"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': a.status_val === 'hadir',
                                          'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': a.status_val === 'izin',
                                          'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': a.status_val === 'sakit' || a.status_val === 'sakit_tambahan',
                                          'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': a.status_val === 'alfa' || a.status_val === 'terlambat'
                                      }"
                                      x-text="a.status_label"></span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-[11px]" x-text="a.catatan"></td>
                        </tr>
                    </template>
                    <tr x-show="filteredAbsensi.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                            Belum ada rekaman presensi pada filter kajian ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </x-slot:desktop>

        {{-- Mobile Card List View --}}
        <x-slot:mobile>
            <template x-for="(a, idx) in filteredAbsensi" :key="a.id">
                <div class="p-3 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10.5px] font-semibold text-slate-500" x-text="a.tanggal"></span>
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold"
                              :class="{
                                  'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': a.status_val === 'hadir',
                                  'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': a.status_val === 'izin',
                                  'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': a.status_val === 'sakit' || a.status_val === 'sakit_tambahan',
                                  'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': a.status_val === 'alfa' || a.status_val === 'terlambat'
                              }"
                              x-text="a.status_label"></span>
                    </div>
                    <div class="font-bold text-xs text-slate-900 dark:text-white" x-text="a.nama_kelas"></div>
                    <div class="text-[10.5px] text-slate-400" x-text="a.catatan !== '-' ? a.catatan : 'Tidak ada catatan khusus.'"></div>
                </div>
            </template>
            <div x-show="filteredAbsensi.length === 0" class="p-6 text-center text-xs text-slate-400">
                Belum ada rekaman presensi pada filter kajian ini.
            </div>
        </x-slot:mobile>
    </x-table-container>
</div>