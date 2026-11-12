@php
    $tabItems = [
        [
            'id' => 'semua', 'label' => 'Semua', 'short_label' => 'Semua',
            'count' => $stats->total_izin ?? 0, 'active' => !request('status'),
            'url' => route('kedisiplinan.perizinan.index', request()->except(['status', 'page'])),
        ],
        [
            'id' => 'berlaku', 'label' => 'Sedang Berlaku', 'short_label' => 'Berlaku',
            'count' => $stats->total_berlaku ?? 0, 'active' => request('status') === 'berlaku',
            'url' => route('kedisiplinan.perizinan.index', array_merge(request()->except('page'), ['status' => 'berlaku'])),
        ],
        [
            'id' => 'selesai', 'label' => 'Telah Kembali', 'short_label' => 'Kembali',
            'count' => $stats->total_selesai ?? 0, 'active' => request('status') === 'selesai',
            'url' => route('kedisiplinan.perizinan.index', array_merge(request()->except('page'), ['status' => 'selesai'])),
        ],
        [
            'id' => 'dibatalkan', 'label' => 'Dibatalkan', 'short_label' => 'Batal',
            'count' => $stats->total_dibatalkan ?? 0, 'active' => request('status') === 'dibatalkan',
            'url' => route('kedisiplinan.perizinan.index', array_merge(request()->except('page'), ['status' => 'dibatalkan'])),
        ],
    ];
@endphp

<x-app-layout title="Perizinan & Kedisiplinan">
    <div class="space-y-4 sm:space-y-5" x-data="{ mhsTab: 'izin' }">
        <!-- Header Halaman -->
        <x-page-header
            :breadcrumbs="[['label' => $isMahasantri ? 'Perizinan & Kedisiplinan Saya' : 'Perizinan Santri']]"
            :title="$isMahasantri ? 'Perizinan & Kedisiplinan Saya' : 'Perizinan Mahasantri'"
            :description="$isMahasantri ? 'Riwayat perizinan keluar asrama dan catatan pembinaan disiplin Anda.' : 'Kelola surat izin keluar, dispensasi, kepulangan, dan pemantauan masa izin santri aktif.'"
        >
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    @if(auth()->user()?->hasRole('admin'))
                        <button
                            type="button"
                            @click="$dispatch('open-export-perizinan')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition cursor-pointer"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Ekspor Data</span>
                        </button>
                    @endif
                    @if(!$isMahasantri)
                        <button
                            type="button"
                            @click="$dispatch('open-add-izin')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>Tambah Izin Santri</span>
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        @if(!$isMahasantri)
            <!-- 1. Statistik Agregat untuk Admin/Mudabbir -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
                <x-stat-card label="Total Pengajuan" :value="number_format($stats->total_izin ?? 0)" unit="Berkas" color="blue"><x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon></x-stat-card>
                <x-stat-card label="Sedang Berlaku" :value="number_format($stats->total_berlaku ?? 0)" unit="Santri" color="amber"><x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon></x-stat-card>
                <x-stat-card label="Telah Kembali" :value="number_format($stats->total_selesai ?? 0)" unit="Santri" color="emerald"><x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon></x-stat-card>
                <x-stat-card label="Dibatalkan" :value="number_format($stats->total_dibatalkan ?? 0)" unit="Berkas" color="rose"><x-slot:icon><svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></x-slot:icon></x-stat-card>
            </div>

            <!-- Toolbar Filter Admin/Mudabbir -->
            <div class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs space-y-2.5">
                <form method="GET" action="{{ route('kedisiplinan.perizinan.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    <div class="flex-1">
                        <x-search-input name="search" :value="request('search', '')" placeholder="Ketik nama santri, NIM, atau alasan izin..." />
                    </div>
                    <div class="flex items-center gap-2">
                        <select name="jenis_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-1.5 px-3">
                            <option value="">Semua Kategori</option>
                            @foreach($jenisList as $j)<option value="{{ $j->id }}" {{ (string) request('jenis_id') === (string) $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>@endforeach
                        </select>
                    </div>
                </form>
                <x-tab-switcher :tabs="$tabItems" />
            </div>

            <x-kedisiplinan.perizinan-table :perizinanList="$perizinanList" />
        @else
            <!-- TAMPILAN TERPADU UNTUK MAHASANTRI -->
            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700">
                    <button type="button" @click="mhsTab = 'izin'" :class="mhsTab === 'izin' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="px-3.5 py-1 text-xs rounded-lg transition cursor-pointer">
                        Riwayat Perizinan ({{ $perizinanList->total() }})
                    </button>
                    <button type="button" @click="mhsTab = 'pelanggaran'" :class="mhsTab === 'pelanggaran' ? 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="px-3.5 py-1 text-xs rounded-lg transition cursor-pointer">
                        Catatan Disiplin ({{ count($pelanggaranSaya ?? []) }})
                    </button>
                </div>
            </div>

            <div x-show="mhsTab === 'izin'">
                <x-kedisiplinan.perizinan-table :perizinanList="$perizinanList" />
            </div>

            <div x-show="mhsTab === 'pelanggaran'" x-cloak>
                <x-table-container>
                    <x-slot:desktop>
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-2.5">Tanggal</th>
                                    <th class="px-4 py-2.5">Pelanggaran</th>
                                    <th class="px-4 py-2.5">Kronologi</th>
                                    <th class="px-4 py-2.5">Sanksi / Pembinaan</th>
                                    <th class="px-4 py-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($pelanggaranSaya ?? [] as $pel)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                        <td class="px-4 py-3 font-mono text-[11px] text-slate-500">{{ $pel->tanggal->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $pel->jenisPelanggaran->nama ?? 'Pelanggaran' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 max-w-xs">{{ $pel->deskripsi }}</td>
                                        <td class="px-4 py-3 text-amber-700 dark:text-amber-400 font-medium">{{ $pel->hukuman ?: '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ ($pel->status->value ?? $pel->status) === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ ($pel->status->value ?? $pel->status) === 'selesai' ? 'Pembinaan Selesai' : 'Perlu Tindak Lanjut' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">Tidak ada catatan pelanggaran disiplin. Pertahankan kedisiplinan Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </x-slot:desktop>
                    <x-slot:mobile>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($pelanggaranSaya ?? [] as $pel)
                                <div class="p-3 space-y-1 text-xs">
                                    <div class="flex justify-between items-start">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $pel->jenisPelanggaran->nama ?? 'Pelanggaran' }}</p>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($pel->status->value ?? $pel->status) === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ($pel->status->value ?? $pel->status) === 'selesai' ? 'Selesai' : 'Pending' }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $pel->tanggal->format('d/m/Y') }}</p>
                                    <p class="text-slate-600 dark:text-slate-300">{{ $pel->deskripsi }}</p>
                                    @if($pel->hukuman)<p class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">Sanksi: {{ $pel->hukuman }}</p>@endif
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-slate-400">Tidak ada catatan pelanggaran disiplin.</div>
                            @endforelse
                        </div>
                    </x-slot:mobile>
                </x-table-container>
            </div>
        @endif

        @if(!$isMahasantri)
            <x-kedisiplinan.perizinan-modal :activePeriode="$activePeriode" :santriList="$santriList" :jenisList="$jenisList" />
            <x-kedisiplinan.perizinan-detail-modal />
            @if(auth()->user()?->hasRole('admin'))
                <x-kedisiplinan.export-dialog dialogId="perizinan" title="Ekspor Data Perizinan Santri" :action="route('kedisiplinan.perizinan.export')" :activePeriode="$activePeriode" />
            @endif
        @endif
    </div>
</x-app-layout>
