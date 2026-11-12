<x-app-layout>
    <x-slot name="title">{{ $kelompok->nama }} &mdash; Detail Kelompok</x-slot>

    @php
        $backUrl = auth()->user()->hasRole('mudabbir') ? route('dashboard') : route('kelompok.index');
        $backLabel = auth()->user()->hasRole('mudabbir') ? 'Dashboard' : 'Kelompok Kajian';
    @endphp

    <div class="space-y-4 sm:space-y-5 max-w-7xl mx-auto">
        {{-- Header --}}
        <x-page-header
            :breadcrumbs="[
                ['label' => $backLabel, 'url' => $backUrl],
                ['label' => $kelompok->nama]
            ]"
            title="{{ $kelompok->nama }}"
            description="{{ $kelompok->anggota->count() }} / {{ $kelompok->kapasitas }} santri terdaftar{{ $kelompok->keterangan ? ' • ' . $kelompok->keterangan : '' }}"
        >
            <x-slot:actions>
                <a href="{{ $backUrl }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                @hasrole('admin')
                    <button type="button" @click="$dispatch('open-modal', 'modal-edit-kelompok')"
                        class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs cursor-pointer">
                        Edit Kelompok
                    </button>
                    <a href="{{ route('kelompok.anggota.create', $kelompok) }}"
                        onclick="try{sessionStorage.removeItem('kelompok_{{ $kelompok->id }}_selected');}catch(e){}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-98 transition shadow-xs cursor-pointer">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Santri
                    </a>
                @endhasrole
            </x-slot:actions>
        </x-page-header>

        {{-- 1. Tabel Kajian & Tutor Pengampu (Partial) --}}
        @include('kelompok.partials.kajian-table')

        {{-- 2. Tabel Anggota Santri dengan Search & Pagination 40/hal (Partial) --}}
        @include('kelompok.partials.anggota-table')
    </div>

    @hasrole('admin')
        @include('kelompok.partials.modals')
    @endhasrole

    <script>
        try { sessionStorage.removeItem('kelompok_{{ $kelompok->id }}_selected'); } catch (e) {}
        window.addEventListener('pageshow', function() {
            try { sessionStorage.removeItem('kelompok_{{ $kelompok->id }}_selected'); } catch (e) {}
        });
    </script>
</x-app-layout>
