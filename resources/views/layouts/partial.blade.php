<title>{{ isset($title) ? $title . ' — ' . config('app.name', "SIMUKIM") : config('app.name', "SIMUKIM") }}</title>

<!-- Main Content Area: Dynamic SPA Slot -->
<main style="contain: content; -webkit-overflow-scrolling: touch;" class="flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5 pb-36 sm:pb-32 lg:pb-10 focus:outline-none" tabindex="-1">
    <div class="max-w-7xl mx-auto space-y-4 sm:space-y-5">
        @isset($header)
            {{ $header }}
        @endisset

        @if(session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                x-init="setTimeout(() => show = false, 5000)"
                class="p-4 rounded-2xl bg-emerald-50 dark:bg-slate-800 border border-emerald-200/80 dark:border-slate-700 text-emerald-900 dark:text-slate-100 text-xs sm:text-sm flex items-center justify-between shadow-2xs"
            >
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-xl bg-emerald-100 dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
                <button
                    type="button"
                    @click="show = false"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="p-4 rounded-2xl bg-rose-50 dark:bg-slate-800 border border-rose-200/80 dark:border-slate-700 text-rose-900 dark:text-slate-100 text-xs sm:text-sm flex items-center justify-between shadow-2xs"
            >
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-xl bg-rose-100 dark:bg-slate-700 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
                <button
                    type="button"
                    @click="show = false"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{ $slot }}
    </div>
</main>

