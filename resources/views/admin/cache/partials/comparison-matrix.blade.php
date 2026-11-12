@props([
    'activePeriode',
    'localStats',
    'sahajaSummary' => null,
    'lastSyncTime' => null,
])

@php
$sahajaOnline = !empty($sahajaSummary['online']);
$sahajaData = $sahajaSummary['summary'] ?? [];
$diffTotal = ($localStats['total_santri'] ?? 0) - ($sahajaData['total_mukim'] ?? 0);
@endphp

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
    <!-- Header Matriks -->
    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-0.5">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full {{ $sahajaOnline ? 'bg-emerald-500 animate-pulse' : 'bg-amber-400' }}"></span>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Matriks Komparasi Data Pangkalan</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Perbandingan data operasional asrama ma'had dengan pangkalan data kampus (SAHAJA)
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if($sahajaOnline)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800">
                    <svg class="size-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Terhubung ke Kampus</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800">
                    <svg class="size-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Lokal (Menunggu Sinkron)</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <th class="py-3 px-4 font-semibold">Indikator Data</th>
                    <th class="py-3 px-4 font-semibold">Pangkalan Asrama (Aplikasi)</th>
                    <th class="py-3 px-4 font-semibold">Pangkalan Kampus (SAHAJA)</th>
                    <th class="py-3 px-4 font-semibold text-right">Status Keselarasan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <!-- Baris 1: Periode Akademik -->
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                        Periode Akademik Aktif
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        {{ $activePeriode?->nama ?? 'Belum Ditentukan' }}
                        <span class="text-slate-400 font-mono text-[11px]">({{ $activePeriode?->kode ?? '-' }})</span>
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        {{ $sahajaData['periode_filter'] ?? ($activePeriode?->nama . ' (' . ($activePeriode?->kode ?? '-') . ')') }}
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80">
                            Selaras
                        </span>
                    </td>
                </tr>

                <!-- Baris 2: Total Mahasantri Muqim -->
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                        Mahasantri Muqim
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($localStats['total_santri']) }}</span> Santri
                        <span class="text-[11px] text-slate-400">({{ $localStats['ikhwan_santri'] }} Ikhwan, {{ $localStats['akhwat_santri'] }} Akhwat)</span>
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($sahajaData['total_mukim'] ?? 0) }}</span> Santri
                        <span class="text-[11px] text-slate-400">({{ $sahajaData['total_laki_laki'] ?? 0 }} Ikhwan, {{ $sahajaData['total_perempuan'] ?? 0 }} Akhwat)</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($diffTotal === 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80">
                                Sinkron Penuh
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80">
                                Selisih {{ abs($diffTotal) }} Santri
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- Baris 3: Status Penempatan Kamar -->
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                        Penetapan Kamar
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($localStats['assigned_santri']) }}</span> Ditempatkan
                        <span class="text-[11px] text-slate-400">({{ number_format($localStats['unassigned_santri']) }} Belum)</span>
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ number_format($sahajaData['sudah_dapat_kamar'] ?? 0) }}</span> Ditetapkan di Portal
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80">
                            Otoritas Asrama
                        </span>
                    </td>
                </tr>

                <!-- Baris 4: Status Pembayaran -->
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                        Pembayaran Asrama (Muqim)
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($localStats['lunas_santri']) }}</span> Lunas
                        <span class="text-slate-400">&bull;</span>
                        <span class="font-semibold text-rose-600 dark:text-rose-400">{{ number_format($localStats['belum_lunas_santri']) }}</span> Belum Lunas
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        Pencatatan Billing Kampus (BRIVA / BSB)
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80">
                            Tervalidasi
                        </span>
                    </td>
                </tr>

                <!-- Baris 5: Sinkronisasi Terakhir -->
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                        Waktu Pembaruan Terakhir
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                        Real-time <span class="text-[11px] text-slate-400">(Pangkalan Lokal)</span>
                    </td>
                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-medium">
                        {{ $lastSyncTime ?? 'Belum Pernah Disinkronkan' }}
                    </td>
                    <td class="py-3 px-4 text-right">
                        <form method="POST" action="{{ route('admin.cache.sahaja.sync') }}" class="inline"
                            data-confirm="Sinkronkan data mahasantri dan penetapan dari portal kampus (SAHAJA) sekarang?"
                            data-confirm-title="Sinkronisasi Pangkalan Kampus"
                            data-confirm-btn="Ya, Sinkronkan"
                            data-confirm-type="primary">
                            @csrf
                            <input type="hidden" name="kode_periode" value="{{ $activePeriode?->kode }}">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-2xs cursor-pointer">
                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Sinkronkan</span>
                            </button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (Dual-Slot Responsive Sesuai Aturan 5) -->
    <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800 text-xs p-3 space-y-3">
        <!-- Item 1: Periode -->
        <div class="pt-2 first:pt-0 space-y-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Periode Akademik Aktif</span>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $activePeriode?->nama ?? '-' }} ({{ $activePeriode?->kode ?? '-' }})</p>
                    <p class="text-[11px] text-slate-400">SAHAJA: {{ $sahajaData['periode_filter'] ?? ($activePeriode?->nama ?? '-') }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Selaras</span>
            </div>
        </div>

        <!-- Item 2: Mahasantri -->
        <div class="pt-3 space-y-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mahasantri Muqim</span>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ number_format($localStats['total_santri']) }} Santri (Lokal)</p>
                    <p class="text-[11px] text-slate-400">SAHAJA: {{ number_format($sahajaData['total_mukim'] ?? 0) }} Santri</p>
                </div>
                @if($diffTotal === 0)
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Sinkron</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Selisih {{ abs($diffTotal) }}</span>
                @endif
            </div>
        </div>

        <!-- Item 3: Kamar & Pembayaran -->
        <div class="pt-3 space-y-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kamar & Pembayaran</span>
            <div class="space-y-1">
                <p class="text-slate-800 dark:text-slate-200">
                    <span class="font-semibold text-emerald-600">{{ number_format($localStats['assigned_santri']) }}</span> Kamar Ditempatkan
                    &bull;
                    <span class="font-semibold text-emerald-600">{{ number_format($localStats['lunas_santri']) }}</span> Lunas
                </p>
                <p class="text-[11px] text-slate-400">Pembaruan: {{ $lastSyncTime ?? 'Belum Pernah' }}</p>
            </div>
        </div>
    </div>
</div>

