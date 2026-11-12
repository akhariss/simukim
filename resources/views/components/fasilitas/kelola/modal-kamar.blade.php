<!-- Modal Tambah & Edit Kamar Tunggal -->
<div
    x-data="{
        open: false,
        isEdit: false,
        actionUrl: '{{ route('fasilitas.kelola.kamar.store') }}',
        form: {
            id: '',
            lantai_id: '',
            nomor_kamar: '',
            gender: 'L',
            kapasitas_maks: 4,
            tipe_ruang: 'kamar',
            status: 'tersedia',
            catatan: ''
        },
        resetForm() {
            this.isEdit = false;
            this.actionUrl = '{{ route('fasilitas.kelola.kamar.store') }}';
            this.form = { id: '', lantai_id: '', nomor_kamar: '', gender: 'L', kapasitas_maks: 4, tipe_ruang: 'kamar', status: 'tersedia', catatan: '' };
        }
    }"
    @open-add-kamar.window="resetForm(); open = true;"
    @open-edit-kamar.window="
        isEdit = true;
        actionUrl = '{{ url('dashboard/fasilitas/kelola/kamar') }}/' + $event.detail.id;
        form = { ...$event.detail };
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
                <h3 class="font-bold text-sm text-slate-900 dark:text-white" x-text="isEdit ? 'Edit Data Kamar' : 'Tambah Kamar Baru'"></h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="actionUrl" method="POST" class="space-y-3.5">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Pilih Unit Lantai</label>
                    <select
                        name="lantai_id"
                        x-model="form.lantai_id"
                        required
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                    >
                        <option value="">-- Pilih Lantai --</option>
                        @foreach($allGedung as $g)
                            <optgroup label="{{ $g->nama }}">
                                @foreach($g->lantai as $l)
                                    <option value="{{ $l->id }}">{{ $g->nama }} - Lantai {{ $l->nomor_lantai }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Kamar</label>
                        <input
                            type="text"
                            name="nomor_kamar"
                            x-model="form.nomor_kamar"
                            required
                            placeholder="JD-201"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Zona Gender</label>
                        <select
                            name="gender"
                            x-model="form.gender"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                        >
                            <option value="L">Ikhwan (Putra)</option>
                            <option value="P">Akhwat (Putri)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas Maksimal (Bed)</label>
                        <input
                            type="number"
                            name="kapasitas_maks"
                            x-model="form.kapasitas_maks"
                            min="1"
                            max="10"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tipe Ruangan</label>
                        <select
                            name="tipe_ruang"
                            x-model="form.tipe_ruang"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                        >
                            <option value="kamar">Kamar Mahasantri</option>
                            <option value="mudabbir">Kamar Mudabbir</option>
                            <option value="kantor">Kantor / Administrasi</option>
                            <option value="wc">Toilet / Kamar Mandi</option>
                            <option value="tangga">Tangga / Akses</option>
                            <option value="koridor">Koridor</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Ketersediaan</label>
                    <select
                        name="status"
                        x-model="form.status"
                        required
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                    >
                        <option value="tersedia">Tersedia (Bisa Dihuni)</option>
                        <option value="perbaikan">Perbaikan / Renovasi</option>
                        <option value="nonaktif">Non-Aktif / Terkunci</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                    <input
                        type="text"
                        name="catatan"
                        x-model="form.catatan"
                        placeholder="Dekat tangga darurat, ventilasi utara..."
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="open = false" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition">Simpan Kamar</button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </template>
</div>
