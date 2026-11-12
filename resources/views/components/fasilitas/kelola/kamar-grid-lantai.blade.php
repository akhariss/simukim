<!-- MODE 1: VIEW KAMAR DIKELOMPOKKAN PER LANTAI (ATOMIC CARD) -->
<div class="space-y-8">
    @forelse($gedungList as $gedung)
        <div class="space-y-6">
            <!-- Judul Gedung -->
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <span class="size-8 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-black text-xs flex items-center justify-center">
                        {{ $gedung->kode }}
                    </span>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                            {{ $gedung->nama }}
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            {{ $gedung->gender_khusus === 'P' ? 'Khusus Mahasantriwati / Akhwat' : ($gedung->gender_khusus === 'L' ? 'Khusus Mahasantri / Ikhwan' : 'Zona Campur (Putra & Putri)') }}
                            • {{ $gedung->lantai->count() }} Unit Lantai Aktif
                        </p>
                    </div>
                </div>

                <span class="text-xs font-semibold text-slate-500">
                    Total: {{ $gedung->lantai->sum(fn($l) => $l->kamar->count()) }} Kamar
                </span>
            </div>

            <!-- Iterasi Lantai di Gedung Ini -->
            @forelse($gedung->lantai as $lantai)
                @php
                    $kamars = $lantai->kamar;
                    $totalKamarLantai = $kamars->count();
                    $totalBed = $kamars->sum('kapasitas_maks');
                    $totalTerisi = $kamars->sum(fn($k) => $k->penempatanAktif->count());
                    $defaultKamarGender = $kamars->first()->gender ?? ($gedung->gender_khusus === 'P' ? 'P' : 'L');
                @endphp
                <div class="space-y-4 pt-1">
                    <!-- Header Bar Lantai -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200/70 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="size-7 rounded-lg bg-slate-100 dark:bg-slate-800 font-extrabold text-xs text-slate-800 dark:text-slate-200 flex items-center justify-center border border-slate-200 dark:border-slate-700">
                                Lt. {{ $lantai->nomor_lantai }}
                            </div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">
                                    Lantai {{ $lantai->nomor_lantai }}
                                </h4>
                            </div>
                            <span class="text-[11px] text-slate-400 hidden sm:inline">
                                • {{ $totalKamarLantai }} Kamar • {{ $totalTerisi }}/{{ $totalBed }} Terisi
                            </span>
                        </div>

                        <button
                            type="button"
                            @click="$dispatch('open-add-kamar', { lantai_id: {{ $lantai->id }}, gender: '{{ $defaultKamarGender }}' })"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer self-start sm:self-auto"
                        >
                            + Tambah Kamar di Lt. {{ $lantai->nomor_lantai }}
                        </button>
                    </div>

                    <!-- Grid Kamar Menggunakan Komponen Atomic <x-kamar-card /> -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-4.5 lg:gap-5">
                        @forelse($kamars as $k)
                            <x-kamar-card :kamar="$k" :editable="true" />
                        @empty
                            <div class="col-span-full py-8 text-center text-xs text-slate-400 bg-slate-50/50 dark:bg-slate-800/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                {{ request()->hasAny(['gender', 'tipe_ruang', 'status', 'search']) ? 'Tidak ada kamar di Lantai ' . $lantai->nomor_lantai . ' yang memenuhi kriteria filter.' : 'Belum ada unit kamar di Lantai ' . $lantai->nomor_lantai . '.' }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl text-center text-xs text-slate-400">
                    Belum ada unit lantai di gedung ini.
                </div>
            @endforelse
        </div>
    @empty
        <div class="p-8 bg-white dark:bg-slate-900 rounded-2xl text-center text-slate-400 border border-slate-200/80 dark:border-slate-800">
            Tidak ada gedung yang ditemukan sesuai filter yang dipilih.
        </div>
    @endforelse
</div>
