<x-app-layout title="Penilaian Akademik Kajian">
    <div class="space-y-4">
        @if(!$hasSelected)
            @include('kelas.nilai.partials.kelas-list')
        @else
            {{-- VIEW 2: DEDICATED SHEET PENILAIAN KELAS INI --}}
            <x-page-header
                :breadcrumbs="[
                    ['label' => ($isSaya ?? false) ? 'Nilai Santri' : 'Rekap Nilai', 'url' => route('kelas.nilai.index', array_filter(['mode' => request('mode')]))],
                    ['label' => $selectedKelas->nama . ' (' . $selectedKelompok->nama . ')']
                ]"
                :title="$selectedKelas->nama"
                :description="'Kelompok: ' . $selectedKelompok->nama . ' • Semester ' . ($periode?->nama ?? '-')"
            >
                <x-slot:actions>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('kelas.nilai.index', array_filter(['mode' => request('mode')])) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Daftar Kelas</span>
                        </a>
                        <button
                            type="button"
                            @click="$dispatch('open-modal', 'modal-komponen-nilai')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 transition shadow-2xs cursor-pointer"
                        >
                            <svg class="size-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Komponen Nilai</span>
                        </button>
                        <a
                            href="{{ route('kelas.nilai.export', ['format' => 'excel', 'kelompok_id' => $selectedKelompok->id, 'kelas_id' => $selectedKelas->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 transition shadow-2xs"
                        >
                            <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Ekspor Excel</span>
                        </a>
                        <a
                            href="{{ route('kelas.nilai.export', ['format' => 'pdf', 'kelompok_id' => $selectedKelompok->id, 'kelas_id' => $selectedKelas->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 transition shadow-2xs"
                        >
                            <svg class="size-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Ekspor PDF</span>
                        </a>
                    </div>
                </x-slot:actions>
            </x-page-header>

            {{-- Form Spreadsheet Nilai Massal --}}
            <form method="POST" action="{{ route('kelas.nilai.bulk', array_filter([$selectedKelompok, $selectedKelas, 'mode' => request('mode')])) }}"
                x-data="{
                    isMobile: window.innerWidth < 768,
                    isEdit: {{ (Auth::user()->hasRole('admin') && request('mode') !== 'saya') ? 'false' : 'true' }}
                }"
                x-init="window.addEventListener('resize', () => isMobile = window.innerWidth < 768)">
                @csrf
                <x-table-container>
                    <x-slot:toolbar>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $anggotaList->count() }} Mahasantri</span>
                                <span>&bull;</span>
                                <span class="text-blue-600 dark:text-blue-400 font-medium">Presensi otomatis dari {{ $totalSesi }} sesi pertemuan</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    x-show="!isEdit"
                                    @click="isEdit = true"
                                    class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 rounded-xl transition shadow-xs cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Edit Nilai</span>
                                </button>

                                <div x-show="isEdit" class="flex items-center gap-2">
                                    @if(Auth::user()->hasRole('admin') && request('mode') !== 'saya')
                                        <button
                                            type="button"
                                            @click="isEdit = false"
                                            class="px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 rounded-xl transition cursor-pointer"
                                        >
                                            Batal
                                        </button>
                                    @endif
                                    <button
                                        type="submit"
                                        class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 rounded-xl transition shadow-xs cursor-pointer flex items-center gap-1.5"
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Simpan Rekapitulasi Nilai</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-slot:toolbar>

                    <x-slot:desktop>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                                    <tr>
                                        <th class="px-3 py-3 w-8">No</th>
                                        <th class="px-4 py-3 min-w-[140px]">Mahasantri</th>
                                        @foreach($komponenList as $komp)
                                            <th class="px-3 py-3 text-center min-w-[90px]">
                                                {{ $komp->nama }}<br>
                                                <span class="text-[9.5px] font-mono text-blue-600 dark:text-blue-400">({{ $komp->bobot }}%)</span>
                                            </th>
                                        @endforeach
                                        <th class="px-3 py-3 text-center w-20">Nilai Akhir</th>
                                        <th class="px-3 py-3 text-center w-14">Predikat</th>
                                        <th class="px-3 py-3 min-w-[160px]">Catatan Pembina</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($anggotaList as $idx => $item)
                                        @php
                                            $santri = $item->santri;
                                            $nilai = $penilaianMap->get($santri->id);
                                            $autoAbs = $autoAbsensiMap[$santri->id] ?? 100.0;
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="px-3 py-2.5 font-mono text-slate-400">{{ $idx + 1 }}</td>
                                            <td class="px-4 py-2.5">
                                                <p class="font-bold text-slate-900 dark:text-white truncate">{{ $santri->user->name ?? '-' }}</p>
                                                <p class="text-[10.5px] font-mono text-slate-400">{{ $santri->nomor_induk }}</p>
                                            </td>
                                            @foreach($komponenList as $komp)
                                                <td class="px-3 py-2.5 text-center">
                                                    @if($komp->is_absensi)
                                                        <input type="hidden" name="nilai[{{ $santri->id }}][komponen][{{ $komp->id }}]" value="{{ $autoAbs }}" :disabled="isMobile || !isEdit">
                                                        <span class="inline-flex px-2 py-1 rounded-lg text-xs font-mono font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/60" title="Kehadiran: {{ $autoAbs }}%">
                                                            {{ $autoAbs }}%
                                                        </span>
                                                    @else
                                                        <div x-show="!isEdit" class="font-bold font-mono text-slate-800 dark:text-slate-200">
                                                            {{ $nilai?->nilai_komponen[$komp->id] !== null && $nilai?->nilai_komponen[$komp->id] !== '' ? $nilai->nilai_komponen[$komp->id] : '-' }}
                                                        </div>
                                                        <div x-show="isEdit">
                                                            <input
                                                                type="number"
                                                                step="0.5"
                                                                min="0"
                                                                max="100"
                                                                name="nilai[{{ $santri->id }}][komponen][{{ $komp->id }}]"
                                                                value="{{ $nilai?->nilai_komponen[$komp->id] ?? '' }}"
                                                                placeholder="0-100"
                                                                :disabled="isMobile || !isEdit"
                                                                class="w-18 text-center px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                                            />
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-3 py-2.5 text-center font-bold font-mono text-slate-900 dark:text-white">
                                                {{ $nilai?->nilai_angka ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                                    {{ $nilai?->nilai_huruf ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <div x-show="!isEdit" class="text-xs text-slate-600 dark:text-slate-400">
                                                    {{ $nilai?->catatan ?: '-' }}
                                                </div>
                                                <div x-show="isEdit">
                                                    <input
                                                        type="text"
                                                        name="nilai[{{ $santri->id }}][catatan]"
                                                        value="{{ $nilai?->catatan }}"
                                                        placeholder="Evaluasi..."
                                                        :disabled="isMobile || !isEdit"
                                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ 5 + $komponenList->count() }}" class="px-4 py-8 text-center text-slate-400 text-xs">Belum ada mahasantri di kelompok ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-slot:desktop>

                    <x-slot:mobile>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 p-3 space-y-3">
                            @forelse($anggotaList as $idx => $item)
                                @php
                                    $santri = $item->santri;
                                    $nilai = $penilaianMap->get($santri->id);
                                    $autoAbs = $autoAbsensiMap[$santri->id] ?? 100.0;
                                @endphp
                                <div class="p-3 rounded-xl bg-slate-50/50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold text-xs text-slate-900 dark:text-white">{{ $santri->user->name ?? '-' }}</p>
                                        <span class="text-xs font-mono font-bold">{{ $nilai?->nilai_huruf ?? '-' }} ({{ $nilai?->nilai_angka ?? '-' }})</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        @foreach($komponenList as $komp)
                                            <div>
                                                <span class="text-[10px] text-slate-500">{{ $komp->nama }} ({{ $komp->bobot }}%)</span>
                                                @if($komp->is_absensi)
                                                    <input type="hidden" name="nilai[{{ $santri->id }}][komponen][{{ $komp->id }}]" value="{{ $autoAbs }}" :disabled="!isMobile || !isEdit">
                                                    <p class="font-bold text-blue-600 font-mono">{{ $autoAbs }}%</p>
                                                @else
                                                    <div x-show="!isEdit" class="font-bold font-mono text-slate-800 dark:text-slate-200 py-1">
                                                        {{ $nilai?->nilai_komponen[$komp->id] !== null && $nilai?->nilai_komponen[$komp->id] !== '' ? $nilai->nilai_komponen[$komp->id] : '-' }}
                                                    </div>
                                                    <div x-show="isEdit">
                                                        <input type="number" step="0.5" min="0" max="100" name="nilai[{{ $santri->id }}][komponen][{{ $komp->id }}]" value="{{ $nilai?->nilai_komponen[$komp->id] ?? '' }}" :disabled="!isMobile || !isEdit" class="w-full px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div>
                                        <div x-show="!isEdit" class="text-xs text-slate-600 dark:text-slate-400 py-1">
                                            {{ $nilai?->catatan ?: '-' }}
                                        </div>
                                        <div x-show="isEdit">
                                            <input type="text" name="nilai[{{ $santri->id }}][catatan]" value="{{ $nilai?->catatan }}" placeholder="Catatan evaluasi..." :disabled="!isMobile || !isEdit" class="w-full px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-slate-400">Belum ada mahasantri.</div>
                            @endforelse
                        </div>
                    </x-slot:mobile>
                </x-table-container>
            </form>

            @include('kelas.nilai.komponen-modal')
        @endif
    </div>
</x-app-layout>