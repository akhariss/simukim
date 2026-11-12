<!-- KPI Metrics & Status Cards (Atomic KPI Cards) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
    <!-- Cache Store Driver -->
    <x-kpi-card
        label="Driver Cache"
        :value="strtoupper($driver)"
        note="Penyimpanan disk lokal file"
        color="emerald"
    >
        <x-slot:icon>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/80 dark:border-slate-700">
                <span class="size-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                Aktif
            </span>
        </x-slot:icon>
    </x-kpi-card>

    <!-- Cache Data Size -->
    <x-kpi-card
        label="Ukuran Cache Data"
        :value="$cacheStats['size_formatted']"
        :note="$cacheStats['count'] . ' berkas • storage/framework/cache'"
        color="blue"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3zm0 5h16"/></svg>
        </x-slot:icon>
    </x-kpi-card>

    <!-- Compiled Views -->
    <x-kpi-card
        label="Compiled Views"
        :value="$viewsStats['size_formatted']"
        :note="$viewsStats['count'] . ' view • storage/framework/views'"
        color="indigo"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </x-slot:icon>
    </x-kpi-card>

    <!-- Session Storage -->
    <x-kpi-card
        label="Driver Sesi"
        value="120 Menit"
        :note="'Driver: ' . strtoupper($sessionDriver) . ' • Idle TTL'"
        color="amber"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-slot:icon>
    </x-kpi-card>
</div>
