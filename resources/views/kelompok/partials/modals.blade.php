{{-- Modal Edit Kelompok (Persis Standar Menu Kelola Asrama) --}}
<x-modal name="modal-edit-kelompok" maxWidth="md">
    <div class="p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Edit Pengaturan Kelompok</h3>
                <p class="text-[11px] text-slate-400">Perbarui identitas, kuota, atau status keaktifan kelompok.</p>
            </div>
            <button type="button" @click="$dispatch('close-modal', 'modal-edit-kelompok')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('kelompok.update', $kelompok) }}" class="space-y-3.5">
            @csrf
            @method('PUT')

            {{-- Periode Info Context --}}
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 text-[11px] flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Periode Akademik:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $kelompok->periode?->nama ?? '-' }}</span>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Kode Kelompok</label>
                    <input type="text" name="kode" value="{{ old('kode', $kelompok->kode) }}" placeholder="KLP-01" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 uppercase font-bold">
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Kelompok</label>
                    <input type="text" name="nama" value="{{ old('nama', $kelompok->nama) }}" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-medium">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between text-[11px] mb-1">
                    <label class="font-semibold text-slate-600 dark:text-slate-300">Kapasitas Maksimal Santri</label>
                    <span class="text-slate-400">Terdaftar: <strong class="text-slate-700 dark:text-slate-200 font-semibold">{{ $kelompok->anggota->count() }} santri</strong></span>
                </div>
                <input type="number" name="kapasitas" min="{{ max(1, $kelompok->anggota->count()) }}" max="500" value="{{ old('kapasitas', $kelompok->kapasitas) }}" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold">
                @if($kelompok->anggota->count() > 0)
                    <p class="text-[10px] text-slate-400 mt-1">Kapasitas tidak boleh lebih kecil dari jumlah santri yang sudah terdaftar ({{ $kelompok->anggota->count() }} santri).</p>
                @endif
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Keterangan / Catatan Lokasi</label>
                <textarea name="keterangan" rows="2" placeholder="Catatan lokasi halaqah atau deskripsi kelompok..." class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">{{ old('keterangan', $kelompok->keterangan ?? $kelompok->deskripsi) }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="chk_is_active" value="1" {{ old('is_active', $kelompok->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <label for="chk_is_active" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Status Kelompok Aktif</label>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button"
                    data-confirm="Hapus kelompok {{ $kelompok->nama }}? Aksi ini hanya dapat dilakukan bila kelompok tidak memiliki anggota."
                    data-action="{{ route('kelompok.destroy', $kelompok) }}"
                    data-method="DELETE"
                    class="px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition cursor-pointer confirm-trigger">
                    Hapus Kelompok
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$dispatch('close-modal', 'modal-edit-kelompok')" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">Perbarui Kelompok</button>
                </div>
            </div>
        </form>
    </div>
</x-modal>

@php
    $otherKelompokData = $otherKelompokList->map(fn($k) => [
        'id' => $k->id,
        'nama' => $k->nama,
        'kode' => $k->kode ?? '',
        'kapasitas' => $k->kapasitas,
        'terisi' => $k->anggota_count ?? 0,
        'sisa' => max(0, $k->kapasitas - ($k->anggota_count ?? 0)),
    ])->values();
@endphp

{{-- Modal Pindahkan Santri Tunggal Terpusat --}}
<div
    x-data="{
        santriId: null,
        santriName: '',
        santriNim: '',
        targetKelompokId: '',
        search: '',
        allKelompok: {{ \Illuminate\Support\Js::from($otherKelompokData) }},
        openPindahModal(detail) {
            this.santriId = detail.id;
            this.santriName = detail.name;
            this.santriNim = detail.nim;
            this.targetKelompokId = '';
            this.search = '';
            this.$dispatch('open-modal', 'modal-pindah-santri');
        },
        get filteredKelompok() {
            if (!this.search.trim()) return this.allKelompok;
            const q = this.search.toLowerCase().trim();
            return this.allKelompok.filter(k => 
                k.nama.toLowerCase().includes(q) || 
                (k.kode && k.kode.toLowerCase().includes(q))
            );
        }
    }"
    @pindah-santri.window="openPindahModal($event.detail)"
>
    <x-modal name="modal-pindah-santri" maxWidth="md">
        <div class="p-5 space-y-4 text-left">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Pindahkan Santri ke Kelompok Lain</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pindahkan keanggotaan santri ke kelompok aktif lainnya.</p>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'modal-pindah-santri')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer p-1">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" :action="'{{ url('dashboard/kelompok/' . $kelompok->id . '/anggota') }}/' + santriId + '/pindah'" class="space-y-3.5">
                @csrf

                {{-- Baris Info Santri & Kelompok Sekarang --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Mahasantri</label>
                    <div class="px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                        <div class="min-w-0 pr-2">
                            <span class="font-bold text-slate-900 dark:text-white truncate block text-xs" x-text="santriName"></span>
                            <span class="font-mono text-slate-500 dark:text-slate-400 text-[11px]" x-text="'NIM: ' + santriNim"></span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] text-slate-400 block font-medium">Kelompok Saat Ini:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60">
                                {{ $kelompok->nama }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pilih Kelompok Tujuan dengan Filter Pencarian Langsung yang Nyaman --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">
                            Pilih Kelompok Tujuan
                        </label>
                        <span class="text-[11px] text-slate-400 font-mono">
                            <strong class="text-blue-600 dark:text-blue-400" x-text="filteredKelompok.length"></strong> kelompok cocok
                        </span>
                    </div>

                    {{-- Kolom Input Pencarian --}}
                    <div class="relative">
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Ketik nama atau kode kelompok tujuan..."
                            class="w-full text-xs rounded-xl pl-9 pr-8 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                        />
                        <svg class="size-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-0.5 cursor-pointer">&times;</button>
                    </div>

                    {{-- Daftar Pilihan Kelompok (In-Flow, Max Height, Scrollable Rapi & Tidak Kepotong) --}}
                    <div class="max-h-40 overflow-y-auto space-y-1.5 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/50">
                        <template x-for="k in filteredKelompok" :key="k.id">
                            <label
                                class="flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition select-none"
                                :class="targetKelompokId == k.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40 ring-1 ring-blue-500 shadow-2xs' : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <input type="radio" name="target_kelompok_id" :value="k.id" x-model="targetKelompokId" required class="text-blue-600 focus:ring-blue-500 cursor-pointer size-4 shrink-0">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-xs text-slate-900 dark:text-white truncate" x-text="k.nama"></span>
                                            <template x-if="k.kode">
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300" x-text="k.kode"></span>
                                            </template>
                                        </div>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5" x-text="'Kapasitas: ' + k.terisi + '/' + k.kapasitas + ' santri'"></span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full inline-block"
                                        :class="k.sisa > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200/60'"
                                        x-text="'Sisa ' + k.sisa + ' slot'"></span>
                                </div>
                            </label>
                        </template>

                        <div x-show="filteredKelompok.length === 0" class="py-6 text-center text-xs text-slate-400">
                            <span x-show="allKelompok.length === 0">Tidak ada kelompok lain yang aktif di periode ini.</span>
                            <span x-show="allKelompok.length > 0">Tidak ada kelompok yang cocok dengan pencarian.</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons (Selalu Terlihat di Bagian Bawah Modal, Dijamin Tidak Tertutup) --}}
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="$dispatch('close-modal', 'modal-pindah-santri')" class="px-3.5 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">Batal</button>
                    <button type="submit" :disabled="!santriId || !targetKelompokId" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">Pindahkan Santri</button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
