<header
    x-data="{ mobileMenuOpen: false }"
    class="sticky top-0 z-50 bg-white dark:bg-slate-900 border-b border-slate-200/90 dark:border-slate-800 shadow-xs transition-colors duration-200"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Brand Logo with Clean Monogram (Tanpa Background Biru) -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Ma'had Al-Jami'ah" class="size-10 object-contain group-hover:scale-105 transition-transform">
            <div>
                <div class="flex items-center">
                    <span class="text-base sm:text-lg font-black tracking-tight text-slate-950 dark:text-white leading-none">SIMUKIM</span>
                </div>
                <div class="text-[10.5px] text-slate-500 dark:text-slate-400 font-medium leading-none mt-1">
                    Ma'had Al-Jami'ah UINSSC
                </div>
            </div>
        </a>

        <!-- Middle Navigation Links (Desktop) -->
        <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-600 dark:text-slate-300">
            <a href="#fitur" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Pilar Ekosistem</a>
            <a href="#showcase" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Konsol Sistem</a>
            <a href="#peran" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Akses Peran</a>
            <a href="#keunggulan" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Keunggulan</a>
        </nav>

        <!-- Right Nav Actions -->
        <div class="flex items-center gap-3">
            <!-- Theme Switcher -->
            <button
                type="button"
                class="size-9 rounded-xl flex items-center justify-center transition-colors border border-slate-200/90 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer"
                x-data="{ currentTheme: window.getCurrentTheme ? window.getCurrentTheme() : 'light' }"
                @theme-changed.window="currentTheme = $event.detail.theme"
                :title="currentTheme === 'dark' ? 'Mode Terang' : 'Mode Gelap'"
                @click="currentTheme = window.toggleTheme()"
            >
                <svg x-show="currentTheme === 'dark'" x-cloak class="size-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg x-show="currentTheme !== 'dark'" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>

            <!-- Auth Button (Desktop) -->
            @if (Route::has('login'))
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        data-no-spa
                        class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all cursor-pointer"
                    >
                        <span>Ke Dashboard</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        data-no-spa
                        class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500 shadow-xs transition-all cursor-pointer"
                    >
                        <span>Masuk ke Portal</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endauth
            @endif

            <!-- Mobile Hamburger Toggle -->
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden size-9 rounded-xl flex items-center justify-center border border-slate-200/90 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 cursor-pointer"
                aria-label="Menu"
            >
                <svg x-show="!mobileMenuOpen" class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>

    <!-- Mobile Slide-Down Drawer (Touch-Optimized) -->
    <div
        x-show="mobileMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3 space-y-2.5 shadow-lg"
    >
        <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
            <a @click="mobileMenuOpen = false" href="#fitur" class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60">Pilar Ekosistem</a>
            <a @click="mobileMenuOpen = false" href="#showcase" class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60">Konsol Sistem</a>
            <a @click="mobileMenuOpen = false" href="#peran" class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60">Akses Peran</a>
            <a @click="mobileMenuOpen = false" href="#keunggulan" class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60">Keunggulan</a>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            @auth
                <a href="{{ route('dashboard') }}" data-no-spa class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600">
                    <span>Buka Dashboard Utama</span>
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            @else
                <a href="{{ route('login') }}" data-no-spa class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 dark:bg-blue-600">
                    <span>Masuk ke Akun SIMUKIM</span>
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            @endauth
        </div>
    </div>
</header>
