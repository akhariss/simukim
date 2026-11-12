<!-- Modal Edit Unit Lantai -->
<div
    x-data="{
        open: false,
        isSubmitting: false,
        id: '',
        gedungNama: '',
        nomorLantai: '',
        kapasitasStandar: 24
    }"
    @open-edit-lantai.window="
        id = $event.detail.id;
        gedungNama = $event.detail.gedung_nama;
        nomorLantai = $event.detail.nomor_lantai;
        kapasitasStandar = $event.detail.kapasitas_kamar_standar || 24;
        isSubmitting = false;
        open = true;
    "
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
            class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-800 p-5 space-y-4"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Edit Unit Lantai</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="'Gedung: ' + gedungNama"></p>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('dashboard/fasilitas/kelola/lantai') }}/' + id" method="POST" @submit="isSubmitting = true" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Nomor Lantai (Min: 2)
                    </label>
                    <input
                        type="number"
                        name="nomor_lantai"
                        x-model="nomorLantai"
                        min="2"
                        max="20"
                        required
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold"
                    >
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Kapasitas Kamar Standar
                    </label>
                    <input
                        type="number"
                        name="kapasitas_kamar_standar"
                        x-model="kapasitasStandar"
                        min="1"
                        max="50"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="open = false" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                    >
                        <span x-show="!isSubmitting">Simpan Perubahan</span>
                        <span x-show="isSubmitting" x-cloak class="flex items-center gap-1">
                            <svg class="animate-spin size-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </template>
</div>
