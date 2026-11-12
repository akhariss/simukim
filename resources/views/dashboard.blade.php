<x-app-layout title="Dashboard">
    <div class="space-y-4 sm:space-y-5">
        <!-- Welcome Banner Akademik Ma'had Al-Jami'ah -->
        <div class="p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs relative overflow-hidden">
            @php
                $roleBadge = match($currentRole) {
                    'admin' => ['label' => 'Administrator', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200/80 dark:border-blue-800'],
                    'tutor' => ['label' => 'Tutor Dosen', 'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200/80 dark:border-indigo-800'],
                    'mudabbir' => ['label' => 'Mudabbir Pembina', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200/80 dark:border-amber-800'],
                    'mahasantri' => ['label' => 'Mahasantri', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800'],
                    default => ['label' => ucfirst($currentRole), 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700'],
                };
            @endphp
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="size-13 sm:size-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-bold text-lg sm:text-xl flex items-center justify-center shadow-2xs shrink-0">
                        {{ $currentUser->initials }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                Ahlan wa Sahlan, {{ $currentUser->name }}
                            </h1>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $roleBadge['class'] }}">
                                <span class="size-1.5 rounded-full bg-current"></span>
                                <span>{{ $roleBadge['label'] }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300">
                        <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-Specific Action Hub Content -->
        @if($currentRole === 'mudabbir')
            <x-dashboard.mudabbir
                :totalKamar="$totalKamar"
                :kamarTerisi="$kamarTerisi"
                :santriAktif="$santriAktif"
                :izinAktif="$izinAktif"
                :pelanggaranPending="$pelanggaranPending"
                :kelompokReadOnlyList="$kelompokReadOnlyList"
            />
        @elseif($currentRole === 'tutor')
            <x-dashboard.tutor
                :myKajianList="$myKajianList"
                :totalSantriBinaan="$totalSantriBinaan"
                :sesiBulanIni="$sesiBulanIni"
                :laporanBulanIni="$laporanBulanIni"
                :unrecordedSessions="$unrecordedSessions"
                :jadwalMengajar="$jadwalMengajar"
            />
        @elseif($currentRole === 'mahasantri')
            <x-dashboard.mahasantri
                :santri="$santri"
                :kamar="$kamar"
                :roommates="$roommates"
                :myKelompok="$myKelompok"
                :todayJadwal="$todayJadwal ?? collect()"
                :rekapNilaiCount="$rekapNilaiCount ?? 0"
                :gradedCount="$gradedCount ?? 0"
            />
        @else
            <x-dashboard.admin
                :totalMahasantri="$totalMahasantri ?? 0"
                :totalMudabbir="$totalMudabbir ?? 0"
                :totalTutor="$totalTutor ?? 0"
                :totalAdmin="$totalAdmin ?? 0"
                :totalKamar="$totalKamar ?? 0"
                :totalKapasitas="$totalKapasitas ?? 0"
                :kamarTerisi="$kamarTerisi ?? 0"
                :kamarKosong="$kamarKosong ?? 0"
                :kamarPerbaikan="$kamarPerbaikan ?? 0"
                :santriAktif="$santriAktif ?? 0"
                :santriMenempati="$santriMenempati ?? 0"
                :okupansi="$okupansi ?? 0"
                :santriUnassignedRoom="$santriUnassignedRoom ?? 0"
                :santriUnassignedKelompok="$santriUnassignedKelompok ?? 0"
                :perizinanAktif="$perizinanAktif ?? 0"
                :pelanggaranPending="$pelanggaranPending ?? 0"
                :laporanTutorPending="$laporanTutorPending ?? 0"
                :totalKelompok="$totalKelompok ?? 0"
                :totalKelas="$totalKelas ?? 0"
                :unassignedSantriSample="$unassignedSantriSample ?? collect()"
                :recentPelanggaranSample="$recentPelanggaranSample ?? collect()"
                :recentPerizinanSample="$recentPerizinanSample ?? collect()"
                :activePeriode="$activePeriode"
            />
        @endif
    </div>
</x-app-layout>
