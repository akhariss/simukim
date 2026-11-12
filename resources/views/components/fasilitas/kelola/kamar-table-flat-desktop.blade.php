<!-- 1. Tampilan Desktop & Tablet (md ke atas): Tabel Rapi -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-800/60 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
            <tr>
                <th class="px-4 py-3">No. Kamar</th>
                <th class="px-4 py-3">Lokasi (Gedung & Lt)</th>
                <th class="px-4 py-3">Gender</th>
                <th class="px-4 py-3">Kapasitas</th>
                <th class="px-4 py-3">Penghuni Aktif</th>
                <th class="px-4 py-3">Tipe Ruang</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
            @forelse($kamarList as $kamar)
                @php
                    $terisi = $kamar->penempatanAktif->count();
                    $isFull = $terisi >= $kamar->kapasitas_maks;
                    $isPerbaikan = ($kamar->status->value ?? $kamar->status) === 'perbaikan';
                    $genderVal = $kamar->gender->value ?? $kamar->gender;
                @endphp
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                    <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white">
                        {{ $kamar->nomor_kamar }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                        {{ $kamar->lantai->gedung->nama ?? '-' }} • Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-gender-badge :gender="$genderVal" />
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold {{ $isFull ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
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
                                            class="size-5 rounded-full bg-blue-600 text-white font-bold text-[8.5px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900"
                                        >
                                            {{ strtoupper(substr($p->santri->user->name ?? 'S', 0, 1)) }}
                                        </span>
                                    @endforeach
                                </div>
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 truncate max-w-[120px]">
                                    {{ $kamar->penempatanAktif->first()->santri->user->name ?? '' }}
                                    @if($kamar->penempatanAktif->count() > 1)
                                        <span class="text-slate-400 font-normal">(+{{ $kamar->penempatanAktif->count() - 1 }})</span>
                                    @endif
                                </span>
                            </div>
                        @else
                            <span class="text-slate-400 text-[11px] italic">Kosong</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 capitalize">
                            {{ $kamar->tipe_ruang instanceof \App\Enums\TipeRuang ? $kamar->tipe_ruang->label() : ($kamar->tipe_ruang ? ucfirst((string) $kamar->tipe_ruang) : 'Santri') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <x-status-badge :status="$isPerbaikan ? 'perbaikan' : ($isFull ? 'penuh' : 'tersedia')" />
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button
                                type="button"
                                @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                                class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition cursor-pointer"
                                title="Lihat Detail & Penghuni"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>

                            <button
                                type="button"
                                @click="$dispatch('open-edit-kamar', {
                                    id: {{ $kamar->id }},
                                    nomor_kamar: '{{ $kamar->nomor_kamar }}',
                                    lantai_id: {{ $kamar->lantai_id }},
                                    gender: '{{ $kamar->gender }}',
                                    kapasitas_maks: {{ $kamar->kapasitas_maks }},
                                    tipe_ruang: '{{ $kamar->tipe_ruang }}',
                                    status: '{{ $kamar->status }}',
                                    catatan: '{{ addslashes($kamar->catatan ?? '') }}'
                                })"
                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-lg transition cursor-pointer"
                                title="Edit Kamar"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>

                            <form
                                action="{{ route('fasilitas.kelola.kamar.destroy', $kamar->id) }}"
                                method="POST"
                                class="inline"
                                data-confirm="Hapus data Kamar {{ $kamar->nomor_kamar }}?"
                                data-confirm-title="Hapus Unit Kamar"
                                data-confirm-type="danger"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition cursor-pointer"
                                    title="Hapus Kamar"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                        Belum ada data kamar pada filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
