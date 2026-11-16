@props([
    'perizinanList',
    'activePeriode' => null,
])

@if($perizinanList->count() > 0)
    <x-table-container>
        <!-- Desktop Table (>= md) -->
        <x-slot:desktop>
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-[10.5px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="w-12 px-3.5 py-3 text-center">No</th>
                        <th class="px-3.5 py-3">Mahasantri</th>
                        <th class="px-3.5 py-3">Kamar</th>
                        <th class="px-3.5 py-3">Kategori</th>
                        <th class="px-3.5 py-3">Jadwal &amp; Durasi</th>
                        <th class="px-3.5 py-3">Status</th>
                        <th class="px-3.5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($perizinanList as $izin)
                        @php
                            $statusVal = $izin->status instanceof \App\Enums\PerizinanStatus ? $izin->status->value : $izin->status;
                            $isBerlaku = $statusVal === 'berlaku';
                            $isSelesai = $statusVal === 'selesai';
                            $durasi = $izin->tanggal_mulai->diffInDays($izin->tanggal_selesai) + 1;
                            $kamar = $izin->santri->penempatanAktif?->kamar;
                            $gedungNama = $kamar?->lantai?->gedung?->nama ?? '';
                            $kamarStr = $kamar ? "{$gedungNama} • K.{$kamar->nomor_kamar}" : 'Belum Ditempatkan';
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="px-3.5 py-3 text-center font-mono text-slate-400">{{ $perizinanList->firstItem() + $loop->index }}</td>
                            <td class="px-3.5 py-3">
                                <div class="font-bold text-slate-900 dark:text-white truncate max-w-[190px]">{{ $izin->santri->user->name ?? 'Mahasantri' }}</div>
                                <div class="text-[10.5px] text-slate-400 font-mono">{{ $izin->santri->nomor_induk }}</div>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                @if($kamar)
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300">
                                        <span>K.{{ $kamar->nomor_kamar }}</span>
                                        <span class="text-[10.5px] font-normal text-slate-400">({{ $gedungNama }})</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum Ditempatkan</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $izin->jenisPerizinan->nama ?? 'Izin Santri' }}
                                </span>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $izin->tanggal_mulai->format('d/m') }} - {{ $izin->tanggal_selesai->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-slate-400 font-semibold ml-1">({{ $durasi }} hr)</span>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                @if($isBerlaku)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-blue-300 border border-blue-200/60 dark:border-slate-700"><span class="size-1.5 rounded-full bg-blue-600"></span>Berlaku</span>
                                @elseif($isSelesai)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-slate-700"><span class="size-1.5 rounded-full bg-emerald-600"></span>Selesai</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-rose-50 text-rose-700 dark:bg-slate-800 dark:text-rose-300 border border-rose-200/60 dark:border-slate-700"><span class="size-1.5 rounded-full bg-rose-600"></span>Batal</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Tombol Detail -->
                                    <button
                                        type="button"
                                        @click="$dispatch('open-detail-izin', {
                                            id: {{ $izin->id }},
                                            nama: @js($izin->santri->user->name ?? 'Mahasantri'),
                                            nim: @js((string) $izin->santri->nomor_induk),
                                            gender: @js((($izin->santri->gender?->value ?? $izin->santri->gender) === 'L') ? 'Ikhwan' : 'Akhwat'),
                                            prodi: @js($izin->santri->prodi ?? ''),
                                            kamar: @js($kamarStr),
                                            kategori: @js($izin->jenisPerizinan->nama ?? 'Izin Santri'),
                                            tanggal_mulai: @js($izin->tanggal_mulai->format('d M Y')),
                                            tanggal_selesai: @js($izin->tanggal_selesai->format('d M Y')),
                                            durasi: {{ $durasi }},
                                            alasan: @js($izin->alasan ?? ''),
                                            status: @js($statusVal),
                                            input_by_name: @js($izin->inputBy->name ?? 'Admin Asrama'),
                                            created_at: @js($izin->created_at->format('d M Y, H:i') . ' WIB'),
                                            cetak_url: @js(route('kedisiplinan.perizinan.cetak', $izin->id))
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 transition cursor-pointer"
                                        title="Lihat Detail Izin"
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>

                                    <!-- Tombol Cetak -->
                                    <a href="{{ route('kedisiplinan.perizinan.cetak', $izin->id) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Cetak Surat Izin">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>

                                    @if($isBerlaku)
                                        <form action="{{ route('kedisiplinan.perizinan.status', $izin->id) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="selesai">
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-800 transition cursor-pointer" title="Tandai Santri Telah Kembali">
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('kedisiplinan.perizinan.destroy', $izin->id) }}" method="POST" class="inline" data-confirm="Hapus berkas perizinan santri ini?" data-confirm-title="Hapus Data Perizinan" data-confirm-type="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer" title="Hapus Data Izin">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-slot:desktop>

        <!-- Mobile Stacked Cards (< md) -->
        <x-slot:mobile>
            @foreach($perizinanList as $izin)
                @php
                    $statusVal = $izin->status instanceof \App\Enums\PerizinanStatus ? $izin->status->value : $izin->status;
                    $isBerlaku = $statusVal === 'berlaku';
                    $isSelesai = $statusVal === 'selesai';
                    $durasi = $izin->tanggal_mulai->diffInDays($izin->tanggal_selesai) + 1;
                    $kamar = $izin->santri->penempatanAktif?->kamar;
                    $gedungNama = $kamar?->lantai?->gedung?->nama ?? '';
                    $kamarStr = $kamar ? "{$gedungNama} • K.{$kamar->nomor_kamar}" : 'Belum Ditempatkan';
                @endphp
                <div class="p-3.5 space-y-2 border-b border-slate-100 dark:border-slate-800 last:border-0 text-xs">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h4 class="font-bold text-slate-900 dark:text-white truncate">{{ $izin->santri->user->name ?? 'Mahasantri' }}</h4>
                            <div class="text-[10.5px] text-slate-400 font-mono">{{ $izin->santri->nomor_induk }} &bull; {{ $kamar ? 'K.' . $kamar->nomor_kamar : 'Belum Ditempatkan' }}</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $isBerlaku ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-blue-300' : ($isSelesai ? 'bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-slate-800 dark:text-rose-300') }}">
                            {{ $isBerlaku ? 'Berlaku' : ($isSelesai ? 'Selesai' : 'Batal') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-2.5 py-1.5 rounded-lg">
                        <span>{{ $izin->jenisPerizinan->nama ?? 'Izin Santri' }}</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $izin->tanggal_mulai->format('d/m') }} - {{ $izin->tanggal_selesai->format('d/m/Y') }} ({{ $durasi }} hr)</span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button
                            type="button"
                            @click="$dispatch('open-detail-izin', {
                                id: {{ $izin->id }},
                                nama: @js($izin->santri->user->name ?? 'Mahasantri'),
                                nim: @js((string) $izin->santri->nomor_induk),
                                gender: @js((($izin->santri->gender?->value ?? $izin->santri->gender) === 'L') ? 'Ikhwan' : 'Akhwat'),
                                prodi: @js($izin->santri->prodi ?? ''),
                                kamar: @js($kamarStr),
                                kategori: @js($izin->jenisPerizinan->nama ?? 'Izin Santri'),
                                tanggal_mulai: @js($izin->tanggal_mulai->format('d M Y')),
                                tanggal_selesai: @js($izin->tanggal_selesai->format('d M Y')),
                                durasi: {{ $durasi }},
                                alasan: @js($izin->alasan ?? ''),
                                status: @js($statusVal),
                                input_by_name: @js($izin->inputBy->name ?? 'Admin Asrama'),
                                created_at: @js($izin->created_at->format('d M Y, H:i') . ' WIB'),
                                cetak_url: @js(route('kedisiplinan.perizinan.cetak', $izin->id))
                            })"
                            class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                        >
                            Detail Lengkap &rarr;
                        </button>

                        <div class="flex items-center gap-1">
                            <a href="{{ route('kedisiplinan.perizinan.cetak', $izin->id) }}" target="_blank" class="p-1 rounded text-slate-400 hover:text-slate-600">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </a>
                            @if($isBerlaku)
                                <form action="{{ route('kedisiplinan.perizinan.status', $izin->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" class="p-1 rounded text-slate-400 hover:text-emerald-600 cursor-pointer">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </x-slot:mobile>
    </x-table-container>

    <div class="mt-4">
        {{ $perizinanList->links() }}
    </div>
@else
    <x-empty-state
        title="Tidak Ada Data Perizinan"
        description="Belum ada riwayat surat perizinan santri untuk periode atau filter yang dipilih."
        action-label="Ajukan Izin Pertama"
        action-event="open-add-izin"
    />
@endif
