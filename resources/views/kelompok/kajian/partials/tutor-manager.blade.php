{{-- Panel Manajemen & Penugasan Tutor Pengampu (Searchable Combobox) --}}
<div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Tutor Pengampu Kajian</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Penetapan pengajar resmi untuk kajian {{ $kelas->nama }} di {{ $kelompok->nama }}.</p>
        </div>
        @if ($pengampu?->tutor)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/60">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                Aktif Mengampu
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200/60">
                <span class="size-1.5 rounded-full bg-amber-500"></span>
                Belum Ditugaskan
            </span>
        @endif
    </div>

    <!-- Profil Tutor Saat Ini (Jika Ada) -->
    @if ($pengampu?->tutor)
        <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 font-bold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($pengampu->tutor->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $pengampu->tutor->name }}</h3>
                    <p class="text-xs font-mono text-slate-500 dark:text-slate-400">NIP/ID: {{ $pengampu->tutor->nomor_induk ?? '-' }} &bull; {{ $pengampu->tutor->email }}</p>
                </div>
            </div>
            @hasrole('admin')
                <button type="button"
                    data-confirm="Lepas penugasan tutor {{ $pengampu->tutor->name }} dari kajian {{ $kelas->nama }} kelompok ini?"
                    data-action="{{ route('kelompok.kajian.unassign.tutor', [$kelompok, $kelas]) }}"
                    data-method="DELETE"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 transition cursor-pointer shrink-0 confirm-trigger">
                    Lepas Tutor
                </button>
            @endhasrole
        </div>
    @else
        <div class="p-3.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-xs text-amber-800 dark:text-amber-300">
            Kajian ini belum memiliki tutor pengampu di rombel {{ $kelompok->nama }}. Silakan cari dan pilih tutor pada formulir di bawah.
        </div>
    @endif

    <!-- Formulir Ganti / Tambah Tutor (Searchable Combobox) -->
    @hasrole('admin')
        <form method="POST" action="{{ route('kelompok.kajian.assign.tutor', [$kelompok, $kelas]) }}"
            class="space-y-3.5 pt-2 border-t border-slate-100 dark:border-slate-800"
            x-data="{
                open: false,
                search: '',
                selectedId: '{{ $pengampu?->tutor_id ?? '' }}',
                selectedName: '{{ addslashes($pengampu?->tutor?->name ?? '') }}',
                selectedNim: '{{ $pengampu?->tutor?->nomor_induk ?? '-' }}',
                tutors: @js($tutorCandidates),
                get availableTutors() {
                    // Sesuai Aturan: hanya tutor eligible yang muncul
                    return this.tutors.filter(t => t.is_eligible);
                },
                get filteredTutors() {
                    let s = this.search.toLowerCase().trim();
                    if (!s) return this.availableTutors;
                    return this.availableTutors.filter(t => 
                        (t.name && t.name.toLowerCase().includes(s)) ||
                        (t.nomor_induk && t.nomor_induk.toLowerCase().includes(s))
                    );
                },
                selectTutor(t) {
                    this.selectedId = t.id;
                    this.selectedName = t.name;
                    this.selectedNim = t.nomor_induk;
                    this.search = '';
                    this.open = false;
                },
                clear() {
                    this.selectedId = '';
                    this.selectedName = '';
                    this.selectedNim = '';
                    this.search = '';
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                        this.open = true;
                    });
                }
            }">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="tutor_id" :value="selectedId">

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        {{ $pengampu?->tutor ? 'Ganti Tutor Pengampu:' : 'Pilih & Tugaskan Tutor Baru:' }}
                    </label>
                    <span class="text-[11px] text-slate-400">
                        <strong class="text-blue-600 dark:text-blue-400 font-mono" x-text="availableTutors.length"></strong> tutor tersedia
                    </span>
                </div>

                <!-- Box Pilihan: Saat Tutor Terpilih -->
                <div x-show="selectedId" class="p-3 rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/30 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="size-8 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                            <span x-text="selectedName ? selectedName.substring(0, 2).toUpperCase() : '?'"></span>
                        </div>
                        <div class="min-w-0">
                            <strong class="text-xs text-slate-800 dark:text-slate-100 block truncate" x-text="selectedName"></strong>
                            <span class="font-mono text-[10px] text-slate-400 block" x-text="'NIP/ID: ' + selectedNim"></span>
                        </div>
                    </div>
                    <button type="button" @click="clear()" class="px-2.5 py-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-lg transition cursor-pointer shrink-0 shadow-2xs">
                        Ganti Pilihan
                    </button>
                </div>

                <!-- Box Pencarian: Saat Belum Memilih / Mau Ganti -->
                <div x-show="!selectedId" class="relative">
                    <div class="relative">
                        <input type="text"
                            x-ref="searchInput"
                            x-model="search"
                            @focus="open = true"
                            @click="open = true"
                            @keydown.escape="open = false"
                            placeholder="Ketik Nama atau NIP tutor..."
                            class="w-full text-xs rounded-xl pl-9 pr-8 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <svg class="size-4 text-slate-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 p-0.5">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Floating Dropdown Hasil Pencarian -->
                    <div x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-98"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute z-30 left-0 right-0 mt-1.5 max-h-56 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl divide-y divide-slate-100 dark:divide-slate-700/60">

                        <template x-for="t in filteredTutors" :key="t.id">
                            <div @click="selectTutor(t)" class="p-2.5 hover:bg-blue-50/70 dark:hover:bg-slate-700/50 cursor-pointer flex items-center justify-between gap-2 transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <strong class="text-xs text-slate-800 dark:text-slate-100" x-text="t.name"></strong>
                                        <template x-if="t.is_current">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">Tutor Saat Ini</span>
                                        </template>
                                    </div>
                                    <span class="font-mono text-[10px] text-slate-400 block" x-text="'NIP: ' + t.nomor_induk + ' • ' + t.email"></span>
                                </div>
                                <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 shrink-0">Pilih &rarr;</span>
                            </div>
                        </template>

                        <template x-if="filteredTutors.length === 0">
                            <div class="p-4 text-center text-xs text-slate-400">
                                <p>Tidak ditemukan tutor yang cocok atau tersedia.</p>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Pastikan ejaan nama atau NIP benar.</span>
                            </div>
                        </template>
                    </div>
                </div>

                @if($unavailableCount > 0)
                    <p class="text-[10.5px] text-slate-400 mt-1.5 flex items-center gap-1">
                        <svg class="size-3 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $unavailableCount }} tutor lainnya tidak ditampilkan karena sudah mengampu kajian ini di kelompok lain (aturan 1 tutor 1 kelompok).</span>
                    </p>
                @endif
            </div>

            <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-slate-800/80 border border-blue-100 dark:border-slate-700 text-[11px] text-blue-900 dark:text-blue-300 space-y-1">
                <span class="font-semibold block">Aturan Penugasan Pengampu:</span>
                <p>&bull; Untuk kajian <strong>Al-Qur'an &amp; Kitab Kuning</strong>: 1 tutor hanya dapat mengampu 1 kelompok agar bimbingan intensif.</p>
                <p>&bull; Untuk kajian <strong>Masal</strong>: 1 tutor diperbolehkan mengampu beberapa kelompok sekaligus.</p>
            </div>

            <div class="pt-1 flex items-center justify-end">
                <button type="submit"
                    :disabled="!selectedId"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-2xs cursor-pointer">
                    {{ $pengampu?->tutor ? 'Simpan Perubahan Tutor' : 'Tetapkan Sebagai Tutor' }}
                </button>
            </div>
        </form>
    @endhasrole
</div>

