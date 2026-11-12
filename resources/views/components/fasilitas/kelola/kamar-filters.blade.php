<!-- Toolbar Filter & Aksi Cepat -->
<div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs space-y-3">
    <form method="GET" action="{{ route('fasilitas.kelola.index') }}" class="flex flex-col gap-3">
        <input type="hidden" name="tab" value="kamar">
        <input type="hidden" name="view" value="{{ $viewMode }}">

        <!-- Filter Grid: Symmetrical 6 Filters -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
            <!-- 1. Dropdown Gedung -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Gedung</label>
                <select
                    name="gedung_id"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Semua Gedung ({{ $allGedung->count() }})</option>
                    @foreach($allGedung as $g)
                        <option value="{{ $g->id }}" {{ request('gedung_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} ({{ $g->gender_khusus === 'L' ? 'Putra' : ($g->gender_khusus === 'P' ? 'Putri' : 'Campur') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Dropdown Lantai -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Lantai</label>
                <select
                    name="lantai_id"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Semua Lantai</option>
                    @foreach($allGedung as $g)
                        @if(!request('gedung_id') || request('gedung_id') == $g->id)
                            <optgroup label="{{ $g->nama }}">
                                @foreach($g->lantai as $l)
                                    <option value="{{ $l->id }}" {{ request('lantai_id') == $l->id ? 'selected' : '' }}>
                                        Lantai {{ $l->nomor_lantai }} ({{ $l->kamar_count ?? $l->kamar->count() }} Kamar)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- 3. Filter Gender -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Zona Gender</label>
                <select
                    name="gender"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Semua Gender</option>
                    <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Ikhwan (Putra)</option>
                    <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Akhwat (Putri)</option>
                </select>
            </div>

            <!-- 4. Filter Tipe Ruang (Mahasantri vs Mudabbir) -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tipe Ruang</label>
                <select
                    name="tipe_ruang"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Semua Tipe Ruang</option>
                    <option value="kamar" {{ request('tipe_ruang') === 'kamar' ? 'selected' : '' }}>Kamar Mahasantri</option>
                    <option value="mudabbir" {{ request('tipe_ruang') === 'mudabbir' ? 'selected' : '' }}>Kamar Mudabbir</option>
                </select>
            </div>

            <!-- 5. Filter Status (Ketersediaan & Okupansi) -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Status Okupansi</label>
                <select
                    name="status"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Semua Status</option>
                    <option value="tersedia" {{ request('status') === 'tersedia' ? 'selected' : '' }}>Tersedia (Sisa Kuota)</option>
                    <option value="penuh" {{ request('status') === 'penuh' ? 'selected' : '' }}>Terisi Penuh</option>
                    <option value="kosong" {{ request('status') === 'kosong' ? 'selected' : '' }}>Belum Berpenghuni</option>
                    <option value="perbaikan" {{ request('status') === 'perbaikan' ? 'selected' : '' }}>Pemeliharaan / Perbaikan</option>
                </select>
            </div>

            <!-- 6. Pencarian No Kamar -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Cari No. Kamar</label>
                <x-search-input
                    name="search"
                    :value="request('search', '')"
                    placeholder="Contoh: JD2-01..."
                    class="w-full max-w-none"
                />
            </div>
        </div>

        <!-- Toolbar Bawah: Switcher & Tombol Aksi -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                @if(request()->hasAny(['gedung_id', 'lantai_id', 'gender', 'tipe_ruang', 'status', 'search']))
                    <a
                        href="{{ route('fasilitas.kelola.index', ['tab' => 'kamar', 'view' => $viewMode]) }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-block text-center"
                    >
                        Reset
                    </a>
                @endif

                <!-- Switcher View: Per Lantai vs Tabel -->
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700">
                    <a
                        href="{{ request()->fullUrlWithQuery(['view' => 'per_lantai']) }}"
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $viewMode === 'per_lantai' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}"
                        title="Tampilan Grup Per Lantai"
                    >
                        Grup Lantai
                    </a>
                    <a
                        href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $viewMode === 'table' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}"
                        title="Tampilan Tabel Flat"
                    >
                        Tabel Flat
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="$dispatch('open-add-kamar')"
                    class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Kamar
                </button>

                <button
                    type="button"
                    @click="$dispatch('open-batch-kamar')"
                    class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Batch
                </button>
            </div>
        </div>
    </form>
</div>
