<!-- Tab Kelola Unit Lantai -->
<div class="space-y-4">
    <!-- Toolbar Filter & Tambah Lantai -->
    <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('fasilitas.kelola.index') }}" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="lantai">
            
            <div class="w-60">
                <select
                    name="gedung_id"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Semua Gedung ({{ $allGedung->count() }} Gedung)</option>
                    @foreach($allGedung as $g)
                        <option value="{{ $g->id }}" {{ request('gedung_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} ({{ $g->gender_khusus === 'L' ? 'Putra' : ($g->gender_khusus === 'P' ? 'Putri' : 'Campur') }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request('gedung_id'))
                <a
                    href="{{ route('fasilitas.kelola.index', ['tab' => 'lantai']) }}"
                    class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition"
                >
                    Reset
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="$dispatch('open-add-lantai')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Lantai
            </button>
        </div>
    </div>

    <!-- Grid Kartu Lantai -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($lantaiList as $lantai)
            @php
                $kamars = $lantai->kamar;
                $totalKamar = $kamars->count();
                $totalBed = $kamars->sum('kapasitas_maks');
                $totalTerisi = $kamars->sum(fn($k) => $k->penempatanAktif->count());
                $sisaBed = max(0, $totalBed - $totalTerisi);
                $persen = $totalBed > 0 ? round(($totalTerisi / $totalBed) * 100) : 0;
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                <div>
                    <!-- Header Lantai -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="size-10 rounded-xl bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 font-extrabold flex items-center justify-center text-sm border border-blue-100 dark:border-slate-700">
                                Lt. {{ $lantai->nomor_lantai }}
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white leading-tight">
                                    Lantai {{ $lantai->nomor_lantai }}
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $lantai->gedung->nama }}
                                </p>
                            </div>
                        </div>

                        <!-- Jumlah Kamar -->
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60">
                            {{ $totalKamar }} Kamar
                        </span>
                    </div>

                    <!-- Statistik Kamar & Bed -->
                    <div class="mt-4 grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/50 dark:border-slate-800 text-center">
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">Kamar</div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ $totalKamar }} Unit
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">Terisi</div>
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">
                                {{ $totalTerisi }} Santri
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 font-medium">Slot Kosong</div>
                            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                {{ $sisaBed }} Bed
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Okupansi -->
                    <div class="mt-3 space-y-1">
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span>Okupansi Lantai</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $persen }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                            <div
                                class="h-1.5 rounded-full {{ $persen >= 100 ? 'bg-rose-500' : ($persen > 0 ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700') }}"
                                style="width: {{ $persen }}%"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Footer Aksi -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <a
                        href="{{ route('fasilitas.kelola.index', ['tab' => 'kamar', 'gedung_id' => $lantai->gedung_id, 'lantai_id' => $lantai->id]) }}"
                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
                    >
                        <span>Kelola {{ $totalKamar }} Kamar</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="$dispatch('open-edit-lantai', {
                                id: {{ $lantai->id }},
                                gedung_id: {{ $lantai->gedung_id }},
                                gedung_nama: '{{ addslashes($lantai->gedung->nama) }}',
                                nomor_lantai: {{ $lantai->nomor_lantai }},
                                kapasitas_kamar_standar: {{ $lantai->kapasitas_kamar_standar }}
                            })"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Edit Lantai"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>

                        <form action="{{ route('fasilitas.kelola.lantai.destroy', $lantai->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer"
                                title="Hapus Lantai"
                                data-confirm="Hapus Lantai {{ $lantai->nomor_lantai }} di {{ $lantai->gedung->nama }}? Semua kamar di lantai ini akan ikut terhapus."
                                data-confirm-title="Hapus Unit Lantai"
                                data-confirm-type="danger"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl text-center">
                <p class="text-sm text-slate-500">Belum ada unit lantai terdaftar. Klik tombol Tambah Lantai di atas.</p>
            </div>
        @endforelse
    </div>
</div>
