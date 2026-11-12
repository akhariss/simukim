@props([
    'kamar' => null,
    'editable' => false,
])

@php
    if (!isset($kamar) || !$kamar) {
        return;
    }

    $penempatanList = $kamar->penempatanAktif ?? collect();
    $terisi = $penempatanList->count();
    $kapasitas = (int) ($kamar->kapasitas_maks ?: 4);
    $sisa = max(0, $kapasitas - $terisi);

    $isPerbaikan = ($kamar->status->value ?? $kamar->status) === 'perbaikan';
    $genderVal = $kamar->gender->value ?? $kamar->gender;
    $tipeRuangVal = $kamar->tipe_ruang->value ?? $kamar->tipe_ruang;
    $isFull = $terisi >= $kapasitas;
    $isEmpty = $terisi === 0;

    // Status Badge Bersih & Ramping (Tanpa Teks Berulang / Gelembung Aneh)
    if ($isPerbaikan) {
        $statusBadge = 'bg-amber-50 text-amber-800 border-amber-200/80 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700';
        $statusDot = 'bg-amber-500';
        $statusText = 'Perbaikan';
    } elseif ($isFull) {
        $statusBadge = 'bg-rose-50 text-rose-800 border-rose-200/80 dark:bg-slate-800 dark:text-rose-300 dark:border-slate-700';
        $statusDot = 'bg-rose-500';
        $statusText = 'Penuh';
    } elseif ($isEmpty) {
        $statusBadge = 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
        $statusDot = 'bg-slate-400';
        $statusText = 'Kosong';
    } else {
        $statusBadge = 'bg-emerald-50 text-emerald-800 border-emerald-200/80 dark:bg-slate-800 dark:text-emerald-300 dark:border-slate-700';
        $statusDot = 'bg-emerald-500';
        $statusText = 'Sisa ' . $sisa . ' Bed';
    }

    $persenOkupansi = $kapasitas > 0 ? min(100, round(($terisi / $kapasitas) * 100)) : 0;
@endphp

<div class="group rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-2xs hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 flex flex-col justify-between h-full">
    <!-- Bagian Atas: Identitas Lengkap Kamar -->
    <div>
        <!-- Baris 1: Nomor Kamar, Tag Gender/Mudabbir, & Status Badge Ramping -->
        <div class="flex items-start justify-between gap-2.5 pb-2.5 border-b border-slate-100 dark:border-slate-800/80">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h3 class="font-black text-base text-slate-900 dark:text-white tracking-tight">
                        {{ $kamar->nomor_kamar }}
                    </h3>

                    <!-- Tag Gender Jelas -->
                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md {{ $genderVal === 'L' ? 'bg-blue-50 text-blue-800 border border-blue-200/60 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700' : 'bg-pink-50 text-pink-800 border border-pink-200/60 dark:bg-slate-800 dark:text-pink-300 dark:border-slate-700' }}">
                        {{ $genderVal === 'L' ? 'Ikhwan' : 'Akhwat' }}
                    </span>

                    @if($tipeRuangVal === 'mudabbir')
                        <span class="inline-flex items-center text-[9.5px] font-bold px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700">
                            Mudabbir
                        </span>
                    @endif
                </div>

                <!-- Subteks Gedung & Lantai Penting -->
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1 flex items-center gap-1 truncate">
                    <svg class="size-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="truncate">{{ $kamar->lantai->gedung->nama ?? 'Gedung Asrama' }} • Lt. {{ $kamar->lantai->nomor_lantai ?? '1' }}</span>
                </p>
            </div>

            <!-- Status Badge Natural Ramping -->
            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full border shrink-0 {{ $statusBadge }}">
                <span class="size-1.5 rounded-full {{ $statusDot }}"></span>
                <span>{{ $statusText }}</span>
            </span>
        </div>

        <!-- Baris 2: Progres Okupansi Tempat Tidur -->
        <div class="py-2.5 space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">
                    Kapasitas Kasur
                </span>
                <span class="font-bold {{ $isFull ? 'text-rose-600 dark:text-rose-400' : ($terisi > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400') }}">
                    {{ $terisi }}/{{ $kapasitas }} Bed Terisi
                </span>
            </div>

            <!-- Bar Progres Halus & Bersih -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div
                    class="h-full rounded-full transition-all duration-300 {{ $isFull ? 'bg-rose-500' : ($terisi > 0 ? 'bg-blue-600 dark:bg-blue-500' : 'bg-transparent') }}"
                    style="width: {{ $persenOkupansi }}%;"
                ></div>
            </div>
        </div>

        <!-- Baris 3: Daftar Mahasantri Penghuni (Info Utama Lengkap) -->
        <div class="space-y-1.5 mb-3">
            @if($terisi > 0)
                @foreach($penempatanList as $penempatan)
                    <div class="flex items-center justify-between text-xs py-1.5 px-2.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="size-5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ $loop->iteration }}
                            </span>
                            <div class="min-w-0">
                                <div class="font-semibold text-xs text-slate-800 dark:text-slate-200 truncate" title="{{ $penempatan->santri->user->name ?? 'Mahasantri' }}">
                                    {{ $penempatan->santri->user->name ?? 'Mahasantri' }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                    {{ $penempatan->santri->nomor_induk ?? '-' }}
                                    @if(!empty($penempatan->santri->prodi))
                                        • {{ $penempatan->santri->prodi }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($penempatan->santri->status === 'mudabbir' || $kamar->tipe_ruang === 'mudabbir')
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 dark:bg-slate-800 dark:text-amber-300 dark:border dark:border-slate-700 shrink-0 ml-1.5">
                                Mudabbir
                            </span>
                        @endif
                    </div>
                @endforeach

                <!-- Slot Kasur Kosong Sisa -->
                @for($empty = 0; $empty < $sisa; $empty++)
                    <div class="flex items-center gap-2 text-xs py-1.5 px-2.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800/80 text-slate-400 dark:text-slate-500">
                        <span class="size-5 rounded-full border border-dashed border-slate-300 dark:border-slate-700 text-[10px] font-medium flex items-center justify-center shrink-0">
                            {{ $terisi + $empty + 1 }}
                        </span>
                        <span class="text-[11px] italic">Kasur Kosong (Tersedia)</span>
                    </div>
                @endfor
            @else
                <!-- State Bersih Saat Kamar Benar-benar Kosong -->
                <div class="py-6 px-3 text-center rounded-2xl bg-slate-50/60 dark:bg-slate-800/30 border border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center gap-1.5">
                    <svg class="size-6 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-400">Kamar Kosong</div>
                    <div class="text-[11px] text-slate-400 dark:text-slate-500">Tersedia {{ $kapasitas }} slot tempat tidur</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer Tombol Aksi: Teratur, Proporsional, & Seimbang -->
    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-2 mt-auto">
        @if($editable)
            <!-- Mode Kelola: Tombol Detail + Edit + Hapus -->
            <button
                type="button"
                @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
            >
                <svg class="size-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Detail</span>
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
                class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-800 transition cursor-pointer"
                title="Edit Kamar"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>

            <form action="{{ route('fasilitas.kelola.kamar.destroy', $kamar->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-800 transition cursor-pointer"
                    title="Hapus Kamar"
                    data-confirm="Hapus unit Kamar {{ $kamar->nomor_kamar }}?"
                    data-confirm-title="Hapus Unit Kamar"
                    data-confirm-type="danger"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </form>
        @else
            <!-- Mode Monitoring: Tombol Penuh Detail Kamar -->
            <button
                type="button"
                @click="$dispatch('open-room-modal', { roomId: {{ $kamar->id }} })"
                class="w-full py-2 px-3 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200/80 hover:border-blue-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
            >
                <svg class="size-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Lihat Detail Kamar</span>
            </button>
        @endif
    </div>
</div>
