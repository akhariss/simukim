@props([
    'label',
    'value',
    'unit' => null,
    'description' => null,
    'color' => 'slate',
    'icon' => null,
])

@php
    $theme = match($color) {
        'blue' => [
            'card' => 'border-slate-200/80 dark:border-slate-800',
            'label' => 'text-slate-600 dark:text-slate-400',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-slate-200/60 dark:border-slate-700/60',
            'value' => 'text-slate-900 dark:text-slate-100',
            'unit' => 'text-slate-500 dark:text-slate-400',
        ],
        'emerald' => [
            'card' => 'border-slate-200/80 dark:border-slate-800',
            'label' => 'text-slate-600 dark:text-slate-400',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border border-slate-200/60 dark:border-slate-700/60',
            'value' => 'text-slate-900 dark:text-slate-100',
            'unit' => 'text-slate-500 dark:text-slate-400',
        ],
        'amber' => [
            'card' => 'border-slate-200/80 dark:border-slate-800',
            'label' => 'text-slate-600 dark:text-slate-400',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 border border-slate-200/60 dark:border-slate-700/60',
            'value' => 'text-slate-900 dark:text-slate-100',
            'unit' => 'text-slate-500 dark:text-slate-400',
        ],
        'rose' => [
            'card' => 'border-slate-200/80 dark:border-slate-800',
            'label' => 'text-slate-600 dark:text-slate-400',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-slate-200/60 dark:border-slate-700/60',
            'value' => 'text-slate-900 dark:text-slate-100',
            'unit' => 'text-slate-500 dark:text-slate-400',
        ],
        'indigo' => [
            'card' => 'border-slate-200/80 dark:border-slate-800',
            'label' => 'text-slate-600 dark:text-slate-400',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-slate-200/60 dark:border-slate-700/60',
            'value' => 'text-slate-900 dark:text-slate-100',
            'unit' => 'text-slate-500 dark:text-slate-400',
        ],
        default => [
            'card' => 'border-slate-200/80 dark:border-slate-800',
            'label' => 'text-slate-600 dark:text-slate-400',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/60 dark:border-slate-700/60',
            'value' => 'text-slate-900 dark:text-slate-100',
            'unit' => 'text-slate-500 dark:text-slate-400',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'p-3 sm:p-3.5 rounded-2xl border bg-white dark:bg-slate-900 shadow-2xs ' . $theme['card']]) }}>
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-semibold {{ $theme['label'] }}">{{ $label }}</span>
        @if(isset($icon) && $icon)
            <span class="size-7 rounded-lg {{ $theme['icon_bg'] }} flex items-center justify-center">
                @if(is_string($icon) && !str_starts_with(trim($icon), '<'))
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
                    </svg>
                @else
                    {{ $icon }}
                @endif
            </span>
        @endif
    </div>
    <div class="mt-2 flex items-baseline gap-1.5">
        <span class="text-lg sm:text-xl font-bold tracking-tight {{ $theme['value'] }}">
            {{ $value }}
        </span>
        @if($unit)
            <span class="text-[11px] font-medium {{ $theme['unit'] }}">{{ $unit }}</span>
        @endif
    </div>
    @if($description)
        <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ $description }}</p>
    @endif
</div>
