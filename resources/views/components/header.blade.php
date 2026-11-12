@php
$user = auth()->user();
$periodeAktif = \App\Models\Periode::getActive();
@endphp

<header
    class="h-14 border-b sticky top-0 z-40 transition-colors duration-150 flex items-center justify-between px-4 sm:px-6 shrink-0 bg-white/98 dark:bg-slate-900/98 border-slate-200/80 dark:border-slate-800/80"
>
    <!-- Left Area: Mobile toggle & Context Info -->
    <div class="flex items-center gap-3">
        <!-- Mobile sidebar toggle -->
        <button
            type="button"
            class="lg:hidden p-1.5 rounded-lg transition-colors border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shadow-2xs"
            aria-label="Toggle sidebar"
            @click="$dispatch('toggle-sidebar')"
        >
            <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Mobile Brand (only visible on mobile when sidebar is hidden) -->
        <div class="flex items-center gap-2 lg:hidden">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Ma'had Al-Jami'ah" class="size-6 object-contain">
            <span class="font-bold text-xs text-slate-800 dark:text-slate-100">SIMUKIM</span>
        </div>

        <!-- Academic Period Badge (Desktop: Khusus Admin & Tutor) -->
        @if($user && ($user->hasRole('admin') || $user->hasRole('tutor')))
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 text-[11px]">
                <span class="size-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                <span class="font-medium text-slate-600 dark:text-slate-300">
                    {{ $periodeAktif->nama ?? '2026 Genap' }}
                </span>
            </div>
        @endif
    </div>

    <!-- Right Area: Theme Toggle & User Profile -->
    <div class="flex items-center gap-2 sm:gap-2.5">
        <!-- Academic Period Badge (Mobile only: Khusus Admin & Tutor) -->
        @if($user && ($user->hasRole('admin') || $user->hasRole('tutor')))
            <div class="flex sm:hidden items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 text-[10.5px]">
                <span class="size-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                <span class="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[95px]">
                    {{ $periodeAktif->nama ?? '2026 Ganjil' }}
                </span>
            </div>
        @endif

        <!-- Tombol Cepat Sinkronisasi SAHAJA (Khusus Role Administrator) -->
        @if($user && $user->hasRole('admin'))
            <form method="POST" action="{{ route('admin.cache.sahaja.sync') }}" class="inline"
                data-confirm="Sinkronkan data mahasantri dan penetapan dari portal kampus (SAHAJA) sekarang?"
                data-confirm-title="Sinkronisasi Pangkalan Kampus"
                data-confirm-btn="Ya, Sinkronkan"
                data-confirm-type="primary">
                @csrf
                <input type="hidden" name="kode_periode" value="{{ $periodeAktif?->kode }}">
                <button type="submit"
                    class="size-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 shadow-2xs cursor-pointer transition"
                    title="Sinkronisasi Data Kampus (SAHAJA)">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </form>
        @endif

        <!-- Theme Switcher -->
        <button
            type="button"
            class="size-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shadow-2xs cursor-pointer transition"
            x-data="{ currentTheme: window.getCurrentTheme ? window.getCurrentTheme() : 'light' }"
            @theme-changed.window="currentTheme = $event.detail.theme"
            :title="currentTheme === 'dark' ? 'Mode Terang' : 'Mode Gelap'"
            @click="currentTheme = window.toggleTheme()"
        >
            <svg x-show="currentTheme === 'dark'" x-cloak class="size-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg x-show="currentTheme !== 'dark'" class="size-3.5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <!-- User Profile Menu -->
        @auth
            <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="size-7.5 rounded-full object-cover shadow-2xs border border-slate-200 dark:border-slate-700 shrink-0" loading="lazy" onerror="this.classList.add('hidden'); if(this.nextElementSibling) { this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex'); }">
                        <div class="hidden size-7.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 items-center justify-center text-xs font-bold shadow-2xs shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="size-7.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs font-bold shadow-2xs shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="hidden md:flex flex-col text-left">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate max-w-36 leading-tight">
                            {{ $user->name }}
                        </span>
                        <span class="text-[10px] text-slate-400 truncate max-w-36 font-medium">
                            {{ $user->email }}
                        </span>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="p-1 rounded-lg text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        title="Keluar"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        @endauth
    </div>
</header>
