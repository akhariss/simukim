<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\KelasKomponenNilai;
use App\Models\Kelompok;
use App\Models\Penilaian;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PenilaianService
{
    public function getSheetData(Kelompok $kelompok, Kelas $kelas, int $userId, bool $isSaya): array
    {
        if ($isSaya) {
            $isAssigned = DB::table('mahad_kelompok_pengampu')
                ->where('kelompok_id', $kelompok->id)
                ->where('kelas_id', $kelas->id)
                ->where('tutor_id', $userId)
                ->exists();
            if (!$isAssigned) abort(403, 'Anda tidak memiliki penugasan pada kelompok dan kajian ini.');
        }

        $kelompok->load('anggota.santri.user');
        $anggotaList = $kelompok->anggota;

        $komponenList = KelasKomponenNilai::where('kelas_id', $kelas->id)
            ->where(fn($q) => $q->where('kelompok_id', $kelompok->id)->orWhereNull('kelompok_id'))
            ->orderBy('urutan')->get();

        if ($komponenList->isEmpty()) {
            foreach (KelasKomponenNilai::defaultKomponen($kelas->id, $kelompok->id) as $def) {
                KelasKomponenNilai::create($def);
            }
            $komponenList = KelasKomponenNilai::where('kelas_id', $kelas->id)
                ->where(fn($q) => $q->where('kelompok_id', $kelompok->id)->orWhereNull('kelompok_id'))
                ->orderBy('urutan')->get();
        }

        $totalSesi = DB::table('mahad_absensi')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->distinct('tanggal')->count('tanggal');

        $hadirCounts = DB::table('mahad_absensi')
            ->where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->where('status', 'hadir')
            ->groupBy('santri_id')
            ->select('santri_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'santri_id');

        $autoAbsensiMap = collect();
        foreach ($anggotaList as $item) {
            $hadir = $hadirCounts->get($item->santri_id, 0);
            $autoAbsensiMap[$item->santri_id] = $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 1) : 100.0;
        }

        $penilaianMap = Penilaian::where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->get()->keyBy('santri_id');

        return compact('anggotaList', 'komponenList', 'totalSesi', 'autoAbsensiMap', 'penilaianMap');
    }

    public function getSantriRaporList(?int $periodeId = null): Collection
    {
        $members = DB::table('mahad_kelompok_anggota')
            ->join('mahad_kelompok', 'mahad_kelompok_anggota.kelompok_id', '=', 'mahad_kelompok.id')
            ->join('mahad_periode', 'mahad_kelompok.periode_id', '=', 'mahad_periode.id')
            ->join('mahad_santri', 'mahad_kelompok_anggota.santri_id', '=', 'mahad_santri.id')
            ->join('users', 'mahad_santri.user_id', '=', 'users.id')
            ->when($periodeId, fn($q) => $q->where('mahad_kelompok.periode_id', $periodeId))
            ->select(
                'mahad_kelompok_anggota.kelompok_id',
                'mahad_kelompok.nama as kelompok_nama',
                'mahad_kelompok.periode_id',
                'mahad_periode.nama as periode_nama',
                'mahad_santri.id as santri_id',
                'mahad_santri.nomor_induk',
                'mahad_santri.prodi',
                'users.name as santri_nama'
            )->orderBy('mahad_kelompok.nama')->orderBy('users.name')->get();

        if ($members->isEmpty()) return collect();

        $kelompokIds = $members->pluck('kelompok_id')->unique()->toArray();
        $santriIds = $members->pluck('santri_id')->unique()->toArray();

        $groupCourses = DB::table('mahad_kelompok_pengampu')
            ->join('mahad_kelas', 'mahad_kelompok_pengampu.kelas_id', '=', 'mahad_kelas.id')
            ->join('users', 'mahad_kelompok_pengampu.tutor_id', '=', 'users.id')
            ->whereIn('mahad_kelompok_pengampu.kelompok_id', $kelompokIds)
            ->select(
                'mahad_kelompok_pengampu.kelompok_id',
                'mahad_kelas.id as kelas_id',
                'mahad_kelas.nama as kelas_nama',
                'mahad_kelas.tipe as kelas_tipe',
                'users.name as tutor_nama'
            )->orderBy('mahad_kelas.nama')->get()->groupBy('kelompok_id');

        $grades = DB::table('mahad_penilaian')
            ->whereIn('santri_id', $santriIds)
            ->whereIn('kelompok_id', $kelompokIds)
            ->select('kelompok_id', 'kelas_id', 'santri_id', 'nilai_angka', 'nilai_huruf', 'catatan')
            ->get()->keyBy(fn($r) => $r->kelompok_id . '_' . $r->kelas_id . '_' . $r->santri_id);

        return $members->map(function ($m) use ($groupCourses, $grades) {
            $courses = $groupCourses->get($m->kelompok_id, collect());
            $courseGrades = [];
            $totalNilai = 0;
            $gradedCount = 0;

            foreach ($courses as $c) {
                $key = $m->kelompok_id . '_' . $c->kelas_id . '_' . $m->santri_id;
                $g = $grades->get($key);
                $hasGrade = $g && $g->nilai_angka !== null;
                if ($hasGrade) {
                    $totalNilai += (float) $g->nilai_angka;
                    $gradedCount++;
                }
                $courseGrades[] = [
                    'kelas_id'    => $c->kelas_id,
                    'kelas_nama'  => $c->kelas_nama,
                    'kelas_tipe'  => $c->kelas_tipe,
                    'tutor_nama'  => $c->tutor_nama,
                    'has_grade'   => $hasGrade,
                    'nilai_angka' => $hasGrade ? (float) $g->nilai_angka : null,
                    'nilai_huruf' => $hasGrade ? $g->nilai_huruf : '-',
                    'catatan'     => $g ? $g->catatan : null,
                ];
            }

            $rataRata = $gradedCount > 0 ? round($totalNilai / $gradedCount, 2) : null;
            return (object) [
                'santri_id'     => $m->santri_id,
                'santri_nama'   => $m->santri_nama,
                'nomor_induk'   => $m->nomor_induk,
                'prodi'         => $m->prodi ?? '—',
                'kelompok_id'   => $m->kelompok_id,
                'kelompok_nama' => $m->kelompok_nama,
                'periode_id'    => $m->periode_id,
                'periode_nama'  => $m->periode_nama,
                'total_kajian'  => $courses->count(),
                'graded_count'  => $gradedCount,
                'rata_rata'     => $rataRata,
                'predikat'      => $rataRata !== null ? $this->toHuruf($rataRata) : '-',
                'courses'       => $courseGrades,
            ];
        });
    }

    public function saveBulk(Kelompok $kelompok, Kelas $kelas, array $rows, int $userId): void
    {
        $komponenList = KelasKomponenNilai::where('kelas_id', $kelas->id)
            ->where(fn($q) => $q->where('kelompok_id', $kelompok->id)->orWhereNull('kelompok_id'))
            ->get();
        $totalBobot = $komponenList->sum('bobot') ?: 100.0;

        foreach ($rows as $santriId => $data) {
            $inputKomponen = $data['komponen'] ?? [];
            $totalNilai = 0.0;
            $hasManualGrade = false;

            foreach ($komponenList as $komp) {
                $isSet = isset($inputKomponen[$komp->id]) && trim((string) $inputKomponen[$komp->id]) !== '';
                if (!$komp->is_absensi && $isSet) $hasManualGrade = true;
                $val = $isSet ? (float) $inputKomponen[$komp->id] : 0.0;
                $totalNilai += ($val * ($komp->bobot / $totalBobot));
            }

            $finalAngka = $hasManualGrade ? round($totalNilai, 2) : null;
            $finalHuruf = $hasManualGrade ? $this->toHuruf($finalAngka) : null;

            Penilaian::updateOrCreate(
                ['kelompok_id' => $kelompok->id, 'kelas_id' => $kelas->id, 'santri_id' => $santriId],
                [
                    'nilai_komponen' => $inputKomponen,
                    'nilai_angka'    => $finalAngka,
                    'nilai_huruf'    => $finalHuruf,
                    'catatan'        => $data['catatan'] ?? null,
                    'input_by'       => $userId,
                ]
            );
        }
    }

    public function export(Periode $periode, ?int $kelompokId, ?int $kelasId, string $format): Response
    {
        $data = Penilaian::query()
            ->when($kelompokId, fn ($q) => $q->where('kelompok_id', $kelompokId))
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->whereHas('kelompok', fn ($q) => $q->where('periode_id', $periode->id))
            ->with(['kelompok', 'kelas', 'santri.user'])
            ->orderBy('kelompok_id')->get();

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kelas.nilai.export-pdf', compact('data', 'periode'));
            return $pdf->download("nilai-kajian-{$periode->kode}.pdf");
        }

        return response()->streamDownload(function () use ($data, $periode) {
            echo view('kelas.nilai.export-excel', compact('data', 'periode'))->render();
        }, "nilai-kajian-{$periode->kode}.xlsx", ['Content-Type' => 'application/vnd.ms-excel']);
    }

    private function toHuruf(float $nilai): string
    {
        return match(true) {
            $nilai >= 85 => 'A',
            $nilai >= 75 => 'B',
            $nilai >= 60 => 'C',
            $nilai >= 50 => 'D',
            default      => 'E',
        };
    }
}
