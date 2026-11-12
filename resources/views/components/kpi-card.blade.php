@props([
    'label' => '',
    'value' => '-',
    'note' => '',
    'color' => 'emerald',
])

@php
    $iconColorClasses = [
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        'amber' => 'text-amber-500 dark:text-amber-400',
        'purple' => 'text-purple-600 dark:text-purple-400',
        'rose' => 'text-rose-600 dark:text-rose-400',
        'indigo' => 'text-indigo-600 dark:text-indigo-400',
        'slate' => 'text-slate-500 dark:text-slate-400',
    ][$color] ?? 'text-emerald-600 dark:text-emerald-400';
@endphp

<div {{ $attributes->merge(['class' => 'p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-0.5 sm:space-y-1']) }}>
    <div class="flex items-center justify-between text-slate-400">
        <span class="text-[9.5px] sm:text-[10.5px] font-bold uppercase tracking-wider truncate">{{ $label }}</span>
        @if(isset($icon))
            <span class="shrink-0 {{ $iconColorClasses }}">{{ $icon }}</span>
        @endif
    </div>
    <div class="text-xs sm:text-base font-bold text-slate-900 dark:text-white truncate">{{ $value }}</div>
    @if($note)
        <p class="text-[10px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 truncate">{{ $note }}</p>
    @endif
</div>
