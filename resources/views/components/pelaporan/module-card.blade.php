@props([
    'tipe',
    'badgeTitle' => 'Modul',
    'badgeSub' => '',
    'title',
    'description',
    'color' => 'blue',
    'filterName' => null,
])

@php
    $colorMaps = [
        'blue' => [
            'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/50',
            'icon' => 'text-blue-600 dark:text-blue-400',
        ],
        'indigo' => [
            'badge' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/50',
            'icon' => 'text-indigo-600 dark:text-indigo-400',
        ],
        'rose' => [
            'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/50',
            'icon' => 'text-rose-600 dark:text-rose-400',
        ],
        'amber' => [
            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200/60 dark:border-amber-800/50',
            'icon' => 'text-amber-600 dark:text-amber-400',
        ],
    ][$color] ?? [
        'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/50',
        'icon' => 'text-blue-600 dark:text-blue-400',
    ];
@endphp

<div x-data="{
    filterVal: '',
    buildUrl(format) {
        let base = '{{ route('pelaporan.export') }}?tipe={{ $tipe }}&format=' + format;
        if (periodeId) base += '&periode_id=' + encodeURIComponent(periodeId);
        if (rentang) base += '&rentang=' + encodeURIComponent(rentang);
        if (bulan) base += '&bulan=' + encodeURIComponent(bulan);
        @if($filterName)
        if (this.filterVal) base += '&{{ $filterName }}=' + encodeURIComponent(this.filterVal);
        @endif
        return base;
    }
}" class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 flex flex-col justify-between shadow-2xs space-y-3">
    <div>
        <div class="flex items-center justify-between gap-2 mb-2">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10.5px] font-semibold border {{ $colorMaps['badge'] }}">
                @if($tipe === 'okupansi')
                    <svg class="size-3.5 {{ $colorMaps['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @elseif($tipe === 'nilai')
                    <svg class="size-3.5 {{ $colorMaps['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                @elseif($tipe === 'kehadiran')
                    <svg class="size-3.5 {{ $colorMaps['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @elseif($tipe === 'pelanggaran')
                    <svg class="size-3.5 {{ $colorMaps['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @elseif($tipe === 'perizinan')
                    <svg class="size-3.5 {{ $colorMaps['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                @endif
                <span>{{ $badgeTitle }}</span>
            </span>
            @if($badgeSub)
                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-semibold">
                    {{ $badgeSub }}
                </span>
            @endif
        </div>
        <h4 class="font-bold text-xs sm:text-[13px] text-slate-900 dark:text-white leading-snug">
            {{ $title }}
        </h4>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
            {{ $description }}
        </p>
    </div>

    <div class="space-y-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-xs">
        @if($slot->isNotEmpty())
            <div class="space-y-1">
                {{ $slot }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2 pt-1">
            <a
                :href="buildUrl('pdf')"
                target="_blank"
                class="inline-flex items-center justify-center gap-1.5 py-1.5 px-2.5 rounded-xl font-semibold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
            >
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh PDF</span>
            </a>
            <a
                :href="buildUrl('excel')"
                target="_blank"
                class="inline-flex items-center justify-center gap-1.5 py-1.5 px-2.5 rounded-xl font-semibold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition cursor-pointer"
            >
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Excel</span>
            </a>
        </div>
    </div>
</div>
