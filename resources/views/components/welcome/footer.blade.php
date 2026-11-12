<footer class="border-t border-slate-200/80 dark:border-slate-800/80 text-slate-500 dark:text-slate-400 py-8 text-xs bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Ma'had Al-Jami'ah" class="size-8 object-contain">
            <div class="text-left">
                <div class="flex items-center">
                    <span class="font-black text-sm text-slate-900 dark:text-white">SIMUKIM</span>
                </div>
                <span class="text-[10.5px] text-slate-400 dark:text-slate-500 block leading-tight">Ma'had Al-Jami'ah &bull; UIN Siber Syekh Nurjati Cirebon</span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 dark:text-slate-400">
            <a href="#fitur" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Modul Sistem</a>
            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
            <a href="#peran" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Peran</a>
            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
            <a href="{{ route('login') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-bold text-blue-600 dark:text-blue-400">Login Portal</a>
        </div>

        <div class="text-[11px] text-slate-400 dark:text-slate-500">
            &copy; {{ date('Y') }} SIMUKIM. Seluruh hak cipta dilindungi undang-undang.
        </div>
    </div>
</footer>
