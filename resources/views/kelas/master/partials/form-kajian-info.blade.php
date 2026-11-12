{{-- 1. Identitas & Informasi Kajian --}}
<div class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs space-y-4">
    <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white">1. Informasi Kajian</h2>
        <p class="text-xs text-slate-400">Tentukan nama, klasifikasi materi, dan siklus pertemuan.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2 space-y-1">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Kajian *</label>
            <input
                type="text"
                name="nama"
                x-model="nama"
                required
                placeholder="Contoh: Tahsin Al-Qur'an / Kajian Kitab Fathul Qorib"
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
            >
            @error('nama') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tipe Kajian *</label>
            <select
                name="tipe"
                x-model="tipe"
                required
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 text-slate-900 dark:text-white font-medium"
            >
                <option value="kelompok">Kajian Kelompok</option>
                <option value="masal">Kajian Masal</option>
                <option value="lainnya">Lainnya</option>
            </select>
            @error('tipe') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Siklus Pertemuan</label>
            <select
                name="siklus"
                x-model="siklus"
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 text-slate-900 dark:text-white font-medium"
            >
                <option value="minggu">Per Minggu (Standar)</option>
                <option value="bulan">Per Bulan</option>
            </select>
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Jumlah Pertemuan *</label>
            <input
                type="number"
                name="jumlah_pertemuan"
                x-model.number="jumlahPertemuan"
                @input="updateJumlahPertemuan($event.target.value)"
                min="1"
                max="14"
                required
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 text-slate-900 dark:text-white font-bold"
            >
        </div>
    </div>

    <div class="space-y-1">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Deskripsi / Silabus Materi (Opsional)</label>
        <textarea
            name="deskripsi"
            x-model="deskripsi"
            rows="2"
            placeholder="Ringkasan kurikulum, target capaian materi, atau referensi kitab yang dikaji..."
            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 text-slate-900 dark:text-white"
        ></textarea>
    </div>
</div>
