{{-- Modal Batch Generator Kelompok (Persis Standar Menu Kelola Asrama) --}}
<x-modal name="modal-batch-kelompok" maxWidth="lg">
    <div x-data="{
        prefix: 'Kelompok',
        jumlah: 20,
        nomorMulai: 1,
        kapasitas: 30,
        get previewSamples() {
            let s = parseInt(this.nomorMulai) || 1;
            let j = parseInt(this.jumlah) || 1;
            let e = s + j - 1;
            let p = (this.prefix || 'Kelompok').trim();
            if (j <= 1) return p + ' ' + s;
            if (j === 2) return p + ' ' + s + ', ' + p + ' ' + (s + 1);
            return p + ' ' + s + ', ' + p + ' ' + (s + 1) + ' ... s/d ' + p + ' ' + e;
        }
    }" class="p-5 space-y-4">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-1.5">
                    <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Batch Generator Kelompok Kajian
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Otomatis buat banyak rombongan belajar sekaligus dengan penomoran urut.</p>
            </div>
            <button type="button" @click="$dispatch('close-modal', 'modal-batch-kelompok')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <form method="POST" action="{{ route('kelompok.batch') }}" class="space-y-3.5">
            @csrf

            {{-- Input Utama: Mau Buat Berapa Kelompok? --}}
            <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-slate-800/80 border border-emerald-200/60 dark:border-slate-700">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-emerald-900 dark:text-emerald-300">Mau Buat Berapa Kelompok?</label>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">Standar: 20 Kelompok</span>
                </div>
                <input
                    type="number"
                    name="jumlah"
                    x-model.number="jumlah"
                    min="1"
                    max="100"
                    required
                    class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 font-black text-emerald-900 dark:text-emerald-200 text-base"
                >
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Format Prefix Kelompok</label>
                    <input
                        type="text"
                        name="prefix"
                        x-model="prefix"
                        required
                        placeholder="Kelompok"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold"
                    >
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Preset Format</label>
                    <select
                        @change="prefix = $event.target.value"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                    >
                        <option value="Kelompok">Kelompok (Umum)</option>
                        <option value="Kelompok Ikhwan">Kelompok Ikhwan (Putra)</option>
                        <option value="Kelompok Akhwat">Kelompok Akhwat (Putri)</option>
                        <option value="Halaqah">Halaqah</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Mulai (Start)</label>
                    <input
                        type="number"
                        name="nomor_mulai"
                        x-model.number="nomorMulai"
                        min="1"
                        max="999"
                        required
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium"
                    >
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas (Santri / Kelompok)</label>
                    <input
                        type="number"
                        name="kapasitas"
                        x-model.number="kapasitas"
                        min="1"
                        max="500"
                        required
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium"
                    >
                </div>
            </div>

            {{-- Preview Kotak Hasil --}}
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 text-xs space-y-1">
                <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 font-semibold">
                    <span>Total Kelompok Baru Dibuat:</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-bold" x-text="jumlah + ' Kelompok'"></span>
                </div>
                <div class="text-[11px] text-slate-400">
                    Contoh: <strong class="text-slate-700 dark:text-slate-300 font-mono" x-text="previewSamples"></strong>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$dispatch('close-modal', 'modal-batch-kelompok')" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition">Generate Kelompok</button>
            </div>
        </form>
    </div>
</x-modal>
