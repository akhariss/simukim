<!-- Modal Tambah & Edit Gedung Asrama -->
<div
    x-data="{
        open: false,
        isEdit: false,
        actionUrl: '{{ route('fasilitas.kelola.gedung.store') }}',
        form: { id: '', kode: '', nama: '', gender_khusus: 'campur', deskripsi: '', jumlah_lantai: 4, is_active: 1 },
        blocks: [{ sampai: 12, gender: 'L' }, { sampai: 24, gender: 'P' }],
        getDari(i) {
            return i === 0 ? 1 : (this.getSampai(i - 1) + 1);
        },
        getSampai(i) {
            if (i === this.blocks.length - 1) return 24;
            let val = Number(this.blocks[i]?.sampai) || 12;
            let minVal = i === 0 ? 1 : (this.blocks[i - 1]?.sampai || 1) + 1;
            return Math.min(23, Math.max(minVal, val));
        },
        updateSampai(i, val) {
            let num = parseInt(val, 10);
            if (isNaN(num)) return;
            let minVal = this.getDari(i);
            this.blocks[i].sampai = Math.min(23, Math.max(minVal, num));
        },
        addBlock() {
            if (this.blocks.length >= 6) return;
            let lastIdx = this.blocks.length - 1;
            let lastDari = this.getDari(lastIdx);
            if (lastDari >= 24) return;
            let mid = Math.floor((lastDari + 24) / 2);
            let nextGender = this.blocks[lastIdx].gender === 'L' ? 'P' : 'L';
            this.blocks.splice(lastIdx, 0, { sampai: mid, gender: this.blocks[lastIdx].gender });
            this.blocks[this.blocks.length - 1].gender = nextGender;
        },
        removeBlock(i) {
            if (this.blocks.length > 1) this.blocks.splice(i, 1);
        },
        setPreset(p) {
            if (p === 'simetris') {
                this.blocks = [{ sampai: 12, gender: 'L' }, { sampai: 24, gender: 'P' }];
            } else if (p === 'selang') {
                this.blocks = [
                    { sampai: 6, gender: 'L' }, { sampai: 13, gender: 'P' },
                    { sampai: 18, gender: 'L' }, { sampai: 24, gender: 'P' }
                ];
            }
        },
        payloadBlocks() {
            let list = [];
            for (let i = 0; i < this.blocks.length; i++) {
                list.push({ dari: this.getDari(i), sampai: this.getSampai(i), gender: this.blocks[i].gender });
            }
            return JSON.stringify(list);
        },
        resetForm() {
            this.isEdit = false;
            this.actionUrl = '{{ route('fasilitas.kelola.gedung.store') }}';
            this.form = { id: '', kode: '', nama: '', gender_khusus: 'campur', deskripsi: '', jumlah_lantai: 4, is_active: 1 };
            this.setPreset('simetris');
        }
    }"
    x-init="$watch('open', v => { if (!v) resetForm(); })"
    @open-add-gedung.window="resetForm(); open = true;"
    @open-edit-gedung.window="
        isEdit = true;
        actionUrl = '{{ url('dashboard/fasilitas/kelola/gedung') }}/' + $event.detail.id;
        form = { ...$event.detail };
        open = true;
    "
>
    <template x-teleport="body">
        <div x-show="open" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-y-auto">
            <div
                x-show="open"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 transition-opacity"
                @click="open = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-800 p-5 space-y-4"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white" x-text="isEdit ? 'Edit Data Gedung' : 'Tambah Gedung Asrama Baru'"></h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="actionUrl" method="POST" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kode Gedung</label>
                                <input type="text" name="kode" x-model="form.kode" required placeholder="GDA" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-bold">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Gedung</label>
                                <input type="text" name="nama" x-model="form.nama" required placeholder="Gedung Al-Farabi" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Ketentuan Gender</label>
                            <select name="gender_khusus" x-model="form.gender_khusus" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                                <option value="campur">Campur (Dikelola Per Blok Lantai)</option>
                                <option value="L">Khusus Ikhwan (Putra)</option>
                                <option value="P">Khusus Akhwat (Putri)</option>
                            </select>
                        </div>

                        <template x-if="!isEdit">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah Lantai Hunian</label>
                                    <input type="number" name="jumlah_lantai" x-model.number="form.jumlah_lantai" min="0" max="10" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold">
                                    <span class="text-[10px] text-slate-400">Isi 4 untuk membuat Lantai 2 s/d 5 (masing-masing 24 kamar otomatis).</span>
                                </div>

                                <!-- Konfigurasi Zonasi Blok Gender Kamar -->
                                <div x-show="form.gender_khusus === 'campur' && form.jumlah_lantai > 0" class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-[11px] font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                                Distribusi Zonasi Kamar (Per Lantai)
                                            </h4>
                                            <p class="text-[10px] text-slate-400">Total 24 kamar/lantai (seluruhnya dibuat sebagai kamar santri).</p>
                                        </div>
                                        <div class="flex items-center gap-1 text-[9px]">
                                            <button type="button" @click="setPreset('simetris')" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 font-medium text-slate-600 dark:text-slate-200">2 Sayap (12/12)</button>
                                            <button type="button" @click="setPreset('selang')" class="px-2 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 font-medium text-slate-600 dark:text-slate-200">4 Blok (Contoh: 6-an)</button>
                                        </div>
                                    </div>

                                    <!-- Visual Bar Proporsional (Selalu Pas 100%) -->
                                    <div class="h-3.5 w-full bg-slate-200 dark:bg-slate-700 rounded-lg overflow-hidden flex shadow-inner">
                                        <template x-for="(b, idx) in blocks" :key="idx">
                                            <div
                                                class="h-full transition-all flex items-center justify-center text-[8px] font-bold text-white px-0.5"
                                                :class="b.gender === 'L' ? 'bg-blue-500' : 'bg-pink-500'"
                                                :style="`width: ${((getSampai(idx) - getDari(idx) + 1) / 24) * 100}%`"
                                                :title="`Blok ${idx+1}: Kamar ${getDari(idx)}-${getSampai(idx)} (${b.gender === 'L' ? 'Ikhwan' : 'Akhwat'})`"
                                            >
                                                <span x-text="`${getDari(idx)}-${getSampai(idx)}`" class="truncate"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Daftar Rentang Blok Sambung Otomatis -->
                                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-0.5">
                                        <template x-for="(b, idx) in blocks" :key="idx">
                                            <div class="flex items-center gap-2 p-1.5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200/80 dark:border-slate-700/60 text-xs">
                                                <span class="text-[10px] font-bold text-slate-400 w-10 shrink-0" x-text="`Blok ${idx+1}`"></span>
                                                <div class="flex items-center gap-1 text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                                    <span>Kmr</span>
                                                    <span class="w-7 text-center font-bold text-slate-700 dark:text-slate-200" x-text="String(getDari(idx)).padStart(2, '0')"></span>
                                                    <span>s/d</span>
                                                    <template x-if="idx < blocks.length - 1">
                                                        <input
                                                            type="number"
                                                            :value="b.sampai"
                                                            @input="updateSampai(idx, $event.target.value)"
                                                            min="1"
                                                            max="23"
                                                            class="w-11 px-1 py-0.5 text-center font-bold text-xs rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                                                        >
                                                    </template>
                                                    <template x-if="idx === blocks.length - 1">
                                                        <span class="w-11 px-1 py-0.5 text-center font-bold text-xs rounded border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-500">24</span>
                                                    </template>
                                                </div>
                                                <select x-model="b.gender" class="flex-1 px-2 py-0.5 text-xs font-semibold rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" :class="b.gender === 'L' ? 'text-blue-600 dark:text-blue-400' : 'text-pink-600 dark:text-pink-400'">
                                                    <option value="L">Ikhwan (Putra)</option>
                                                    <option value="P">Akhwat (Putri)</option>
                                                </select>
                                                <button type="button" @click="removeBlock(idx)" x-show="blocks.length > 1" class="text-slate-400 hover:text-rose-500 p-0.5" title="Hapus Blok">
                                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <button type="button" @click="addBlock()" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Tambah Blok Rentang
                                        </button>
                                        <span class="text-[10px] text-slate-400">Tipe: Kamar Santri</span>
                                    </div>
                                    <input type="hidden" name="gender_blocks" :value="payloadBlocks()">
                                </div>
                            </div>
                        </template>

                        <template x-if="isEdit">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Operasional</label>
                                <select name="is_active" x-model="form.is_active" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                    <option value="1">Aktif Beroperasi</option>
                                    <option value="0">Non-Aktif (Renovasi)</option>
                                </select>
                            </div>
                        </template>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Deskripsi / Catatan Fasilitas</label>
                            <textarea name="deskripsi" x-model="form.deskripsi" rows="2" placeholder="Catatan peruntukan atau lokasi asrama..." class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="open = false" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition">Simpan Gedung</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
