<x-app-layout title="Presensi Kajian Santri">
    <div class="space-y-4" x-data="{
        mode: 'input',
        riwayatFilterSesi: 'all',
        setAll(status) {
            document.querySelectorAll('.radio-status-' + status).forEach(r => { r.checked = true; });
            document.querySelectorAll('.status-select-mobile').forEach(s => { s.value = status; s.dispatchEvent(new Event('change')); });
        }
    }">
        @if(!$hasSelected)
            <x-page-header :breadcrumbs="[['label' => 'Akademik Kajian'], ['label' => 'Presensi Harian']]" title="Presensi Harian Mahasantri" description="Pilih kelas kajian yang diampu untuk melakukan presensi harian atau melihat riwayat." />
            @include('kelas.absensi.partials.kajian-list')
        @else
            <x-page-header
                :breadcrumbs="[['label' => 'Akademik Kajian', 'url' => route('kelas.absensi.index')], ['label' => 'Presensi Harian', 'url' => route('kelas.absensi.index')], ['label' => $selectedKelas->nama . ' (' . $selectedKelompok->nama . ')']]"
                title="{{ $selectedKelas->nama }}"
                description="Kelompok: {{ $selectedKelompok->nama }} • Jadwal: {{ $jadwalList->first()?->hari ?? 'Sesuai Jadwal' }} • Semester {{ $periode?->nama ?? '-' }}"
            >
                <x-slot:actions>
                    <a href="{{ route('kelas.absensi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Daftar Kajian</span>
                    </a>
                    <a href="{{ route('kelas.absensi.rekap', ['kelompok_id' => $selectedKelompok->id, 'kelas_id' => $selectedKelas->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        <svg class="size-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Rekapitulasi Kehadiran</span>
                    </a>
                </x-slot:actions>
            </x-page-header>

            {{-- 1. TAB SWITCHER AT THE VERY TOP --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-1">
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80">
                    <button type="button" @click="mode = 'input'" :class="mode === 'input' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="px-2.5 sm:px-3 py-1 text-xs rounded-lg transition cursor-pointer">
                        Presensi Sesi Berjalan
                    </button>
                    <button type="button" @click="mode = 'history'" :class="mode === 'history' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="px-2.5 sm:px-3 py-1 text-xs rounded-lg transition cursor-pointer">
                        Riwayat Presensi Sesi ({{ $riwayatSesi->count() }})
                    </button>
                </div>
                <div x-show="mode === 'input'" class="flex items-center gap-1.5 text-xs">
                    <span class="font-bold text-slate-500 text-[10.5px]">Tandai Semua:</span>
                    <button type="button" @click="setAll('hadir')" class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 text-[11px] font-bold hover:bg-emerald-100 transition cursor-pointer">Hadir Semua</button>
                    <button type="button" @click="setAll('izin')" class="px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 text-[11px] font-bold hover:bg-amber-100 transition cursor-pointer">Izin Semua</button>
                </div>
                <div x-show="mode === 'history'" class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500 whitespace-nowrap">Filter Sesi:</label>
                    <select x-model="riwayatFilterSesi" class="px-3 py-1 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-medium">
                        <option value="all">Semua Sesi Pertemuan (Default)</option>
                        @foreach($jadwalList as $j)<option value="{{ $j->id }}">{{ $j->nama_sesi }}</option>@endforeach
                    </select>
                </div>
            </div>

            {{-- 2. MODE 1: FORM INPUT PRESENSI --}}
            <div x-show="mode === 'input'">
                <form method="POST" action="{{ route('kelas.absensi.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="kelompok_id" value="{{ $selectedKelompok->id }}">
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">
                    <input type="hidden" name="jadwal_id" value="{{ $selectedJadwalId }}">
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    {{-- Sesi Jadwal Pertemuan, Tanggal & Catatan Sesi --}}
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sesi Jadwal Pertemuan</label>
                                <select id="sel-sesi" onchange="window.location.href='{{ route('kelas.absensi.index') }}?kelompok_id={{ $selectedKelompok->id }}&kelas_id={{ $selectedKelas->id }}&jadwal_id=' + this.value + '&tanggal=' + document.getElementById('sel-tgl').value" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium">
                                    @forelse($jadwalList as $j)<option value="{{ $j->id }}" {{ $selectedJadwalId === $j->id ? 'selected' : '' }}>{{ $j->nama_sesi }} ({{ $j->hari }}, {{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }})</option>@empty<option value="">Sesi Kajian</option>@endforelse
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                                <input type="date" id="sel-tgl" value="{{ $tanggal }}" onchange="window.location.href='{{ route('kelas.absensi.index') }}?kelompok_id={{ $selectedKelompok->id }}&kelas_id={{ $selectedKelas->id }}&jadwal_id=' + document.getElementById('sel-sesi').value + '&tanggal=' + this.value" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium" />
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Pertemuan Sesi (Opsional)</label>
                            <input type="text" name="catatan_sesi" value="{{ $absensiMap->first()?->catatan ?? '' }}" placeholder="Catatan materi atau catatan evaluasi untuk pertemuan sesi ini..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>

                    {{-- Tabel Presensi Santri --}}
                    <x-table-container>
                        <x-slot:desktop>
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                                    <tr>
                                        <th class="px-4 py-3 w-10 text-center">No</th>
                                        <th class="px-4 py-3">Mahasantri</th>
                                        <th class="px-4 py-3">Kamar</th>
                                        <th class="px-4 py-3 text-center">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($anggotaList as $idx => $item)
                                        @php
                                            $santri = $item->santri;
                                            $record = $absensiMap->get($santri->id);
                                            $currentStatus = $record ? $record->status->value : 'hadir';
                                            $kamar = $santri->penempatanAktif?->kamar;
                                            $kamarText = $kamar ? $kamar->nomor_kamar : ($santri->kamar_asrama ?? '-');
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="px-4 py-3 font-mono text-slate-400 text-center">{{ $idx + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <input type="hidden" name="items[{{ $idx }}][santri_id]" value="{{ $santri->id }}">
                                                <p class="font-bold text-slate-900 dark:text-white">{{ $santri->user->name ?? '-' }}</p>
                                                <p class="text-[11px] font-mono text-slate-400">{{ $santri->nomor_induk }}</p>
                                            </td>
                                            <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400">{{ $kamarText }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-2.5">
                                                    @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa', 'terlambat' => 'Terlambat'] as $key => $lbl)
                                                        <label class="inline-flex items-center gap-1 cursor-pointer text-xs">
                                                            <input type="radio" name="items[{{ $idx }}][status]" value="{{ $key }}" class="radio-status-{{ $key }} text-blue-600" {{ $currentStatus === $key ? 'checked' : '' }}>
                                                            <span class="text-[11px] font-medium">{{ $lbl }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-4 py-8 text-center text-xs text-slate-400">Tidak ada santri di kelompok ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </x-slot:desktop>

                        <x-slot:mobile>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold uppercase text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                        <tr><th class="px-2.5 py-2 w-8 text-center">No</th><th class="px-2.5 py-2">Mahasantri</th><th class="px-2.5 py-2 text-right">Status</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @forelse($anggotaList as $idx => $item)
                                            @php
                                                $santri = $item->santri;
                                                $record = $absensiMap->get($santri->id);
                                                $currentStatus = $record ? $record->status->value : 'hadir';
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                                <td class="px-2.5 py-2 font-mono text-slate-400 text-center text-[11px]">{{ $idx + 1 }}</td>
                                                <td class="px-2.5 py-2 min-w-0">
                                                    <input type="hidden" name="items[{{ $idx }}][santri_id]" value="{{ $santri->id }}">
                                                    <p class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $santri->user->name ?? '-' }}</p>
                                                    <p class="text-[10px] font-mono text-slate-400">{{ $santri->nomor_induk }}</p>
                                                </td>
                                                <td class="px-2.5 py-2 text-right">
                                                    <select name="items[{{ $idx }}][status]" class="status-select-mobile text-xs font-semibold rounded-lg px-2 py-1 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                                        <option value="hadir" {{ $currentStatus === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                        <option value="izin" {{ $currentStatus === 'izin' ? 'selected' : '' }}>Izin</option>
                                                        <option value="sakit" {{ $currentStatus === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                        <option value="alfa" {{ $currentStatus === 'alfa' ? 'selected' : '' }}>Alfa</option>
                                                        <option value="terlambat" {{ $currentStatus === 'terlambat' ? 'selected' : '' }}>Telat</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400">Belum ada mahasantri.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-slot:mobile>

                        <x-slot:pagination>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs text-slate-500">{{ $anggotaList->count() }} mahasantri terdaftar</span>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 rounded-xl transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan Presensi Sesi</span>
                                </button>
                            </div>
                        </x-slot:pagination>
                    </x-table-container>
                </form>
            </div>

            {{-- 3. MODE 2: RIWAYAT SESI PERTEMUAN --}}
            <div x-show="mode === 'history'" x-cloak class="space-y-3">
                <x-table-container>
                    <x-slot:desktop>
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Sesi Pertemuan</th>
                                    <th class="px-4 py-3 text-center">Hadir</th>
                                    <th class="px-4 py-3 text-center">Izin</th>
                                    <th class="px-4 py-3 text-center">Sakit</th>
                                    <th class="px-4 py-3 text-center">Alfa</th>
                                    <th class="px-4 py-3 text-center">Total</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($riwayatSesi as $r)
                                    <tr x-show="riwayatFilterSesi === 'all' || riwayatFilterSesi == '{{ $r->jadwal_id }}'" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white font-mono">{{ date('d M Y', strtotime($r->tanggal)) }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $r->nama_sesi }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $r->hadir }}</td>
                                        <td class="px-4 py-3 text-center text-amber-600">{{ $r->izin }}</td>
                                        <td class="px-4 py-3 text-center text-indigo-600">{{ $r->sakit }}</td>
                                        <td class="px-4 py-3 text-center text-rose-600">{{ $r->alfa }}</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ $r->total_santri }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $selectedKelompok->id, 'kelas_id' => $selectedKelas->id, 'tanggal' => $r->tanggal, 'jadwal_id' => $r->jadwal_id]) }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                                                Perbarui &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400 text-xs">Belum ada riwayat presensi tercatat untuk mata kajian ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </x-slot:desktop>
                    <x-slot:mobile>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($riwayatSesi as $r)
                                <div x-show="riwayatFilterSesi === 'all' || riwayatFilterSesi == '{{ $r->jadwal_id }}'" class="p-3 flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white font-mono">{{ $r->nama_sesi }} &bull; {{ date('d M Y', strtotime($r->tanggal)) }}</p>
                                        <p class="text-[11px] text-slate-400">H: {{ $r->hadir }} &bull; I: {{ $r->izin }} &bull; S: {{ $r->sakit }} &bull; A: {{ $r->alfa }}</p>
                                    </div>
                                    <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $selectedKelompok->id, 'kelas_id' => $selectedKelas->id, 'tanggal' => $r->tanggal, 'jadwal_id' => $r->jadwal_id]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400">Edit &rarr;</a>
                                </div>
                            @empty
                                <div class="p-6 text-center text-slate-400 text-xs">Belum ada riwayat presensi yang terekam.</div>
                            @endforelse
                        </div>
                    </x-slot:mobile>
                </x-table-container>
            </div>
        @endif
    </div>
</x-app-layout>
