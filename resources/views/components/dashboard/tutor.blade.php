{{-- Dashboard Tutor Dosen --}}
<div class="space-y-4">
    <!-- 2 Metrik Kinerja Pengajaran Bulanan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
        <x-kpi-card label="Presensi Terlaksana" :value="($sesiBulanIni ?? 0) . ' Sesi'" :note="'Bulan ' . \Carbon\Carbon::now()->translatedFormat('F Y')" color="indigo">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Laporan Bulanan" :value="$laporanBulanIni ? 'Terkirim' : 'Belum Terkirim'" :note="'Periode ' . \Carbon\Carbon::now()->translatedFormat('F Y')" :color="$laporanBulanIni ? 'emerald' : 'amber'">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
        </x-kpi-card>
    </div>

    <!-- Jadwal Mengajar Tutor -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Jadwal Mengajar Anda</span>
            </h3>
            <span class="text-[11px] font-semibold text-slate-500">{{ count($jadwalMengajar ?? []) }} Sesi Kajian Terdaftar</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($jadwalMengajar ?? [] as $jm)
                <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                            {{ $jm->hari }}
                        </span>
                        <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ $jm->jam_mulai }} - {{ $jm->jam_selesai }} WIB
                        </span>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $jm->kelas_nama }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kelompok: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $jm->kelompok_nama }}</span></p>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">{{ $jm->nama_sesi }}</span>
                        <a href="{{ route('kelas.absensi.index', ['kelompok_id' => $jm->kelompok_id, 'kelas_id' => $jm->kelas_id]) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Presensi &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-4 text-center text-xs text-slate-400">Belum ada jadwal mengajar yang ditetapkan untuk semester ini.</div>
            @endforelse
        </div>
    </div>

    <!-- Peringatan Presensi Sesi Berjalan -->
    @if(isset($unrecordedSessions) && $unrecordedSessions->isNotEmpty())
        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/60 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div>
                        <h4 class="font-bold text-xs text-amber-900 dark:text-amber-200">Presensi Sesi Hari Ini Memerlukan Pengisian</h4>
                        <p class="text-[11px] text-amber-700 dark:text-amber-300">Terdapat {{ $unrecordedSessions->count() }} kelompok halaqah yang belum mencatatkan presensi pada tanggal {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($unrecordedSessions as $s)
                        <a
                            href="{{ route('kelas.absensi.index', ['kelompok_id' => $s->kelompok_id, 'kelas_id' => $s->kelas_id, 'tanggal' => date('Y-m-d')]) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 transition shadow-2xs"
                        >
                            <span>Input Presensi: {{ $s->kelompok_nama }} &rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Menu Cepat & Status Laporan Bulanan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Status & Pengiriman Laporan Bulanan -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="size-2 rounded-full {{ $laporanBulanIni ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    Laporan Kinerja Pengajaran Bulanan
                </h3>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">Periode {{ \Carbon\Carbon::now()->translatedFormat('M Y') }}</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Rekapitulasi pelaksanaan kajian dan pembinaan halaqah mahasantri disertai berkas dokumentasi visual kegiatan.
            </p>
            <div class="pt-2 flex items-center gap-2">
                <a href="{{ route('kelas.laporan.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
                    {{ $laporanBulanIni ? 'Lihat Laporan Terkirim' : 'Kirim Laporan Pengajaran &rarr;' }}
                </a>
            </div>
        </div>

        <!-- Akses Cepat Modul Pengajaran -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="size-2 rounded-full bg-blue-600"></span>
                    Layanan Pengajaran & Pembinaan
                </h3>
                <span class="text-[10px] font-semibold text-slate-400">Semester Aktif</span>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-1">
                <a href="{{ route('tutor.kajian.index') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-blue-500 transition group">
                    <p class="font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between">
                        <span>Kajian Diampu</span>
                        <span class="text-blue-600 dark:text-blue-400 text-xs transition group-hover:translate-x-0.5">&rarr;</span>
                    </p>
                    <p class="text-[10.5px] text-slate-400 mt-0.5">Daftar mata kajian & kelompok</p>
                </a>
                <a href="{{ route('kelas.absensi.index') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-blue-500 transition group">
                    <p class="font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between">
                        <span>Presensi Harian</span>
                        <span class="text-blue-600 dark:text-blue-400 text-xs transition group-hover:translate-x-0.5">&rarr;</span>
                    </p>
                    <p class="text-[10.5px] text-slate-400 mt-0.5">Pencatatan kehadiran sesi</p>
                </a>
                <a href="{{ route('kelas.nilai.index') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-blue-500 transition group">
                    <p class="font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between">
                        <span>Penilaian Santri</span>
                        <span class="text-blue-600 dark:text-blue-400 text-xs transition group-hover:translate-x-0.5">&rarr;</span>
                    </p>
                    <p class="text-[10.5px] text-slate-400 mt-0.5">Pengelolaan bobot & nilai akhir</p>
                </a>
                <a href="{{ route('tutor.kedisiplinan.index') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-blue-500 transition group">
                    <p class="font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between">
                        <span>Kedisiplinan & Perizinan</span>
                        <span class="text-blue-600 dark:text-blue-400 text-xs transition group-hover:translate-x-0.5">&rarr;</span>
                    </p>
                    <p class="text-[10.5px] text-slate-400 mt-0.5">Mahasantri kelompok diampu</p>
                </a>
            </div>
        </div>
    </div>
</div>
