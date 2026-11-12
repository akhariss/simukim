<!-- Tabel Flat Monitoring Kamar Asrama (Atomic Component) -->
<div class="space-y-4">
    @if($kamarList->count() > 0)
        <x-table-container>
            <x-slot:toolbar>
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200">{{ $kamarList->firstItem() }}–{{ $kamarList->lastItem() }}</span> dari <span class="font-bold text-slate-800 dark:text-slate-200">{{ $kamarList->total() }}</span> unit kamar
                    </div>
                    @if(request()->hasAny(['gedung_id', 'lantai_id', 'status', 'gender', 'tipe_ruang', 'search']))
                        <span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-blue-200/60 dark:border-slate-700">
                            Filter Aktif
                        </span>
                    @endif
                </div>
            </x-slot:toolbar>

            <x-slot:desktop>
                <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">No. Kamar</th>
                            <th class="px-4 py-3">Lokasi</th>
                            <th class="px-4 py-3">Gender</th>
                            <th class="px-4 py-3">Kapasitas</th>
                            <th class="px-4 py-3">Penghuni Aktif</th>
                            <th class="px-4 py-3">Tipe Ruang</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($kamarList as $kamar)
                            @php
                                $terisi = $kamar->penempatanAktif->count();
                                $isFull = $terisi >= $kamar->kapasitas_maks;
                                $isEmpty = $terisi === 0;
                                $isPerbaikan = ($kamar->status->value ?? $kamar->status) === 'perbaikan';
                                $genderVal = $kamar->gender->value ?? $kamar->gender;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                    <span class="text-sm tracking-tight">{{ $kamar->nomor_kamar }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $kamar->lantai->gedung->nama ?? '-' }} • Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-gender-badge :gender="$genderVal" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold {{ $isFull ? 'text-rose-600 dark:text-rose-400' : ($terisi > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400') }}">
                                            {{ $terisi }}/{{ $kamar->kapasitas_maks }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">Bed</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($kamar->penempatanAktif->count() > 0)
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex -space-x-1.5 overflow-hidden">
                                                @foreach($kamar->penempatanAktif->take(3) as $p)
                                                    <span
                                                        title="{{ $p->santri->user->name ?? 'Santri' }}"
                                                        class="size-5 rounded-full bg-blue-600 text-white font-bold text-[8.5px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900 shrink-0"
                                                    >
                                                        {{ strtoupper(substr($p->santri->user->name ?? 'S', 0, 1)) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            <span class="text-[11px] text-slate-700 dark:text-slate-300 truncate max-w-[140px]">
                                                {{ $kamar->penempatanAktif->first()->santri->user->name ?? '' }}
                                                @if($kamar->penempatanAktif->count() > 1)
                                                    <span class="text-slate-400 font-normal">(+{{ $kamar->penempatanAktif->count() - 1 }})</span>
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Kosong</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 capitalize text-slate-700 dark:text-slate-300">
                                    {{ $kamar->tipe_ruang instanceof \App\Enums\TipeRuang ? $kamar->tipe_ruang->label() : ($kamar->tipe_ruang ? ucfirst((string) $kamar->tipe_ruang) : 'Kamar Santri') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($isPerbaikan)
                                        <x-badge variant="warning">Perbaikan</x-badge>
                                    @elseif($isFull)
                                        <x-badge variant="danger">Penuh</x-badge>
                                    @elseif($isEmpty)
                                        <x-badge variant="neutral">Kosong</x-badge>
                                    @else
                                        <x-badge variant="success">Tersedia (Sisa {{ max(0, $kamar->kapasitas_maks - $terisi) }})</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                        title="Lihat Detail Kamar & Penghuni"
                                    >
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot:desktop>

        <!-- 2. Tampilan Khusus Mobile (< md): Stacked List Responsif (HARAM GESER HORIZONTAL) -->
        <x-slot:mobile>
                @foreach($kamarList as $kamar)
                    @php
                        $terisi = $kamar->penempatanAktif->count();
                        $isFull = $terisi >= $kamar->kapasitas_maks;
                        $isEmpty = $terisi === 0;
                        $isPerbaikan = ($kamar->status->value ?? $kamar->status) === 'perbaikan';
                        $genderVal = $kamar->gender->value ?? $kamar->gender;
                    @endphp
                    <div class="p-3.5 space-y-2.5">
                        <!-- Baris Atas: No Kamar & Badges -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-sm font-black text-slate-900 dark:text-white">{{ $kamar->nomor_kamar }}</span>
                                <x-gender-badge :gender="$genderVal" />
                                <span class="text-[9.5px] font-semibold text-slate-400 capitalize">
                                    • {{ $kamar->tipe_ruang instanceof \App\Enums\TipeRuang ? $kamar->tipe_ruang->label() : ($kamar->tipe_ruang ? ucfirst((string) $kamar->tipe_ruang) : 'Santri') }}
                                </span>
                            </div>

                            <!-- Badge Status Ketersediaan -->
                            @if($isPerbaikan)
                                <x-badge variant="warning">Perbaikan</x-badge>
                            @elseif($isFull)
                                <x-badge variant="danger">Penuh</x-badge>
                            @elseif($isEmpty)
                                <x-badge variant="neutral">Kosong</x-badge>
                            @else
                                <x-badge variant="success">Tersedia</x-badge>
                            @endif
                        </div>

                        <!-- Lokasi & Kapasitas Info -->
                        <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                            <div class="flex items-center gap-1.5 font-medium truncate">
                                <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="truncate">{{ $kamar->lantai->gedung->nama ?? '-' }} • Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}</span>
                            </div>
                            <div class="font-bold shrink-0 {{ $isFull ? 'text-rose-600 dark:text-rose-400' : ($terisi > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400') }}">
                                {{ $terisi }}/{{ $kamar->kapasitas_maks }} <span class="text-[10px] font-normal text-slate-400">Bed</span>
                            </div>
                        </div>

                        <!-- Penghuni Aktif -->
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 text-xs">
                            @if($kamar->penempatanAktif->count() > 0)
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <div class="flex -space-x-1.5 overflow-hidden shrink-0">
                                            @foreach($kamar->penempatanAktif->take(3) as $p)
                                                <span
                                                    title="{{ $p->santri->user->name ?? 'Santri' }}"
                                                    class="size-5 rounded-full bg-blue-600 text-white font-bold text-[8.5px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900"
                                                >
                                                    {{ strtoupper(substr($p->santri->user->name ?? 'S', 0, 1)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <span class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 truncate">
                                            {{ $kamar->penempatanAktif->first()->santri->user->name ?? '' }}
                                            @if($kamar->penempatanAktif->count() > 1)
                                                <span class="text-slate-400 font-normal">(+{{ $kamar->penempatanAktif->count() - 1 }})</span>
                                            @endif
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                                        class="px-2 py-1 rounded-lg text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-100/60 dark:hover:bg-blue-900/40 transition shrink-0 cursor-pointer"
                                    >
                                        Detail
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] text-slate-400 italic">Belum ada penghuni</span>
                                    <button
                                        type="button"
                                        @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                                        class="px-2 py-1 rounded-lg text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-100/60 dark:hover:bg-blue-900/40 transition shrink-0 cursor-pointer"
                                    >
                                        Detail
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </x-slot:mobile>

            <x-slot:pagination>
                {{ $kamarList->links() }}
            </x-slot:pagination>
        </x-table-container>
    @else
        <x-empty-state
            title="Tidak ada kamar ditemukan"
            description="Coba sesuaikan kata kunci pencarian atau ubah filter gedung/lantai di toolbar atas."
        />
    @endif
</div>
