<!-- Danger Zone: Reset Database Bersih -->
<div 
    x-data="{ 
        showModal: false, 
        adminPassword: '', 
        confirmation: '',
        isValid() {
            return this.adminPassword.length > 0 && this.confirmation.trim() === 'RESET-DATABASE';
        }
    }" 
    class="rounded-2xl border border-rose-200 dark:border-slate-800 bg-rose-50/30 dark:bg-slate-900/80 p-5 sm:p-6 shadow-2xs"
>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center size-6 rounded-lg bg-rose-100 dark:bg-slate-800 text-rose-700 dark:text-rose-300 text-xs font-black">
                    ⚠️
                </span>
                <h3 class="text-sm font-bold text-rose-800 dark:text-rose-200 uppercase tracking-wider">
                    Zona Bahaya: Reset Database Bersih (Fresh Starter)
                </h3>
            </div>
            <p class="text-xs text-rose-700/80 dark:text-rose-300/70 max-w-3xl leading-relaxed">
                Tindakan ini akan mengosongkan seluruh data fasilitas (gedung, lantai, kamar), seluruh kelas, riwayat absensi, kegiatan, serta catatan perizinan dan pelanggaran. Sistem akan disetel ulang ke kondisi awal dengan 1 akun Administrator dan 1 akun sampel per peran (Tutor Dosen, Mudabbir, Mahasantri Putra & Putri).
            </p>
        </div>

        <button 
            type="button" 
            @click="showModal = true" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all cursor-pointer shrink-0"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Reset Database Sekarang</span>
        </button>
    </div>

    <!-- Modal Konfirmasi Keamanan Ketat -->
    <template x-teleport="body">
        <div 
            x-show="showModal" 
            x-cloak 
            data-modal-portal="true"
            class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-3 sm:p-4"
            @keydown.escape.window="showModal = false"
        >
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 transition-opacity"
                @click="showModal = false"
            ></div>

            <div 
                x-show="showModal" 
                x-transition:enter="ease-out duration-200" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100" 
                x-transition:leave="ease-in duration-150" 
                x-transition:leave-start="opacity-100 scale-100" 
                x-transition:leave-end="opacity-0 scale-95" 
                @click.stop
                class="relative w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden p-6 space-y-4 z-10"
            >
            <div class="flex items-start gap-3">
                <div class="size-10 rounded-xl bg-rose-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200 dark:border-slate-700">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Konfirmasi Keamanan Reset Database</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi Administrator dan ketik teks konfirmasi untuk melanjutkan.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.cache.reset.database') }}" class="space-y-4">
                @csrf
                
                <div class="space-y-1.5">
                    <label for="modal_admin_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Kata Sandi Administrator Anda
                    </label>
                    <input 
                        id="modal_admin_password" 
                        type="password" 
                        name="admin_password" 
                        x-model="adminPassword" 
                        required 
                        placeholder="Masukkan password admin aktif"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500"
                    />
                </div>

                <div class="space-y-1.5">
                    <label for="modal_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Ketik <span class="font-mono text-rose-600 dark:text-rose-400 font-bold select-all">RESET-DATABASE</span> di bawah:
                    </label>
                    <input 
                        id="modal_confirmation" 
                        type="text" 
                        name="confirmation" 
                        x-model="confirmation" 
                        required 
                        placeholder="RESET-DATABASE"
                        class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500 uppercase"
                    />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="showModal = false" 
                        class="px-3.5 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        :disabled="!isValid()" 
                        class="px-4 py-2 text-xs font-bold rounded-xl text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-all cursor-pointer"
                    >
                        Saya Mengerti, Hapus & Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
</div>
