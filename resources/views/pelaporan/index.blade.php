<x-app-layout>
    <x-slot name="title">Pusat Unduh Laporan Eksekutif</x-slot>

    <!-- Header Section Resmi -->
    <x-page-header
        :breadcrumbs="[['label' => 'Pusat Unduh Laporan']]"
        title="Pusat Unduh Laporan Eksekutif"
        description="Penerbitan berkas laporan resmi tata kelola asrama dan akademik Ma'had Al-Jami'ah untuk pimpinan, rektorat, dan pemangku kepentingan."
    />

    <div x-data="{
        periodeId: '{{ $selectedPeriode?->id ?? ($activePeriode?->id ?? '') }}',
        rentang: '{{ $rentang }}',
        bulan: '{{ $bulan }}',
        kategori: '{{ $kategori }}',
    }" class="space-y-4 sm:space-y-5">

        <!-- 1. Panel Kontrol Filter Global (Periode & Rentang Waktu) -->
        <x-pelaporan.filter-bar
            :activePeriode="$activePeriode"
            :periodeList="$periodeList"
        />

        <!-- 2. Master Card: Unduh Semua Laporan (Disatukan / Laporan Eksekutif Utama) -->
        <x-pelaporan.master-card />

        <!-- 3. Grid Unduh Satu per Satu (Spesifik per Fitur) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-800">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                        <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Unduh Berkas Spesifik per Modul</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Pilih modul satuan di bawah ini untuk mengunduh rekapitulasi data individual.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <!-- 1. Okupansi Hunian Asrama -->
                <div x-show="kategori === 'semua' || kategori === 'fasilitas'">
                    <x-pelaporan.module-card
                        tipe="okupansi"
                        filterName="gedung_id"
                        badgeTitle="Fasilitas & Asrama"
                        badgeSub="Kamar"
                        title="Laporan Okupansi & Hunian"
                        description="Kapasitas, jumlah terisi, sisa bed, status kamar, serta daftar mahasantri mukim per unit kamar."
                        color="blue">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Gedung</label>
                            <select x-model="filterVal" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">Semua Gedung Asrama</option>
                                @foreach($gedungList as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </x-pelaporan.module-card>
                </div>

                <!-- 2. Transkrip Nilai Akademik -->
                <div x-show="kategori === 'semua' || kategori === 'akademik'">
                    <x-pelaporan.module-card
                        tipe="nilai"
                        filterName="kelas_id"
                        badgeTitle="Akademik & Halaqah"
                        badgeSub="Nilai"
                        title="Rekapitulasi Nilai Santri"
                        description="Rekap nilai harian, nilai ujian, nilai akhir, huruf mutu, dan kelulusan mahasantri per kelas halaqah."
                        color="indigo">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Kelas</label>
                            <select x-model="filterVal" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">Semua Kelas Kajian</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </x-pelaporan.module-card>
                </div>

                <!-- 3. Rekapitulasi Presensi Belajar -->
                <div x-show="kategori === 'semua' || kategori === 'akademik'">
                    <x-pelaporan.module-card
                        tipe="kehadiran"
                        filterName="kelas_id"
                        badgeTitle="Presensi Pembelajaran"
                        badgeSub="Kehadiran"
                        title="Rekapitulasi Presensi Santri"
                        description="Total kehadiran, izin, sakit, alfa, terlambat, serta persentase kehadiran belajar santri."
                        color="indigo">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Kelas</label>
                            <select x-model="filterVal" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">Semua Kelas Kajian</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </x-pelaporan.module-card>
                </div>

                <!-- 4. Pelanggaran & Kedisiplinan -->
                <div x-show="kategori === 'semua' || kategori === 'kedisiplinan'">
                    <x-pelaporan.module-card
                        tipe="pelanggaran"
                        filterName="status"
                        badgeTitle="Kedisiplinan & Tata Tertib"
                        badgeSub="Kasus"
                        title="Laporan Pelanggaran Disiplin"
                        description="Rekap sanksi pelanggaran ringan/sedang/berat, tindakan disiplin, dan status penyelesaian santri."
                        color="rose">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Status Sanksi</label>
                            <select x-model="filterVal" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">Semua Status Sanksi</option>
                                <option value="terdaftar">Belum Dilaksanakan</option>
                                <option value="selesai">Selesai Dilaksanakan</option>
                            </select>
                        </div>
                    </x-pelaporan.module-card>
                </div>

                <!-- 5. Rekapitulasi Perizinan Santri -->
                <div x-show="kategori === 'semua' || kategori === 'kedisiplinan'">
                    <x-pelaporan.module-card
                        tipe="perizinan"
                        filterName="status"
                        badgeTitle="Perizinan Mahasantri"
                        badgeSub="Izin"
                        title="Rekapitulasi Izin Santri"
                        description="Arsip permohonan izin keluar/pulang kampung, durasi hari izin, alasan, dan verifikasi kepulangan."
                        color="amber">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Status Izin</label>
                            <select x-model="filterVal" class="w-full px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">Semua Status Izin</option>
                                <option value="berlaku">Sedang Berlaku (Di Luar)</option>
                                <option value="selesai">Selesai (Telah Kembali)</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                    </x-pelaporan.module-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
