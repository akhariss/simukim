@props(['gedungList' => []])

<!-- Modal Alokasi Mahasantri ke Unit Kamar (Penempatan Kolektif) -->
<div
    x-data="{
        open: false, loadingRooms: false, loadingSantri: false,
        rooms: [], santriList: [], searchSantri: '', filterTab: 'all', page: 1, perPage: 10,
        gedungId: '', selectedKamarId: '', selectedKamar: null,
        selectedSantriIds: [], tanggalMasuk: '{{ date('Y-m-d') }}', catatan: '',

        initModal() {
            this.$watch('selectedKamarId', (val) => {
                this.selectedSantriIds = []; this.filterTab = 'all'; this.page = 1;
                this.selectedKamar = val ? (this.rooms.find(r => r.id == val) || null) : null;
                if (this.selectedKamar) this.loadSantri(this.selectedKamar.gender);
                else this.santriList = [];
            });
            this.$watch('searchSantri', () => { this.page = 1; });
            this.$watch('filterTab', () => { this.page = 1; });
        },
        openModal(detail = {}) {
            this.open = true; this.selectedSantriIds = []; this.searchSantri = '';
            this.filterTab = 'all'; this.page = 1; this.catatan = '';
            this.loadRooms(detail.kamarId || null);
        },
        loadRooms(presetKamarId = null) {
            this.loadingRooms = true;
            let url = '{{ route('fasilitas.penempatan.available-rooms') }}' + (this.gedungId ? '?gedung_id=' + this.gedungId : '');
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json()).then(data => {
                    this.rooms = data; this.loadingRooms = false;
                    if (presetKamarId) this.selectedKamarId = presetKamarId;
                }).catch(() => { this.loadingRooms = false; });
        },
        loadSantri(gender) {
            this.loadingSantri = true;
            fetch('{{ route('fasilitas.penempatan.unassigned-santri') }}?gender=' + gender, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json()).then(data => { this.santriList = data; this.loadingSantri = false; })
                .catch(() => { this.loadingSantri = false; });
        },
        get selectedSantriObjects() { return this.santriList.filter(s => this.selectedSantriIds.includes(s.id)); },
        get filteredSantri() {
            let list = this.filterTab === 'selected' ? this.selectedSantriObjects : this.santriList;
            if (!this.searchSantri.trim()) return list;
            let q = this.searchSantri.toLowerCase().trim();
            return list.filter(s => s.name.toLowerCase().includes(q) || s.nomor_induk.toLowerCase().includes(q) || (s.prodi && s.prodi.toLowerCase().includes(q)));
        },
        get paginatedSantri() { return this.filteredSantri.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
        get totalPages() { return Math.max(1, Math.ceil(this.filteredSantri.length / this.perPage)); },
        get maxAllowed() { return this.selectedKamar ? this.selectedKamar.sisa : 0; },
        isMaxReached() { return this.selectedSantriIds.length >= this.maxAllowed; },
        toggleSantri(id) {
            let idx = this.selectedSantriIds.indexOf(id);
            if (idx > -1) this.selectedSantriIds.splice(idx, 1);
            else if (!this.isMaxReached()) this.selectedSantriIds.push(id);
        },
        removeSantri(id) {
            let idx = this.selectedSantriIds.indexOf(id);
            if (idx > -1) this.selectedSantriIds.splice(idx, 1);
        }
    }" x-init="initModal()" @open-batch-assign.window="openModal($event.detail || {})">
    <template x-teleport="body">
        <div x-show="open" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-3 sm:p-4">
            {{-- Backdrop --}}
            <div x-show="open" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="open = false"></div>

            {{-- Dialog Box (Strictly Constrained Viewport Height) --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative w-full max-w-xl max-h-[90vh] sm:max-h-[85vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col overflow-hidden z-10">
                <form action="{{ route('fasilitas.penempatan.assign-batch') }}" method="POST" class="flex flex-col h-full overflow-hidden">
                    @csrf

                    {{-- Sticky Modal Header --}}
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
                        <div>
                            <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                <span>Alokasi Mahasantri ke Unit Kamar</span>
                                <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">Penempatan Kolektif</span>
                            </h3>
                            <p class="text-[10.5px] text-slate-500 mt-0.5">Tentukan unit kamar tujuan, lalu pilih mahasantri pengisi.</p>
                        </div>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Scrollable Modal Body --}}
                    <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3.5">
                        {{-- 1. Pemilihan Kamar Tujuan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Gedung Asrama</label>
                                <select x-model="gedungId" @change="loadRooms()" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium cursor-pointer">
                                    <option value="">Semua Gedung</option>
                                    @foreach($gedungList as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Unit Kamar Tujuan (Slot Tersedia)</label>
                                <select name="kamar_id" x-model="selectedKamarId" required class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-900 dark:text-white cursor-pointer">
                                    <option value="">-- Pilih Unit Kamar --</option>
                                    <template x-for="r in rooms" :key="r.id">
                                        <option :value="r.id" x-text="`${r.nomor_kamar} (${r.gender === 'L' ? 'Ikhwan' : 'Akhwat'}) — Sisa ${r.sisa} Slot`"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Card Info Kamar Terpilih --}}
                        <template x-if="selectedKamar">
                            <div class="px-3 py-2 rounded-xl border flex items-center justify-between"
                                 :class="selectedKamar.gender === 'L' ? 'bg-blue-50/70 border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/50' : 'bg-pink-50/70 border-pink-200 dark:bg-pink-950/20 dark:border-pink-900/50'">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white font-mono" x-text="selectedKamar.nomor_kamar"></span>
                                    <span class="text-[9.5px] px-2 py-0.5 rounded-full font-bold"
                                          :class="selectedKamar.gender === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300'"
                                          x-text="selectedKamar.gender === 'L' ? 'Ikhwan' : 'Akhwat'"></span>
                                    <span class="text-[11px] text-slate-500" x-text="selectedKamar.gedung + ' · Lt. ' + selectedKamar.lantai"></span>
                                </div>
                                <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200" x-text="`Sisa ${selectedKamar.sisa} dari ${selectedKamar.kapasitas} Bed`"></span>
                            </div>
                        </template>

                        {{-- Banner & Chips Mahasantri Terpilih --}}
                        <div x-show="selectedKamar && selectedSantriIds.length > 0" class="p-2.5 rounded-xl bg-emerald-50/90 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 space-y-1.5">
                            <div class="flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-200">
                                <div class="flex items-center gap-1.5 font-semibold text-[11px]">
                                    <span class="size-4.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center" x-text="selectedSantriIds.length"></span>
                                    <span><span x-text="selectedSantriIds.length"></span> Mahasantri terpilih:</span>
                                </div>
                                <button type="button" @click="selectedSantriIds = []" class="text-[10.5px] font-bold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">Kosongkan Pilihan</button>
                            </div>
                            <div class="flex flex-wrap gap-1 max-h-20 overflow-y-auto pt-0.5">
                                <template x-for="s in selectedSantriObjects" :key="'chip-' + s.id">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-white dark:bg-slate-800 border border-emerald-300 dark:border-emerald-700 text-xs shadow-2xs">
                                        <span class="font-bold text-[11px] text-slate-800 dark:text-slate-100" x-text="s.name"></span>
                                        <span class="text-[9.5px] text-slate-400 font-mono" x-text="s.nomor_induk"></span>
                                        <button type="button" @click="removeSantri(s.id)" class="text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Hapus">
                                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- 2. Pencarian & Pemilihan Mahasantri --}}
                        <div x-show="selectedKamar" class="space-y-2 pt-0.5">
                            {{-- Tab Switcher & Status Kuota --}}
                            <div class="flex items-center justify-between gap-2">
                                <x-tab-switcher
                                    model="filterTab"
                                    color="blue"
                                    :tabs="[
                                        [
                                            'id' => 'all',
                                            'label' => 'Semua Antrean',
                                            'short_label' => 'Semua',
                                        ],
                                        [
                                            'id' => 'selected',
                                            'label' => 'Terpilih',
                                            'short_label' => 'Terpilih',
                                        ],
                                    ]"
                                />
                                <span class="text-[10.5px] font-medium" :class="isMaxReached() ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500'">
                                    <span x-text="isMaxReached() ? 'Batas kuota tercapai' : `Sisa: ${maxAllowed - selectedSantriIds.length} slot`"></span>
                                </span>
                            </div>

                            {{-- Search Input Atomik --}}
                            <x-search-input
                                x-model="searchSantri"
                                placeholder="Cari nama lengkap, NIM, prodi..."
                            />

                            {{-- List Box Paginated --}}
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 overflow-hidden">
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-52 overflow-y-auto">
                                    <template x-if="loadingSantri">
                                        <div class="p-8 text-center text-xs text-slate-400">Memuat antrean mahasantri...</div>
                                    </template>
                                    <template x-if="!loadingSantri && paginatedSantri.length === 0">
                                        <div class="p-8 text-center text-xs text-slate-400">
                                            <span x-text="filterTab === 'selected' ? 'Belum ada mahasantri yang dipilih.' : 'Tidak ada mahasantri yang cocok.'"></span>
                                        </div>
                                    </template>
                                    <template x-for="s in paginatedSantri" :key="s.id">
                                        <label @click.prevent="toggleSantri(s.id)" class="flex items-center justify-between py-1.5 px-3 cursor-pointer transition select-none hover:bg-white dark:hover:bg-slate-800"
                                               :class="selectedSantriIds.includes(s.id) ? 'bg-blue-50/90 dark:bg-blue-950/50' : ''">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <input type="checkbox" name="santri_ids[]" :value="s.id" :checked="selectedSantriIds.includes(s.id)" :disabled="!selectedSantriIds.includes(s.id) && isMaxReached()" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 size-3.5 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shrink-0">
                                                <div class="min-w-0">
                                                    <div class="font-bold text-xs text-slate-900 dark:text-white truncate" x-text="s.name"></div>
                                                    <div class="text-[9.5px] text-slate-500 font-mono" x-text="`${s.nomor_induk} • ${s.prodi}`"></div>
                                                </div>
                                            </div>
                                            <span x-show="s.is_mudabbir" class="text-[8.5px] px-1.5 py-0.2 rounded font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300 shrink-0">Mudabbir</span>
                                        </label>
                                    </template>
                                </div>

                                {{-- Paginasi Modal --}}
                                <div x-show="totalPages > 1" class="px-3 py-1.5 bg-slate-100/70 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-[10.5px] text-slate-500">
                                    <span>Menampilkan <strong class="text-slate-700 dark:text-slate-300" x-text="filteredSantri.length"></strong> Mahasantri</span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="page--" :disabled="page <= 1" class="px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 disabled:opacity-40 hover:bg-white transition cursor-pointer">Prev</button>
                                        <span class="px-1.5 font-semibold text-slate-700 dark:text-slate-300" x-text="`${page}/${totalPages}`"></span>
                                        <button type="button" @click="page++" :disabled="page >= totalPages" class="px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 disabled:opacity-40 hover:bg-white transition cursor-pointer">Next</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Tanggal & Catatan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                            <div>
                                <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai Menempati</label>
                                <input type="date" name="tanggal_masuk" x-model="tanggalMasuk" required class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Penempatan (Opsional)</label>
                                <input type="text" name="catatan" x-model="catatan" placeholder="Penempatan kolektif..." class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Modal Footer --}}
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/60 flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                            Terpilih: <strong class="text-blue-600 dark:text-blue-400" x-text="selectedSantriIds.length"></strong> Mahasantri
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="open = false" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer">Batal</button>
                            <button type="submit" :disabled="!selectedKamarId || selectedSantriIds.length === 0" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="`Simpan Penempatan (${selectedSantriIds.length})`"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
