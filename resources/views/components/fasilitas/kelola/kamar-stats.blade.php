<!-- Ringkasan Eksekutif Kapasitas & Hunian Kamar (Atomic KPI Cards) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
    <!-- 1. Total Kamar -->
    <x-kpi-card
        label="Total Kamar"
        :value="number_format($roomStats->total_kamar ?? 0) . ' Unit'"
        note="Seluruh unit terdaftar"
        color="slate"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </x-slot:icon>
    </x-kpi-card>

    <!-- 2. Kamar Tersedia -->
    <x-kpi-card
        label="Kamar Tersedia"
        :value="number_format($roomStats->total_tersedia ?? 0) . ' Unit'"
        :note="number_format($roomStats->total_kosong ?? 0) . ' belum berpenghuni'"
        color="emerald"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-slot:icon>
    </x-kpi-card>

    <!-- 3. Terisi Penuh -->
    <x-kpi-card
        label="Terisi Penuh"
        :value="number_format($roomStats->total_penuh ?? 0) . ' Unit'"
        note="Okupansi kapasitas 100%"
        color="rose"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </x-slot:icon>
    </x-kpi-card>

    <!-- 4. Okupansi Bed -->
    <x-kpi-card
        label="Okupansi Bed"
        :value="number_format(($totalPenghuni ?? 0) ?: ($roomStats->total_terisi ?? 0)) . ' / ' . number_format($roomStats->total_bed ?? 0)"
        :note="number_format(max(0, ($roomStats->total_bed ?? 0) - (($totalPenghuni ?? 0) ?: ($roomStats->total_terisi ?? 0)))) . ' bed belum terisi'"
        color="blue"
    >
        <x-slot:icon>
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </x-slot:icon>
    </x-kpi-card>
</div>
