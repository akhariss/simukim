{{-- 2. Konfigurasi Jadwal Sesi Pertemuan Otomatis --}}
<div class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                2. Konfigurasi Jadwal Pertemuan (<span x-text="sesiList.length"></span> Sesi per <span x-text="siklus"></span>)
            </h2>
            <p class="text-xs text-slate-400">Atur hari, jam mulai-selesai, dan lokasi ruangan untuk setiap pertemuan.</p>
        </div>
        <button
            type="button"
            @click="tambahSesi()"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-xl transition"
        >
            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Sesi</span>
        </button>
    </div>

    <div class="space-y-3">
        <template x-for="(sesi, idx) in sesiList" :key="idx">
            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/70 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                        Sesi Pertemuan #<span x-text="idx + 1"></span>
                    </span>
                    <button
                        type="button"
                        x-show="sesiList.length > 1"
                        @click="hapusSesi(idx)"
                        class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition"
                    >
                        Hapus Sesi
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-1 space-y-1">
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">Nama Sesi</label>
                        <input
                            type="text"
                            :name="'sesi['+idx+'][nama_sesi]'"
                            x-model="sesi.nama_sesi"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-medium"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">Hari</label>
                        <select
                            :name="'sesi['+idx+'][hari]'"
                            x-model="sesi.hari"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-medium"
                        >
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">Jam Mulai</label>
                        <input
                            type="time"
                            :name="'sesi['+idx+'][jam_mulai]'"
                            x-model="sesi.jam_mulai"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-medium"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">Jam Selesai</label>
                        <input
                            type="time"
                            :name="'sesi['+idx+'][jam_selesai]'"
                            x-model="sesi.jam_selesai"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-medium"
                        >
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">Ruangan / Tempat Sesi (Opsional)</label>
                    <input
                        type="text"
                        :name="'sesi['+idx+'][ruangan]'"
                        x-model="sesi.ruangan"
                        placeholder="Contoh: Masjid Utama / Aula Lt. 1 / Kamar Binaan"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700"
                    >
                </div>
            </div>
        </template>
    </div>
</div>
