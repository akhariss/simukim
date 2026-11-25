@php
    $themeCookie = request()->cookie('sahaja_theme', 'light');
    $isDark = $themeCookie === 'dark';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $isDark ? 'dark' : '' }}" data-theme="{{ $isDark ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' . config('app.name', "SIMUKIM") : config('app.name', "SIMUKIM") }}</title>

    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

    <!-- PWA & Mobile Web App Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SIMUKIM">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SIMUKIM SW registered:', reg.scope))
                    .catch(err => console.warn('SIMUKIM SW failed:', err));
            });
        }
    </script>

    <!-- Instant Zero-Lag Theme Sync (Executes synchronously before CSS render) -->
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('sahaja_theme');
                if (saved) {
                    if (saved === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.documentElement.setAttribute('data-theme', 'dark');
                    } else if (saved === 'light') {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.setAttribute('data-theme', 'light');
                    }
                }
            } catch(e) {}
        })();
    </script>

    <!-- Fonts: Local Self-Hosted Preload (0ms external network delay) -->
    <link rel="preload" href="/fonts/plus-jakarta-sans-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/outfit-latin.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased selection:bg-blue-600 selection:text-white">
    <!-- App Shell: Clean 2-column layout (Full height Sidebar on Left, Content on Right) -->
    <div
        class="h-screen h-[100dvh] w-screen overflow-hidden flex bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100"
        x-data="{ sidebarOpen: false }"
        @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
        @close-sidebar.window="sidebarOpen = false"
    >
        <!-- 1. Left Sidebar: Full 100vh height -->
        <x-sidebar />

        <!-- 2. Right Canvas: Navbar + Main Scrollable Content -->
        <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden">
            <!-- Top Navbar above main content -->
            <x-header />

            <!-- Main Content Area: Only area that scrolls -->
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

                    @if($errors->any())
                        <div
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="p-4 rounded-2xl bg-rose-50 dark:bg-slate-800 border border-rose-200/80 dark:border-rose-900/60 text-rose-900 dark:text-slate-100 text-xs sm:text-sm flex items-start justify-between shadow-2xs"
                        >
                            <div class="flex items-start gap-3">
                                <div class="size-8 rounded-xl bg-rose-100 dark:bg-slate-700 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="font-bold text-rose-900 dark:text-white">Periksa kembali isian formulir:</p>
                                    <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
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
        </div>

        <!-- Mobile bottom navigation -->
        <x-bottom-nav />
    </div>

    <!-- Global Custom Confirm Modal Dialog (Bebas dari browser default alert) -->
    <x-confirm-dialog />

    @stack('scripts')
</body>
</html>
