<x-app-layout title="Detail Kajian — {{ $kelas->nama }}">
    @php
        $santriData = $kelompok->anggota->map(fn($item, $idx) => [
            'id' => $item->santri_id,
            'index' => $idx + 1,
            'nim' => $item->santri->nomor_induk ?? '-',
            'name' => $item->santri->user->name ?? '-',
            'gender' => ($item->santri->gender?->value ?? $item->santri->gender) === 'L' ? 'L' : 'P',
            'prodi' => $item->santri->prodi ?? 'Umum',
            'kamar' => $item->santri->penempatanAktif?->kamar
                ? (($item->santri->penempatanAktif->kamar->lantai->gedung->nama ?? '') . ' - ' . $item->santri->penempatanAktif->kamar->nomor_kamar)
                : 'Belum ada kamar',
        ])->values();
    @endphp

    <div class="space-y-4 sm:space-y-5" x-data="{
        search: '',
        currentPage: 1,
        perPage: 30,
        santriList: {{ \Illuminate\Support\Js::from($santriData) }},
        get filteredList() {
            if (!this.search.trim()) return this.santriList;
            const q = this.search.toLowerCase().trim();
            return this.santriList.filter(s =>
                s.name.toLowerCase().includes(q) ||
                s.nim.toLowerCase().includes(q) ||
                s.prodi.toLowerCase().includes(q) ||
                s.kamar.toLowerCase().includes(q)
            );
        },
        get paginatedList() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredList.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredList.length / this.perPage));
        }
    }">
        {{-- Header Halaman & Breadcrumbs --}}
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Akademik Kajian', 'url' => route('tutor.kajian.index')],
                ['label' => 'Kajian Saya', 'url' => route('tutor.kajian.index')],
                ['label' => $kelas->nama]
            ]"
            title="{{ $kelas->nama }}"
            description="Kelompok Halaqah: {{ $kelompok->nama }}"
        >
            <x-slot:actions>
                <a href="{{ route('tutor.kajian.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $kelompok->id, 'kelas_id' => $kelas->id]) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition shadow-xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Presensi Sesi</span>
                </a>
                <a href="{{ route('kelas.nilai.index', ['kelompok_id' => $kelompok->id, 'kelas_id' => $kelas->id]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Lembar Nilai</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Ringkasan Indikator Sesi & Laporan --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {{-- Presensi Terlaksana Bulan Ini --}}
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Presensi Terlaksana</span>
                <p class="font-bold text-lg text-slate-900 dark:text-white">{{ $sesiBulanIni }} Sesi</p>
                <p class="text-[11px] text-slate-500">Bulan {{ $namaBulan }} <span class="text-slate-400 font-mono">({{ $totalSesi }} Sesi Total)</span></p>
            </div>

            {{-- Laporan Bulanan --}}
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Laporan Bulanan</span>
                    <p class="font-bold text-sm text-{{ $statusLaporanColor }}-600 dark:text-{{ $statusLaporanColor }}-400 mt-1">
                        {{ $statusLaporanLabel }}
                    </p>
                    <p class="text-[11px] text-slate-500">Periode {{ $namaBulan }}</p>
                </div>
                <a href="{{ route('kelas.laporan.kelas', ['kelompok' => $kelompok->id, 'kelas' => $kelas->id]) }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1 inline-flex items-center gap-1">
                    <span>Kelola Laporan</span>
                    <span>&rarr;</span>
                </a>
            </div>

            {{-- Mahasantri Terdaftar & Nilai --}}
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mahasantri Terdaftar</span>
                <p class="font-bold text-lg text-slate-900 dark:text-white">{{ $kelompok->anggota->count() }} Santri</p>
                <p class="text-[11px] {{ $sudahDinilai > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-500' }}">
                    Nilai: {{ $sudahDinilai > 0 ? "Terekam ({$sudahDinilai} Santri)" : 'Belum Diisi' }}
                </p>
            </div>
        </div>

        {{-- Daftar Mahasantri Kelompok Halaqah --}}
        <x-table-container>
            <x-slot:toolbar>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Daftar Mahasantri Kelompok</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Seluruh mahasantri yang mengikuti kajian {{ $kelas->nama }} pada kelompok ini.</p>
                    </div>
                    <div class="w-full sm:w-64">
                        <x-search-input x-model="search" placeholder="Cari nama, NIM, jurusan..." />
                    </div>
                </div>
            </x-slot:toolbar>

            <x-slot:desktop>
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-2.5 w-12 text-center">No</th>
                            <th class="px-4 py-2.5">NIM / Nomor Induk</th>
                            <th class="px-4 py-2.5">Nama Lengkap</th>
                            <th class="px-4 py-2.5">Program Studi</th>
                            <th class="px-4 py-2.5">Kamar Asrama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(s, idx) in paginatedList" :key="s.id">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-4 py-2.5 text-slate-400 font-mono text-center" x-text="(currentPage - 1) * perPage + idx + 1"></td>
                                <td class="px-4 py-2.5 font-mono font-semibold text-slate-700 dark:text-slate-300" x-text="s.nim"></td>
                                <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white" x-text="s.name"></td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300" x-text="s.prodi"></td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300" x-text="s.kamar"></td>
                            </tr>
                        </template>
                        <tr x-show="filteredList.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">Tidak ada data mahasantri yang cocok.</td>
                        </tr>
                    </tbody>
                </table>
            </x-slot:desktop>

            <x-slot:mobile>
                <div class="p-3 space-y-2">
                    <template x-for="(s, idx) in paginatedList" :key="s.id">
                        <div class="p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-mono text-slate-400" x-text="'#' + ((currentPage - 1) * perPage + idx + 1)"></span>
                                <span class="font-mono font-semibold text-slate-600 dark:text-slate-300" x-text="s.nim"></span>
                            </div>
                            <p class="font-bold text-xs text-slate-900 dark:text-white" x-text="s.name"></p>
                            <p class="text-[11px] text-slate-500" x-text="s.prodi + ' • ' + s.kamar"></p>
                        </div>
                    </template>
                </div>
            </x-slot:mobile>
        </x-table-container>
    </div>
</x-app-layout>

