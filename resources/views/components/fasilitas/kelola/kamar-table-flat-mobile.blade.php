<!-- 2. Tampilan Khusus Mobile (< md): Stacked List Responsif (HARAM GESER HORIZONTAL) -->
<div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
    @forelse($kamarList as $kamar)
        @php
            $terisi = $kamar->penempatanAktif->count();
            $isFull = $terisi >= $kamar->kapasitas_maks;
            $isPerbaikan = ($kamar->status->value ?? $kamar->status) === 'perbaikan';
            $genderVal = $kamar->gender->value ?? $kamar->gender;
        @endphp
        <div class="p-3.5 space-y-2.5">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="text-sm font-black text-slate-900 dark:text-white">
                        {{ $kamar->nomor_kamar }}
                    </span>
                    <x-gender-badge :gender="$genderVal" />
                    <span class="text-[9.5px] font-semibold text-slate-400 capitalize">
                        • {{ $kamar->tipe_ruang instanceof \App\Enums\TipeRuang ? $kamar->tipe_ruang->label() : ($kamar->tipe_ruang ? ucfirst((string) $kamar->tipe_ruang) : 'Santri') }}
                    </span>
                </div>

                <x-status-badge :status="$isPerbaikan ? 'perbaikan' : ($isFull ? 'penuh' : 'tersedia')" />
            </div>

            <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-1.5 font-medium truncate">
                    <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ $kamar->lantai->gedung->nama ?? '-' }} • Lt. {{ $kamar->lantai->nomor_lantai ?? '-' }}</span>
                </div>
                <div class="font-bold shrink-0 {{ $isFull ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                    {{ $terisi }}/{{ $kamar->kapasitas_maks }} <span class="text-[10px] font-normal text-slate-400">Bed</span>
                </div>
            </div>

            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 text-xs">
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
                            class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline shrink-0"
                        >
                            Semua
                        </button>
                    </div>
                @else
                    <span class="text-slate-400 text-[11px] italic">Belum berpenghuni</span>
                @endif
            </div>

            <div class="flex items-center justify-end gap-1.5 pt-1">
                <button
                    type="button"
                    @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Detail
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
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-slate-700 transition cursor-pointer"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit
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
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-slate-800 hover:bg-rose-100 dark:hover:bg-slate-700 transition cursor-pointer"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-xs text-slate-400">
            Belum ada data kamar pada filter ini.
        </div>
    @endforelse
</div>
