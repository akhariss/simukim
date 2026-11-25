<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIMUKIM — Sistem Informasi Mahasantri Mukim | Ma'had Al-Jami'ah UIN Siber Cirebon</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

    <!-- PWA & Mobile Web App Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SIMUKIM">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Service Worker Registration & PWA Install Engine -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SIMUKIM SW registered:', reg.scope))
                    .catch(err => console.warn('SIMUKIM SW failed:', err));
            });
        }

        let deferredPwaPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPwaPrompt = e;
            window.dispatchEvent(new CustomEvent('pwa-ready'));
        });

        window.installPWA = function() {
            if (deferredPwaPrompt) {
                deferredPwaPrompt.prompt();
                deferredPwaPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User installed SIMUKIM PWA');
                    }
                    deferredPwaPrompt = null;
                });
            } else {
                const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                if (isIos) {
                    alert("Untuk menginstall di iPhone/iPad:\n1. Ketuk tombol 'Share/Bagikan' di browser Safari (ikon kotak berpanah atas di bagian bawah layar).\n2. Gulir ke bawah lalu pilih 'Tambah ke Layar Utama' (Add to Home Screen).");
                } else if (window.matchMedia('(display-mode: standalone)').matches) {
                    alert("Aplikasi SIMUKIM sudah terpasang di perangkat Anda!");
                } else {
                    alert("Untuk mengunduh/memasang aplikasi di HP:\n1. Buka menu browser Anda (ikon titik tiga di pojok kanan atas).\n2. Pilih 'Pasang aplikasi' atau 'Tambahkan ke Layar Utama' (Add to Home screen).");
                }
            }
        };
    </script>

    <!-- Fonts: Local Self-Hosted Preload (0ms external network delay) -->
    <link rel="preload" href="/fonts/plus-jakarta-sans-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/outfit-latin.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Anti-FOUC Theme Init -->
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('sahaja_theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = saved || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch(e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 selection:bg-blue-600 selection:text-white antialiased">

    <x-welcome.navbar />

    <main style="contain: content;">
        <x-welcome.hero />
        <x-welcome.features />
    </main>

    <x-welcome.footer />

</body>
</html>
