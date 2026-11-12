<x-app-layout title="Kelola Asrama">
    @php
        $activeTab = request('tab', 'gedung');
    @endphp

    <!-- Header Halaman -->
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Kelola Asrama']
        ]"
        title="Kelola Asrama"
        description="Manajemen data infrastruktur gedung, unit lantai, kamar asrama, dan generator massal."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($activeTab === 'gedung')
                    <button
                        type="button"
                        @click="$dispatch('open-add-gedung')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Gedung
                    </button>
                @elseif($activeTab === 'lantai')
                    <button
                        type="button"
                        @click="$dispatch('open-add-lantai')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Lantai
                    </button>
                @else
                    <button
                        type="button"
                        @click="$dispatch('open-add-kamar')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Kamar
                    </button>
                @endif
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Tab Switcher Navigasi: Gedung, Lantai, Kamar (Atomic Tab Switcher) -->
    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
        <x-tab-switcher
            :tabs="[
                ['id' => 'gedung', 'label' => 'Kelola Gedung', 'short_label' => 'Gedung', 'count' => $allGedung->count(), 'url' => route('fasilitas.kelola.index', ['tab' => 'gedung'])],
                ['id' => 'lantai', 'label' => 'Kelola Lantai', 'short_label' => 'Lantai', 'count' => $totalLantaiCount, 'url' => route('fasilitas.kelola.index', ['tab' => 'lantai'])],
                ['id' => 'kamar', 'label' => 'Kelola Kamar', 'short_label' => 'Kamar', 'count' => $totalKamarCount, 'url' => route('fasilitas.kelola.index', ['tab' => 'kamar'])],
            ]"
            :active="$activeTab"
            color="blue"
        />
    </div>

    <!-- Area Konten Tab -->
    @if($activeTab === 'kamar')
        <x-fasilitas.kelola.kamar-tab :kamarList="$kamarList" :gedungList="$gedungList" :allGedung="$allGedung" :roomStats="$roomStats" :stats="$roomStats" :totalPenghuni="$totalPenghuni" :viewMode="$viewMode" />
    @elseif($activeTab === 'lantai')
        <x-fasilitas.kelola.lantai-tab :lantaiList="$lantaiList" :allGedung="$allGedung" />
    @else
        <x-fasilitas.kelola.gedung-tab :gedungList="$gedungList" />
    @endif

    <!-- Modals -->
    <x-fasilitas.kelola.modal-gedung />
    <x-fasilitas.kelola.modal-lantai :allGedung="$allGedung" />
    <x-fasilitas.kelola.modal-edit-lantai />
    <x-fasilitas.kelola.modal-kamar :allGedung="$allGedung" />
    <x-fasilitas.kelola.modal-batch :allGedung="$allGedung" />
    <x-room-detail-modal />
</x-app-layout>
