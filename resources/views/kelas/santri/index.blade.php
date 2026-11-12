<x-app-layout title="Kajian & Kelompok Saya">
    @php
        $graded = $rekapNilaiList->where('is_graded', true);
        $avg = $graded->isNotEmpty() ? $graded->avg('nilai_angka') : null;
        $portalTabs = [
            [
                'id' => 'kelompok', 'label' => 'Kelompok & Rekan', 'short_label' => 'Kelompok',
                'count' => $anggotaList->count(),
                'icon' => '<svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
            ],
            [
                'id' => 'presensi', 'label' => 'Presensi Saya', 'short_label' => 'Presensi',
                'count' => $absensiList->count(),
                'icon' => '<svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
            ],
            [
                'id' => 'nilai', 'label' => 'Nilai Kajian', 'short_label' => 'Nilai',
                'count' => $rekapNilaiList->count(),
                'icon' => '<svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
            ],
        ];
    @endphp

    {{-- Header Halaman --}}
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Kajian & Kelompok Saya']
        ]"
        title="Kajian & Kelompok Saya"
        description="Informasi kelompok kajian, rekan anggota, jadwal, riwayat presensi, dan hasil penilaian."
    />

    <div
        x-data="{
            activeTab: 'kelompok', searchMember: '', filterKajian: 'all',
            allAnggota: {{ \Illuminate\Support\Js::from($anggotaList) }},
            absensi: {{ \Illuminate\Support\Js::from($absensiList) }},
            get filteredAnggota() {
                if (!this.searchMember.trim()) return this.allAnggota;
                const q = this.searchMember.toLowerCase().trim();
                return this.allAnggota.filter(m => m.name.toLowerCase().includes(q) || m.nim.toLowerCase().includes(q) || m.prodi.toLowerCase().includes(q) || m.kamar.toLowerCase().includes(q));
            },
            get filteredAbsensi() {
                return this.filterKajian === 'all' ? this.absensi : this.absensi.filter(a => a.kelas_id == this.filterKajian);
            }
        }"
        class="space-y-3 sm:space-y-4"
    >
        {{-- ── 1. RINGKASAN AKADEMIK (KPI CARDS ATOMIC) ───────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
            <x-kpi-card label="Kelompok Kajian" :value="$kelompok?->nama ?? 'Belum Terdaftar'" :note="$anggotaList->count() . ' Anggota Terdaftar'" color="emerald">
                <x-slot:icon><svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot:icon>
            </x-kpi-card>
            <x-kpi-card label="Kehadiran" :value="$rekapAbsensi['persen'] . '%'" :note="'Hadir ' . $rekapAbsensi['hadir'] . ' dari ' . $rekapAbsensi['total'] . ' sesi'" color="blue">
                <x-slot:icon><svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
            </x-kpi-card>
            <x-kpi-card label="Izin & Sakit" :value="($rekapAbsensi['izin'] + $rekapAbsensi['sakit']) . ' Sesi'" :note="'S: ' . $rekapAbsensi['sakit'] . ' · I: ' . $rekapAbsensi['izin'] . ' · A: ' . $rekapAbsensi['alfa']" color="amber">
                <x-slot:icon><svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon>
            </x-kpi-card>
            <x-kpi-card label="Capaian Nilai" :value="$avg !== null ? number_format($avg, 1) : 'Belum Ada'" :note="$graded->count() . ' dari ' . $rekapNilaiList->count() . ' Dinilai'" color="purple">
                <x-slot:icon><svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></x-slot:icon>
            </x-kpi-card>
        </div>

        {{-- ── 2. TAB NAVIGASI KONTEN (ATOMIC TAB SWITCHER) ────────────── --}}
        <x-tab-switcher :tabs="$portalTabs" model="activeTab" color="emerald" />

        {{-- ── 3. KONTEN TAB PARTIALS ───────────────────────────────────── --}}
        @include('kelas.santri.partials.kelompok-tab')
        @include('kelas.santri.partials.presensi-tab')
        @include('kelas.santri.partials.nilai-tab')
    </div>
</x-app-layout>