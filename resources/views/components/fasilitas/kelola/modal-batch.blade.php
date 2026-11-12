<!-- Modal Batch Generator Kamar Massal -->
<div
    x-data="{
        open: false,
        lantaiId: '',
        jumlahKamar: 24,
        prefix: 'JD-2',
        startNum: 1,
        endNum: 24,
        padding: 2,
        gender: 'L',
        kapasitasMaks: 4,
        tipeRuang: 'kamar',
        updateJumlahKamar(val) {
            let n = parseInt(val) || 0;
            this.jumlahKamar = n;
            this.startNum = 1;
            this.endNum = n > 0 ? n : 1;
        },
        onLantaiChange(e) {
            let selectedOpt = e.target.options[e.target.selectedIndex];
            if (selectedOpt) {
                let p = selectedOpt.getAttribute('data-prefix');
                let g = selectedOpt.getAttribute('data-gender');
                if (p) this.prefix = p;
                if (g) this.gender = g;
            }
        },
        get totalRooms() {
            let total = parseInt(this.endNum) - parseInt(this.startNum) + 1;
            return total > 0 ? total : 0;
        },
        get previewSamples() {
            let s = parseInt(this.startNum), e = parseInt(this.endNum);
            if (e < s) return '';
            let pad = parseInt(this.padding);
            let p1 = this.prefix + String(s).padStart(pad, '0');
            let p2 = this.prefix + String(s + 1).padStart(pad, '0');
            let pEnd = this.prefix + String(e).padStart(pad, '0');
            return p1 + ', ' + p2 + ' ... s/d ' + pEnd;
        }
    }"
    @open-batch-kamar.window="open = true;"
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            data-modal-portal="true"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
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
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-1.5">
                        <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Batch Generator Kamar Massal
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Otomatis buat banyak unit kamar sekaligus untuk suatu lantai.</p>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('fasilitas.kelola.kamar.batch') }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Target Lantai Asrama</label>
                    <select name="lantai_id" x-model="lantaiId" @change="onLantaiChange($event)" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                        <option value="">-- Pilih Lantai Tujuan --</option>
                        @foreach($allGedung as $g)
                            <optgroup label="{{ $g->nama }}">
                                @foreach($g->lantai as $l)
                                    @php
                                        $prefixFormat = $g->kode . '-' . $l->nomor_lantai;
                                        $defaultGen = $g->gender_khusus === 'campur' ? ($l->nomor_lantai <= 3 ? 'L' : 'P') : $g->gender_khusus;
                                    @endphp
                                    <option value="{{ $l->id }}" data-prefix="{{ $prefixFormat }}" data-gender="{{ $defaultGen }}">
                                        {{ $g->nama }} - Lantai {{ $l->nomor_lantai }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <!-- Input Utama: Berapa kamar yang mau dibuat? -->
                <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-slate-800/80 border border-emerald-200/60 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-emerald-900 dark:text-emerald-300">Mau Buat Berapa Kamar?</label>
                        <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">Standar: 24 Kamar</span>
                    </div>
                    <input type="number" x-model="jumlahKamar" @input="updateJumlahKamar($event.target.value)" min="1" max="50" required class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 font-black text-emerald-900 dark:text-emerald-200 text-base">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Format Prefix Kamar</label>
                        <input type="text" name="prefix" x-model="prefix" required placeholder="JD2-" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Digit Padding Nol</label>
                        <select name="padding" x-model="padding" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <option value="2">2 Digit (01, 02 .. 24)</option>
                            <option value="3">3 Digit (001, 002 ..)</option>
                            <option value="1">1 Digit (1, 2 ..)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Mulai (Start)</label>
                        <input type="number" name="nomor_mulai" x-model="startNum" min="1" max="999" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Selesai (End)</label>
                        <input type="number" name="nomor_selesai" x-model="endNum" min="1" max="999" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Zona Gender</label>
                        <select name="gender" x-model="gender" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                            <option value="L">Ikhwan (Putra)</option>
                            <option value="P">Akhwat (Putri)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tipe Ruang</label>
                        <select name="tipe_ruang" x-model="tipeRuang" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                            <option value="kamar">Kamar Santri</option>
                            <option value="mudabbir">Kamar Mudabbir</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas (Bed)</label>
                        <input type="number" name="kapasitas_maks" x-model="kapasitasMaks" min="1" max="10" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                    </div>
                </div>

                <!-- Preview Kotak Hasil -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 text-xs space-y-1">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 font-semibold">
                        <span>Total Kamar Baru Dibuat:</span>
                        <span class="px-2 py-0.5 rounded-full badge-success font-bold" x-text="totalRooms + ' Unit Kamar'"></span>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        Contoh: <strong class="text-slate-700 dark:text-slate-300 font-mono" x-text="previewSamples"></strong>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="open = false" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition cursor-pointer"
                        data-confirm="Buat unit-unit kamar tersebut secara otomatis?"
                        data-confirm-title="Konfirmasi Batch Generator"
                        data-confirm-type="success"
                    >
                        Buat Kamar Otomatis
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </template>
</div>
