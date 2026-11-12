@php
$linkClass = function(bool $active) {
    if ($active) {
        return 'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-100 bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white dark:border dark:border-slate-700/80 shadow-2xs group';
    }
    return 'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-colors duration-100 text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50 group';
};

$iconClass = function(bool $active) {
    if ($active) {
        return 'size-4 shrink-0 text-blue-600 dark:text-blue-400';
    }
    return 'size-4 shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors';
};

$user = auth()->user();
$userRole = $user?->roles->first()?->name ?? 'mahasantri';

$roleMeta = match($userRole) {
    'admin' => ['label' => 'Administrator', 'bg' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200/80 dark:border-blue-800'],
    'tutor' => ['label' => 'Tutor Dosen', 'bg' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200/80 dark:border-indigo-800'],
    'mudabbir' => ['label' => 'Mudabbir Pembina', 'bg' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200/80 dark:border-amber-800'],
    'mahasantri' => ['label' => 'Mahasantri', 'bg' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800'],
    default => ['label' => ucfirst($userRole), 'bg' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border-slate-200 dark:border-slate-700'],
};

// Cek apakah Admin terdaftar sebagai pengampu kelompok halaqah
$adminIsTeaching = ($userRole === 'admin' && $user)
    ? \Illuminate\Support\Facades\DB::table('mahad_kelompok_pengampu')->where('tutor_id', $user->id)->exists()
    : false;

$icDashboard = 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6';
$icIzin = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
$icPelanggaran = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
$icAgenda = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
$icMonitoring = 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z';
$icPenempatan = 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z';
$icAsrama = 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4';
$icKajian = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
$icPresensi = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4';
$icNilai = 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
$icLaporan = 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
$icEkspor = 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4';
$icUser = 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z';
$icSystem = 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10';

// Struktur Menu per Role Sesuai Alur Kerja Standar
$roleMenuGroups = [
    'admin' => array_filter([
        'Ringkasan' => [
            ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => $icDashboard],
        ],
        'Operasional' => [
            ['name' => 'Izin Santri', 'route' => 'kedisiplinan.perizinan.index', 'prefix' => 'kedisiplinan.perizinan.*', 'icon' => $icIzin],
            ['name' => 'Pelanggaran Santri', 'route' => 'kedisiplinan.pelanggaran.index', 'prefix' => 'kedisiplinan.pelanggaran.*', 'icon' => $icPelanggaran],
            ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => $icAgenda],
        ],
        'Asrama' => [
            ['name' => 'Monitoring Kamar', 'route' => 'fasilitas.monitoring.index', 'prefix' => 'fasilitas.monitoring.*', 'icon' => $icMonitoring],
            ['name' => 'Penempatan Santri', 'route' => 'fasilitas.penempatan.index', 'prefix' => 'fasilitas.penempatan.*', 'icon' => $icPenempatan],
            ['name' => 'Data Asrama', 'route' => 'fasilitas.kelola.index', 'prefix' => 'fasilitas.kelola.*', 'icon' => $icAsrama],
        ],
        $adminIsTeaching ? 'Kelas Saya' : null => $adminIsTeaching ? [
            ['name' => 'Presensi Kelas', 'route' => 'tutor.kajian.index', 'prefix' => 'tutor.kajian.*', 'icon' => $icKajian],
            ['name' => 'Nilai Santri', 'route' => 'kelas.nilai.index', 'params' => ['mode' => 'saya'], 'is_active' => fn() => request()->routeIs('kelas.nilai.*') && request('mode') === 'saya', 'icon' => $icNilai],
            ['name' => 'Laporan Bulanan', 'route' => 'kelas.laporan.index', 'params' => ['mode' => 'saya'], 'is_active' => fn() => request()->routeIs('kelas.laporan.*') && request('mode') === 'saya', 'icon' => $icLaporan],
        ] : null,
        'Manajemen Kajian' => [
            ['name' => 'Rekap Presensi', 'route' => 'kelas.absensi.index', 'prefix' => 'kelas.absensi.*', 'icon' => $icPresensi],
            ['name' => 'Rekap Nilai', 'route' => 'kelas.nilai.index', 'is_active' => fn() => request()->routeIs('kelas.nilai.*') && request('mode') !== 'saya', 'icon' => $icNilai],
            ['name' => 'Review Laporan', 'route' => 'kelas.laporan.index', 'is_active' => fn() => request()->routeIs('kelas.laporan.*') && request('mode') !== 'saya', 'icon' => $icLaporan],
            ['name' => 'Kelompok Kajian', 'route' => 'kelompok.index', 'prefix' => 'kelompok.*', 'icon' => $icPenempatan],
            ['name' => 'Kurikulum Kajian', 'route' => 'kelas.master.index', 'prefix' => 'kelas.master.*', 'icon' => $icKajian],
        ],
        'Sistem' => [
            ['name' => 'Ekspor Laporan', 'route' => 'pelaporan.index', 'prefix' => 'pelaporan.*', 'icon' => $icEkspor],
            ['name' => 'Kelola Pengguna', 'route' => 'users.index', 'prefix' => 'users.*', 'icon' => $icUser],
            ['name' => 'Sinkron & Cache', 'route' => 'admin.cache.index', 'prefix' => 'admin.cache.*', 'icon' => $icSystem],
        ],
    ]),
    'tutor' => [
        'Ringkasan' => [
            ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => $icDashboard],
            ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => $icAgenda],
        ],
        'Kelas Saya' => [
            ['name' => 'Presensi Kelas', 'route' => 'tutor.kajian.index', 'prefix' => 'tutor.kajian.*', 'icon' => $icKajian],
            ['name' => 'Nilai Santri', 'route' => 'kelas.nilai.index', 'prefix' => 'kelas.nilai.*', 'icon' => $icNilai],
            ['name' => 'Laporan Bulanan', 'route' => 'kelas.laporan.index', 'prefix' => 'kelas.laporan.*', 'icon' => $icLaporan],
        ],
        'Kedisiplinan' => [
            ['name' => 'Catatan Santri', 'route' => 'tutor.kedisiplinan.index', 'prefix' => 'tutor.kedisiplinan.*', 'icon' => $icIzin],
        ],
    ],
    'mudabbir' => [
        'Ringkasan' => [
            ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => $icDashboard],
            ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => $icAgenda],
        ],
        'Asrama & Kedisiplinan' => [
            ['name' => 'Monitoring Kamar', 'route' => 'fasilitas.monitoring.index', 'prefix' => 'fasilitas.monitoring.*', 'icon' => $icMonitoring],
            ['name' => 'Izin Santri', 'route' => 'kedisiplinan.perizinan.index', 'prefix' => 'kedisiplinan.perizinan.*', 'icon' => $icIzin],
            ['name' => 'Pelanggaran Santri', 'route' => 'kedisiplinan.pelanggaran.index', 'prefix' => 'kedisiplinan.pelanggaran.*', 'icon' => $icPelanggaran],
        ],
    ],
    'mahasantri' => [
        'Ringkasan' => [
            ['name' => 'Dashboard', 'route' => 'dashboard', 'prefix' => 'dashboard', 'icon' => $icDashboard],
            ['name' => 'Agenda Asrama', 'route' => 'kegiatan.index', 'prefix' => 'kegiatan.*', 'icon' => $icAgenda],
        ],
        'Aktivitas' => [
            ['name' => 'Perizinan', 'route' => 'kedisiplinan.perizinan.index', 'prefix' => 'kedisiplinan.perizinan.*', 'is_active' => fn() => request()->routeIs('kedisiplinan.perizinan.*') && request('tab') !== 'pelanggaran', 'icon' => $icIzin],
            ['name' => 'Catatan Disiplin', 'route' => 'kedisiplinan.perizinan.index', 'params' => ['tab' => 'pelanggaran'], 'prefix' => 'kedisiplinan.perizinan.*', 'is_active' => fn() => request()->routeIs('kedisiplinan.perizinan.*') && request('tab') === 'pelanggaran', 'icon' => $icPelanggaran],
            ['name' => 'Kelas Saya', 'route' => 'kelas.santri.index', 'prefix' => 'kelas.santri.*', 'icon' => $icKajian],
        ],
    ],
];

$activeMenuGroups = $roleMenuGroups[$userRole] ?? $roleMenuGroups['mahasantri'];
@endphp

<!-- Backdrop (mobile) -->
<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden"
    @click="sidebarOpen = false"
    x-cloak
></div>

<!-- Sidebar panel: Full height 100vh on the left -->
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed top-0 left-0 bottom-0 w-60 border-r transition-transform duration-200 ease-in-out lg:transition-none flex flex-col z-50 lg:static lg:h-screen lg:shrink-0 lg:translate-x-0 lg:z-auto bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800/80"
>
    <!-- Brand Header at Top of Sidebar -->
    <div class="h-14 flex items-center justify-between px-4 border-b shrink-0 border-slate-200/80 dark:border-slate-800/80">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Ma'had Al-Jami'ah" class="size-8 object-contain group-hover:scale-105 transition-transform">
            <div>
                <div class="logo-wordmark text-[16px] leading-tight font-extrabold tracking-tight">
                    <span class="text-slate-900 dark:text-white">SIMUKIM</span>
                </div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium leading-none">
                    Ma'had Al-Jami'ah
                </div>
            </div>
        </a>

        <!-- Mobile close button -->
        <button type="button" @click="sidebarOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 lg:hidden">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation List -->
    <nav
        id="sidebar-nav"
        class="flex-1 overflow-y-auto py-3 px-3 space-y-3.5"
        @scroll.passive="sessionStorage.setItem('sidebar_scroll_pos', $el.scrollTop)"
        x-init="$el.scrollTop = parseInt(sessionStorage.getItem('sidebar_scroll_pos') || '0', 10)"
    >
        @foreach($activeMenuGroups as $title => $items)
            @if(!empty($items))
                <div>
                    <p class="px-3 mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        {{ $title }}
                    </p>
                    <div class="space-y-0.5">
                        @foreach($items as $item)
                            @php
                                $prefix = $item['prefix'] ?? ($item['route'] . '*');
                                $isActive = isset($item['is_active'])
                                    ? $item['is_active']()
                                    : (Route::has($item['route']) && request()->routeIs($prefix));
                                $url = Route::has($item['route'])
                                    ? route($item['route'], $item['params'] ?? [])
                                    : '#';
                            @endphp
                            <a
                                href="{{ $url }}"
                                class="{{ $linkClass($isActive) }}"
                                @click="sidebarOpen = false"
                            >
                                <svg class="{{ $iconClass($isActive) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
                                </svg>
                                <span class="truncate">{{ $item['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    <!-- Sidebar footer with user role display -->
    <div class="px-4 py-3 border-t shrink-0 border-slate-200/80 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center justify-between text-[11px] gap-2">
            <div class="flex flex-col min-w-0">
                <span class="truncate font-semibold text-slate-800 dark:text-slate-200">{{ $user?->name }}</span>
                <span class="text-[9.5px] text-slate-400 font-mono truncate">{{ $user?->nomor_induk ?: $user?->email }}</span>
            </div>
            <span class="font-bold text-[9.5px] px-2 py-0.5 rounded-md border shrink-0 inline-flex items-center gap-1 {{ $roleMeta['bg'] }}">
                <span class="size-1.5 rounded-full bg-current"></span>
                <span>{{ $roleMeta['label'] }}</span>
            </span>
        </div>
    </div>
</aside>
