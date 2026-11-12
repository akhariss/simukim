<!-- Toolbar Filter & Pencarian Monitoring Asrama -->
<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 shadow-2xs">
    <form method="GET" action="{{ route('fasilitas.monitoring.index') }}" class="space-y-3" id="filterForm">
        <input type="hidden" name="view" value="{{ request('view', 'grid') }}">

        <!-- Symmetrical 6 Filters Grid (6 in lg, 3 in md, 2 in mobile) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
            <!-- 1. Gedung Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Gedung</label>
                <select
                    name="gedung_id"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                >
                    <option value="">Semua Gedung</option>
                    @foreach($gedungList as $g)
                        <option value="{{ $g->id }}" {{ request('gedung_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }} ({{ $g->gender_khusus === 'L' ? 'Putra' : ($g->gender_khusus === 'P' ? 'Putri' : 'Campur') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Lantai Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Lantai</label>
                <select
                    name="lantai_id"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                >
                    <option value="">Semua Lantai</option>
                    @foreach($gedungList as $g)
                        @if(!request('gedung_id') || request('gedung_id') == $g->id)
                            <optgroup label="{{ $g->nama }}">
                                @foreach($g->lantai as $l)
                                    <option value="{{ $l->id }}" {{ request('lantai_id') == $l->id ? 'selected' : '' }}>
                                        Lantai {{ $l->nomor_lantai }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- 3. Gender Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Zona Gender</label>
                <select
                    name="gender"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                >
                    <option value="">Semua Gender</option>
                    <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Ikhwan (Putra)</option>
                    <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Akhwat (Putri)</option>
                </select>
            </div>

            <!-- 4. Tipe Ruang Filter (Mahasantri vs Mudabbir) -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tipe Ruang</label>
                <select
                    name="tipe_ruang"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                >
                    <option value="">Semua Tipe Ruang</option>
                    <option value="kamar" {{ request('tipe_ruang') === 'kamar' ? 'selected' : '' }}>Kamar Mahasantri</option>
                    <option value="mudabbir" {{ request('tipe_ruang') === 'mudabbir' ? 'selected' : '' }}>Kamar Mudabbir</option>
                </select>
            </div>

            <!-- 5. Status Okupansi Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Status Okupansi</label>
                <select
                    name="status"
                    onchange="this.form.requestSubmit()"
                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                >
                    <option value="">Semua Status</option>
                    <option value="tersedia" {{ request('status') === 'tersedia' ? 'selected' : '' }}>Tersedia (Sisa Kuota)</option>
                    <option value="penuh" {{ request('status') === 'penuh' ? 'selected' : '' }}>Terisi Penuh</option>
                    <option value="kosong" {{ request('status') === 'kosong' ? 'selected' : '' }}>Belum Berpenghuni</option>
                    <option value="perbaikan" {{ request('status') === 'perbaikan' ? 'selected' : '' }}>Pemeliharaan / Perbaikan</option>
                </select>
            </div>

            <!-- 6. Search Box -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Cari No. Kamar / Santri</label>
                <x-search-input
                    name="search"
                    :value="request('search', '')"
                    placeholder="Kamar, NIM, nama..."
                    class="w-full max-w-none"
                />
            </div>
        </div>

        <!-- Footer Filter: Tombol Terapkan & Reset -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800/60 text-xs">
            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="px-3.5 py-1.5 rounded-xl font-semibold bg-blue-600 hover:bg-blue-700 text-white transition cursor-pointer shadow-2xs"
                >
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search', 'gedung_id', 'lantai_id', 'gender', 'tipe_ruang', 'status']))
                    <a
                        href="{{ route('fasilitas.monitoring.index', ['view' => request('view', 'grid')]) }}"
                        class="px-3 py-1.5 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        Reset Filter
                    </a>
                @endif
            </div>

            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                Ditemukan <span class="font-bold text-slate-700 dark:text-slate-300">{{ $kamarList->total() }}</span> unit kamar
            </div>
        </div>
    </form>
</div>
