@props([
    'model' => null,
    'placeholder' => 'Cari data...',
    'name' => 'search',
    'value' => '',
    'id' => null,
    'inputClass' => '',
])

<div {{ $attributes->merge(['class' => 'relative flex-1 max-w-md']) }}>
    {{-- Search Icon --}}
    <div class="absolute inset-y-0 left-0 pl-3 sm:pl-3.5 flex items-center pointer-events-none text-slate-400">
        <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>

    {{-- Input Element (Hybrid Alpine x-model / Native Name) --}}
    @if($model)
        <input
            type="text"
            id="{{ $id ?? $name }}"
            name="{{ $name }}"
            x-model="{{ $model }}"
            placeholder="{{ $placeholder }}"
            class="w-full text-xs rounded-xl pl-8 sm:pl-9 pr-7 sm:pr-8 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none font-medium transition {{ $inputClass }}"
            {{ $attributes->whereStartsWith('@') }}
        >
        {{-- Clear Button for Alpine --}}
        <button
            type="button"
            x-show="{{ $model }}"
            x-cloak
            @click="{{ $model }} = ''; $dispatch('search-cleared')"
            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
            title="Hapus pencarian"
        >
            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @else
        <input
            type="text"
            id="{{ $id ?? $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="w-full text-xs rounded-xl pl-8 sm:pl-9 pr-7 sm:pr-8 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none font-medium transition {{ $inputClass }}"
            {{ $attributes->whereStartsWith('@') }}
        >
    @endif
</div>

