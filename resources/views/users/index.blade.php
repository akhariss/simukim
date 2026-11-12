<x-app-layout title="Kelola Pengguna">
    <div class="space-y-4 sm:space-y-5">
        <!-- 1. MODULAR PAGE HEADER -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kelola Pengguna']
            ]"
            title="Kelola Pengguna Sistem"
            description="Manajemen seluruh akun pengguna aplikasi Ma'had (Admin, Tutor, Mudabbir, dan Mahasantri)."
        >
            <x-slot:actions>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
                    <!-- Switcher Mode Periode -->
                    <x-tab-switcher
                        :tabs="[
                            ['id' => 'active', 'label' => 'Periode Aktif (' . ($activePeriode?->kode ?? 'Aktif') . ')', 'short_label' => 'Aktif', 'url' => request()->fullUrlWithQuery(['periode_filter' => 'active'])],
                            ['id' => 'all', 'label' => 'Semua Periode', 'short_label' => 'Semua', 'url' => request()->fullUrlWithQuery(['periode_filter' => 'all'])],
                        ]"
                        :active="$periodeFilter"
                        color="blue"
                    />

                    <!-- Tambah Pengguna Baru -->
                    <a
                        href="{{ route('users.create') }}"
                        class="w-full sm:w-auto px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs flex items-center justify-center gap-1.5 transition"
                        title="Buka Halaman Tambah Pengguna Baru"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Pengguna</span>
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- 2. FILTERS & SEARCH -->
        <x-users.filters :perPage="$perPage" :counts="$counts" :users="$users" />

        <!-- 3. TABLE DATA (Dengan Link Direct ke New Page Show & Edit) -->
        <x-users.table :users="$users" />
    </div>
</x-app-layout>
