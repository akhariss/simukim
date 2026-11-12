@props([
    'title',
    'subtitle' => null,
    'description' => null,
    'badge' => null,
    'breadcrumbs' => [],
])

@php
    $desc = $description ?? $subtitle;
    $crumbs = $breadcrumbs;
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 sm:pb-3.5 border-b border-slate-200/80 dark:border-slate-800">
    <div class="min-w-0 flex-1">
        {{-- 1. BREADCRUMBS & BADGES BAR --}}
        <div class="flex items-center gap-2 flex-wrap text-xs mb-1.5 min-w-0">
            @if(!empty($crumbs))
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 flex-wrap" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition shrink-0">
                        <svg class="size-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    @foreach($crumbs as $crumb)
                        <svg class="size-3 text-slate-300 dark:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>

                        @if(!empty($crumb['url']) && !$loop->last)
                            <a href="{{ $crumb['url'] }}" class="hover:text-blue-600 dark:hover:text-blue-400 font-medium transition truncate max-w-[160px] sm:max-w-none">
                                {{ html_entity_decode((string) $crumb['label'], ENT_QUOTES, 'UTF-8') }}
                            </a>
                        @else
                            <span class="{{ $loop->last ? 'text-slate-800 dark:text-slate-200 font-semibold' : 'text-slate-500 dark:text-slate-400 font-medium' }} truncate max-w-[200px] sm:max-w-none">
                                {{ html_entity_decode((string) $crumb['label'], ENT_QUOTES, 'UTF-8') }}
                            </span>
                        @endif
                    @endforeach
                </nav>
            @elseif($badge)
                <div class="inline-flex items-center gap-1.5 text-[10.5px] font-bold tracking-wider text-blue-600 dark:text-blue-400 uppercase">
                    <span>{!! $badge !!}</span>
                </div>
            @endif
        </div>

        {{-- 2. JUDUL HALAMAN (H1) --}}
        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
            {{ html_entity_decode((string) $title, ENT_QUOTES, 'UTF-8') }}
        </h1>

        {{-- 3. DESKRIPSI / SUBTITLE --}}
        @if($desc)
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                {!! $desc !!}
            </p>
        @endif
    </div>

    {{-- 4. ACTION BUTTONS --}}
    @if(isset($actions) && $actions->isNotEmpty())
        <div class="flex items-center gap-2 flex-wrap shrink-0 self-start sm:self-auto">
            {{ $actions }}
        </div>
    @endif
</div>
