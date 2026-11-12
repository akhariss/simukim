@php
$userRole = auth()->user()?->roles->first()?->name ?? 'mahasantri';

$navItems = match($userRole) {
    'mudabbir' => [
        ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['name' => 'Monitoring', 'route' => 'fasilitas.monitoring.index', 'prefix' => 'fasilitas.*', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['name' => 'Izin Santri', 'route' => 'kedisiplinan.perizinan.index', 'prefix' => 'kedisiplinan.perizinan.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['name' => 'Pelanggaran', 'route' => 'kedisiplinan.pelanggaran.index', 'prefix' => 'kedisiplinan.pelanggaran.*', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ],
    'tutor' => [
        ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['name' => 'Presensi Kelas', 'route' => 'tutor.kajian.index', 'prefix' => 'tutor.kajian.*', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['name' => 'Laporan Bulanan', 'route' => 'kelas.laporan.index', 'prefix' => 'kelas.laporan.*', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['name' => 'Catatan Santri', 'route' => 'tutor.kedisiplinan.index', 'prefix' => 'tutor.kedisiplinan.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ],
    'mahasantri' => [
        ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['name' => 'Kelas Saya', 'route' => 'kelas.santri.index', 'prefix' => 'kelas.santri.*', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['name' => 'Perizinan', 'route' => 'kedisiplinan.perizinan.index', 'prefix' => 'kedisiplinan.perizinan.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['name' => 'Profil', 'route' => 'profile.edit', 'prefix' => 'profile.*', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ],
    default => [ // admin
        ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['name' => 'Monitoring', 'route' => 'fasilitas.monitoring.index', 'prefix' => 'fasilitas.*', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['name' => 'Rekap Presensi', 'route' => 'kelas.absensi.index', 'prefix' => 'kelas.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['name' => 'Izin Santri', 'route' => 'kedisiplinan.perizinan.index', 'prefix' => 'kedisiplinan.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['name' => 'Profil', 'route' => 'profile.edit', 'prefix' => 'profile.*', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ],
};
@endphp

<!-- Mobile-only bottom navigation bar -->
<nav id="bottom-nav" class="lg:hidden fixed bottom-0 left-0 right-0 h-14 border-t z-40 flex items-center shadow-md bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800">
    <div class="flex w-full items-center justify-around px-1">
        @foreach($navItems as $item)
            @php
                $isActive = Route::has($item['route']) && request()->routeIs($item['prefix'] ?? $item['route']);
                $url = Route::has($item['route']) ? route($item['route']) : '#';
            @endphp
            <a
                href="{{ $url }}"
                class="flex-1 flex flex-col items-center justify-center py-1 transition-colors {{ $isActive ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
            >
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
                </svg>
                <span class="text-[9.5px] mt-0.5 truncate max-w-[62px] text-center">{{ $item['name'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
