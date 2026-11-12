@props([
    'jenisList' => [],
])

<div
    x-data="{
        open: false,
        id: null,
        nama: '',
        nim: '',
        kamar: '',
        jenis_id: '',
        tanggal: '',
        poin: 5,
        deskripsi: '',
        hukuman: '',
        status: 'terdaftar',
        init() {
            window.addEventListener('open-edit-pelanggaran', (e) => {
                const d = e.detail;
                this.id = d.id;
                this.nama = d.nama;
                this.nim = d.nim;
                this.kamar = d.kamar;
                this.jenis_id = d.jenis_id;
                this.tanggal = d.tanggal;
                this.poin = d.poin || 5;
                this.deskripsi = d.deskripsi;
                this.hukuman = d.hukuman || '';
                this.status = d.status || 'terdaftar';
                this.open = true;
            });
        },
        updatePoin(el) {
            const opt = el.options[el.selectedIndex];
            if (opt && opt.dataset.poin) {
                this.poin = parseInt(opt.dataset.poin) || 5;
            }
        }
    }"
    @keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Full Viewport Backdrop -->
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
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Edit Data Pelanggaran Santri</h3>
                            <p class="text-[10.5px] text-slate-400 mt-0.5">Perbarui catatan kedisiplinan dan sanksi santri</p>
                        </div>
                        <button type="button" @click="open = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Form Container -->
                    <form :action="'{{ url('dashboard/kedisiplinan/pelanggaran') }}/' + id" method="POST" class="flex flex-col flex-1 min-h-0">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="poin" :value="poin">

                        <!-- 2. Scrollable Body -->
                        <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3 text-xs">
                            <!-- Info Santri (Readonly Card) -->
                            <div class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 dark:text-white truncate" x-text="nama"></div>
                                    <div class="text-[10.5px] text-slate-400 font-mono" x-text="'NIM: ' + nim"></div>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 shrink-0" x-text="kamar"></span>
                            </div>

                            <!-- Jenis / Tingkat Pelanggaran & Tanggal -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                        Jenis / Tingkat Pelanggaran *
                                    </label>
                                    <select
                                        name="jenis_pelanggaran_id"
                                        x-model="jenis_id"
                                        @change="updatePoin($event.target)"
                                        required
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 cursor-pointer"
                                    >
                                        @foreach($jenisList as $jp)
                                            @php
                                                $tingkat = match(true) {
                                                    $jp->poin_default <= 5 || str_contains(strtolower($jp->nama), 'ringan') => 'Pelanggaran Ringan',
                                                    $jp->poin_default >= 20 || str_contains(strtolower($jp->nama), 'berat') => 'Pelanggaran Berat',
                                                    default => 'Pelanggaran Sedang',
                                                };
                                            @endphp
                                            <option value="{{ $jp->id }}" data-poin="{{ $jp->poin_default }}">
                                                {{ $tingkat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                        Tanggal Kejadian *
                                    </label>
                                    <input
                                        type="date"
                                        name="tanggal"
                                        x-model="tanggal"
                                        required
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                                    >
                                </div>
                            </div>

                            <!-- Status Pelaksanaan Sanksi -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                    Status Pelaksanaan Sanksi *
                                </label>
                                <select
                                    name="status"
                                    x-model="status"
                                    required
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 cursor-pointer"
                                >
                                    <option value="terdaftar">Belum Dilaksanakan</option>
                                    <option value="selesai">Selesai Dilaksanakan</option>
                                </select>
                            </div>

                            <!-- Tindakan Disiplin / Sanksi -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                    Tindakan Disiplin / Sanksi (Opsional)
                                </label>
                                <input
                                    type="text"
                                    name="hukuman"
                                    x-model="hukuman"
                                    placeholder="Contoh: Pembersihan area asrama / peringatan lisan"
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                                >
                            </div>

                            <!-- Kronologi / Deskripsi Kejadian -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                    Kronologi / Catatan Kejadian *
                                </label>
                                <textarea
                                    name="deskripsi"
                                    rows="3"
                                    x-model="deskripsi"
                                    required
                                    placeholder="Uraikan waktu, saksi, dan detail pelanggaran santri..."
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-normal text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                                ></textarea>
                            </div>
                        </div>

                        <!-- 3. Sticky Footer -->
                        <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="open = false"
                                class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition cursor-pointer"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

