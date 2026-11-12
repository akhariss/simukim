{{-- Dashboard Mudabbir Pembina --}}
<div class="space-y-4" x-data="{
    tab: 'ringkasan',
    searchKelompok: ''
}">
    <!-- 4 Metrik Operasional Asrama -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
        <x-kpi-card label="Kapasitas Kamar" :value="($kamarTerisi ?? 0) . ' / ' . ($totalKamar ?? 0)" note="Kamar Berpenghuni" color="blue">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Mahasantri Mukim" :value="$santriAktif ?? 0" note="Santri Aktif Terdaftar" color="emerald">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Izin Keluar Aktif" :value="$izinAktif ?? 0" note="Sedang Izin Keluar" color="amber">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Menunggu Pembinaan" :value="$pelanggaranPending ?? 0" note="Tindak Lanjut Disiplin" color="rose">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon>
        </x-kpi-card>
    </div>

    <!-- Tab Navigasi Dashboard -->
    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
        <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700">
            <button type="button" @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="px-3.5 py-1 text-xs rounded-lg transition cursor-pointer">
                Ringkasan Operasional Asrama
            </button>
            <button type="button" @click="tab = 'kelompok'" :class="tab === 'kelompok' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="px-3.5 py-1 text-xs rounded-lg transition cursor-pointer">
                Daftar Kelompok Halaqah
            </button>
        </div>
    </div>

    <!-- TAB 1: Ringkasan Operasional & Modul Layanan -->
    <div x-show="tab === 'ringkasan'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="{{ route('fasilitas.monitoring.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:border-blue-500 transition group">
                <div class="size-8 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 flex items-center justify-center font-bold mb-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-blue-600">Monitoring Asrama</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Pemantauan okupansi kamar dan penempatan mahasantri</p>
            </a>
            <a href="{{ route('kedisiplinan.perizinan.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:border-amber-500 transition group">
                <div class="size-8 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center font-bold mb-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-amber-600">Perizinan Mahasantri</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Pengelolaan izin keluar dan dispensasi asrama</p>
            </a>
            <a href="{{ route('kedisiplinan.pelanggaran.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:border-rose-500 transition group">
                <div class="size-8 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 flex items-center justify-center font-bold mb-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-rose-600">Catatan Kedisiplinan</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Pencatatan tata tertib dan pembinaan mahasantri</p>
            </a>
            <a href="{{ route('kegiatan.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:border-indigo-500 transition group">
                <div class="size-8 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center font-bold mb-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-indigo-600">Agenda Asrama</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Jadwal kegiatan taklim dan agenda asrama</p>
            </a>
        </div>
    </div>

    <!-- TAB 2: Daftar Kelompok Halaqah -->
    <div x-show="tab === 'kelompok'" x-cloak class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Daftar kelompok halaqah dan penugasan tutor pengampu mahasantri pada semester aktif.
                </p>
            </div>
            <div class="w-full sm:w-72">
                <x-search-input x-model="searchKelompok" placeholder="Cari nama kelompok, tutor, atau mahasantri..." />
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($kelompokReadOnlyList ?? [] as $k)
                @php
                    $tutorText = $k->pengampu->map(fn($p) => ($p->kelas->nama ?? 'Mata Kajian') . ' ' . ($p->tutor->name ?? ''))->join(' ');
                    $santriText = $k->anggota->map(fn($a) => $a->santri->user->name ?? '')->join(' ');
                    $searchBlob = strtolower($k->nama . ' ' . $tutorText . ' ' . $santriText);
                @endphp
                <div
                    x-show="!searchKelompok.trim() || '{{ addslashes($searchBlob) }}'.includes(searchKelompok.toLowerCase().trim())"
                    class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between hover:border-blue-500/50 transition"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('kelompok.show', $k->id) }}" class="font-bold text-xs text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition">
                                {{ $k->nama }}
                            </a>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                Kelompok Halaqah
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500">{{ $k->anggota_count }} Mahasantri Terdaftar</p>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] space-y-1">
                            <span class="font-bold text-slate-400 uppercase text-[9.5px]">Tutor Pengampu:</span>
                            @forelse($k->pengampu as $p)
                                <div class="flex justify-between items-center text-slate-700 dark:text-slate-300">
                                    <span>{{ $p->kelas->nama ?? 'Mata Kajian' }}</span>
                                    <span class="font-semibold">{{ $p->tutor->name ?? '-' }}</span>
                                </div>
                            @empty
                                <p class="text-slate-400 italic">Belum ada penugasan tutor pengampu</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400 font-medium">Informasi &amp; Anggota</span>
                        <a href="{{ route('kelompok.show', $k->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">
                            <span>Lihat Detail</span>
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-xs text-slate-400">
                    Belum ada kelompok halaqah yang terdaftar pada semester ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
