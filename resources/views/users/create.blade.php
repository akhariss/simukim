<x-app-layout title="Tambah Pengguna — SIMUKIM — Ma'had Al-Jami'ah">
    <div class="space-y-3.5 sm:space-y-4" x-data="{
        activeTab: 'single',
        role: @js((string) old('role', 'mahasantri')),
        authSource: @js((string) old('auth_source', 'campus')),
        nomorInduk: @js((string) old('nomor_induk', '')),
        name: @js((string) old('name', '')),
        gender: @js((string) old('gender', 'L')),
        prodi: @js((string) old('prodi', '')),
        email: @js((string) old('email', '')),
        showPassword: false,
        loading: false,
        lookupMsg: '',
        lookupFound: false,
        lookup() {
            let val = (this.nomorInduk || '').trim();
            if (!val) return;
            this.loading = true;
            this.lookupMsg = '';
            this.lookupFound = false;
            fetch('{{ route('users.campus.lookup') }}?id=' + encodeURIComponent(val))
                .then(r => r.json())
                .then(res => {
                    this.loading = false;
                    if (res.found && res.data) {
                        this.lookupFound = true;
                        if (!this.name || this.name.trim() === '') {
                            this.name = res.data.nama || '';
                        }
                        if (res.data.gender && (!this.gender || this.gender === '')) {
                            this.gender = res.data.gender;
                        }
                        if (res.data.prodi && (!this.prodi || this.prodi === '')) {
                            this.prodi = res.data.prodi;
                        }
                        if (res.data.local_registered) {
                            this.lookupMsg = 'Akun sudah terdaftar di Ma\'had (' + (res.data.local_role || 'pengguna') + ').';
                        } else {
                            this.lookupMsg = 'Data kampus terverifikasi: ' + res.data.nama;
                        }
                    } else {
                        this.lookupFound = false;
                        this.lookupMsg = 'Data belum ada di server kampus. Silakan ketik nama langsung secara manual.';
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.lookupFound = false;
                    this.lookupMsg = 'Pengecekan server kampus offline. Anda dapat mengisi nama secara manual.';
                });
        }
    }">
        <!-- 1. MODULAR PAGE HEADER -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kelola Pengguna', 'url' => route('users.index')],
                ['label' => 'Tambah Pengguna Baru']
            ]"
            title="Tambah Pengguna Baru"
            description="Pendaftaran cepat akun pengguna baru atau import data massal via Excel."
        >
            <x-slot:actions>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- 2. PILIHAN METODE TAMBAH (COMPACT SEGMENTED PILLS) -->
        <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 w-fit">
            <button type="button" @click="activeTab = 'single'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                :class="activeTab === 'single' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Formulir Satuan</span>
            </button>
            <button type="button" @click="activeTab = 'excel'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                :class="activeTab === 'excel' ? 'bg-white dark:bg-slate-900 text-violet-600 dark:text-violet-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Import Excel / CSV</span>
            </button>
        </div>

        <!-- 3. CONTAINER FORM -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
            @include('users.partials.form-create-single')
            @include('users.partials.tab-excel')
        </div>
    </div>
</x-app-layout>
