<x-app-layout title="Monitoring Asrama">
    @php
        $viewMode = request('view', 'grid');
    @endphp
    <!-- Header Halaman -->
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Monitoring Asrama']
        ]"
        title="Monitoring Asrama"
        description="Pantau kapasitas asrama dan ketersediaan kamar secara real-time."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('fasilitas.penempatan.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Penempatan Mahasantri
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- 1. Statistik Agregat Kapasitas & Okupansi -->
    <x-fasilitas.monitoring.stats
        :totalGedung="$totalGedung"
        :totalKamar="$totalKamar"
        :totalKapasitas="$totalKapasitas"
        :totalTerisi="$totalTerisi"
        :sisaBed="$sisaBed"
        :okupansiPersen="$okupansiPersen"
        :kamarPerbaikan="$kamarPerbaikan"
        :hasActiveFilter="$hasActiveFilter"
    />

    <!-- 2. Switcher Mode Tampilan (Grid Kamar vs Tabel Flat via Atomic Tab Switcher) -->
    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
        <x-tab-switcher
            :tabs="[
                ['id' => 'grid', 'label' => 'Grid Kamar', 'short_label' => 'Grid', 'url' => request()->fullUrlWithQuery(['view' => 'grid'])],
                ['id' => 'table', 'label' => 'Tabel Flat', 'short_label' => 'Tabel', 'url' => request()->fullUrlWithQuery(['view' => 'table'])],
            ]"
            :active="$viewMode"
            color="blue"
        />
    </div>

    <!-- 3. Toolbar Pencarian & Filter -->
    <x-fasilitas.monitoring.filters :gedungList="$gedungList" :totalKamar="$totalKamar" :kamarList="$kamarList" />

    <!-- 4. Area Konten Berdasarkan View Mode -->
    @if($viewMode === 'table')
        <x-fasilitas.monitoring.table :kamarList="$kamarList" />
    @else
        <x-fasilitas.monitoring.grid :kamarList="$kamarList" />
    @endif

    <!-- 5. Modal Detail Kamar AJAX (Blade Component) -->
    <x-room-detail-modal />
</x-app-layout>
