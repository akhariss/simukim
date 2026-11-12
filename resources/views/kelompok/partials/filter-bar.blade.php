{{-- Compact Smart Filter & Search Bar --}}
<div class="p-2 sm:p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
    <form method="GET" action="{{ route('kelompok.index') }}" class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
        {{-- Search Input --}}
        <div class="flex-1">
            <x-search-input
                name="search"
                :value="$search"
                placeholder="Cari nama kelompok, kode, atau keterangan..."
            />
        </div>

        {{-- Filter Kapasitas Santri (Inline) --}}
        <select
            name="filter_kapasitas"
            onchange="this.form.requestSubmit()"
            class="w-full sm:w-auto px-3 py-1.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-1 focus:ring-emerald-500"
        >
            <option value="">Kapasitas: Semua</option>
            <option value="tersedia" {{ $filterKapasitas === 'tersedia' ? 'selected' : '' }}>Kapasitas: Ada Kuota</option>
            <option value="penuh" {{ $filterKapasitas === 'penuh' ? 'selected' : '' }}>Kapasitas: Penuh</option>
            <option value="kosong" {{ $filterKapasitas === 'kosong' ? 'selected' : '' }}>Kapasitas: Kosong (0)</option>
        </select>

        {{-- Filter Penugasan Tutor (Inline) --}}
        <select
            name="filter_tutor"
            onchange="this.form.requestSubmit()"
            class="w-full sm:w-auto px-3 py-1.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-1 focus:ring-emerald-500"
        >
            <option value="">Tutor: Semua</option>
            <option value="lengkap" {{ $filterTutor === 'lengkap' ? 'selected' : '' }}>Tutor: Lengkap</option>
            <option value="belum_lengkap" {{ $filterTutor === 'belum_lengkap' ? 'selected' : '' }}>Tutor: Sebagian</option>
            <option value="kosong" {{ $filterTutor === 'kosong' ? 'selected' : '' }}>Tutor: Belum Ada</option>
        </select>

        {{-- Reset Filter Button (Only when active) --}}
        @if($search !== '' || $filterKapasitas !== '' || $filterTutor !== '')
            <a
                href="{{ route('kelompok.index') }}"
                class="px-2.5 py-1.5 text-xs font-semibold rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition shrink-0 text-center"
                title="Reset filter"
            >
                Reset
            </a>
        @endif
    </form>
</div>
