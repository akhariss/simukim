<!-- Tab Kelola Gedung & Unit Lantai -->
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($gedungList as $gedung)
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-2xs hover:shadow-xs transition flex flex-col justify-between">
                <div>
                    <!-- Header Kartu Gedung -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="size-9 rounded-xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold flex items-center justify-center text-xs border border-blue-100 dark:border-slate-700">
                                {{ $gedung->kode }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white leading-tight">
                                    {{ $gedung->nama }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <x-badge :variant="$gedung->gender_khusus === 'L' ? 'ikhwan' : ($gedung->gender_khusus === 'P' ? 'akhwat' : 'neutral')">
                                        {{ $gedung->gender_khusus === 'L' ? 'Zona Putra' : ($gedung->gender_khusus === 'P' ? 'Zona Putri' : 'Gedung Campur') }}
                                    </x-badge>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown / Aksi Gedung -->
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                @click="$dispatch('open-edit-gedung', {
                                    id: {{ $gedung->id }},
                                    kode: '{{ $gedung->kode }}',
                                    nama: '{{ addslashes($gedung->nama) }}',
                                    gender_khusus: '{{ $gedung->gender_khusus }}',
                                    deskripsi: '{{ addslashes($gedung->deskripsi ?? '') }}',
                                    is_active: {{ $gedung->is_active ? 1 : 0 }}
                                })"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                title="Edit Gedung"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            <form action="{{ route('fasilitas.kelola.gedung.destroy', $gedung->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer"
                                    title="Hapus Gedung"
                                    data-confirm="Apakah Anda yakin ingin menghapus Gedung '{{ $gedung->nama }}'? Tindakan ini akan menghapus seluruh data unit lantai dan kamar di dalamnya."
                                    data-confirm-title="Hapus Gedung Asrama"
                                    data-confirm-type="danger"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($gedung->deskripsi)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                            {{ $gedung->deskripsi }}
                        </p>
                    @endif

                    <!-- Daftar Unit Lantai -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <a href="{{ route('fasilitas.kelola.index', ['tab' => 'lantai', 'gedung_id' => $gedung->id]) }}" class="hover:text-blue-600 transition flex items-center gap-1">
                                <span>Unit Lantai ({{ $gedung->lantai->count() }})</span>
                                <svg class="size-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <button
                                type="button"
                                @click="$dispatch('open-add-lantai', { gedungId: {{ $gedung->id }}, gedungNama: '{{ addslashes($gedung->nama) }}' })"
                                class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                            >
                                + Tambah Lantai
                            </button>
                        </div>

                        <div class="space-y-1.5 max-h-44 overflow-y-auto">
                            @forelse($gedung->lantai as $lantai)
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/50 dark:border-slate-800 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="size-5 rounded-md bg-white dark:bg-slate-700 font-bold text-[10px] flex items-center justify-center text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-slate-600">
                                            {{ $lantai->nomor_lantai }}
                                        </span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">
                                            Lantai {{ $lantai->nomor_lantai }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            ({{ $lantai->kamar_count }} Kamar)
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <a
                                            href="{{ route('fasilitas.kelola.index', ['gedung_id' => $gedung->id, 'lantai_id' => $lantai->id, 'tab' => 'kamar']) }}"
                                            class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline mr-1"
                                        >
                                            Kelola Kamar
                                        </a>

                                        <button
                                            type="button"
                                            @click="$dispatch('open-edit-lantai', {
                                                id: {{ $lantai->id }},
                                                gedung_id: {{ $gedung->id }},
                                                gedung_nama: '{{ addslashes($gedung->nama) }}',
                                                nomor_lantai: {{ $lantai->nomor_lantai }},
                                                kapasitas_kamar_standar: {{ $lantai->kapasitas_kamar_standar }}
                                            })"
                                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                                            title="Edit Lantai {{ $lantai->nomor_lantai }}"
                                        >
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form action="{{ route('fasilitas.kelola.lantai.destroy', $lantai->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-slate-400 hover:text-rose-600 transition p-1 rounded-md hover:bg-rose-50 dark:hover:bg-slate-800 cursor-pointer"
                                                title="Hapus Lantai {{ $lantai->nomor_lantai }}"
                                                data-confirm="Hapus Lantai {{ $lantai->nomor_lantai }} di {{ $gedung->nama }}?"
                                                data-confirm-title="Hapus Unit Lantai"
                                                data-confirm-type="danger"
                                            >
                                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="py-2 text-center text-[11px] text-slate-400">
                                    Belum ada unit lantai. Klik + Tambah Lantai.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Footer Kartu -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Status: <strong class="{{ $gedung->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $gedung->is_active ? 'Aktif Beroperasi' : 'Non-Aktif' }}</strong></span>
                    <a
                        href="{{ route('fasilitas.monitoring.index', ['gedung_id' => $gedung->id]) }}"
                        class="text-blue-600 dark:text-blue-400 font-semibold hover:underline"
                    >
                        Pantau Occupancy →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-8 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl text-center">
                <p class="text-sm text-slate-500">Belum ada gedung asrama terdaftar. Klik tombol Tambah Gedung di atas.</p>
            </div>
        @endforelse
    </div>
</div>
