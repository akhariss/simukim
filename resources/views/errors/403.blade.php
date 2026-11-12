<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Dibatasi — 403 Ma'had Al-Jami'ah</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 flex items-center justify-center p-4 font-sans">
    <div class="max-w-md w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-6 sm:p-8 text-center space-y-5">
        <div class="size-16 rounded-2xl bg-amber-50 dark:bg-slate-800 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-slate-700 flex items-center justify-center mx-auto text-2xl font-black shadow-2xs">
            🛡️
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-slate-800 dark:text-rose-300 border border-rose-200 dark:border-slate-700">
                Error 403 — Hak Akses Dibatasi
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                Menu Terbatas untuk Admin
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Halaman yang Anda coba buka hanya dapat diakses oleh akun <strong>Administrator Asrama</strong>. Akun Anda yang saat ini masuk (Mahasantri / Mudabbir / Tutor) tidak memiliki hak untuk mengelola menu sistem ini.
            </p>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-2">
            <a 
                href="{{ route('dashboard') }}" 
                class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition shadow-2xs"
            >
                Kembali ke Dashboard Saya
            </a>
            
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto inline">
                @csrf
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition cursor-pointer"
                >
                    Ganti Akun Lain
                </button>
            </form>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400">
            Ma'had Al-Jami'ah
        </div>
    </div>
</body>
</html>
