<?php

namespace App\Services;

use App\Models\Absensi;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    /**
     * Batch simpan / update presensi santri kelompok dalam transaksi atomik.
     */
    public function savePresensi(
        int $kelompokId,
        int $kelasId,
        ?int $jadwalId,
        string $tanggal,
        array $items,
        int $userId,
        ?string $catatanSesi = null
    ): int {
        return DB::transaction(function () use ($kelompokId, $kelasId, $jadwalId, $tanggal, $items, $userId, $catatanSesi) {
            $savedCount = 0;
            foreach ($items as $item) {
                Absensi::updateOrCreate(
                    [
                        'kelompok_id' => $kelompokId,
                        'kelas_id'    => $kelasId,
                        'jadwal_id'   => $jadwalId,
                        'santri_id'   => $item['santri_id'],
                        'tanggal'     => $tanggal,
                    ],
                    [
                        'status'   => $item['status'],
                        'catatan'  => $catatanSesi ?? ($item['catatan'] ?? null),
                        'input_by' => $userId,
                    ]
                );
                $savedCount++;
            }
            return $savedCount;
        });
    }

    /**
     * Salin presensi dari tanggal sebelumnya ke tanggal tujuan.
     */
    public function copyPresensi(
        int $kelompokId,
        int $kelasId,
        ?int $jadwalId,
        string $fromTanggal,
        string $toTanggal,
        int $userId
    ): int {
        return DB::transaction(function () use ($kelompokId, $kelasId, $jadwalId, $fromTanggal, $toTanggal, $userId) {
            $sourceRecords = Absensi::where('kelompok_id', $kelompokId)
                ->where('kelas_id', $kelasId)
                ->when($jadwalId, fn ($q) => $q->where('jadwal_id', $jadwalId))
                ->whereDate('tanggal', $fromTanggal)
                ->get();

            if ($sourceRecords->isEmpty()) {
                return 0;
            }

            $copied = 0;
            foreach ($sourceRecords as $rec) {
                Absensi::updateOrCreate(
                    [
                        'kelompok_id' => $kelompokId,
                        'kelas_id'    => $kelasId,
                        'jadwal_id'   => $jadwalId,
                        'santri_id'   => $rec->santri_id,
                        'tanggal'     => $toTanggal,
                    ],
                    [
                        'status'   => $rec->status,
                        'catatan'  => $rec->catatan ? "(Salinan) " . $rec->catatan : null,
                        'input_by' => $userId,
                    ]
                );
                $copied++;
            }
            return $copied;
        });
    }

    /**
     * Single aggregated query ringkasan kehadiran untuk kelompok dan kelas tertentu.
     */
    public function getHariIniStats(int $kelompokId, int $kelasId, ?int $jadwalId, string $tanggal): object
    {
        return DB::table('mahad_absensi')
            ->where('kelompok_id', $kelompokId)
            ->where('kelas_id', $kelasId)
            ->when($jadwalId, fn ($q) => $q->where('jadwal_id', $jadwalId))
            ->where('tanggal', $tanggal)
            ->selectRaw('
                COUNT(*) as total_input,
                COALESCE(SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END), 0) as hadir,
                COALESCE(SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END), 0) as izin,
                COALESCE(SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END), 0) as sakit,
                COALESCE(SUM(CASE WHEN status = "alfa" THEN 1 ELSE 0 END), 0) as alfa,
                COALESCE(SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END), 0) as terlambat
            ')
            ->first() ?? (object) [
                'total_input' => 0,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alfa' => 0,
                'terlambat' => 0,
            ];
    }
}
