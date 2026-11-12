<!-- TAB IMPORT EXCEL PER ROLE -->
<div x-show="activeTab === 'excel'" class="space-y-4 max-w-xl" x-data="{ importRole: 'mahasantri' }">
    <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-violet-50/70 dark:bg-violet-950/30 border border-violet-200/60 dark:border-violet-900/40 text-[11px] text-violet-800 dark:text-violet-300">
        <span>📊</span>
        <span><strong>Import Data Massal:</strong> Pilih peran tujuan, unduh template khusus untuk peran tersebut, lalu unggah berkas spreadsheet.</span>
    </div>

    <!-- Pilih Peran & Download Template -->
    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
        <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                Pilih Peran Pengguna yang Diunggah <span class="text-rose-500">*</span>
            </label>
            <select x-model="importRole" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-semibold focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition">
                <option value="mahasantri">🎓 Mahasantri Asrama (NIM, Nama, L/P, Prodi, Angkatan)</option>
                <option value="mudabbir">⭐ Mudabbir Pembina (NIM, Nama, L/P, Prodi, Angkatan)</option>
                <option value="tutor">👨‍🏫 Tutor Pengajar (NIP/NIDN/NIM, Nama, Unit/Fakultas, HP)</option>
                <option value="admin">🛡️ Administrator (NIP/ID, Nama, Email, Password)</option>
            </select>
        </div>

        <!-- Kolom yang Diharapkan (Keterangan Cerdas) -->
        <div class="p-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-700/80 text-[11px] space-y-1">
            <span class="font-bold text-slate-700 dark:text-slate-300 block">Kolom yang Disediakan dalam Template:</span>
            <div class="font-mono text-[10px] text-slate-500 dark:text-slate-400">
                <span x-show="importRole === 'mahasantri'">nim, nama, jenis_kelamin (L/P), prodi, angkatan, no_hp, email, password</span>
                <span x-show="importRole === 'mudabbir'">nim, nama, jenis_kelamin (L/P), prodi, angkatan, no_hp, email, password</span>
                <span x-show="importRole === 'tutor'">nip_nim, nama, jenis_kelamin, unit_prodi, no_hp, email, password</span>
                <span x-show="importRole === 'admin'">nip_id, nama, email, no_hp, password</span>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2 pt-1">
            <a :href="'{{ route('users.import.template') }}?role=' + importRole"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 transition shadow-2xs cursor-pointer">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Template Khusus (.xlsx)</span>
            </a>
            <a href="{{ route('users.import.template', ['role' => 'all']) }}" class="text-[11px] text-slate-500 hover:text-violet-600 transition underline underline-offset-2">
                Unduh Semua Role (Multi-Sheet)
            </a>
        </div>
    </div>

    <!-- Form Unggah Berkas -->
    <form method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <input type="hidden" name="role" :value="importRole">

        <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                Pilih Berkas Spreadsheet (.xlsx / .csv) <span class="text-rose-500">*</span>
            </label>
            <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                class="w-full text-xs rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 transition cursor-pointer">
        </div>

        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 active:scale-[0.99] transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Unggah & Proses Import Data</span>
        </button>
    </form>
</div>
