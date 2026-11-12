@props([
    'activePeriode',
    'santriList' => [],
    'jenisList' => [],
])

<div
    x-data="{
        open: false,
        santriData: @js($santriList),
        searchQuery: '',
        showDropdown: false,
        selectedSantri: null,

        get filteredSantri() {
            if (!this.searchQuery.trim()) return this.santriData.slice(0, 8);
            const q = this.searchQuery.toLowerCase().trim();
            return this.santriData.filter(s =>
                s.nama.toLowerCase().includes(q) || s.nim.toLowerCase().includes(q) || s.prodi.toLowerCase().includes(q)
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
    }"
    @open-add-izin.window="open = true; clearSantri();"
    @keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Full Viewport Backdrop (Mencakup Navbar & Sidebar) -->
            <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="open = false"></div>

            <!-- Centering Wrapper (Safe Scrolling with my-auto) -->
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 py-6 sm:py-8">
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="relative w-full max-w-lg my-auto max-h-[calc(100vh-3rem)] sm:max-h-[calc(100vh-4rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col overflow-hidden z-10"
                >
                    <!-- 1. Sticky Header -->
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Formulir Perizinan Mahasantri</h3>
                            <p class="text-[10.5px] text-slate-400 mt-0.5">Catat surat izin keluar atau dispensasi santri aktif</p>
                        </div>
                        <button type="button" @click="open = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Form Container -->
                    <form action="{{ route('kedisiplinan.perizinan.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                        @csrf

                        <!-- 2. Scrollable Body -->
                        <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3 text-xs">
                            <!-- Cari & Pilih Mahasantri Aktif -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                    Cari & Pilih Mahasantri *
                                </label>
                                <input type="hidden" name="santri_id" :value="selectedSantri ? selectedSantri.id : ''" required>

                                <div class="relative" @click.outside="showDropdown = false">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            x-model="searchQuery"
                                            @focus="showDropdown = true"
                                            @input="showDropdown = true"
                                            placeholder="Ketik Nama atau NIM santri..."
                                            class="w-full pl-8 pr-8 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                            autocomplete="off"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <button
                                            type="button"
                                            x-show="selectedSantri || searchQuery"
                                            @click="clearSantri()"
                                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                                        >
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <!-- Autocomplete Dropdown List -->
                                    <div
                                        x-show="showDropdown"
                                        x-cloak
                                        class="absolute top-full left-0 right-0 mt-1 max-h-44 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-30 divide-y divide-slate-100 dark:divide-slate-800"
                                    >
                                        <template x-for="s in filteredSantri" :key="s.id">
                                            <div
                                                @click="selectSantri(s)"
                                                class="px-3 py-2 text-xs hover:bg-blue-50 dark:hover:bg-slate-800 cursor-pointer transition flex items-center justify-between"
                                            >
                                                <div class="min-w-0 pr-2">
                                                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate" x-text="s.nama"></div>
                                                    <div class="text-[10px] text-slate-400 dark:text-slate-500" x-text="`${s.nim} • ${s.prodi}`"></div>
                                                </div>
                                                <span class="text-[9.5px] px-2 py-0.5 rounded-md font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 shrink-0" x-text="s.kamar"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredSantri.length === 0" class="px-3 py-3 text-center text-xs text-slate-400">
                                            Santri aktif tidak ditemukan
                                        </div>
                                    </div>
                                </div>

                                <!-- Live Preview Info Santri Terpilih -->
                                <div
                                    x-show="selectedSantri"
                                    x-cloak
                                    class="mt-2 p-2.5 rounded-xl border border-blue-200/80 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/20 text-xs flex items-center justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <div class="font-bold text-blue-900 dark:text-blue-300 truncate" x-text="selectedSantri?.nama"></div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-x-2">
                                            <span>NIM: <strong class="font-mono text-slate-700 dark:text-slate-300" x-text="selectedSantri?.nim"></strong></span>
                                            <span>&bull;</span>
                                            <span>Jurusan: <strong class="text-slate-700 dark:text-slate-300" x-text="selectedSantri?.prodi"></strong></span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-700 text-blue-700 dark:text-blue-300 shrink-0">
                                        🏢 <span x-text="selectedSantri?.kamar"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Kategori Izin (Sakit, Pulang, Lainnya) -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                    Kategori Izin *
                                </label>
                                <select
                                    name="jenis_perizinan_id"
                                    required
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer"
                                >
                                    <option value="">-- Pilih Kategori Izin --</option>
                                    @foreach($jenisList as $j)
                                        @php
                                            $label = match(true) {
                                                str_contains(strtolower($j->nama), 'sakit') => 'Sakit',
                                                str_contains(strtolower($j->nama), 'pulang') => 'Pulang',
                                                default => 'Lainnya',
                                            };
                                        @endphp
                                        <option value="{{ $j->id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Rentang Tanggal -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                                    <input
                                        type="date"
                                        name="tanggal_mulai"
                                        value="{{ date('Y-m-d') }}"
                                        required
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    >
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
                                    <input
                                        type="date"
                                        name="tanggal_selesai"
                                        value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                        required
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    >
                                </div>
                            </div>

                            <!-- Alasan Izin -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Alasan Keperluan Izin *</label>
                                <textarea
                                    name="alasan"
                                    rows="3"
                                    required
                                    placeholder="Tuliskan keterangan lengkap izin santri..."
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-normal text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                ></textarea>
                            </div>
                        </div>

                        <!-- 3. Sticky Footer -->
                        <div class="shrink-0 px-4 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="open = false"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="!selectedSantri"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                Simpan Permohonan Izin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
