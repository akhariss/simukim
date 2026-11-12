<x-app-layout>
    <x-slot name="title">Laporan Bulanan Tutor Kajian</x-slot>

    @if((auth()->user()->hasRole('tutor') && !auth()->user()->hasRole('admin')) || request('mode') === 'saya')
        <!-- TUTOR VIEW: Daftar Kelas / Kelompok Diampu -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kajian Saya', 'url' => route('tutor.kajian.index')],
                ['label' => 'Laporan Bulanan']
            ]"
            title="Laporan Bulanan Kegiatan Kajian"
            description="Pilih mata kajian yang Anda ampu untuk mengelola arsip dan mengirimkan laporan kegiatan bulanan."
        />

        <x-table-container>
            <x-slot:desktop>
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Kelompok Halaqah</th>
                            <th class="px-4 py-3">Mata Kajian</th>
                            <th class="px-4 py-3">Jadwal Sesi</th>
                            <th class="px-4 py-3 text-center">Status Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</th>
                            <th class="px-4 py-3 text-center">Total Terkirim</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                        @forelse($myKajianList as $k)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">{{ $k->kelompok_nama }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $k->kelas_nama }}</span>
                                    <span class="ml-1 text-[10px] text-slate-400">({{ $k->kelas_tipe }})</span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    {{ $k->jadwal_text }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($k->laporan_bulan_ini)
                                        <x-badge :class="\App\Enums\LaporanStatus::from($k->laporan_bulan_ini->status)->badgeClass()">
                                            {{ \App\Enums\LaporanStatus::from($k->laporan_bulan_ini->status)->label() }}
                                        </x-badge>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                            Belum Terkirim
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-slate-700 dark:text-slate-300">
                                    {{ $k->total_laporan }} Laporan
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('kelas.laporan.kelas', [$k->kelompok_id, $k->kelas_id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-400 transition cursor-pointer">
                                        <span>Kelola Laporan</span>
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada kelompok kajian yang diampu pada periode aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-slot:desktop>

            <x-slot:mobile>
                @forelse($myKajianList as $k)
                    <div class="p-3.5 space-y-2.5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $k->kelompok_nama }}</h4>
                                <p class="text-[11px] text-slate-500">{{ $k->kelas_nama }} &bull; {{ $k->jadwal_text }}</p>
                            </div>
                            @if($k->laporan_bulan_ini)
                                <x-badge :class="\App\Enums\LaporanStatus::from($k->laporan_bulan_ini->status)->badgeClass()">
                                    {{ \App\Enums\LaporanStatus::from($k->laporan_bulan_ini->status)->label() }}
                                </x-badge>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                    Belum Terkirim
                                </span>
                            @endif
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-slate-400 text-[11px]">{{ $k->total_laporan }} Laporan Terkirim</span>
                            <a href="{{ route('kelas.laporan.kelas', [$k->kelompok_id, $k->kelas_id]) }}" class="font-bold text-blue-600 hover:underline">Kelola &rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada kelompok kajian yang diampu.</div>
                @endforelse
            </x-slot:mobile>
        </x-table-container>

    @else
        <!-- ADMIN VIEW: Monitoring & Verifikasi Laporan -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Laporan Bulanan']
            ]"
            title="Laporan Bulanan Tutor"
            description="Monitoring pelaksanaan kegiatan kajian santri per kelompok dan verifikasi admin."
        />

        @if($stats)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                <x-kpi-card label="Total Laporan" :value="$stats->total ?? 0" color="slate" />
                <x-kpi-card label="Menunggu Review" :value="$stats->menunggu ?? 0" color="amber" />
                <x-kpi-card label="Sedang Direview" :value="$stats->direview ?? 0" color="blue" />
                <x-kpi-card label="Selesai Diverifikasi" :value="$stats->selesai ?? 0" color="emerald" />
            </div>
        @endif

        <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
            <form method="GET" action="{{ route('kelas.laporan.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">Semua Status</option>
                    @foreach(\App\Enums\LaporanStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                    @endforeach
                </select>
                <select name="bulan" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">Semua Bulan</option>
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $bi => $bn)
                        <option value="{{ $bi + 1 }}" {{ request('bulan') == $bi + 1 ? 'selected' : '' }}>{{ $bn }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 transition">Filter</button>
                <a href="{{ route('kelas.laporan.index') }}" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700">Reset</a>
            </form>
        </div>

        <x-table-container>
            <x-slot:desktop>
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Tutor</th>
                            <th class="px-4 py-3">Kelompok</th>
                            <th class="px-4 py-3">Kajian</th>
                            <th class="px-4 py-3">Bulan / Tahun</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                        @forelse($query as $laporan)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $laporan->tutor?->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">{{ $laporan->kelompok?->nama ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $laporan->kelas?->nama ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $laporan->nama_bulan }} {{ $laporan->tahun }}</td>
                                <td class="px-4 py-3 text-center">
                                    <x-badge :class="$laporan->status->badgeClass()">{{ $laporan->status->label() }}</x-badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('kelas.laporan.show', $laporan) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 transition">
                                        Detail / Review &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada laporan kegiatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-slot:desktop>

            <x-slot:mobile>
                @forelse($query as $laporan)
                    <div class="p-3.5 space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-bold text-xs text-slate-900 dark:text-white">{{ $laporan->tutor?->name ?? '—' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $laporan->kelompok?->nama ?? '-' }} &bull; {{ $laporan->kelas?->nama ?? '-' }}</p>
                            </div>
                            <x-badge :class="$laporan->status->badgeClass()">{{ $laporan->status->label() }}</x-badge>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-slate-400 text-[11px]">{{ $laporan->nama_bulan }} {{ $laporan->tahun }}</span>
                            <a href="{{ route('kelas.laporan.show', $laporan) }}" class="font-bold text-blue-600 hover:underline">Detail &rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada laporan kegiatan.</div>
                @endforelse
            </x-slot:mobile>

            @if($query->hasPages())
                <x-slot:pagination>
                    {{ $query->links() }}
                </x-slot:pagination>
            @endif
        </x-table-container>
    @endif
</x-app-layout>