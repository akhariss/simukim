<!-- Modal Pintar Assign Mahasantri ke Kamar -->
<div
    x-data="{
        open: false, loadingRooms: false, santriId: '', santriName: '', gender: 'L',
        gedungId: '', selectedKamarId: '', availableRooms: [],
        loadRooms() {
            this.loadingRooms = true; this.selectedKamarId = '';
            let url = '{{ route('fasilitas.penempatan.available-rooms') }}?gender=' + this.gender;
            if (this.gedungId) url += '&gedung_id=' + this.gedungId;
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json()).then(data => { this.availableRooms = data; this.loadingRooms = false; })
                .catch(() => { this.loadingRooms = false; });
        }
    }"
    @open-assign-modal.window="santriId = $event.detail.santriId; santriName = $event.detail.santriName; gender = $event.detail.gender; open = true; loadRooms();"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-3 sm:p-4">
            <div x-show="open" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="open = false"></div>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative w-full max-w-lg max-h-[90vh] sm:max-h-[85vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col overflow-hidden z-10">
                <form action="{{ route('fasilitas.penempatan.assign') }}" method="POST" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    <input type="hidden" name="santri_id" :value="santriId">

                    {{-- Sticky Header --}}
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900">
                        <div>
                            <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                <span>Penempatan Mahasantri ke Kamar</span>
                                <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-full border shadow-2xs" :class="gender === 'L' ? 'badge-ikhwan' : 'badge-akhwat'" x-text="gender === 'L' ? 'Zona Ikhwan' : 'Zona Akhwat'"></span>
                            </h3>
                            <p class="text-[10.5px] text-slate-500 mt-0.5" x-text="'Target Mahasantri: ' + santriName"></p>
                        </div>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Scrollable Body --}}
                    <div class="flex-1 overflow-y-auto min-h-0 p-4 sm:p-5 space-y-3.5">
                        <div>
                            <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Filter Gedung (Opsional)</label>
                            <select x-model="gedungId" @change="loadRooms()" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium cursor-pointer">
                                <option value="">Semua Gedung yang Kompatibel</option>
                                @foreach($gedungList as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Pilih Kamar yang Tersedia (Ada Slot Kosong)</label>
                            <div x-show="loadingRooms" class="py-6 text-center text-xs text-slate-400">Mengecek ketersediaan kamar...</div>
                            <div x-show="!loadingRooms && availableRooms.length > 0" class="max-h-52 overflow-y-auto space-y-1.5 p-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30">
                                <template x-for="r in availableRooms" :key="r.id">
                                    <label class="flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition"
                                           :class="selectedKamarId == r.id ? 'border-blue-600 bg-blue-50/70 dark:bg-slate-800 ring-1 ring-blue-600' : 'border-slate-200 dark:border-slate-700/80 hover:bg-white dark:hover:bg-slate-800/50'">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="kamar_id" :value="r.id" x-model="selectedKamarId" required class="text-blue-600 focus:ring-blue-500 cursor-pointer">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-xs text-slate-900 dark:text-white font-mono" x-text="'Kamar ' + r.nomor_kamar"></span>
                                                    <span x-show="r.tipe_ruang === 'mudabbir'" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full badge-mudabbir">Mudabbir</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400" x-text="r.gedung + ' • Lantai ' + r.lantai"></div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full badge-success" x-text="'Sisa ' + r.sisa + ' Bed'"></span>
                                            <div class="text-[9.5px] text-slate-400 mt-0.5" x-text="r.terisi + '/' + r.kapasitas + ' Terisi'"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                            <div x-show="!loadingRooms && availableRooms.length === 0" class="py-6 text-center text-xs text-rose-500 bg-rose-50/50 dark:bg-slate-800/40 rounded-xl border border-rose-200 dark:border-slate-700">
                                Tidak ada kamar kosong yang cocok dengan kriteria gender ini saat ini.
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                            <div>
                                <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai Menempati</label>
                                <input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}" required class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Penempatan (Opsional)</label>
                                <input type="text" name="catatan" placeholder="Penempatan reguler..." class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Footer --}}
                    <div class="shrink-0 px-4 py-3 sm:px-5 sm:py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/60 flex items-center justify-end gap-2">
                        <button type="button" @click="open = false" class="px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer">Batal</button>
                        <button type="submit" :disabled="!selectedKamarId" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Penempatan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>