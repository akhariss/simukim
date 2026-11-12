@props([
    'title' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs overflow-hidden']) }}>
    {{-- Optional Toolbar / Header Slot --}}
    @if(isset($toolbar))
        <div class="p-3 sm:p-4 bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800">
            {{ $toolbar }}
        </div>
    @elseif($title)
        <div class="p-3 sm:p-4 bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
            <span class="text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">{{ $title }}</span>
            @if($badge)
                <span class="px-2 py-0.5 rounded-full text-[9.5px] sm:text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ $badge }}</span>
            @endif
        </div>
    @endif

    {{-- ── DESKTOP VIEW (md+) ──────────────────────────────────── --}}
    @if(isset($desktop))
        <div class="hidden md:block">
            {{ $desktop }}
        </div>
    @endif

    {{-- ── MOBILE CARD VIEW (< md) — HARAM SCROLL HORIZONTAL ─────── --}}
    @if(isset($mobile))
        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            {{ $mobile }}
        </div>
    @endif

    {{-- Fallback Default Slot --}}
    {{ $slot }}

    {{-- Paginasi Slot --}}
    @if(isset($pagination))
        <div class="px-3.5 py-2.5 sm:px-4 sm:py-3 bg-slate-50/75 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800">
            {{ $pagination }}
        </div>
    @endif
</div>

