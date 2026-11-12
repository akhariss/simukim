<x-app-layout title="Penempatan Mahasantri">
    @php
        $activeTab = request('tab', 'aktif');
        $initialMode = request('mode', 'santri');
    @endphp

    {{-- Header Halaman --}}
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Penempatan Mahasantri']
        ]"
        title="Penempatan Mahasantri"
        description="Kelola penempatan mahasantri ke kamar asrama, alokasi kolektif, dan kartu tinggal."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($activeTab === 'unassigned' && $counts['unassigned'] > 0)
                    <form action="{{ route('fasilitas.penempatan.auto-assign') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="tanggal_masuk" value="{{ date('Y-m-d') }}">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition cursor-pointer"
                            data-confirm="Alokasikan penempatan kamar secara otomatis untuk {{ $counts['unassigned'] }} mahasantri yang belum ditempatkan?"
                            data-confirm-title="Penempatan Otomatis"
                            data-confirm-type="primary"
                        >
                            <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Penempatan Otomatis</span>
                        </button>
                    </form>
                @endif

                <a
                    href="{{ route('fasilitas.monitoring.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition"
                >
                    <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Monitoring Kamar</span>
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if(session('info'))
        <div class="px-4 py-2.5 rounded-xl bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 text-xs text-blue-800 dark:text-blue-300 font-medium">{{ session('info') }}</div>
    @endif
    @if(session('warning'))
        <div class="px-4 py-2.5 rounded-xl bg-amber-50 dark:bg-slate-800 border border-amber-200 dark:border-slate-700 text-xs text-amber-800 dark:text-amber-300 font-medium">{{ session('warning') }}</div>
    @endif

    {{-- Navigasi Tab Utama --}}
    <x-fasilitas.penempatan.nav-tabs :activeTab="$activeTab" :counts="$counts" />

    {{-- Konten Utama Terpadu --}}
    <div
        id="penempatan-table-container"
        x-data="{
            viewMode: '{{ $initialMode }}',
            search: '{{ request('search') }}',
            gender: '{{ request('gender') }}',
            role: '{{ request('role') }}',
            gedung_id: '{{ request('gedung_id') }}',
            activeTab: '{{ $activeTab }}',
            isLoading: false,
            timer: null,
            switchMode(mode) {
                this.viewMode = mode;
                this.search = '';
                this.fetchTable();
            },
            onInput() {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.fetchTable(), 300);
            },
            clearSearch() {
                this.search = '';
                this.fetchTable();
            },
            abortCtrl: null,
            fetchTable(pageUrl = null) {
                if (this.abortCtrl) this.abortCtrl.abort();
                this.abortCtrl = new AbortController();
                this.isLoading = true;
                const url = pageUrl ? new URL(pageUrl, window.location.origin) : new URL(window.location.href);
                url.searchParams.set('tab', this.activeTab);
                if (this.activeTab === 'unassigned') url.searchParams.set('mode', this.viewMode);

                if (!pageUrl) {
                    ['search', 'gender', 'role', 'gedung_id'].forEach(k => this[k] ? url.searchParams.set(k, this[k]) : url.searchParams.delete(k));
                    url.searchParams.delete('page');
                    url.searchParams.delete('kamar_page');
                }

                window.history.replaceState({}, '', url.toString());

                fetch(url.toString(), {
                    signal: this.abortCtrl.signal,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-SPA-Navigation': 'true' }
                })
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const target = doc.getElementById('table-records-wrapper');
                    const currentWrapper = document.getElementById('table-records-wrapper');
                    if (target && currentWrapper) {
                        currentWrapper.innerHTML = target.innerHTML;
                        if (window.Alpine?.initTree) {
                            try { window.Alpine.initTree(currentWrapper); } catch (e) {}
                        }
                    }
                })
                .catch((err) => {
                    if (err.name !== 'AbortError') console.error('[Penempatan] Fetch error:', err);
                })
                .finally(() => { this.isLoading = false; });
            }
        }"
        x-init="
            $el.addEventListener('click', (e) => {
                const link = e.target.closest('#table-records-wrapper nav a');
                if (link && link.href) {
                    e.preventDefault();
                    fetchTable(link.href);
                }
            });
        "
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-xs overflow-hidden"
    >
        {{-- Mode Alokasi Bar: Khusus Tab Belum Ber-Kamar --}}
        @if($activeTab === 'unassigned')
            <div class="px-3.5 py-2.5 sm:px-4 sm:py-3 bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 sm:gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] sm:text-[10.5px] font-bold text-slate-500 uppercase tracking-wider hidden sm:inline">Metode Alokasi:</span>
                    <x-tab-switcher
                        model="viewMode"
                        color="blue"
                        @tab-changed.window="switchMode($event.detail)"
                        :tabs="[
                            [
                                'id' => 'santri',
                                'label' => 'Daftar Mahasantri',
                                'short_label' => 'Santri',
                                'count' => $counts['unassigned'],
                            ],
                            [
                                'id' => 'kamar',
                                'label' => 'Katalog Kamar',
                                'short_label' => 'Kamar',
                            ],
                        ]"
                    />
                </div>
                <div class="text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-blue-500 shrink-0"></span>
                    <span x-show="viewMode === 'santri'" class="truncate">Alokasi per individu mahasantri.</span>
                    <span x-show="viewMode === 'kamar'" class="truncate">Alokasi kolektif per unit kamar siap huni.</span>
                </div>
            </div>
        @endif

        {{-- Toolbar Terpadu: Search & Filter (Simetris Mobile & Desktop) --}}
        <div class="p-3 sm:p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 sm:gap-3 bg-slate-50/60 dark:bg-slate-900/60">
            <div class="flex-1 max-w-md">
                <x-search-input
                    x-model="search"
                    @input="onInput()"
                    :placeholder="request('mode') === 'kamar' ? 'Cari nomor kamar atau gedung...' : 'Cari nama, NIM, atau kamar...'"
                />
            </div>

            {{-- Filter Sesuai Mode --}}
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                {{-- Filter Gedung (Ditampilkan saat Mode Kamar atau tab Aktif/Riwayat) --}}
                <select
                    x-show="viewMode === 'kamar' || activeTab !== 'unassigned'"
                    x-model="gedung_id"
                    @change="fetchTable()"
                    class="text-xs rounded-xl px-2.5 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium focus:outline-none cursor-pointer"
                >
                    <option value="">Semua Gedung</option>
                    @foreach($gedungList as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>

                <select
                    x-model="gender"
                    @change="fetchTable()"
                    class="text-xs rounded-xl px-2.5 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium focus:outline-none cursor-pointer"
                >
                    <option value="">Semua Gender</option>
                    <option value="L">Ikhwan</option>
                    <option value="P">Akhwat</option>
                </select>

                {{-- Filter Peran (Hanya saat Mode Santri) --}}
                <select
                    x-show="viewMode === 'santri' && activeTab === 'unassigned'"
                    x-model="role"
                    @change="fetchTable()"
                    class="text-xs rounded-xl px-2.5 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium focus:outline-none cursor-pointer"
                >
                    <option value="">Semua Peran</option>
                    <option value="mudabbir">Mudabbir</option>
                    <option value="mahasantri">Mahasantri</option>
                </select>

                <button
                    x-show="search || gender || role || gedung_id"
                    x-cloak
                    type="button"
                    @click="search = ''; gender = ''; role = ''; gedung_id = ''; fetchTable();"
                    class="px-2.5 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer text-center"
                >
                    Reset
                </button>
            </div>
        </div>

        {{-- Dynamic Table Records Container --}}
        <div id="table-records-wrapper">
            @if($activeTab === 'unassigned')
                <div x-show="viewMode === 'santri'">
                    <x-fasilitas.penempatan.table-unassigned :unassignedSantri="$unassignedSantri" />
                </div>
                <div x-show="viewMode === 'kamar'" x-cloak>
                    <x-fasilitas.penempatan.table-kamar-available :availableRooms="$availableRooms" />
                </div>
            @elseif($activeTab === 'riwayat')
                <x-fasilitas.penempatan.table-riwayat :penempatanList="$penempatanList" />
            @else
                <x-fasilitas.penempatan.table-aktif :penempatanList="$penempatanList" />
            @endif
        </div>
    </div>

    {{-- Modals --}}
    <x-fasilitas.penempatan.modal-assign :gedungList="$gedungList" />
    <x-fasilitas.penempatan.modal-batch-assign :gedungList="$gedungList" />
    <x-fasilitas.penempatan.modal-pindah :gedungList="$gedungList" />
</x-app-layout>
