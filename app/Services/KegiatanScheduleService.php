<?php

namespace App\Services;

use App\Enums\KegiatanJenis;
use App\Enums\KelasType;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokPengampu;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KegiatanScheduleService
{
    public const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /**
     * Bangun jadwal kelas akademik sesuai konteks pengguna (Mahasantri, Tutor, atau Admin).
     */
    public function buildKelasSchedule(?User $user, ?Periode $activePeriode, string $search = ''): Collection
    {
        $kelasListQuery = Kelas::with('jadwal')
            ->where('is_active', true)
            ->when($activePeriode, fn($q) => $q->where('periode_id', $activePeriode->id))
            ->when($search !== '', fn($q) => $q->where(fn($sub) => $sub->where('nama', 'like', "%{$search}%")->orWhere('deskripsi', 'like', "%{$search}%")));

        $allActiveKelas = $kelasListQuery->get();
        $isMahasantri = $user && ($user->hasRole('mahasantri') || ($user->santri && !$user->hasAnyRole(['admin', 'tutor'])));
        $isTutor = $user && $user->hasRole('tutor') && !$user->hasRole('admin');

        // 1. Konteks Mahasantri: Tampilkan jadwal khusus kelompoknya sendiri
        if ($isMahasantri && $user->santri && $activePeriode) {
            $myKelompok = Kelompok::where('periode_id', $activePeriode->id)
                ->whereHas('anggota', fn($q) => $q->where('santri_id', $user->santri->id))
                ->with(['pengampu.tutor', 'pengampu.kelas'])
                ->first();

            if ($myKelompok) {
                $res = collect();
                foreach ($allActiveKelas as $k) {
                    $p = $myKelompok->pengampu->firstWhere('kelas_id', $k->id);
                    $tutorName = $p?->tutor?->name ?? ($k->tipe?->value === 'masal' ? 'Tutor Pengampu' : 'Belum Ditunjuk');
                    $this->appendSesiItems($res, $k, " ({$myKelompok->nama})", $tutorName, $myKelompok->nama, route('kelompok.show', $myKelompok));
                }
                return $res;
            }
        }

        // 2. Konteks Tutor: Tampilkan hanya kelas & kelompok yang diampunya
        if ($isTutor && $activePeriode) {
            $myPengampu = KelompokPengampu::where('tutor_id', $user->id)
                ->whereHas('kelompok', fn($q) => $q->where('periode_id', $activePeriode->id))
                ->with(['kelompok', 'kelas.jadwal'])
                ->get();

            if ($myPengampu->isNotEmpty()) {
                $res = collect();
                foreach ($myPengampu as $p) {
                    $k = $p->kelas;
                    if ($search !== '' && !str_contains(strtolower($k->nama), strtolower($search))) continue;
                    $this->appendSesiItems($res, $k, " ({$p->kelompok->nama})", "{$user->name} (Anda)", $p->kelompok->nama, route('kelompok.kajian.show', [$p->kelompok, $k]));
                }
                return $res;
            }
        }

        // 3. Konteks Admin & Default: Tampilkan data aktual penugasan kelompok & tutor
        $pengampuMap = $activePeriode ? KelompokPengampu::whereHas('kelompok', fn($q) => $q->where('periode_id', $activePeriode->id))
            ->with(['tutor', 'kelompok'])->get()->groupBy('kelas_id') : collect();

        $res = collect();
        foreach ($allActiveKelas as $k) {
            $assigned = $pengampuMap->get($k->id) ?? collect();
            $tutorName = $assigned->isEmpty() ? 'Tutor Belum Ditunjuk'
                : ($assigned->count() === 1 ? ($assigned->first()->tutor?->name ?? 'Tutor') . " ({$assigned->first()->kelompok?->nama})"
                : $assigned->count() . ' Kelompok (' . $assigned->pluck('tutor.name')->filter()->unique()->take(2)->implode(', ') . ')');
            $this->appendSesiItems($res, $k, '', $tutorName, null, route('kelas.master.index'));
        }
        return $res;
    }

    private function appendSesiItems(Collection &$res, Kelas $k, string $suffix, string $tutorName, ?string $lokasiOverride, string $url): void
    {
        $tipeEnum = $k->tipe instanceof KelasType ? $k->tipe : KelasType::tryFrom((string) $k->tipe);
        $tipeLabel = $tipeEnum ? $tipeEnum->label() : 'Kajian Akademik';
        $tipeShort = $tipeEnum ? $tipeEnum->shortLabel() : 'Akademik';
        $tipeBadge = $tipeEnum ? $tipeEnum->badgeClass() : 'bg-emerald-50 text-emerald-700';

        foreach ($k->jadwal as $jadwal) {
            $res->push((object) [
                'id' => 'kelas-' . $k->id . '-j-' . $jadwal->id . ($suffix ? '-' . md5($suffix) : ''),
                'kelas_id' => $k->id,
                'nama' => $k->nama . ' - ' . $jadwal->nama_sesi . $suffix,
                'tipe' => $tipeEnum?->value ?? (string) $k->tipe,
                'tipe_label' => $tipeLabel,
                'tipe_short' => $tipeShort,
                'tipe_badge' => $tipeBadge,
                'jenis_code' => 'kelas',
                'jenis_label' => $tipeLabel,
                'is_kelas' => true,
                'is_active' => true,
                'hari' => $jadwal->hari,
                'jam_mulai' => $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : null,
                'jam_selesai' => $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : null,
                'lokasi' => $lokasiOverride ?? ($jadwal->ruangan ?? 'Masjid / Ruang Asrama'),
                'tutor_name' => $tutorName,
                'kapasitas' => null,
                'deskripsi' => $k->deskripsi,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
                'url' => $url,
            ]);
        }
    }

    /**
     * Build weekly schedule grouped by day and specialized items.
     */
    public function buildWeeklySchedule($activeKelasList, $kegiatanList): array
    {
        $dayList = self::DAYS;
        $scheduleByDay = [];

        $kegiatanCollection = $kegiatanList instanceof LengthAwarePaginator
            ? collect($kegiatanList->items())
            : collect($kegiatanList);

        foreach ($dayList as $dayName) {
            $kelasOnDay = $activeKelasList->filter(fn($k) => $this->matchesDay($k->hari, $dayName));
            $kegiatanOnDay = $kegiatanCollection->filter(
                fn($kg) => $kg->jenis === KegiatanJenis::Rutin && $this->matchesDay($kg->hari, $dayName)
            );

            // Smart Grouping kelas paralel grup/mudabbir serentak
            $nonGrupKelas = collect();
            $grupBuckets = [];
            foreach ($kelasOnDay as $k) {
                $tipeStr = $k->tipe instanceof KelasType ? $k->tipe->value : (string) $k->tipe;
                if (in_array($tipeStr, ['grup', 'mudabir'])) {
                    $timeKey = ($k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '00:00') . '_' . $tipeStr;
                    $grupBuckets[$timeKey][] = $k;
                } else {
                    $nonGrupKelas->push((object) [
                        'is_grouped' => false,
                        'is_kelas' => true,
                        'item' => $k,
                        'jam_mulai' => $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : null,
                        'jam_selesai' => $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : null,
                    ]);
                }
            }

            $groupedKelasItems = $nonGrupKelas;
            foreach ($grupBuckets as $timeKey => $items) {
                if (count($items) > 1) {
                    $first = $items[0];
                    $tipeStr = $first->tipe instanceof KelasType ? $first->tipe->value : (string) $first->tipe;
                    $labelTipe = $tipeStr === 'mudabir' ? 'Dauroh/Kelas Mudabbir' : 'Halaqah / Kelas Grup';
                    $groupedKelasItems->push((object) [
                        'is_grouped' => true,
                        'is_kelas' => true,
                        'nama' => "{$labelTipe} (" . count($items) . " Kelompok Serentak)",
                        'tipe' => $tipeStr,
                        'jam_mulai' => $first->jam_mulai ? substr($first->jam_mulai, 0, 5) : null,
                        'jam_selesai' => $first->jam_selesai ? substr($first->jam_selesai, 0, 5) : null,
                        'sub_items' => $items,
                        'item' => $first,
                    ]);
                } else {
                    $groupedKelasItems->push((object) [
                        'is_grouped' => false,
                        'is_kelas' => true,
                        'item' => $items[0],
                        'jam_mulai' => $items[0]->jam_mulai ? substr($items[0]->jam_mulai, 0, 5) : null,
                        'jam_selesai' => $items[0]->jam_selesai ? substr($items[0]->jam_selesai, 0, 5) : null,
                    ]);
                }
            }

            $kegiatanItems = $kegiatanOnDay->map(fn($kg) => (object) [
                'is_grouped' => false,
                'is_kelas' => false,
                'item' => $kg,
                'jam_mulai' => $kg->jam_mulai ? substr($kg->jam_mulai, 0, 5) : null,
                'jam_selesai' => $kg->jam_selesai ? substr($kg->jam_selesai, 0, 5) : null,
            ]);

            $scheduleByDay[$dayName] = $groupedKelasItems->concat($kegiatanItems)
                ->sortBy(fn($ev) => $ev->jam_mulai ?? '99:99')
                ->values();
        }

        $khususList = $kegiatanCollection->filter(fn($kg) => $kg->jenis === KegiatanJenis::Khusus)->values();

        return [
            'dayList' => $dayList,
            'scheduleByDay' => $scheduleByDay,
            'khususList' => $khususList,
        ];
    }

    private function matchesDay(?string $eventHari, string $targetDay): bool
    {
        if (!$eventHari) return false;
        $target = strtolower(trim($targetDay));
        $str = strtolower(trim($eventHari));
        if ($target === 'minggu') {
            if (str_contains($str, 'ahad') || str_contains($str, 'minggu')) return true;
        }
        return str_contains($str, $target);
    }
}
