@props([
    'tabs' => [],
    'model' => 'activeTab',
    'active' => null,
    'color' => 'emerald',
])

@php
    $activeBgClasses = [
        'emerald' => 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs',
        'blue' => 'bg-white dark:bg-slate-700 text-blue-700 dark:text-blue-400 shadow-xs',
        'rose' => 'bg-white dark:bg-slate-700 text-rose-700 dark:text-rose-400 shadow-xs',
        'purple' => 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-400 shadow-xs',
        'amber' => 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-400 shadow-xs',
    ][$color] ?? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-1 overflow-x-auto no-scrollbar p-0.5 sm:p-1 rounded-xl bg-slate-200/60 dark:bg-slate-800/80 border border-slate-300/60 dark:border-slate-700/80 text-xs font-semibold shadow-2xs w-full sm:w-auto shrink-0 scroll-smooth']) }}>
    @foreach($tabs as $tab)
        @php
            $tabId = $tab['id'] ?? $tab['key'] ?? '';
            $label = $tab['label'];
            $shortLabel = $tab['short_label'] ?? $label;
            $count = $tab['count'] ?? null;
            $icon = $tab['icon'] ?? null;
            $url = $tab['url'] ?? null;
            $isActive = $active !== null ? ($active === $tabId) : false;
        @endphp

        @if($url)
            <a
                href="{{ $url }}"
                class="shrink-0 inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg transition text-center select-none whitespace-nowrap text-[11px] sm:text-xs font-semibold {{ $isActive ? $activeBgClasses : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}"
            >
                @if($icon)
                    <span class="shrink-0">{!! $icon !!}</span>
                @endif
                <span class="sm:hidden">{{ $shortLabel }}</span>
                <span class="hidden sm:inline">{{ $label }}</span>
                @if($count !== null)
                    <span class="px-1.5 py-0.2 rounded-full text-[9px] sm:text-[10px] {{ $isActive ? 'bg-slate-100 dark:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold' : 'bg-slate-300/60 dark:bg-slate-700 text-slate-600 dark:text-slate-400 font-semibold' }}">
                        {{ $count }}
                    </span>
                @endif
            </a>
        @else
            <button
                type="button"
                @click="{{ $model }} = '{{ $tabId }}'; $dispatch('tab-changed', '{{ $tabId }}')"
                :class="{{ $model }} === '{{ $tabId }}' ? '{{ $activeBgClasses }}' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                class="shrink-0 inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg transition cursor-pointer text-center select-none whitespace-nowrap text-[11px] sm:text-xs font-semibold"
            >
                @if($icon)
                    <span class="shrink-0">{!! $icon !!}</span>
                @endif
                <span class="sm:hidden">{{ $shortLabel }}</span>
                <span class="hidden sm:inline">{{ $label }}</span>
                @if($count !== null)
                    <span class="px-1.5 py-0.2 rounded-full text-[9px] sm:text-[10px] {{ $model }} === '{{ $tabId }}' ? 'bg-slate-100 dark:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold' : 'bg-slate-300/60 dark:bg-slate-700 text-slate-600 dark:text-slate-400 font-semibold'">
                        {{ $count }}
                    </span>
                @endif
            </button>
        @endif
    @endforeach
</div>
