<x-app-layout>
    <x-slot name="title">Alokasi Anggota &mdash; {{ $kelompok->nama }}</x-slot>

    @php
        $santriTabs = [
            [
                'id' => 'all', 'label' => 'Direktori Mahasantri', 'short_label' => 'Direktori',
                'count' => count($santriData),
                'icon' => '<svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
            ],
            [
                'id' => 'selected', 'label' => 'Mahasantri Terpilih', 'short_label' => 'Terpilih',
                'icon' => '<svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            ],
        ];
    @endphp

    <div
        x-data="{
            search: '', filterGender: '', tab: 'all', currentPage: 1, perPage: 40,
            allSantri: {{ \Illuminate\Support\Js::from($santriData) }},
            selected: {{ $isFreshVisit ? '[]' : "JSON.parse(sessionStorage.getItem('kelompok_{{$kelompok->id}_selected') || '[]').map(Number)" }},
            init() { @if($isFreshVisit) try { sessionStorage.removeItem('kelompok_{{ $kelompok->id }}_selected'); } catch (e) {} @endif },
            toggle(id) {
                const num = Number(id), i = this.selected.indexOf(num);
                i === -1 ? this.selected.push(num) : this.selected.splice(i, 1);
                this.save();
            },
            isSelected(id) { return this.selected.includes(Number(id)); },
            selectAllVisible() {
                const visibleIds = this.paginatedList.map(s => Number(s.id));
                const allChecked = visibleIds.every(id => this.selected.includes(id));
                this.selected = allChecked ? this.selected.filter(id => !visibleIds.includes(id)) : [...new Set([...this.selected, ...visibleIds])];
                this.save();
            },
            clearAll() { this.selected = []; this.save(); },
            save() { sessionStorage.setItem('kelompok_{{ $kelompok->id }}_selected', JSON.stringify(this.selected)); },
            get selectedSantriList() { return this.allSantri.filter(s => this.selected.includes(Number(s.id))); },
            get filteredList() {
                let list = this.tab === 'selected' ? this.selectedSantriList : this.allSantri;
                if (this.filterGender === 'L' || this.filterGender === 'P') list = list.filter(s => s.gender === this.filterGender);
                if (this.search.trim()) {
                    const q = this.search.toLowerCase().trim();
                    list = list.filter(s => s.nim.toLowerCase().includes(q) || s.name.toLowerCase().includes(q) || (s.prodi && s.prodi.toLowerCase().includes(q)) || (s.kamar && s.kamar.toLowerCase().includes(q)));
                }
                return list;
            },
            get paginatedList() { return this.filteredList.slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage); },
            get totalPages() { return Math.max(1, Math.ceil(this.filteredList.length / this.perPage)); },
            setPage(p) { if (p >= 1 && p <= this.totalPages) this.currentPage = p; }
        }"
        x-effect="search; filterGender; tab; currentPage = 1"
        class="space-y-3"
    >
        {{-- Header Halaman: Breadcrumb & Tombol Aksi --}}
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kelompok Kajian', 'url' => route('kelompok.index')],
                ['label' => $kelompok->nama, 'url' => route('kelompok.show', $kelompok)],
                ['label' => 'Alokasi Mahasantri']
            ]"
            title="Alokasi Mahasantri: {{ $kelompok->nama }}"
            description="Kapasitas Maksimal: {{ $kelompok->kapasitas }} Mahasantri • Sisa Kuota Tersedia: {{ $sisaSlot }} Mahasantri"
        >
            <x-slot:actions>
                <a href="{{ route('kelompok.show', $kelompok) }}" @click="sessionStorage.removeItem('kelompok_{{ $kelompok->id }}_selected')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                <button type="submit" form="form-bulk-santri" :disabled="selected.length === 0" :class="selected.length > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed'" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Alokasi</span>
                    <span x-show="selected.length > 0" class="ml-0.5 px-1.5 py-0.2 rounded-full bg-white/20 text-[11px]" x-text="selected.length"></span>
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- Switcher Tab Mode (Atomic Tab Switcher) --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <x-tab-switcher :tabs="$santriTabs" model="tab" color="emerald" />
            <div class="text-[11px] sm:text-xs text-slate-500 flex items-center justify-between sm:justify-end gap-1.5">
                <span class="size-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                <span>Total: <strong class="text-slate-800 dark:text-slate-200" x-text="filteredList.length"></strong> Mahasantri Terdata</span>
            </div>
        </div>

        {{-- Banner & Chips Mahasantri Terpilih --}}
        <div x-show="selected.length > 0" x-cloak class="p-3 sm:p-3.5 rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 text-xs border border-emerald-200/80 dark:border-emerald-800/80 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="size-5 rounded-full bg-emerald-600 text-white text-[10.5px] font-bold flex items-center justify-center shrink-0" x-text="selected.length"></span>
                    <span class="font-bold text-xs"><span x-text="selected.length"></span> Mahasantri Terpilih Siap Dialokasikan</span>
                </div>
                <button type="button" @click="clearAll()" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer shrink-0">Kosongkan Pilihan</button>
            </div>
            <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pt-0.5">
                <template x-for="s in selectedSantriList" :key="'chip-' + s.id">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-emerald-300 dark:border-emerald-700 text-xs shadow-2xs">
                        <span class="font-bold text-slate-800 dark:text-slate-100" x-text="s.name"></span>
                        <span class="text-[10px] text-slate-400 font-mono" x-text="s.nim"></span>
                        <button type="button" @click="toggle(s.id)" class="text-slate-400 hover:text-rose-600 transition p-0.5 cursor-pointer" title="Hapus dari pilihan">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>

        {{-- Form Payload --}}
        <form id="form-bulk-santri" method="POST" action="{{ route('kelompok.anggota.bulk', $kelompok) }}" @submit="sessionStorage.removeItem('kelompok_{{ $kelompok->id }}_selected')">
            @csrf
            <template x-for="id in selected" :key="id"><input type="hidden" name="santri_ids[]" :value="id" /></template>

            {{-- Kartu Tabel (Atomic Table Container) --}}
            <x-table-container>
                <x-slot:toolbar>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 sm:gap-3">
                        <x-search-input model="search" placeholder="Cari nama lengkap, NIM, prodi, atau kamar..." class="flex-1 max-w-md" />
                        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                            <select x-model="filterGender" class="text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium focus:outline-none cursor-pointer text-center sm:text-left">
                                <option value="">Semua Gender</option><option value="L">Ikhwan (Putra)</option><option value="P">Akhwat (Putri)</option>
                            </select>
                            <button x-show="tab === 'all'" type="button" @click="selectAllVisible()" class="px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 transition cursor-pointer text-center truncate">Pilih Halaman Ini</button>
                        </div>
                    </div>
                </x-slot:toolbar>

                {{-- Desktop Table View (md+) --}}
                <x-slot:desktop>
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3 w-10 text-center">&check;</th>
                                <th class="px-4 py-3">NIM / Nomor Induk</th><th class="px-4 py-3">Nama Lengkap Mahasantri</th>
                                <th class="px-4 py-3">Program Studi</th><th class="px-4 py-3">Zonasi Gender</th><th class="px-4 py-3">Unit Kamar Asrama</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                            <template x-for="santri in paginatedList" :key="santri.id">
                                <tr @click="toggle(santri.id)" :class="isSelected(santri.id) ? 'bg-emerald-50/60 dark:bg-emerald-950/20' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40'" class="cursor-pointer transition">
                                    <td class="px-4 py-3 text-center"><input type="checkbox" :checked="isSelected(santri.id)" @click.stop="toggle(santri.id)" class="rounded border-slate-300 text-emerald-600 size-4 cursor-pointer" /></td>
                                    <td class="px-4 py-3 font-mono font-semibold text-slate-700 dark:text-slate-300 text-xs" x-text="santri.nim"></td>
                                    <td class="px-4 py-3 font-bold text-slate-900 dark:text-white text-xs" x-text="santri.name"></td>
                                    <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[11px] font-medium text-slate-700 dark:text-slate-300" x-text="santri.prodi"></span></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold" :class="santri.gender === 'L' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-pink-50 text-pink-700 dark:bg-pink-950/40 dark:text-pink-300'" x-text="santri.gender === 'L' ? 'Ikhwan' : (santri.gender === 'P' ? 'Akhwat' : '-')"></span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500 font-medium" x-text="santri.kamar || 'Belum ditempatkan'"></td>
                                </tr>
                            </template>
                            <tr x-show="filteredList.length === 0">
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">
                                    <span x-text="tab === 'selected' ? 'Belum ada mahasantri yang dipilih.' : 'Tidak ada mahasantri yang sesuai filter atau pencarian.'"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </x-slot:desktop>

                {{-- Mobile Card List View (< md) --}}
                <x-slot:mobile>
                    <div class="p-3 space-y-2">
                        <template x-for="santri in paginatedList" :key="santri.id">
                            <div @click="toggle(santri.id)"
                                :class="isSelected(santri.id) ? 'border-emerald-500/80 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs' : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs'"
                                class="p-3 rounded-xl border cursor-pointer transition flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <input type="checkbox" :checked="isSelected(santri.id)" @click.stop="toggle(santri.id)" class="rounded border-slate-300 text-emerald-600 size-4 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-slate-900 dark:text-white truncate" x-text="santri.name"></p>
                                        <p class="text-[11px] text-slate-500 font-mono mt-0.5" x-text="santri.nim"></p>
                                        <p class="text-[10px] text-slate-400 mt-0.5 truncate" x-text="santri.prodi"></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold" :class="santri.gender === 'L' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40' : 'bg-pink-50 text-pink-700 dark:bg-pink-950/40'" x-text="santri.gender === 'L' ? 'Ikhwan' : 'Akhwat'"></span>
                                    <p class="text-[10.5px] text-slate-500 font-mono mt-0.5 max-w-[120px] truncate" x-text="santri.kamar || '-'"></p>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredList.length === 0" class="p-8 text-center text-xs text-slate-400">
                            <span x-text="tab === 'selected' ? 'Belum ada mahasantri yang dipilih.' : 'Tidak ada mahasantri yang sesuai filter atau pencarian.'"></span>
                        </div>
                    </div>
                </x-slot:mobile>

                {{-- Navigasi Paginasi --}}
                <x-slot:pagination>
                    <div x-show="totalPages > 1" class="flex items-center justify-between text-xs text-slate-500">
                        <div>
                            Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="(currentPage - 1) * perPage + 1"></span> &ndash; <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="Math.min(currentPage * perPage, filteredList.length)"></span> dari <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="filteredList.length"></span> Mahasantri
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="setPage(currentPage - 1)" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer'" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-200 transition">&larr; Sebelumnya</button>
                            <span class="px-2 font-medium">Hal <span x-text="currentPage"></span>/<span x-text="totalPages"></span></span>
                            <button type="button" @click="setPage(currentPage + 1)" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer'" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-200 transition">Selanjutnya &rarr;</button>
                        </div>
                    </div>
                </x-slot:pagination>
            </x-table-container>

            {{-- Tombol Aksi Bawah --}}
            <div x-show="selected.length > 0" x-cloak class="pt-2 flex justify-end">
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Alokasi (<span x-text="selected.length"></span> Mahasantri) ke Kelompok</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>