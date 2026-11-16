{{-- Dashboard Administrator: Pusat Komando & Aksi Cepat --}}
@props([
    'totalMahasantri' => 0, 'totalMudabbir' => 0, 'totalTutor' => 0, 'totalAdmin' => 0,
    'totalKamar' => 0, 'totalKapasitas' => 0, 'kamarTerisi' => 0, 'kamarKosong' => 0, 'kamarPerbaikan' => 0,
    'santriAktif' => 0, 'santriMenempati' => 0, 'okupansi' => 0, 'santriUnassignedRoom' => 0, 'santriUnassignedKelompok' => 0,
    'perizinanAktif' => 0, 'pelanggaranPending' => 0, 'laporanTutorPending' => 0,
    'totalKelompok' => 0, 'totalKelas' => 0,
    'unassignedSantriSample' => collect(), 'recentPelanggaranSample' => collect(), 'recentPerizinanSample' => collect(),
    'activePeriode' => null,
])

<div class="space-y-4 sm:space-y-5">
    <!-- 1. Antrean Aksi & Status Kritis (Action Required Alert Banner) -->
    @php
        $totalPerluTindakan = $santriUnassignedRoom + $santriUnassignedKelompok + $pelanggaranPending + $laporanTutorPending;
    @endphp
    @if($totalPerluTindakan > 0)
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 dark:bg-amber-950/20 dark:border-amber-800/40">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="size-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>Perhatian Operasional Administrator</span>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-500 text-white">{{ $totalPerluTindakan }} Tindakan</span>
                        </h2>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-600 dark:text-slate-400">
                            @if($santriUnassignedRoom > 0)
                                <a href="{{ route('fasilitas.penempatan.index', ['tab' => 'unassigned']) }}" class="hover:text-amber-700 dark:hover:text-amber-300 underline font-medium">
                                    • {{ $santriUnassignedRoom }} Santri Belum Ber-Kamar
                                </a>
                            @endif
                            @if($santriUnassignedKelompok > 0)
                                <a href="{{ route('kelompok.index') }}" class="hover:text-amber-700 dark:hover:text-amber-300 underline font-medium">
                                    • {{ $santriUnassignedKelompok }} Santri Belum Ada Halaqah
                                </a>
                            @endif
                            @if($pelanggaranPending > 0)
                                <a href="{{ route('kedisiplinan.pelanggaran.index', ['status' => 'terdaftar']) }}" class="hover:text-amber-700 dark:hover:text-amber-300 underline font-medium">
                                    • {{ $pelanggaranPending }} Kasus Disiplin Belum Selesai
                                </a>
                            @endif
                            @if($laporanTutorPending > 0)
                                <a href="{{ route('kelas.laporan.index') }}" class="hover:text-amber-700 dark:hover:text-amber-300 underline font-medium">
                                    • {{ $laporanTutorPending }} Laporan Tutor Menunggu Verifikasi
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('fasilitas.penempatan.index', ['tab' => 'unassigned']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-2xs transition">
                        Buka Penempatan &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. Ringkasan Indikator Kunci (4 KPI Cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
        <x-kpi-card label="Mahasantri Mukim" :value="$santriAktif" :note="$totalMahasantri . ' Akun Terdaftar'" color="emerald">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Okupansi Asrama" :value="$okupansi . '%'" :note="$santriMenempati . ' Terisi / ' . max(0, $totalKapasitas - $santriMenempati) . ' Kosong'" color="blue">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Halaqah & Kelas" :value="$totalKelompok" :note="$totalKelas . ' Kelas Kajian Aktif'" color="indigo">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></x-slot:icon>
        </x-kpi-card>
        <x-kpi-card label="Kedisiplinan" :value="$pelanggaranPending" :note="$perizinanAktif . ' Mahasantri Izin Aktif'" color="rose">
            <x-slot:icon><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></x-slot:icon>
        </x-kpi-card>
    </div>

    <!-- 3. Pusat Komando Aksi Administrator (4 Modul Operasional Utama) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Modul 1: Fasilitas & Hunian -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                        <span>🚪</span> Fasilitas & Asrama
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $totalKamar }} Kamar</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Kelola denah gedung, penempatan kamar santri baru, perpindahan kamar, dan monitoring bed.
                </p>
            </div>
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('fasilitas.penempatan.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-800 transition">
                    <span>Penempatan Mahasantri</span>
                    <span class="text-blue-600 dark:text-blue-400">&rarr;</span>
                </a>
                <a href="{{ route('fasilitas.monitoring.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Monitoring Asrama</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
                <a href="{{ route('fasilitas.kelola.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Kelola Gedung & Kamar</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Modul 2: Akademik & Halaqah -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                        <span>📖</span> Akademik & Halaqah
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $totalTutor }} Tutor</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Pembagian rombel kelompok santri, penugasan tutor, rekapitulasi nilai, dan verifikasi laporan.
                </p>
            </div>
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('kelompok.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-slate-800 transition">
                    <span>Kelola Kelompok Halaqah</span>
                    <span class="text-indigo-600 dark:text-indigo-400">&rarr;</span>
                </a>
                <a href="{{ route('kelas.nilai.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Rekap Nilai Santri</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
                <a href="{{ route('kelas.laporan.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Laporan Bulanan Tutor</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Modul 3: Kedisiplinan & Perizinan -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <span>⚖️</span> Kedisiplinan & Izin
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $perizinanAktif }} Izin Aktif</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Verifikasi izin keluar/pulang kampung, pencatatan poin pelanggaran, dan eksekusi sanksi santri.
                </p>
            </div>
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('kedisiplinan.perizinan.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-rose-50 dark:hover:bg-slate-800 transition">
                    <span>Verifikasi Perizinan</span>
                    <span class="text-rose-600 dark:text-rose-400">&rarr;</span>
                </a>
                <a href="{{ route('kedisiplinan.pelanggaran.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Catat Pelanggaran & Sanksi</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
                <a href="{{ route('kegiatan.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Agenda & Presensi Kegiatan</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Modul 4: Pelaporan & Tata Kelola -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <span>📊</span> Laporan & Sistem
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">Pimpinan</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Pusat unduh laporan eksekutif resmi pimpinan, sinkronisasi data kampus SAHAJA, dan manajemen akun.
                </p>
            </div>
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('pelaporan.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 transition">
                    <span>Pusat Unduh Laporan</span>
                    <span class="text-emerald-600 dark:text-emerald-400">&rarr;</span>
                </a>
                <a href="{{ route('users.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Manajemen Pengguna</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
                <a href="{{ route('admin.cache.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <span>Sinkronisasi & Cache</span>
                    <span class="text-slate-400">&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4. Banner Akses Cepat Ekspor Laporan Eksekutif -->
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e1b4b 50%, #0f172a 100%); color: #ffffff;">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-500/30 text-blue-200 border border-blue-400/30 uppercase tracking-wider">
                    Fitur Eksekutif Pimpinan
                </span>
                <span class="text-xs text-blue-200/80">• Periode Aktif: {{ $activePeriode?->nama ?? 'Tahun Ini' }}</span>
            </div>
            <h2 class="text-base sm:text-lg font-bold">Laporan Terpadu Tata Kelola Asrama Ma'had</h2>
            <p class="text-xs text-blue-200/90 max-w-2xl leading-relaxed">
                Unduh rekapitulasi terintegrasi dari seluruh pilar asrama (hunian, akademik halaqah, presensi, kedisiplinan, dan perizinan) dalam satu berkas laporan resmi PDF atau Excel untuk pelaporan kepada Rektorat dan Pimpinan.
            </p>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            <a href="{{ route('pelaporan.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-blue-900 hover:bg-blue-50 shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Buka Pusat Unduh Laporan &rarr;</span>
            </a>
        </div>
    </div>
</div>
