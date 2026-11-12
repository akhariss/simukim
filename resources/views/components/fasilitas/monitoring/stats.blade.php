@props([
    'totalKapasitas' => 0,
    'totalTerisi' => 0,
    'sisaBed' => 0,
    'okupansiPersen' => 0,
    'totalKamar' => 0,
    'totalGedung' => 0,
    'kamarPerbaikan' => 0,
    'hasActiveFilter' => false,
])

<!-- 4 Kartu Statistik Kapasitas & Okupansi Asrama -->
<div class="space-y-3">
    @if($hasActiveFilter ?? false)
        <div class="flex items-center justify-between gap-2 px-3.5 py-2 rounded-xl bg-slate-100/70 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 text-xs">
            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300 font-medium truncate">
                <span class="size-2 rounded-full bg-blue-500 shrink-0"></span>
                <span class="truncate">Metrik statistik di atas otomatis disesuaikan dengan kriteria filter yang aktif.</span>
            </div>
            <a
                href="{{ route('fasilitas.monitoring.index', ['view' => request('view', 'grid')]) }}"
                class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline shrink-0"
            >
                Reset ke Total Keseluruhan
            </a>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
        <!-- Stat 1: Total Kapasitas -->
        <x-kpi-card
            label="Total Kapasitas"
            :value="number_format($totalKapasitas) . ' Bed'"
            :note="($hasActiveFilter ?? false) ? number_format($totalKamar) . ' kamar' : number_format($totalKamar) . ' kamar • ' . number_format($totalGedung) . ' gedung'"
            color="blue"
        >
            <x-slot:icon>
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </x-slot:icon>
        </x-kpi-card>

        <!-- Stat 2: Penghuni Aktif & Persentase Okupansi -->
        <x-kpi-card
            label="Terisi (Okupansi)"
            :value="number_format($totalTerisi) . ' (' . $okupansiPersen . '%)'"
            note="Penghuni aktif saat ini"
            color="emerald"
        >
            <x-slot:icon>
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-slot:icon>
        </x-kpi-card>

        <!-- Stat 3: Kapasitas Tersedia (Kosong) -->
        <x-kpi-card
            label="Bed Tersedia"
            :value="number_format($sisaBed) . ' Slot'"
            note="Siap ditempati santri"
            color="amber"
        >
            <x-slot:icon>
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-kpi-card>

        <!-- Stat 4: Kamar Perbaikan / Maintenance -->
        <x-kpi-card
            label="Kamar Perbaikan"
            :value="number_format($kamarPerbaikan) . ' Unit'"
            note="Perawatan fasilitas / terkunci"
            color="rose"
        >
            <x-slot:icon>
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </x-slot:icon>
        </x-kpi-card>
    </div>
</div>
