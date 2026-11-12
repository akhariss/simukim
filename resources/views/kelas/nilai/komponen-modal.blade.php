{{-- Modal Pengaturan Komponen Nilai Kajian --}}
<x-modal name="modal-komponen-nilai" focusable>
    <div class="p-5 space-y-4" x-data="{ editingId: null, editNama: '', editBobot: 0 }">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Konfigurasi Komponen Penilaian Akademik</h3>
                <p class="text-xs text-slate-500">{{ $selectedKelas?->nama }} &bull; {{ $selectedKelompok?->nama }}</p>
            </div>
            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full {{ $komponenList->sum('bobot') == 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                Akumulasi Bobot: {{ $komponenList->sum('bobot') }}%
            </span>
        </div>

        <div class="space-y-2">
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Komponen Penilaian Terdaftar</h4>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @foreach($komponenList as $komp)
                    <div x-show="editingId !== {{ $komp->id }}" class="py-2.5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $komp->nama }}</span>
                            @if($komp->is_absensi)
                                <span class="text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 font-semibold border border-blue-200/60 shrink-0">Kalkulasi Otomatis (Presensi)</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300 text-xs px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded">{{ $komp->bobot }}%</span>

                            {{-- Tombol Edit --}}
                            <button
                                type="button"
                                @click="editingId = {{ $komp->id }}; editNama = '{{ addslashes($komp->nama) }}'; editBobot = {{ $komp->bobot }};"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-950/30 transition cursor-pointer"
                                title="Edit Bobot & Nama Komponen"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>

                            @if(!$komp->is_absensi && !$komp->is_fixed)
                                <form method="POST" action="{{ route('kelas.nilai.komponen.destroy', $komp->id) }}" class="inline" data-confirm="Hapus komponen penilaian {{ $komp->nama }}?" data-confirm-title="Hapus Komponen Penilaian" data-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer" title="Hapus Komponen">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] text-slate-400 font-medium italic">Komponen Baku</span>
                            @endif
                        </div>
                    </div>

                    {{-- Form Inline Edit Bobot & Nama --}}
                    <div x-show="editingId === {{ $komp->id }}" x-cloak class="py-2">
                        <form method="POST" action="{{ route('kelas.nilai.komponen.update', $komp->id) }}" class="flex items-center justify-between gap-2 bg-blue-50/60 dark:bg-blue-950/30 p-2.5 rounded-xl border border-blue-200 dark:border-blue-900">
                            @csrf @method('PUT')
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="nama"
                                    x-model="editNama"
                                    required
                                    placeholder="Nama komponen"
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500"
                                >
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <div class="flex items-center gap-1">
                                    <input
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        max="100"
                                        name="bobot"
                                        x-model="editBobot"
                                        required
                                        placeholder="0-100"
                                        class="w-16 px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 text-center"
                                    >
                                    <span class="text-xs font-mono font-bold text-slate-500">%</span>
                                </div>
                                <button type="submit" class="px-2.5 py-1 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-2xs cursor-pointer">
                                    Simpan
                                </button>
                                <button type="button" @click="editingId = null" class="px-2 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg hover:bg-slate-50 transition cursor-pointer">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tambah Komponen Baru --}}
        <form method="POST" action="{{ route('kelas.nilai.komponen.store') }}" class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2.5">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $selectedKelas?->id }}">
            <input type="hidden" name="kelompok_id" value="{{ $selectedKelompok?->id }}">
            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Tambah Komponen Tambahan</h5>
            <div class="grid grid-cols-3 gap-2">
                <div class="col-span-2">
                    <input type="text" name="nama" required placeholder="Nama komponen (contoh: Tugas Mandiri / Ujian Lisan)" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
                <div>
                    <input type="number" step="0.5" min="1" max="100" name="bobot" required placeholder="Bobot (%)" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>
            <button type="submit" class="w-full py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition cursor-pointer">Tambah Komponen</button>
        </form>

        <div class="flex justify-end pt-1">
            <button type="button" @click="$dispatch('close')" class="px-4 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">Tutup</button>
        </div>
    </div>
</x-modal>
