@php
    $anggotaData = $kelompok->anggota->map(fn($item, $idx) => [
        'id' => $item->santri_id,
        'index' => $idx + 1,
        'nim' => $item->santri->nomor_induk ?? '-',
        'name' => $item->santri->user->name ?? '-',
        'prodi' => $item->santri->prodi ?? 'Umum',
        'kamar' => $item->santri->penempatanAktif?->kamar
            ? (($item->santri->penempatanAktif->kamar->lantai->gedung->nama ?? '') . ' - ' . $item->santri->penempatanAktif->kamar->nomor_kamar)
            : 'Belum ada kamar',
        'destroy_url' => route('kelompok.anggota.destroy', [$kelompok, $item->santri_id]),
    ])->values();
@endphp

<div
    x-data="{
        search: '',
        currentPage: 1,
        perPage: 40,
        allAnggota: {{ \Illuminate\Support\Js::from($anggotaData) }},
        get filteredList() {
            if (!this.search.trim()) return this.allAnggota;
            const q = this.search.toLowerCase().trim();
            return this.allAnggota.filter(i => 
                i.nim.toLowerCase().includes(q) || 
                i.name.toLowerCase().includes(q) || 
                i.prodi.toLowerCase().includes(q) || 
                i.kamar.toLowerCase().includes(q)
            );
        },
        get paginatedList() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredList.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredList.length / this.perPage));
        },
        setPage(p) {
            if (p >= 1 && p <= this.totalPages) this.currentPage = p;
        }
    }"
    x-effect="search; currentPage = 1"
>
    <x-table-container>
        <x-slot:toolbar>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Anggota Santri</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                            <span x-text="filteredList.length"></span> santri
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
                        <span x-show="!search.trim()">{{ $kelompok->anggota->count() }} santri aktif di kelompok ini (Kapasitas: {{ $kelompok->kapasitas }}).</span>
                        <span x-show="search.trim()">Ditemukan <strong x-text="filteredList.length"></strong> santri dari pencarian "<span x-text="search"></span>".</span>
                    </p>
                </div>

                <div class="w-full sm:w-64">
                    <x-search-input
                        x-model="search"
                        placeholder="Cari nama, NIM, jurusan..."
                    />
                </div>
            </div>
        </x-slot:toolbar>

        <x-slot:desktop>
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5 w-12">No</th>
                        <th class="px-4 py-2.5">NIM / Nomor Induk</th>
                        <th class="px-4 py-2.5">Nama Lengkap</th>
                        <th class="px-4 py-2.5">Jurusan / Program Studi</th>
                        <th class="px-4 py-2.5">Kamar Asrama</th>
                        @hasrole('admin') <th class="px-4 py-2.5 text-right">Aksi</th> @endhasrole
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                    <template x-for="(item, idx) in paginatedList" :key="item.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-2.5 text-slate-400 font-mono text-xs" x-text="(currentPage - 1) * perPage + idx + 1"></td>
                            <td class="px-4 py-2.5 font-mono font-semibold text-slate-700 dark:text-slate-300" x-text="item.nim"></td>
                            <td class="px-4 py-2.5 font-semibold text-slate-900 dark:text-white" x-text="item.name"></td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[11px] font-medium text-slate-700 dark:text-slate-300" x-text="item.prodi"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs" :class="item.kamar === 'Belum ada kamar' ? 'text-slate-400 italic text-[11px]' : 'text-slate-700 dark:text-slate-200 font-medium'" x-text="item.kamar"></span>
                            </td>
                            @hasrole('admin')
                                <td class="px-4 py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                            @click="$dispatch('pindah-santri', { id: item.id, name: item.name, nim: item.nim })"
                                            class="px-2 py-1 text-[11px] font-semibold rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 transition cursor-pointer">Pindah</button>
                                        <button type="button"
                                            :data-confirm="'Keluarkan ' + item.name + ' dari kelompok ini?'"
                                            :data-action="item.destroy_url"
                                            data-method="DELETE"
                                            class="px-2 py-1 text-[11px] font-semibold rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 transition cursor-pointer confirm-trigger">Keluarkan</button>
                                    </div>
                                </td>
                            @endhasrole
                        </tr>
                    </template>
                    <tr x-show="filteredList.length === 0 && allAnggota.length > 0">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            Tidak ada santri yang cocok dengan pencarian "<span class="font-semibold text-slate-600 dark:text-slate-300" x-text="search"></span>".
                        </td>
                    </tr>
                    <tr x-show="allAnggota.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">Belum ada mahasantri yang didaftarkan ke kelompok ini.</td>
                    </tr>
                </tbody>
            </table>
        </x-slot:desktop>

        <x-slot:mobile>
            <div class="p-3 space-y-2">
                <template x-for="(item, idx) in paginatedList" :key="item.id">
                    <div class="p-3 rounded-xl bg-slate-50/50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400" x-text="'#' + ((currentPage - 1) * perPage + idx + 1)"></span>
                            <span class="text-[11px] font-mono font-semibold text-slate-600 dark:text-slate-300" x-text="item.nim"></span>
                        </div>
                        <p class="font-bold text-xs text-slate-900 dark:text-white" x-text="item.name"></p>
                        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium" x-text="item.prodi"></span>
                            <span class="text-slate-400">&bull;</span>
                            <span :class="item.kamar === 'Belum ada kamar' ? 'text-slate-400 italic' : 'text-slate-600 dark:text-slate-300'" x-text="item.kamar"></span>
                        </div>
                        @hasrole('admin')
                            <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center justify-end gap-2">
                                <button type="button"
                                    @click="$dispatch('pindah-santri', { id: item.id, name: item.name, nim: item.nim })"
                                    class="px-2.5 py-1 text-[11px] font-semibold rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 transition cursor-pointer">Pindah</button>
                                <button type="button"
                                    :data-confirm="'Keluarkan ' + item.name + ' dari kelompok ini?'"
                                    :data-action="item.destroy_url"
                                    data-method="DELETE"
                                    class="px-2.5 py-1 text-[11px] font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 rounded-lg transition cursor-pointer confirm-trigger">Keluarkan</button>
                            </div>
                        @endhasrole
                    </div>
                </template>
                <div x-show="filteredList.length === 0 && allAnggota.length > 0" class="p-6 text-center text-xs text-slate-400">
                    Tidak ada santri yang cocok dengan pencarian.
                </div>
                <div x-show="allAnggota.length === 0" class="p-6 text-center text-xs text-slate-400">
                    Belum ada mahasantri yang didaftarkan ke kelompok ini.
                </div>
            </div>
        </x-slot:mobile>

        <x-slot:pagination>
            <div x-show="totalPages > 1" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500">
                <div>
                    Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="(currentPage - 1) * perPage + 1"></span> &ndash; <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="Math.min(currentPage * perPage, filteredList.length)"></span> dari <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="filteredList.length"></span> santri
                </div>
                <div class="flex items-center gap-1.5 self-center sm:self-auto">
                    <button type="button" @click="setPage(currentPage - 1)" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer'" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-200 transition">
                        &larr; Sebelumnya
                    </button>
                    <span class="px-2 font-medium">Hal <span x-text="currentPage"></span>/<span x-text="totalPages"></span></span>
                    <button type="button" @click="setPage(currentPage + 1)" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer'" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-200 transition">
                        Selanjutnya &rarr;
                    </button>
                </div>
            </div>
        </x-slot:pagination>
    </x-table-container>
</div>
