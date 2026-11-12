@php
    $currentStatus = request('status', '');
    $statusTabs = [
        [
            'id' => '',
            'label' => 'Semua Kasus',
            'short_label' => 'Semua',
            'count' => $stats->total_kasus ?? $pelanggaranList->total(),
            'url' => route('kedisiplinan.pelanggaran.index', array_merge(request()->except('page', 'status'))),
        ],
        [
            'id' => 'terdaftar',
            'label' => 'Belum Dilaksanakan',
            'short_label' => 'Belum Selesai',
            'count' => $stats->total_terdaftar ?? 0,
            'url' => route('kedisiplinan.pelanggaran.index', array_merge(request()->except('page'), ['status' => 'terdaftar'])),
        ],
        [
            'id' => 'selesai',
            'label' => 'Selesai Dilaksanakan',
            'short_label' => 'Selesai',
            'count' => $stats->total_selesai ?? 0,
            'url' => route('kedisiplinan.pelanggaran.index', array_merge(request()->except('page'), ['status' => 'selesai'])),
        ],
    ];
@endphp

<x-app-layout title="Kedisiplinan & Pelanggaran">
    <div class="space-y-4 sm:space-y-5">
        <!-- Header Halaman -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kedisiplinan & Pelanggaran']
            ]"
            title="Kedisiplinan & Pelanggaran"
            description="Pencatatan tata tertib, tindak lanjut sanksi santri, dan pemantauan disiplin asrama."
        >
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    @if(auth()->user()?->hasRole('admin'))
                        <button
                            type="button"
                            @click="$dispatch('open-export-pelanggaran')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Ekspor Data</span>
                        </button>
                    @endif
                    <button
                        type="button"
                        @click="$dispatch('open-add-pelanggaran')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Catat Pelanggaran</span>
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- 1. Statistik Agregat Dinamis Sesuai Filter -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
            <x-stat-card label="Total Kasus" :value="number_format($stats->total_kasus ?? 0)" unit="Kejadian" :description="request()->hasAny(['status', 'jenis_id', 'search']) ? 'Hasil filter aktif' : 'Total periode aktif'">
                <x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon>
            </x-stat-card>
            <x-stat-card label="Kasus Bulan Ini" :value="number_format($stats->total_bulan_ini ?? 0)" unit="Kasus" description="Pelanggaran bulan berjalan" color="blue">
                <x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></x-slot:icon>
            </x-stat-card>
            <x-stat-card label="Belum Dilaksanakan" :value="number_format($stats->total_terdaftar ?? 0)" unit="Kasus" description="Menunggu tindak lanjut" color="amber">
                <x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
            </x-stat-card>
            <x-stat-card label="Selesai Dilaksanakan" :value="number_format($stats->total_selesai ?? 0)" unit="Kasus" description="Sanksi telah dituntaskan" color="emerald">
                <x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
            </x-stat-card>
        </div>

        <!-- 2. Radar Disiplin: Santri Perlu Perhatian -->
        <x-kedisiplinan.top-violators-card :topViolators="$topViolators" />

        <!-- 3. Toolbar Filter Sesuai Token & Desain Atomik -->
        <div class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs space-y-2.5">
            <!-- Form Pencarian & Kategori -->
            <form method="GET" action="{{ route('kedisiplinan.pelanggaran.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="flex-1">
                    <x-search-input
                        name="search"
                        :value="request('search', '')"
                        placeholder="Ketik nama santri, NIM, atau sanksi..."
                    />
                </div>
                <div class="flex items-center gap-2">
                    <select
                        name="jenis_id"
                        onchange="this.form.requestSubmit()"
                        class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer max-w-[160px] truncate"
                    >
                        <option value="">Semua Tingkat</option>
                        @foreach($jenisList as $j)
                            @php
                                $tingkat = match(true) {
                                    $j->poin_default <= 5 || str_contains(strtolower($j->nama), 'ringan') => 'Pelanggaran Ringan',
                                    $j->poin_default >= 20 || str_contains(strtolower($j->nama), 'berat') => 'Pelanggaran Berat',
                                    default => 'Pelanggaran Sedang',
                                };
                            @endphp
                            <option value="{{ $j->id }}" {{ request('jenis_id') == $j->id ? 'selected' : '' }}>{{ $tingkat }}</option>
                        @endforeach
                    </select>

                    <button
                        type="submit"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition cursor-pointer shrink-0"
                    >
                        Cari
                    </button>

                    @if(request()->hasAny(['status', 'jenis_id', 'search']))
                        <a
                            href="{{ route('kedisiplinan.pelanggaran.index') }}"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-center shrink-0"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Compact Status Switcher Ribbon (Mobile Friendly, No-Wrap) -->
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex-1 min-w-0">
                    <x-tab-switcher
                        :tabs="$statusTabs"
                        :active="$currentStatus"
                        color="rose"
                    />
                </div>
                <span class="hidden sm:inline text-[11px] text-slate-400 shrink-0 text-right">
                    Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ $pelanggaranList->total() }}</strong> kasus
                </span>
            </div>
        </div>

        <!-- 4. Tabel Data Desktop vs Stacked Mobile Cards -->
        <x-kedisiplinan.pelanggaran-table :pelanggaranList="$pelanggaranList" :activePeriode="$activePeriode" />

        <!-- 5. Modal Catat Pelanggaran -->
        <x-kedisiplinan.pelanggaran-modal :activePeriode="$activePeriode" :santriList="$santriList" :jenisList="$jenisList" />

        <!-- 6. Modal Detail Pelanggaran Santri -->
        <x-kedisiplinan.pelanggaran-detail-modal />

        <!-- 7. Modal Edit Pelanggaran Santri -->
        <x-kedisiplinan.pelanggaran-edit-modal :jenisList="$jenisList" />

        <!-- 8. Modal Ekspor Data Pelanggaran -->
        @if(auth()->user()?->hasRole('admin'))
            <x-kedisiplinan.export-dialog dialogId="pelanggaran" title="Ekspor Data Kedisiplinan" :action="route('kedisiplinan.pelanggaran.export')" :activePeriode="$activePeriode" />
        @endif
    </div>
</x-app-layout>
