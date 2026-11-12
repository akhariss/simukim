<x-app-layout>
    <x-slot name="title">Agenda & Kegiatan Asrama</x-slot>

    <div class="space-y-4 sm:space-y-5">
        <!-- 1. ATOMIC PAGE HEADER (Standar Theme Aplikasi Sesuai Rules) -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Agenda & Kegiatan']
            ]"
            title="Agenda & Jadwal Terpadu"
            description="Kalender terpadu aktivitas ma'had: jadwal kelas akademik dan kegiatan umum asrama."
        >
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <!-- View Switcher -->
                    <x-tab-switcher
                        :tabs="[
                            ['id' => 'list', 'label' => 'Daftar', 'short_label' => 'Daftar', 'url' => route('kegiatan.index', array_merge(request()->query(), ['view' => 'list']))],
                            ['id' => 'calendar', 'label' => 'Kalender', 'short_label' => 'Kalender', 'url' => route('kegiatan.index', array_merge(request()->query(), ['view' => 'calendar']))],
                        ]"
                        :active="$viewMode"
                        color="blue"
                    />

                    @hasrole('admin')
                        <button
                            type="button"
                            @click="$dispatch('open-modal-kegiatan')"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition shadow-xs cursor-pointer"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah Kegiatan</span>
                        </button>
                    @endhasrole
                </div>
            </x-slot:actions>
        </x-page-header>

        @if($viewMode === 'calendar')
            <!-- Calendar View Component Terpadu -->
            <x-kegiatan.calendar :all-events="$allEvents" :bulan="$bulan" :tahun="$tahun" />
        @else
            <!-- List View -->
            <div class="space-y-3">
                <!-- Filter Bar -->
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <form method="GET" action="{{ route('kegiatan.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <input type="hidden" name="view" value="list">
                        <div class="sm:col-span-6 lg:col-span-5">
                            <label for="search" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Cari Agenda / Kelas</label>
                            <x-search-input
                                id="search"
                                name="search"
                                :value="$search"
                                placeholder="Ketik nama kegiatan, kelas, lokasi..."
                                class="w-full max-w-none"
                            />
                        </div>

                        <div class="sm:col-span-4 lg:col-span-4">
                            <label for="jenis" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jenis Agenda</label>
                            <select
                                name="jenis"
                                id="jenis"
                                onchange="this.form.requestSubmit()"
                                class="w-full px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"
                            >
                                <option value="">Semua Agenda & Kelas</option>
                                <option value="kelas" {{ $jenisFilter === 'kelas' ? 'selected' : '' }}>Kelas Akademik ({{ $totalKelas }})</option>
                                <option value="rutin" {{ $jenisFilter === 'rutin' ? 'selected' : '' }}>Kegiatan Rutin Asrama ({{ $stats->rutin }})</option>
                                <option value="khusus" {{ $jenisFilter === 'khusus' ? 'selected' : '' }}>Khusus / Insidental ({{ $stats->khusus }})</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-2">
                            <button
                                type="submit"
                                class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer"
                            >
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Feed Jadwal & Agenda Terpadu (Responsif Desktop & Mobile) -->
                @include('kegiatan.table')

                @if($kegiatanList->hasPages())
                    <div class="mt-4">
                        {{ $kegiatanList->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Kegiatan (Admin Only) -->
    @hasrole('admin')
        <x-kegiatan.modal-create />
    @endhasrole
</x-app-layout>
