{{-- Modal Tambah Kelompok (Persis Standar Menu Kelola Asrama) --}}
<x-modal name="modal-tambah-kelompok" maxWidth="md">
    <div class="p-5 space-y-4">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Tambah Kelompok Kajian Baru</h3>
            <button type="button" @click="$dispatch('close-modal', 'modal-tambah-kelompok')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <form method="POST" action="{{ route('kelompok.store') }}" class="space-y-3.5">
            @csrf

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kode Kelompok</label>
                    <input
                        type="text"
                        name="kode"
                        placeholder="KLP-01"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-bold"
                    >
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Kelompok</label>
                    <input
                        type="text"
                        name="nama"
                        required
                        placeholder="Contoh: Kelompok Al-Fatihah (Ikhwan A)"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                    >
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas Maksimal Santri</label>
                <input
                    type="number"
                    name="kapasitas"
                    min="1"
                    max="500"
                    value="30"
                    required
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold"
                >
                <span class="text-[10px] text-slate-400">Kapasitas standar per rombongan belajar adalah 30 santri.</span>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Deskripsi / Catatan Lokasi</label>
                <textarea
                    name="deskripsi"
                    rows="2"
                    placeholder="Lokasi halaqah atau catatan kategori kelompok..."
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$dispatch('close-modal', 'modal-tambah-kelompok')" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition">Simpan Kelompok</button>
            </div>
        </form>
    </div>
</x-modal>
