@php
    $isBinaan = (bool) ($isSaya ?? false);
@endphp

{{-- Header Halaman Sesuai Konteks Role/Menu --}}
<x-page-header
    :breadcrumbs="[
        ['label' => $isBinaan ? 'Kelas Saya' : 'Manajemen Kajian'],
        ['label' => $isBinaan ? 'Nilai Santri' : 'Rekap Nilai']
    ]"
    title="{{ $isBinaan ? 'Penilaian Kelas Binaan Saya' : 'Rekapitulasi Nilai Hasil Belajar Santri' }}"
    description="{{ $isBinaan ? 'Pilih kelompok kajian binaan Anda untuk mengelola komponen nilai dan merekapitulasi nilai akhir santri.' : 'Pantau dan rekapitulasi penilaian hasil belajar santri lintas kelompok halaqah atau perorangan mahasantri.' }}"
/>

@if($isBinaan)
    {{-- TAMPILAN KHUSUS KELAS SAYA (TUTOR): Bersih, Fokus pada Kelas Binaan Sendiri --}}
    <div x-data="{
        isBinaan: true,
        matchesItem() { return true; }
    }">
        @include('kelas.nilai.partials.table-kelompok')
    </div>
@else
    {{-- TAMPILAN MANAJEMEN KAJIAN (ADMIN): Switch Per Kelompok / Per Mahasantri + Search & Filter Tanpa Refresh --}}
    <div x-data="{
        viewMode: 'kelompok',
        search: '',
        filterPeriode: '{{ $periode?->id }}',
        isBinaan: false,
        matchesItem(kelompok, kelas, jadwal, keywords, pId) {
            const matchP = !this.filterPeriode || this.filterPeriode === 'all' || this.filterPeriode == pId;
            if (!matchP) return false;
            const q = this.search.toLowerCase().trim();
            if (!q) return true;
            return (kelompok + ' ' + kelas + ' ' + jadwal + ' ' + keywords).toLowerCase().includes(q);
        },
        matchesSantri(nama, nim, kelompok, prodi, pId) {
            const matchP = !this.filterPeriode || this.filterPeriode === 'all' || this.filterPeriode == pId;
            if (!matchP) return false;
            const q = this.search.toLowerCase().trim();
            if (!q) return true;
            return (nama + ' ' + nim + ' ' + kelompok + ' ' + prodi).toLowerCase().includes(q);
        }
    }" class="space-y-4">

        {{-- Switch Tab & Toolbar Terpadu --}}
        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
            {{-- 1. Switch Mode: Per Kelompok vs Per Mahasantri --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 self-start">
                    <button type="button" @click="viewMode = 'kelompok'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                            :class="viewMode === 'kelompok' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Per Kelompok Halaqah</span>
                    </button>
                    <button type="button" @click="viewMode = 'santri'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                            :class="viewMode === 'santri' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Per Mahasantri</span>
                    </button>
                </div>
                <span class="text-[11px] text-slate-400 font-medium" x-text="viewMode === 'kelompok' ? 'Tampilan rekap per kelompok & mata kajian' : 'Tampilan rekap nilai per nama mahasantri'"></span>
            </div>

            {{-- 2. Input Pencarian (di atas filter semester) --}}
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search"
                       :placeholder="viewMode === 'kelompok' ? 'Cari nama santri, NIM, kelompok halaqah, atau mata kajian...' : 'Cari nama mahasantri, NIM, program studi, atau kelompok...'"
                       class="w-full pl-9 pr-8 py-2 rounded-xl text-xs border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/20">
                <button type="button" x-show="search" @click="search = ''" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- 3. Filter Semester (di bawah pencarian, tanpa refresh halaman) --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-semibold text-slate-500 shrink-0">Filter Semester:</span>
                    <select x-model="filterPeriode"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                        <option value="all">Semua Semester</option>
                        @foreach($allPeriode ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} {{ $p->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <span class="text-[10.5px] text-slate-400 font-medium">Filter instan tanpa refresh halaman</span>
            </div>
        </div>

        {{-- Konten Sesuai Mode Switch --}}
        <div x-show="viewMode === 'kelompok'">
            @include('kelas.nilai.partials.table-kelompok')
        </div>

        <div x-show="viewMode === 'santri'" x-cloak>
            @include('kelas.nilai.partials.table-santri')
        </div>
    </div>
@endif
