<!-- Pusat Rekonsiliasi & Kontrol SAHAJA -->
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs overflow-hidden" x-data="{ confirming: false, syncing: false }">
    <!-- Header Bar: Indikator Koneksi + Pengalih Semester Terintegrasi -->
    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="size-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 flex-wrap">
                    <span>Pusat Sinkronisasi & Rekonsiliasi SAHAJA</span>
                    @if ($sahajaInfo['online'])
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <span class="size-1.5 rounded-full bg-slate-400"></span>
                            Mode DB Lokal
                        </span>
                    @endif
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Komparasi data integritas pangkalan kampus vs lokal secara presisi.</p>
            </div>
        </div>

        <!-- Quick Switcher Semester Aktif -->
        <form method="POST" action="{{ route('admin.cache.periode.switch') }}" class="flex items-center gap-1.5 self-start sm:self-auto shrink-0 w-full sm:w-auto">
            @csrf
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 p-1.5 rounded-xl text-xs w-full sm:w-auto">
                <select name="periode_id" class="text-xs font-semibold bg-transparent border-0 text-slate-800 dark:text-slate-100 focus:ring-0 cursor-pointer pr-4 flex-1 sm:flex-initial">
                    @foreach ($periodes as $p)
                        <option value="{{ $p->id }}" {{ $p->is_active ? 'selected' : '' }}>
                            {{ $p->nama }} ({{ $p->kode }}) {{ $p->is_active ? '● Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition cursor-pointer shrink-0">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Banner Hasil Sinkronisasi Terakhir (Jika Ada) -->
    @if (session('reconciliation_report'))
        @php $report = session('reconciliation_report'); @endphp
        <div class="m-3 sm:m-4 p-3 rounded-xl bg-blue-50/70 dark:bg-slate-800/80 border border-blue-200/80 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 text-blue-900 dark:text-blue-200 font-bold">
                <span>✓ Sinkronisasi Berhasil:</span>
                <span class="font-mono text-slate-600 dark:text-slate-400 font-normal">{{ $report['synced'] ?? 0 }} data ({{ $report['new'] ?? 0 }} baru, {{ $report['updated'] ?? 0 }} diperbarui)</span>
            </div>
            <span class="text-[11px] font-mono text-slate-400">Target Semester: {{ $activePeriode?->kode }}</span>
        </div>
    @endif

    <!-- 4 Matriks Komparasi Proporsional & Mobile-Friendly -->
    <div class="p-3 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
            <!-- 1. Total Mahasantri Mukim -->
            <div class="p-3 sm:p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 space-y-1">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span>1. Santri Mukim</span>
                    @if ($sahajaInfo['online'] && $appStats['selisih'] > 0)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Kurang {{ $appStats['selisih'] }}</span>
                    @elseif ($sahajaInfo['online'])
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Sinkron</span>
                    @else
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Lokal</span>
                    @endif
                </div>
                <div class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                    {{ $appStats['campus_santri'] }} <span class="text-xs font-medium text-slate-400">Santri</span>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/60 flex items-center justify-between">
                    <span>SAHAJA: <strong class="text-slate-600 dark:text-slate-300">{{ $sahajaInfo['online'] ? ($sahajaInfo['summary']['total_mukim'] ?? 0) : 'Offline' }}</strong></span>
                    <span>Lokal: <strong class="text-slate-600 dark:text-slate-300">{{ $appStats['local_santri'] }}</strong></span>
                </div>
            </div>

            <!-- 2. Gender L / P -->
            <div class="p-3 sm:p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 space-y-1">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span>2. Rasio Gender</span>
                    <span class="text-[10px] font-mono text-slate-400">L / P</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight">
                    {{ $appStats['campus_l'] }}L <span class="text-sm font-bold text-slate-400">/</span> <span class="text-rose-500">{{ $appStats['campus_p'] }}P</span>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/60 flex items-center justify-between">
                    <span>Banin: <strong class="text-slate-600 dark:text-slate-300">{{ $appStats['campus_l'] }}</strong></span>
                    <span>Banat: <strong class="text-slate-600 dark:text-slate-300">{{ $appStats['campus_p'] }}</strong></span>
                </div>
            </div>

            <!-- 3. Alokasi Kamar -->
            <div class="p-3 sm:p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 space-y-1">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span>3. Alokasi Kamar</span>
                    @if ($appStats['unassigned'] > 0)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">{{ $appStats['unassigned'] }} Belum</span>
                    @else
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Tuntas</span>
                    @endif
                </div>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                    {{ $appStats['assigned'] }} <span class="text-xs font-medium text-slate-400">Ditempatkan</span>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/60 flex items-center justify-between">
                    <span>Sudah: <strong class="text-emerald-600">{{ $appStats['assigned'] }}</strong></span>
                    <span>Belum: <strong class="{{ $appStats['unassigned'] > 0 ? 'text-amber-600 font-bold' : 'text-slate-600' }}">{{ $appStats['unassigned'] }}</strong></span>
                </div>
            </div>

            <!-- 4. Tagihan BRIVA -->
            <div class="p-3 sm:p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 space-y-1">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span>4. Billing BRIVA</span>
                    @if ($appStats['unpaid'] > 0)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">{{ $appStats['unpaid'] }} Menunggak</span>
                    @else
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Lunas Semua</span>
                    @endif
                </div>
                <div class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                    {{ $appStats['lunas'] }} <span class="text-xs font-medium text-slate-400">Lunas</span>
                </div>
                <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/60 flex items-center justify-between">
                    <span>Lunas: <strong class="text-emerald-600">{{ $appStats['lunas'] }}</strong></span>
                    <span>Nunggak: <strong class="{{ $appStats['unpaid'] > 0 ? 'text-rose-600 font-bold' : 'text-slate-600' }}">{{ $appStats['unpaid'] }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Kontrol Sinkronisasi & Filter API -->
        <form method="POST" action="{{ route('admin.cache.sahaja.sync') }}" class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80" @submit="syncing = true">
            @csrf
            <input type="hidden" name="kode_periode" value="">

            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 flex-1">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 shrink-0">Filter Tarik:</span>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60 text-blue-700 dark:text-blue-300 text-xs font-bold">
                        <span class="size-2 rounded-full bg-blue-500 animate-pulse"></span>
                        <span>Otomatis Periode Aktif SAHAJA</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 w-full sm:w-auto">
                        <select name="status_pembayaran" class="text-xs rounded-xl px-2.5 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-blue-500">
                            <option value="all">Semua Tagihan</option>
                            <option value="Lunas">Hanya Lunas</option>
                            <option value="Belum Bayar">Belum Bayar</option>
                        </select>
                        <select name="gender" class="text-xs rounded-xl px-2.5 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-blue-500">
                            <option value="all">Semua Gender</option>
                            <option value="L">Banin (L)</option>
                            <option value="P">Banat (P)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <template x-if="!confirming">
                        <button type="button" @click="confirming = true" :disabled="syncing" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 active:scale-98 disabled:opacity-50 transition shadow-2xs cursor-pointer">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Tarik &amp; Sinkronkan SAHAJA</span>
                        </button>
                    </template>

                    <template x-if="confirming">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button type="submit" :disabled="syncing" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 transition shadow-2xs cursor-pointer">
                                <span x-show="!syncing">Ya, Jalankan Sinkron</span>
                                <span x-show="syncing">Menyinkronkan...</span>
                            </button>
                            <button type="button" @click="confirming = false" :disabled="syncing" class="px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition cursor-pointer">
                                Batal
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </form>
    </div>
</div>
