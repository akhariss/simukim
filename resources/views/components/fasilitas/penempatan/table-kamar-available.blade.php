@props(['availableRooms'])

{{-- Tabel Kamar Tersedia (Swap View: Alokasi Berbasis Kamar) --}}
<x-table-container>
    <x-slot:toolbar>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2">
            <div class="flex items-center gap-2">
                <svg class="size-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="text-xs font-bold text-blue-900 dark:text-blue-300">
                    Katalog Unit Kamar Siap Huni — {{ $availableRooms->total() }} Kamar Tersedia
                </span>
            </div>
            <span class="text-[10.5px] sm:text-[11px] text-blue-700 dark:text-blue-400 font-medium">Klik &quot;Alokasi Santri&quot; untuk memilih mahasantri pengisi</span>
        </div>
    </x-slot:toolbar>

    <x-slot:desktop>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-700/80">
                    <th class="px-5 py-3 w-12 text-center text-[10.5px] font-bold uppercase tracking-wider text-slate-500">No</th>
                    <th class="px-5 py-3 text-[10.5px] font-bold uppercase tracking-wider text-slate-500">Unit &amp; Lokasi Asrama</th>
                    <th class="px-5 py-3 text-[10.5px] font-bold uppercase tracking-wider text-slate-500">Zonasi Gender</th>
                    <th class="px-5 py-3 text-[10.5px] font-bold uppercase tracking-wider text-slate-500">Daya Tampung &amp; Kuota</th>
                    <th class="px-5 py-3 text-[10.5px] font-bold uppercase tracking-wider text-slate-500">Penghuni Terdaftar</th>
                    <th class="px-5 py-3 text-right text-[10.5px] font-bold uppercase tracking-wider text-slate-500">Aksi Penempatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($availableRooms as $kamar)
                    @php
                        $terisi = (int) $kamar->terisi_count;
                        $sisa = max(0, $kamar->kapasitas_maks - $terisi);
                        $persen = round(($terisi / $kamar->kapasitas_maks) * 100);
                        $isP = $kamar->gender->value === 'P';
                    @endphp
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-center text-slate-400 font-mono text-xs font-semibold">
                            {{ $availableRooms->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white font-mono">{{ $kamar->nomor_kamar }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    {{ $kamar->lantai->gedung->nama ?? '-' }} · Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full border shadow-2xs {{ $isP ? 'badge-akhwat' : 'badge-ikhwan' }}">
                                {{ $isP ? 'Akhwat (Putri)' : 'Ikhwan (Putra)' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="w-36 space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $terisi }}/{{ $kamar->kapasitas_maks }} Bed</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Sisa {{ $sisa }} Slot</span>
                                </div>
                                <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($kamar->penempatanAktif && $kamar->penempatanAktif->isNotEmpty())
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($kamar->penempatanAktif as $pa)
                                        <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                            {{ Str::limit($pa->santri->user->name ?? $pa->santri->nomor_induk, 14) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum berpenghuni</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <button
                                type="button"
                                @click="$dispatch('open-batch-assign', {
                                    kamarId: {{ $kamar->id }},
                                    nomorKamar: '{{ $kamar->nomor_kamar }}',
                                    gender: '{{ $kamar->gender->value }}',
                                    sisa: {{ $sisa }}
                                })"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition cursor-pointer"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                <span>Alokasi Santri</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                            Seluruh unit kamar terisi penuh atau tidak ditemukan kamar yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse($availableRooms as $kamar)
            @php
                $terisi = (int) $kamar->terisi_count;
                $sisa = max(0, $kamar->kapasitas_maks - $terisi);
                $isP = $kamar->gender->value === 'P';
            @endphp
            <div class="p-3.5 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white font-mono">{{ $kamar->nomor_kamar }}</span>
                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full border {{ $isP ? 'badge-akhwat' : 'badge-ikhwan' }}">
                                {{ $isP ? 'Akhwat' : 'Ikhwan' }}
                            </span>
                        </div>
                        <div class="text-[10.5px] text-slate-500 mt-0.5 truncate">
                            {{ $kamar->lantai->gedung->nama ?? '-' }} · Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 shrink-0">
                        Sisa {{ $sisa }} Bed
                    </span>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-slate-100/80 dark:border-slate-800/80">
                    <span class="text-[11px] text-slate-500">Okupansi: {{ $terisi }}/{{ $kamar->kapasitas_maks }} Bed</span>
                    <button
                        type="button"
                        @click="$dispatch('open-batch-assign', {
                            kamarId: {{ $kamar->id }},
                            nomorKamar: '{{ $kamar->nomor_kamar }}',
                            gender: '{{ $kamar->gender->value }}',
                            sisa: {{ $sisa }}
                        })"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-2xs cursor-pointer"
                    >
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>Alokasi Santri</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-slate-400">Tidak ada kamar tersedia.</div>
        @endforelse
    </x-slot:mobile>

    @if($availableRooms->hasPages())
        <x-slot:pagination>
            {{ $availableRooms->links() }}
        </x-slot:pagination>
    @endif
</x-table-container>
