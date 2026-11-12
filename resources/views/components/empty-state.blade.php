@props([
    'title' => 'Tidak ada data',
    'description' => 'Belum ada data yang tersedia untuk ditampilkan.',
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'p-8 sm:p-12 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50']) }}>
    <div class="size-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>
    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $title }}</h4>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">{{ $description }}</p>
    @if(isset($action))
        <div class="mt-4 flex justify-center">
            {{ $action }}
        </div>
    @endif
</div>
