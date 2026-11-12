<x-app-layout>
    <x-slot name="title">Rekap Bulanan Presensi Santri</x-slot>

    <!-- Header Section -->
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Presensi Harian', 'url' => route('kelas.absensi.index')],
            ['label' => 'Rekap Bulanan']
        ]"
        title="Rekap Kehadiran Bulanan"
        description="Akumulasi persentase kehadiran mahasantri per kelompok dan kajian."
    >
        <x-slot:actions>
            <a
                href="{{ route('kelas.absensi.index', ['kelompok_id' => $selectedKelompok?->id, 'kelas_id' => $selectedKelas?->id]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200/90 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-all shadow-2xs"
            >
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Presensi Harian</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filter Bar: Kelompok, Kajian, Bulan -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
        <form method="GET" action="{{ route('kelas.absensi.rekap') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kelompok</label>
                <select name="kelompok_id" onchange="this.form.requestSubmit()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    @foreach($kelompokList as $klp)
                        <option value="{{ $klp->id }}" {{ $selectedKelompok?->id === $klp->id ? 'selected' : '' }}>{{ $klp->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pilih Kajian</label>
                <select name="kelas_id" onchange="this.form.requestSubmit()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $selectedKelas?->id === $k->id ? 'selected' : '' }}>{{ $k->nama }} ({{ $k->tipe->label() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Bulan Pelaksanaan</label>
                <select name="bulan" onchange="this.form.requestSubmit()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
        </form>
    </div>

    @if($selectedKelompok && $selectedKelas)
        <!-- Table Container (Atomic Dual-Mode) -->
        <x-table-container>
            <x-slot:desktop>
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Mahasantri</th>
                            <th class="py-3 px-3 text-center">Total Pertemuan</th>
                            <th class="py-3 px-3 text-center text-emerald-600">Hadir</th>
                            <th class="py-3 px-3 text-center text-amber-600">Izin</th>
                            <th class="py-3 px-3 text-center text-blue-600">Sakit</th>
                            <th class="py-3 px-3 text-center text-rose-600">Alfa</th>
                            <th class="py-3 px-3 text-center text-indigo-600">Telat</th>
                            <th class="py-3 px-4 text-center">% Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($selectedKelompok->anggota as $idx => $item)
                            @php
                                $santri = $item->santri;
                                $stat = $rekapData->get($santri->id);
                                $total = $stat?->total_pertemuan ?? 0;
                                $hadir = $stat?->hadir ?? 0;
                                $pct = $total > 0 ? round(($hadir / $total) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $santri->user->name ?? '-' }}</p>
                                    <p class="text-[11px] font-mono text-slate-400">{{ $santri->nomor_induk }}</p>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-slate-700 dark:text-slate-300">{{ $total }}</td>
                                <td class="py-3 px-3 text-center font-bold text-emerald-600">{{ $hadir }}</td>
                                <td class="py-3 px-3 text-center text-amber-600">{{ $stat?->izin ?? 0 }}</td>
                                <td class="py-3 px-3 text-center text-blue-600">{{ $stat?->sakit ?? 0 }}</td>
                                <td class="py-3 px-3 text-center text-rose-600">{{ $stat?->alfa ?? 0 }}</td>
                                <td class="py-3 px-3 text-center text-indigo-600">{{ $stat?->terlambat ?? 0 }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold {{ $pct >= 80 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40' : ($pct >= 60 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40') }}">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-xs text-slate-400">Tidak ada santri di kelompok ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-slot:desktop>

            <x-slot:mobile>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold uppercase text-slate-500 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-2.5 py-2 w-8 text-center">No</th>
                                <th class="px-2.5 py-2">Mahasantri</th>
                                <th class="px-2 py-2 text-center">H/I/S/A</th>
                                <th class="px-2.5 py-2 text-center">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($selectedKelompok->anggota as $idx => $item)
                                @php
                                    $santri = $item->santri;
                                    $stat = $rekapData->get($santri->id);
                                    $total = $stat?->total_pertemuan ?? 0;
                                    $hadir = $stat?->hadir ?? 0;
                                    $pct = $total > 0 ? round(($hadir / $total) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                    <td class="px-2.5 py-2 font-mono text-slate-400 text-center text-[11px]">{{ $idx + 1 }}</td>
                                    <td class="px-2.5 py-2 min-w-0">
                                        <p class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $santri->user->name ?? '-' }}</p>
                                        <p class="text-[10px] font-mono text-slate-400">{{ $santri->nomor_induk }}</p>
                                    </td>
                                    <td class="px-2 py-2 text-center text-[11px] font-mono whitespace-nowrap">
                                        <span class="text-emerald-600 font-bold">{{ $hadir }}</span>/<span class="text-amber-600">{{ $stat?->izin ?? 0 }}</span>/<span class="text-blue-600">{{ $stat?->sakit ?? 0 }}</span>/<span class="text-rose-600">{{ $stat?->alfa ?? 0 }}</span>
                                    </td>
                                    <td class="px-2.5 py-2 text-center">
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10.5px] font-bold {{ $pct >= 80 ? 'bg-emerald-50 text-emerald-700' : ($pct >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                            {{ $pct }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-xs text-slate-400">Tidak ada santri di kelompok ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-slot:mobile>
        </x-table-container>
    @endif
</x-app-layout>
