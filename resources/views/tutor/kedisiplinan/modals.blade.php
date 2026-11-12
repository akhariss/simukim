{{-- Modal Tambah Izin (Scoped ke Santri Binaan dengan Searchable Autocomplete & Live Preview) --}}
<x-modal name="modal-tambah-izin" focusable>
    <form method="POST" action="{{ route('kedisiplinan.perizinan.store') }}" class="p-5 space-y-3.5"
        x-data="{
            santriData: @js($santriBinaanList),
            searchQuery: '',
            showDropdown: false,
            selectedSantri: null,
            get filteredSantri() {
                if (!this.searchQuery.trim()) return this.santriData.slice(0, 8);
                const q = this.searchQuery.toLowerCase().trim();
                return this.santriData.filter(s =>
                    s.nama.toLowerCase().includes(q) || s.nim.toLowerCase().includes(q) || (s.prodi && s.prodi.toLowerCase().includes(q))
                ).slice(0, 10);
            },
            selectSantri(s) {
                this.selectedSantri = s;
                this.searchQuery = s.nim + ' - ' + s.nama;
                this.showDropdown = false;
            },
            clearSantri() {
                this.selectedSantri = null;
                this.searchQuery = '';
                this.showDropdown = false;
            }
        }">
        @csrf
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pencatatan Surat Izin Mahasantri</h3>

        {{-- Searchable Autocomplete Mahasantri --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cari &amp; Pilih Mahasantri *</label>
            <input type="hidden" name="santri_id" :value="selectedSantri ? selectedSantri.id : ''" required>
            <div class="relative" @click.outside="showDropdown = false">
                <div class="relative">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @focus="showDropdown = true"
                        @input="showDropdown = true"
                        placeholder="Ketik Nama atau NIM santri binaan..."
                        class="w-full pl-8 pr-8 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        autocomplete="off"
                        required
                    >
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="button" x-show="selectedSantri || searchQuery" @click="clearSantri()" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Autocomplete Dropdown List -->
                <div x-show="showDropdown" x-cloak class="absolute z-20 left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl divide-y divide-slate-100 dark:divide-slate-700/60">
                    <template x-for="s in filteredSantri" :key="s.id">
                        <div @click="selectSantri(s)" class="px-3 py-2 hover:bg-blue-50 dark:hover:bg-slate-700/70 cursor-pointer transition flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-100" x-text="s.nama"></span>
                                <span class="text-[10px] text-slate-400 font-mono ml-1" x-text="'(' + s.nim + ')'"></span>
                                <p class="text-[10.5px] text-slate-500 dark:text-slate-400" x-text="s.prodi + ' • ' + s.kamar"></p>
                            </div>
                            <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400">Pilih &rarr;</span>
                        </div>
                    </template>
                    <div x-show="filteredSantri.length === 0" class="p-3 text-center text-slate-400 text-xs">Tidak ada santri binaan yang cocok.</div>
                </div>
            </div>

            <!-- Preview Santri Terpilih -->
            <div x-show="selectedSantri" x-cloak class="mt-2 p-2 rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/20 text-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-bold text-blue-900 dark:text-blue-300 truncate" x-text="selectedSantri?.nama"></p>
                    <p class="text-[10px] text-slate-500 mt-0.5"><span class="font-mono font-semibold" x-text="selectedSantri?.nim"></span> &bull; <span x-text="selectedSantri?.prodi"></span></p>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-700 text-blue-700 dark:text-blue-300 shrink-0" x-text="selectedSantri?.kamar"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kategori Izin *</label>
                <select name="jenis_perizinan_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    @foreach($jenisIzinList as $j)<option value="{{ $j->id }}">{{ $j->nama }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Mulai s/d Selesai *</label>
                <div class="flex items-center gap-1.5">
                    <input type="date" name="tanggal_mulai" required value="{{ date('Y-m-d') }}" class="w-full px-2 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    <input type="date" name="tanggal_selesai" required value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full px-2 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Alasan / Keperluan Izin *</label>
            <textarea name="alasan" rows="2" required placeholder="Uraikan keperluan izin secara jelas dan lengkap..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" @click="$dispatch('close')" class="px-3.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">Batal</button>
            <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Simpan Izin</button>
        </div>
    </form>
</x-modal>

{{-- Modal Tambah Pelanggaran (Scoped ke Santri Binaan dengan Searchable Autocomplete & Live Preview) --}}
<x-modal name="modal-tambah-pelanggaran" focusable>
    <form method="POST" action="{{ route('kedisiplinan.pelanggaran.store') }}" class="p-5 space-y-3.5"
        x-data="{
            santriData: @js($santriBinaanList),
            searchQuery: '',
            showDropdown: false,
            selectedSantri: null,
            get filteredSantri() {
                if (!this.searchQuery.trim()) return this.santriData.slice(0, 8);
                const q = this.searchQuery.toLowerCase().trim();
                return this.santriData.filter(s =>
                    s.nama.toLowerCase().includes(q) || s.nim.toLowerCase().includes(q) || (s.prodi && s.prodi.toLowerCase().includes(q))
                ).slice(0, 10);
            },
            selectSantri(s) {
                this.selectedSantri = s;
                this.searchQuery = s.nim + ' - ' + s.nama;
                this.showDropdown = false;
            },
            clearSantri() {
                this.selectedSantri = null;
                this.searchQuery = '';
                this.showDropdown = false;
            }
        }">
        @csrf
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pencatatan Pelanggaran Disiplin Mahasantri</h3>

        {{-- Searchable Autocomplete Mahasantri --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cari &amp; Pilih Mahasantri *</label>
            <input type="hidden" name="santri_id" :value="selectedSantri ? selectedSantri.id : ''" required>
            <div class="relative" @click.outside="showDropdown = false">
                <div class="relative">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @focus="showDropdown = true"
                        @input="showDropdown = true"
                        placeholder="Ketik Nama atau NIM santri binaan..."
                        class="w-full pl-8 pr-8 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                        autocomplete="off"
                        required
                    >
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="button" x-show="selectedSantri || searchQuery" @click="clearSantri()" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Autocomplete Dropdown List -->
                <div x-show="showDropdown" x-cloak class="absolute z-20 left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl divide-y divide-slate-100 dark:divide-slate-700/60">
                    <template x-for="s in filteredSantri" :key="s.id">
                        <div @click="selectSantri(s)" class="px-3 py-2 hover:bg-rose-50 dark:hover:bg-slate-700/70 cursor-pointer transition flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-100" x-text="s.nama"></span>
                                <span class="text-[10px] text-slate-400 font-mono ml-1" x-text="'(' + s.nim + ')'"></span>
                                <p class="text-[10.5px] text-slate-500 dark:text-slate-400" x-text="s.prodi + ' • ' + s.kamar"></p>
                            </div>
                            <span class="text-[10px] font-semibold text-rose-600 dark:text-rose-400">Pilih &rarr;</span>
                        </div>
                    </template>
                    <div x-show="filteredSantri.length === 0" class="p-3 text-center text-slate-400 text-xs">Tidak ada santri binaan yang cocok.</div>
                </div>
            </div>

            <!-- Preview Santri Terpilih -->
            <div x-show="selectedSantri" x-cloak class="mt-2 p-2 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/20 text-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-bold text-rose-900 dark:text-rose-300 truncate" x-text="selectedSantri?.nama"></p>
                    <p class="text-[10px] text-slate-500 mt-0.5"><span class="font-mono font-semibold" x-text="selectedSantri?.nim"></span> &bull; <span x-text="selectedSantri?.prodi"></span></p>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-white dark:bg-slate-800 border border-rose-200 dark:border-slate-700 text-rose-700 dark:text-rose-300 shrink-0" x-text="selectedSantri?.kamar"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Jenis Pelanggaran *</label>
                <select name="jenis_pelanggaran_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    @foreach($jenisPelanggaranList as $jp)<option value="{{ $jp->id }}">{{ $jp->nama }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Tanggal Kejadian *</label>
                <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Uraian Kejadian *</label>
            <textarea name="deskripsi" rows="2" required placeholder="Uraikan kronologi kejadian secara objektif dan faktual..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Sanksi / Tindak Lanjut Pembinaan</label>
            <input type="text" name="hukuman" placeholder="Contoh: Teguran lisan dan penugasan kebersihan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" @click="$dispatch('close')" class="px-3.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">Batal</button>
            <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Simpan Catatan Disiplin</button>
        </div>
    </form>
</x-modal>
