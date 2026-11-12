<x-app-layout>
    <x-slot name="title">{{ $kelas->nama }} — {{ $kelompok->nama }}</x-slot>

    @php
        $kelompokIndexUrl = auth()->user()->hasRole('mudabbir') ? route('dashboard') : route('kelompok.index');
        $kelompokIndexLabel = auth()->user()->hasRole('mudabbir') ? 'Dashboard' : 'Kelompok Kajian';
    @endphp

    <div class="space-y-4 sm:space-y-5 max-w-7xl mx-auto">
        <!-- 1. BREADCRUMBS & HEADER -->
        <x-page-header
            :breadcrumbs="[
                ['label' => $kelompokIndexLabel, 'url' => $kelompokIndexUrl],
                ['label' => $kelompok->nama, 'url' => route('kelompok.show', $kelompok)],
                ['label' => $kelas->nama]
            ]"
            title="{{ $kelas->nama }}"
            description="Rombel: {{ $kelompok->nama }} • {{ $kelompok->anggota->count() }} santri aktif • {{ $kelompok->periode?->nama ?? '-' }}"
        >
            <x-slot:actions>
                <a href="{{ route('kelompok.show', $kelompok) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                @hasrole('admin|tutor')
                    <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $kelompok->id, 'kelas_id' => $kelas->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-98 transition shadow-xs">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Presensi Kajian</span>
                    </a>
                @endhasrole
            </x-slot:actions>
        </x-page-header>

        <!-- NOTIFIKASI STATUS -->
        @if (session('success'))
            <div class="p-3.5 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-slate-800 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-slate-700 flex items-center gap-2">
                <svg class="size-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="p-3.5 rounded-xl text-xs font-semibold bg-rose-50 dark:bg-slate-800 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-slate-700 flex items-center gap-2">
                <svg class="size-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 2. KONTEN DETAIL: GRID 2 KOLOM -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5">
            <!-- KOLOM UTAMA (2/3) -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-5">
                <!-- KARTU 1: MANAJEMEN TUTOR PENGAMPU (SEARCHABLE COMBOBOX) -->
                @include('kelompok.kajian.partials.tutor-manager')

                <!-- KARTU 2: ANGGOTA MAHASANTRI PENGIKUT KAJIAN (PARTIAL) -->
                @include('kelompok.kajian.partials.santri-list')
            </div>

            <!-- KOLOM SAMPING (1/3) -->
            @include('kelompok.kajian.partials.sidebar-info')
        </div>
    </div>
</x-app-layout>
