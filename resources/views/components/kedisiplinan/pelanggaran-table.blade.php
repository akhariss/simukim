@props([
    'pelanggaranList',
    'activePeriode' => null,
])

@if($pelanggaranList->count() > 0)
    <x-table-container>
        <!-- A. Desktop View (>= md) -->
        <x-slot:desktop>
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-[10.5px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="w-12 px-3.5 py-3 text-center">No</th>
                        <th class="px-3.5 py-3">Mahasantri</th>
                        <th class="px-3.5 py-3">Tanggal</th>
                        <th class="px-3.5 py-3">Tingkat Pelanggaran</th>
                        <th class="px-3.5 py-3">Sanksi / Tindakan</th>
                        <th class="px-3.5 py-3">Status</th>
                        <th class="px-3.5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($pelanggaranList as $pel)
                        @php
                            $statusVal = $pel->status instanceof \App\Enums\PelanggaranStatus ? $pel->status->value : $pel->status;
                            $isSelesai = $statusVal === 'selesai';
                            $penempatan = $pel->santri->penempatanAktif ?? null;
                            $kamar = $penempatan?->kamar;
                            $kamarStr = $kamar ? ('K.' . $kamar->nomor_kamar) : 'Belum Ditempatkan';
                            $jenisNama = match(true) {
                                $pel->poin <= 5 => 'Pelanggaran Ringan',
                                $pel->poin >= 20 => 'Pelanggaran Berat',
                                default => 'Pelanggaran Sedang',
                            };
                            $detailPayload = json_encode([
                                'id' => $pel->id,
                                'nama' => $pel->santri->user->name ?? 'Mahasantri',
                                'nim' => $pel->santri->nomor_induk,
                                'gender' => ($pel->santri->gender?->value ?? $pel->santri->gender) === 'L' ? 'Ikhwan' : 'Akhwat',
                                'prodi' => $pel->santri->prodi ?? '',
                                'kamar' => $kamarStr,
                                'jenis' => $jenisNama,
                                'jenis_id' => $pel->jenis_pelanggaran_id,
                                'poin' => $pel->poin,
                                'tanggal' => $pel->tanggal->format('d M Y'),
                                'tanggal_raw' => $pel->tanggal->format('Y-m-d'),
                                'deskripsi' => $pel->deskripsi,
                                'hukuman' => $pel->hukuman ?? '',
                                'status' => $statusVal,
                                'input_by_name' => $pel->inputBy->name ?? 'Petugas Disiplin',
                                'created_at' => $pel->created_at->format('d M Y, H:i') . ' WIB',
                            ]);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="px-3.5 py-3 text-center font-mono text-slate-400">{{ $pelanggaranList->firstItem() + $loop->index }}</td>
                            <td class="px-3.5 py-3">
                                <div class="font-bold text-slate-900 dark:text-white truncate max-w-[190px]">{{ $pel->santri->user->name ?? 'Mahasantri' }}</div>
                                <div class="text-[10.5px] text-slate-400 font-mono">{{ $pel->santri->nomor_induk }} &bull; {{ $kamarStr }}</div>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">{{ $pel->tanggal->format('d M Y') }}</td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $pel->poin >= 20 ? 'bg-rose-50 text-rose-700 dark:bg-slate-800 dark:text-rose-300' : ($pel->poin >= 10 ? 'bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300') }}">
                                    {{ $jenisNama }}
                                </span>
                            </td>
                            <td class="px-3.5 py-3 max-w-[200px]">
                                <p class="truncate text-slate-700 dark:text-slate-300" title="{{ $pel->hukuman ?: $pel->deskripsi }}">{{ $pel->hukuman ?: ($pel->deskripsi ?: '-') }}</p>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                @if($isSelesai)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-slate-700"><span class="size-1.5 rounded-full bg-emerald-600"></span>Selesai Dilaksanakan</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-300 border border-amber-200/60 dark:border-slate-700"><span class="size-1.5 rounded-full bg-amber-600"></span>Belum Dilaksanakan</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click="$dispatch('open-detail-pelanggaran', {{ $detailPayload }})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 transition cursor-pointer" title="Lihat Detail Kasus">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button type="button" @click="$dispatch('open-edit-pelanggaran', Object.assign({{ $detailPayload }}, { tanggal: '{{ $pel->tanggal->format('Y-m-d') }}' }))" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-800 transition cursor-pointer" title="Edit Catatan Pelanggaran">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if(!$isSelesai)
                                        <form action="{{ route('kedisiplinan.pelanggaran.status', $pel->id) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="selesai">
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-800 transition cursor-pointer" title="Tandai Selesai Dibina">
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()?->hasRole('admin'))
                                        <form action="{{ route('kedisiplinan.pelanggaran.destroy', $pel->id) }}" method="POST" class="inline" data-confirm="Hapus catatan pelanggaran santri ini?" data-confirm-title="Hapus Kasus Pelanggaran" data-confirm-type="danger">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer" title="Hapus Catatan Pelanggaran">
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-slot:desktop>

        <!-- B. Mobile View (< md) -->
        <x-slot:mobile>
            @foreach($pelanggaranList as $pel)
                @php
                    $statusVal = $pel->status instanceof \App\Enums\PelanggaranStatus ? $pel->status->value : $pel->status;
                    $isSelesai = $statusVal === 'selesai';
                    $kamar = $pel->santri->penempatanAktif?->kamar;
                    $kamarStr = $kamar ? ('K.' . $kamar->nomor_kamar) : 'Belum Ditempatkan';
                    $jenisNama = match(true) {
                        $pel->poin <= 5 => 'Pelanggaran Ringan',
                        $pel->poin >= 20 => 'Pelanggaran Berat',
                        default => 'Pelanggaran Sedang',
                    };
                    $detailPayloadMobile = json_encode([
                        'id' => $pel->id,
                        'nama' => $pel->santri->user->name ?? 'Mahasantri',
                        'nim' => $pel->santri->nomor_induk,
                        'gender' => ($pel->santri->gender?->value ?? $pel->santri->gender) === 'L' ? 'Ikhwan' : 'Akhwat',
                        'prodi' => $pel->santri->prodi ?? '',
                        'kamar' => $kamarStr,
                        'jenis' => $jenisNama,
                        'jenis_id' => $pel->jenis_pelanggaran_id,
                        'poin' => $pel->poin,
                        'tanggal' => $pel->tanggal->format('d M Y'),
                        'tanggal_raw' => $pel->tanggal->format('Y-m-d'),
                        'deskripsi' => $pel->deskripsi,
                        'hukuman' => $pel->hukuman ?? '',
                        'status' => $statusVal,
                        'input_by_name' => $pel->inputBy->name ?? 'Petugas Disiplin',
                        'created_at' => $pel->created_at->format('d M Y, H:i') . ' WIB',
                    ]);
                @endphp
                <div class="p-3.5 space-y-2 border-b border-slate-100 dark:border-slate-800 last:border-0 text-xs">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h4 class="font-bold text-slate-900 dark:text-white truncate">{{ $pel->santri->user->name ?? 'Mahasantri' }}</h4>
                            <div class="text-[10.5px] text-slate-400 font-mono">{{ $pel->santri->nomor_induk }} &bull; {{ $pel->tanggal->format('d/m/Y') }}</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $isSelesai ? 'bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-300' }}">
                            {{ $isSelesai ? 'Selesai Dilaksanakan' : 'Belum Dilaksanakan' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-2.5 py-1.5 rounded-lg">
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $jenisNama }}</span>
                        <span class="text-[10.5px] text-slate-400">{{ $kamarStr }}</span>
                    </div>
                    @if($pel->hukuman)
                        <div class="text-[10.5px] text-amber-700 dark:text-amber-400 font-medium truncate">Sanksi: {{ $pel->hukuman }}</div>
                    @endif
                    <div class="flex items-center justify-between pt-1">
                        <button type="button" @click="$dispatch('open-detail-pelanggaran', {{ $detailPayloadMobile }})" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">
                            Detail Kasus &rarr;
                        </button>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="$dispatch('open-edit-pelanggaran', Object.assign({{ $detailPayloadMobile }}, { tanggal: '{{ $pel->tanggal->format('Y-m-d') }}' }))" class="p-1 rounded text-slate-400 hover:text-amber-600 cursor-pointer">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if(!$isSelesai)
                                <form action="{{ route('kedisiplinan.pelanggaran.status', $pel->id) }}" method="POST" class="inline">
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
        {{ $pelanggaranList->links() }}
    </div>
@else
    <x-empty-state
        title="Tidak Ada Data Pelanggaran"
        description="Belum ada catatan pelanggaran tata tertib santri untuk filter atau periode ini."
        action-label="Catat Kasus Pertama"
        action-event="open-add-pelanggaran"
    />
@endif
