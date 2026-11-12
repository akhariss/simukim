<!-- Tabel Detail Mahasantri & Rekonsiliasi Data -->
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs overflow-hidden"
     x-data="{
         search: '',
         filter: 'all',
         page: 1,
         perPage: 10,
         items: @js($reconciliationList ?? []),
         get filteredItems() {
             let s = this.search.toLowerCase().trim();
             return this.items.filter(i => {
                 let matchFilter = true;
                 if (this.filter === 'missing') matchFilter = (i.sync_status === 'missing');
                 else if (this.filter === 'synced') matchFilter = (i.sync_status === 'synced');
                 else if (this.filter === 'local_manual') matchFilter = (i.sync_status === 'local_manual');
                 else if (this.filter === 'unassigned') matchFilter = (i.status_kamar === 'belum');
                 else if (this.filter === 'unpaid') matchFilter = (i.status_pembayaran !== 'Lunas');

                 let matchSearch = !s || (i.nim && i.nim.toLowerCase().includes(s)) ||
                                   (i.nama && i.nama.toLowerCase().includes(s)) ||
                                   (i.prodi && i.prodi.toLowerCase().includes(s));
                 return matchFilter && matchSearch;
             });
         },
         get paginatedItems() {
             let start = (this.page - 1) * this.perPage;
             return this.filteredItems.slice(start, start + this.perPage);
         },
         get totalPages() {
             return Math.ceil(this.filteredItems.length / this.perPage) || 1;
         },
         setFilter(f) { this.filter = f; this.page = 1; }
     }">

    <!-- Filter & Live Search Toolbar -->
    <div class="p-3.5 sm:p-5 border-b border-slate-100 dark:border-slate-800/80 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">Daftar Rekonsiliasi Mahasantri</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Inspeksi data santri, temukan anomali kamar atau tagihan, dan lacak status sinkronisasi.</p>
            </div>
            <!-- Live Search Bar -->
            <x-search-input
                model="search"
                @input="page = 1"
                placeholder="Cari NIM, Nama, atau Prodi..."
                class="w-full sm:w-72 shrink-0"
            />
        </div>

        <!-- Filter Status Pills: Smooth Horizontal Scroll (Zero Clipping di Mobile) -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 text-xs -mx-3.5 px-3.5 sm:mx-0 sm:px-0">
            <button type="button" @click="setFilter('all')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap cursor-pointer shrink-0" :class="filter === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'">
                <span>Semua Data</span>
                <span class="font-mono text-[11px] opacity-75">(<span x-text="items.length"></span>)</span>
            </button>
            <button type="button" @click="setFilter('missing')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap cursor-pointer shrink-0" :class="filter === 'missing' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-900/50 hover:bg-amber-100'">
                <span class="size-1.5 rounded-full bg-amber-500"></span>
                <span>Belum di App</span>
                <span class="font-mono text-[11px] opacity-75">(<span x-text="items.filter(i => i.sync_status === 'missing').length"></span>)</span>
            </button>
            <button type="button" @click="setFilter('synced')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap cursor-pointer shrink-0" :class="filter === 'synced' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/50 hover:bg-emerald-100'">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                <span>Sudah Sinkron</span>
                <span class="font-mono text-[11px] opacity-75">(<span x-text="items.filter(i => i.sync_status === 'synced').length"></span>)</span>
            </button>
            <button type="button" @click="setFilter('local_manual')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap cursor-pointer shrink-0" :class="filter === 'local_manual' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900/50 hover:bg-blue-100'">
                <span class="size-1.5 rounded-full bg-blue-500"></span>
                <span>Lokal Manual</span>
                <span class="font-mono text-[11px] opacity-75">(<span x-text="items.filter(i => i.sync_status === 'local_manual').length"></span>)</span>
            </button>
            <button type="button" @click="setFilter('unassigned')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap cursor-pointer shrink-0" :class="filter === 'unassigned' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-900/50 hover:bg-rose-100'">
                <span class="size-1.5 rounded-full bg-rose-500"></span>
                <span>Belum Kamar</span>
                <span class="font-mono text-[11px] opacity-75">(<span x-text="items.filter(i => i.status_kamar === 'belum').length"></span>)</span>
            </button>
            <button type="button" @click="setFilter('unpaid')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap cursor-pointer shrink-0" :class="filter === 'unpaid' ? 'bg-purple-600 text-white shadow-2xs' : 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-900/50 hover:bg-purple-100'">
                <span class="size-1.5 rounded-full bg-purple-500"></span>
                <span>Belum Bayar</span>
                <span class="font-mono text-[11px] opacity-75">(<span x-text="items.filter(i => i.status_pembayaran !== 'Lunas').length"></span>)</span>
            </button>
        </div>
    </div>

    <!-- Desktop Table View (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/50 text-slate-500 font-semibold">
                    <th class="py-2.5 px-4">Mahasantri</th>
                    <th class="py-2.5 px-4">Program Studi</th>
                    <th class="py-2.5 px-4">Kamar Asrama</th>
                    <th class="py-2.5 px-4">Billing BRIVA</th>
                    <th class="py-2.5 px-4 text-right">Status Sinkronisasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <template x-for="item in paginatedItems" :key="item.nim">
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <span class="size-6 rounded-lg text-[10px] font-black flex items-center justify-center shrink-0" :class="item.gender === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'" x-text="item.gender"></span>
                                <div>
                                    <strong class="text-slate-800 dark:text-slate-100 block" x-text="item.nama"></strong>
                                    <span class="font-mono text-[11px] text-slate-400" x-text="item.nim"></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300" x-text="item.prodi || '-'"></td>
                        <td class="py-3 px-4">
                            <template x-if="item.status_kamar === 'sudah'">
                                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-300">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                    <span x-text="item.kamar || 'Kamar Terisi'"></span>
                                </span>
                            </template>
                            <template x-if="item.status_kamar === 'belum'">
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200/60 dark:border-amber-900/40">
                                    Belum Ada Kamar
                                </span>
                            </template>
                        </td>
                        <td class="py-3 px-4">
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold" :class="item.status_pembayaran === 'Lunas' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200/60'" x-text="item.status_pembayaran || 'Belum Bayar'"></span>
                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.briva_number ? 'VA: ' + item.briva_number : '-'"></span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <template x-if="item.sync_status === 'synced'">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/80">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Tersinkron</span>
                                </span>
                            </template>
                            <template x-if="item.sync_status === 'missing'">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200/80">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                                    <span>Belum di App</span>
                                </span>
                            </template>
                            <template x-if="item.sync_status === 'local_manual'">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200/80">
                                    <span class="size-1.5 rounded-full bg-blue-500"></span>
                                    <span>Lokal Manual</span>
                                </span>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="filteredItems.length === 0">
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                            Tidak ditemukan data yang sesuai dengan filter yang dipilih.
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (< 768px): Bersih, Rapi & Terstruktur -->
    <div class="block md:hidden p-3 space-y-2.5">
        <template x-for="item in paginatedItems" :key="item.nim">
            <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2.5 shadow-2xs">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="size-6 rounded-lg text-[10px] font-black flex items-center justify-center shrink-0" :class="item.gender === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'" x-text="item.gender"></span>
                        <div class="min-w-0">
                            <strong class="text-xs text-slate-800 dark:text-slate-100 block truncate" x-text="item.nama"></strong>
                            <span class="font-mono text-[11px] text-slate-400 block" x-text="item.nim"></span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" :class="item.sync_status === 'synced' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/60' : (item.sync_status === 'missing' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200/60' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200/60')">
                        <span class="size-1 rounded-full" :class="item.sync_status === 'synced' ? 'bg-emerald-500' : (item.sync_status === 'missing' ? 'bg-amber-500' : 'bg-blue-500')"></span>
                        <span x-text="item.sync_status === 'synced' ? 'Tersinkron' : (item.sync_status === 'missing' ? 'Belum di App' : 'Lokal')"></span>
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Program Studi:</span>
                        <span class="text-slate-700 dark:text-slate-300 font-medium truncate block" x-text="item.prodi || '-'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kamar Asrama:</span>
                        <span class="font-semibold block truncate" :class="item.status_kamar === 'sudah' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'" x-text="item.kamar || 'Belum Ada Kamar'"></span>
                    </div>
                    <div class="col-span-2 flex items-center justify-between pt-1">
                        <span class="text-slate-400 text-[10px]" x-text="item.briva_number ? 'VA: ' + item.briva_number : 'Status Tagihan:'"></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="item.status_pembayaran === 'Lunas' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200/60'" x-text="item.status_pembayaran || 'Belum Bayar'"></span>
                    </div>
                </div>
            </div>
        </template>
        <template x-if="filteredItems.length === 0">
            <div class="py-6 text-center text-slate-400 text-xs">
                Tidak ditemukan data yang sesuai dengan filter.
            </div>
        </template>
    </div>

    <!-- Pagination Footer: Responsif -->
    <div class="p-3.5 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
        <span>Menampilkan <strong x-text="filteredItems.length === 0 ? 0 : ((page - 1) * perPage) + 1"></strong> - <strong x-text="Math.min(page * perPage, filteredItems.length)"></strong> dari <strong x-text="filteredItems.length"></strong> data</span>
        <div class="flex items-center gap-1.5">
            <button type="button" @click="page--" :disabled="page <= 1" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 disabled:opacity-40 cursor-pointer">
                &larr; Prev
            </button>
            <span class="font-mono text-[11px]" x-text="page + ' / ' + totalPages"></span>
            <button type="button" @click="page++" :disabled="page >= totalPages" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 disabled:opacity-40 cursor-pointer">
                Next &rarr;
            </button>
        </div>
    </div>
</div>
