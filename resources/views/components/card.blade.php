@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs overflow-hidden']) }}>
    @if($title || isset($header) || isset($actions))
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <div>
                @if($title)
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                @endif
                @if($description)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $description }}</p>
                @endif
                @if(isset($header))
                    {{ $header }}
                @endif
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-4 sm:p-5">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
            {{ $footer }}
        </div>
    @endif
</div>
