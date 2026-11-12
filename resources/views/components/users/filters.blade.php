@props([
    'perPage' => request('per_page', 25),
    'counts' => [],
    'users' => null,
])

@php
    $currentRole = request('role');
    $hasActiveFilters = request()->hasAny(['search', 'role']);

    $roleTabs = [
        [
            'id' => '',
            'label' => 'Semua',
            'short_label' => 'Semua',
            'count' => $counts['all'] ?? $users->total(),
            'url' => route('users.index', array_merge(request()->except('page', 'role'))),
        ],
        [
            'id' => 'mahasantri',
            'label' => 'Mahasantri',
            'short_label' => 'Santri',
            'count' => $counts['mahasantri'] ?? 0,
            'url' => route('users.index', array_merge(request()->except('page'), ['role' => 'mahasantri'])),
        ],
        [
            'id' => 'mudabbir',
            'label' => 'Mudabbir',
            'short_label' => 'Mudabbir',
            'count' => $counts['mudabbir'] ?? 0,
            'url' => route('users.index', array_merge(request()->except('page'), ['role' => 'mudabbir'])),
        ],
        [
            'id' => 'tutor',
            'label' => 'Tutor Pengajar',
            'short_label' => 'Tutor',
            'count' => $counts['tutor'] ?? 0,
            'url' => route('users.index', array_merge(request()->except('page'), ['role' => 'tutor'])),
        ],
        [
            'id' => 'admin',
            'label' => 'Admin',
            'short_label' => 'Admin',
            'count' => $counts['admin'] ?? 0,
            'url' => route('users.index', array_merge(request()->except('page'), ['role' => 'admin'])),
        ],
    ];
@endphp

<!-- SMART SEARCH & FILTER RESMI -->
<div class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs space-y-2.5">
    <!-- 1. KOTAK PENCARIAN -->
    <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-2">
        @if(request()->filled('role'))
            <input type="hidden" name="role" value="{{ request('role') }}">
        @endif
        @if(request()->filled('periode_filter'))
            <input type="hidden" name="periode_filter" value="{{ request('periode_filter') }}">
        @endif
        <input type="hidden" name="per_page" value="{{ $perPage }}">

        <div class="flex-1">
            <x-search-input
                name="search"
                :value="request('search')"
                placeholder="Ketik Nama, NIM, NIP, atau Email pengguna..."
            />
        </div>

        <button
            type="submit"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition cursor-pointer flex items-center justify-center gap-1 shrink-0"
        >
            <span>Cari</span>
        </button>

        @if($hasActiveFilters)
            <a
                href="{{ route('users.index') }}"
                class="px-2.5 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-center shrink-0"
                title="Reset Semua Filter"
            >
                Reset
            </a>
        @endif
    </form>

    <!-- 2. ROLE SWITCHER & PER-PAGE DROPDOWN -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
        <x-tab-switcher
            :tabs="$roleTabs"
            :active="$currentRole ?? ''"
            color="blue"
        />

        <div class="flex items-center justify-end gap-2 shrink-0">
            <form method="GET" action="{{ route('users.index') }}">
                @if(request('role')) <input type="hidden" name="role" value="{{ request('role') }}"> @endif
                @if(request('periode_filter')) <input type="hidden" name="periode_filter" value="{{ request('periode_filter') }}"> @endif
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                <select name="per_page" onchange="this.form.requestSubmit()" class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 / hal</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / hal</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / hal</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / hal</option>
                </select>
            </form>
        </div>
    </div>
</div>
