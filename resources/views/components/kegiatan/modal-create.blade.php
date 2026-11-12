<div
    x-data="{ open: false, jenis: 'rutin' }"
    @open-modal-kegiatan.window="open = true"
    @keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            data-modal-portal="true"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="open = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Kegiatan Asrama</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form method="POST" action="{{ route('kegiatan.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kegiatan *</label>
                    <input type="text" name="nama" required placeholder="Contoh: Apel Pagi, Kajian Bulanan" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50" />
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Kegiatan *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="rutin" x-model="jenis" checked />
                            <span>Rutin (Berulang per pekan)</span>
                        </label>
                        <label class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="khusus" x-model="jenis" />
                            <span>Khusus (Insidental)</span>
                        </label>
                    </div>
                </div>

                <div x-show="jenis === 'rutin'" class="space-y-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300">Pilih Hari Rutin *</label>
                    <select name="hari" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50">
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                            <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="jenis === 'khusus'" x-cloak class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tgl Mulai *</label>
                        <input type="date" name="tanggal_mulai" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tgl Selesai</label>
                        <input type="date" name="tanggal_selesai" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50" />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
                    <input type="text" name="lokasi" placeholder="Contoh: Masjid Utama, Lapangan, Aula Lt. 2" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50" />
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi Tambahan</label>
                    <textarea name="deskripsi" rows="2" placeholder="Catatan kegiatan..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold bg-blue-600 hover:bg-blue-700">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>
</div>
