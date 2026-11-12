<x-app-layout title="Perizinan & Kedisiplinan Mahasantri">
    <div class="space-y-4">
        {{-- Header Halaman --}}
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Akademik Kajian'],
                ['label' => 'Perizinan & Kedisiplinan']
            ]"
            title="Perizinan & Kedisiplinan Mahasantri"
            description="Pencatatan perizinan dan pembinaan disiplin mahasantri pada kelompok kajian yang diampu."
        >
            <x-slot:actions>
                @if($tab === 'perizinan')
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'modal-tambah-izin')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Perizinan</span>
                    </button>
                @else
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'modal-tambah-pelanggaran')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-2xs transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Catat Pelanggaran Disiplin</span>
                    </button>
                @endif
            </x-slot:actions>
        </x-page-header>

        {{-- Tab Switcher --}}
        <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
            <x-tab-switcher
                :tabs="[
                    ['id' => 'perizinan', 'label' => 'Perizinan Mahasantri (' . ($statsPerizinan->total ?? 0) . ')', 'short_label' => 'Perizinan', 'url' => request()->fullUrlWithQuery(['tab' => 'perizinan'])],
                    ['id' => 'pelanggaran', 'label' => 'Pelanggaran & Pembinaan (' . ($statsPelanggaran->total ?? 0) . ')', 'short_label' => 'Kedisiplinan', 'url' => request()->fullUrlWithQuery(['tab' => 'pelanggaran'])],
                ]"
                :active="$tab"
                color="blue"
            />

            {{-- Quick Search Form --}}
            <form method="GET" action="{{ route('tutor.kedisiplinan.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama / NIM santri..."
                        class="w-44 sm:w-60 pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                    />
                    <svg class="size-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>
        </div>

        @if($tab === 'perizinan')
            {{-- TAB 1: PERIZINAN --}}
            <div class="grid grid-cols-3 gap-3">
                <x-kpi-card label="Total Perizinan" :value="$statsPerizinan->total ?? 0" note="Mahasantri Kelompok Diampu" color="blue"><x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon></x-kpi-card>
                <x-kpi-card label="Izin Aktif" :value="$statsPerizinan->berlaku ?? 0" note="Sedang Izin Keluar Asrama" color="amber"><x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon></x-kpi-card>
                <x-kpi-card label="Telah Kembali" :value="$statsPerizinan->selesai ?? 0" note="Telah Kembali ke Asrama" color="emerald"><x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon></x-kpi-card>
            </div>

            <x-table-container>
                <x-slot:desktop>
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Mahasantri</th>
                                <th class="px-4 py-3">Kategori Perizinan</th>
                                <th class="px-4 py-3">Masa Berlaku</th>
                                <th class="px-4 py-3">Keperluan Izin</th>
                                <th class="px-4 py-3 text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($perizinanList as $izin)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $izin->santri->user->name ?? '-' }}</p>
                                        <p class="text-[11px] font-mono text-slate-400">{{ $izin->santri->nomor_induk }} &bull; {{ $izin->santri->penempatanAktif?->kamar?->nomor_kamar ? ('Kamar ' . $izin->santri->penempatanAktif->kamar->nomor_kamar) : 'Belum Ditempatkan' }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $izin->jenisPerizinan->nama ?? 'Izin Santri' }}</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">{{ $izin->tanggal_mulai->format('d/m/Y') }} s/d {{ $izin->tanggal_selesai->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate">{{ $izin->alasan }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $izin->status->value === 'berlaku' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                            {{ $izin->status->value === 'berlaku' ? 'Sedang di Luar Asrama' : 'Telah Kembali' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">Tidak ada riwayat perizinan mahasantri pada semester aktif ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-slot:desktop>
                <x-slot:mobile>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($perizinanList as $izin)
                            <div class="p-3.5 space-y-1">
                                <div class="flex justify-between items-start">
                                    <p class="font-bold text-xs text-slate-900 dark:text-white">{{ $izin->santri->user->name ?? '-' }}</p>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $izin->status->value === 'berlaku' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $izin->status->value === 'berlaku' ? 'Di Luar' : 'Kembali' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $izin->santri->nomor_induk }} &bull; {{ $izin->jenisPerizinan->nama ?? 'Izin' }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300">{{ $izin->alasan }}</p>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 text-xs">Tidak ada data perizinan.</div>
                        @endforelse
                    </div>
                </x-slot:mobile>
            </x-table-container>
            <div class="mt-3">{{ $perizinanList->links() }}</div>

        @else
            {{-- TAB 2: PELANGGARAN --}}
            <div class="grid grid-cols-3 gap-3">
                <x-kpi-card label="Total Pelanggaran" :value="$statsPelanggaran->total ?? 0" note="Mahasantri Kelompok Diampu" color="rose"><x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon></x-kpi-card>
                <x-kpi-card label="Menunggu Pembinaan" :value="$statsPelanggaran->terdaftar ?? 0" note="Tindak Lanjut Pembinaan" color="amber"><x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon></x-kpi-card>
                <x-kpi-card label="Pembinaan Selesai" :value="$statsPelanggaran->selesai ?? 0" note="Sanksi Terlaksana" color="emerald"><x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon></x-kpi-card>
            </div>

            <x-table-container>
                <x-slot:desktop>
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Mahasantri</th>
                                <th class="px-4 py-3">Tanggal Kejadian</th>
                                <th class="px-4 py-3">Tingkat Pelanggaran</th>
                                <th class="px-4 py-3">Uraian Kejadian & Sanksi</th>
                                <th class="px-4 py-3 text-center">Status Pembinaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($pelanggaranList as $pel)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $pel->santri->user->name ?? '-' }}</p>
                                        <p class="text-[11px] font-mono text-slate-400">{{ $pel->santri->nomor_induk }} &bull; {{ $pel->santri->penempatanAktif?->kamar?->nomor_kamar ? ('Kamar ' . $pel->santri->penempatanAktif->kamar->nomor_kamar) : 'Belum Ditempatkan' }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[11px]">{{ $pel->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $pel->jenisPelanggaran->nama ?? 'Pelanggaran Disiplin' }}</td>
                                    <td class="px-4 py-3 max-w-xs">
                                        <p class="truncate">{{ $pel->deskripsi }}</p>
                                        @if($pel->hukuman)<p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">Sanksi: {{ $pel->hukuman }}</p>@endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $pel->status->value === 'selesai' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                            {{ $pel->status->value === 'selesai' ? 'Pembinaan Selesai' : 'Menunggu Tindak Lanjut' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">Tidak ada catatan pelanggaran disiplin pada semester aktif ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-slot:desktop>
                <x-slot:mobile>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($pelanggaranList as $pel)
                            <div class="p-3.5 space-y-1">
                                <div class="flex justify-between items-start">
                                    <p class="font-bold text-xs text-slate-900 dark:text-white">{{ $pel->santri->user->name ?? '-' }}</p>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $pel->status->value === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $pel->status->value === 'selesai' ? 'Selesai' : 'Pending' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $pel->santri->nomor_induk }} &bull; {{ $pel->tanggal->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-700 dark:text-slate-200">{{ $pel->deskripsi }}</p>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 text-xs">Tidak ada data pelanggaran disiplin.</div>
                        @endforelse
                    </div>
                </x-slot:mobile>
            </x-table-container>
            <div class="mt-3">{{ $pelanggaranList->links() }}</div>
        @endif
    </div>

    @include('tutor.kedisiplinan.modals')
</x-app-layout>
