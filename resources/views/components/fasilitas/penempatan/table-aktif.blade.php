{{-- Tabel Penempatan Santri Aktif --}}
<x-table-container>
    <x-slot:desktop>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-700/80">
                    <th class="px-5 py-3 w-12 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">No</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Mahasantri</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Kamar</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Tgl Masuk</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penempatanList as $penempatan)
                    @php
                        $santri = $penempatan->santri;
                        $kamar  = $penempatan->kamar;
                        $isMudabbir = $santri->status === 'mudabbir';
                    @endphp
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-center text-slate-400 dark:text-slate-500 font-mono text-xs font-semibold">
                            {{ $penempatanList->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white leading-snug">
                                    {{ $santri->user->name ?? '-' }}
                                </span>
                                @if($isMudabbir)
                                    <x-role-badge role="mudabbir" />
                                @endif
                            </div>
                            <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $santri->nomor_induk ?? '-' }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span>{{ $santri->prodi ?? 'Reguler' }}{{ !empty($santri->angkatan) ? " '" . substr((string)$santri->angkatan, -2) : '' }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span class="{{ $santri->gender === 'L' ? 'text-blue-600 dark:text-blue-400' : 'text-pink-600 dark:text-pink-400' }} font-semibold">
                                    {{ $santri->gender === 'L' ? 'Ikhwan' : 'Akhwat' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1 font-bold text-sm text-blue-700 dark:text-blue-400">
                                Kamar {{ $kamar->nomor_kamar ?? '-' }}
                            </span>
                            <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $kamar->lantai->gedung->nama ?? '-' }}, Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                {{ $penempatan->tanggal_masuk ? $penempatan->tanggal_masuk->format('d M Y') : '-' }}
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                {{ $penempatan->periode->nama ?? 'Reguler' }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex items-center gap-0.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-0.5">
                                <a
                                    href="{{ route('fasilitas.penempatan.kartu', $penempatan->id) }}"
                                    target="_blank"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-white dark:hover:bg-slate-700 transition"
                                    title="Cetak Kartu Tinggal"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <button
                                    type="button"
                                    @click="$dispatch('open-pindah-modal', {
                                        penempatanId: {{ $penempatan->id }},
                                        santriName: @js($santri->user->name ?? ''),
                                        currentRoom: @js($kamar->nomor_kamar ?? ''),
                                        gender: @js((string) $santri->gender)
                                    })"
                                    class="p-1.5 rounded-lg text-amber-600 hover:bg-white dark:hover:bg-slate-700 transition cursor-pointer"
                                    title="Pindah / Mutasi Kamar"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </button>
                                <form action="{{ route('fasilitas.penempatan.keluar', $penempatan->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-white dark:hover:bg-slate-700 transition cursor-pointer"
                                        title="Checkout / Keluar Asrama"
                                        data-confirm="Keluarkan {{ $santri->user->name ?? '' }} dari Kamar {{ $kamar->nomor_kamar }}? Slot akan kembali tersedia."
                                        data-confirm-title="Konfirmasi Checkout"
                                        data-confirm-type="warning"
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">
                            Tidak ada penempatan aktif yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse($penempatanList as $penempatan)
            @php
                $santri = $penempatan->santri;
                $kamar  = $penempatan->kamar;
                $isMudabbir = $santri->status === 'mudabbir';
            @endphp
            <div class="p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono text-[10px] text-slate-400 font-bold">
                                #{{ $penempatanList->firstItem() + $loop->index }}
                            </span>
                            <span class="font-bold text-sm text-slate-900 dark:text-white truncate">
                                {{ $santri->user->name ?? '-' }}
                            </span>
                            @if($isMudabbir)
                                <x-role-badge role="mudabbir" />
                            @endif
                        </div>
                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="font-mono font-semibold">{{ $santri->nomor_induk ?? '-' }}</span>
                            · {{ $santri->prodi ?? 'Reguler' }}
                        </p>
                    </div>
                    <span class="shrink-0 font-bold text-xs text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                        K. {{ $kamar->nomor_kamar ?? '-' }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span class="truncate">{{ $kamar->lantai->gedung->nama ?? '-' }}, Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}</span>
                    <span class="shrink-0 font-medium text-slate-700 dark:text-slate-300">{{ $penempatan->tanggal_masuk ? $penempatan->tanggal_masuk->format('d M Y') : '-' }}</span>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a
                        href="{{ route('fasilitas.penempatan.kartu', $penempatan->id) }}"
                        target="_blank"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 transition flex items-center gap-1"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </a>
                    <button
                        type="button"
                        @click="$dispatch('open-pindah-modal', {
                            penempatanId: {{ $penempatan->id }},
                            santriName: @js($santri->user->name ?? ''),
                            currentRoom: @js($kamar->nomor_kamar ?? ''),
                            gender: @js((string) $santri->gender)
                        })"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition flex items-center gap-1 cursor-pointer"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Mutasi
                    </button>
                    <form action="{{ route('fasilitas.penempatan.keluar', $penempatan->id) }}" method="POST" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-slate-700 transition flex items-center gap-1 cursor-pointer"
                            data-confirm="Keluarkan {{ $santri->user->name ?? '' }} dari Kamar {{ $kamar->nomor_kamar }}?"
                            data-confirm-title="Konfirmasi Checkout"
                            data-confirm-type="warning"
                        >
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-slate-400">
                Tidak ada penempatan aktif yang cocok dengan filter.
            </div>
        @endforelse
    </x-slot:mobile>

    <x-slot:pagination>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200">{{ $penempatanList->firstItem() ?? 0 }}–{{ $penempatanList->lastItem() ?? 0 }}</span>
                dari <span class="font-bold text-slate-800 dark:text-slate-200">{{ $penempatanList->total() }}</span> penempatan
            </div>
            <div>{{ $penempatanList->links() }}</div>
        </div>
    </x-slot:pagination>
</x-table-container>
