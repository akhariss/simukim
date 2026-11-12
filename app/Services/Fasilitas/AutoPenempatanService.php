<?php

namespace App\Services\Fasilitas;

use App\Enums\KamarStatus;
use App\Enums\PenempatanStatus;
use App\Enums\TipeRuang;
use App\Models\Kamar;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AutoPenempatanService
{
    /**
     * Penempatan Otomatis secara batch untuk mahasantri yang belum memiliki kamar
     */
    public function autoAssign(string $tanggalMasuk, int $assignedById): array
    {
        $periode = Periode::getActive();
        if (!$periode) {
            throw new Exception("Tidak ada periode akademik aktif.");
        }

        $unassigned = Santri::where('status', 'aktif')
            ->where('periode_id', $periode->id)
            ->whereDoesntHave('penempatanAktif')
            ->with('user.roles')
            ->get();

        if ($unassigned->isEmpty()) {
            return ['berhasil' => 0, 'gagal' => 0, 'pesan_gagal' => []];
        }

        $berhasil = 0;
        $gagal = 0;
        $pesanGagal = [];

        foreach ($unassigned as $santri) {
            try {
                DB::transaction(function () use ($santri, $tanggalMasuk, $periode, $assignedById, &$berhasil) {
                    $targetTipe = $santri->isMudabbir() ? TipeRuang::Mudabbir->value : TipeRuang::Kamar->value;
                    $occupantsSub = DB::table('mahad_penempatan_kamar')
                        ->select('kamar_id', DB::raw('COUNT(*) as occupants'))
                        ->where('status', PenempatanStatus::Aktif->value)
                        ->where('periode_id', $periode->id)
                        ->groupBy('kamar_id');

                    $findKamar = function (?string $tipe) use ($santri, $occupantsSub) {
                        $q = Kamar::query()
                            ->select('mahad_kamar.*')
                            ->leftJoinSub($occupantsSub, 'occ', 'occ.kamar_id', '=', 'mahad_kamar.id')
                            ->where('mahad_kamar.gender', $santri->gender)
                            ->whereNotIn('mahad_kamar.status', [KamarStatus::Perbaikan->value, KamarStatus::Nonaktif->value])
                            ->whereRaw('COALESCE(occ.occupants, 0) < mahad_kamar.kapasitas_maks')
                            ->orderByRaw('COALESCE(occ.occupants, 0) ASC')
                            ->lockForUpdate();

                        if ($tipe) $q->where('mahad_kamar.tipe_ruang', $tipe);
                        return $q->first();
                    };

                    $kamarLocked = $findKamar($targetTipe);
                    if (!$kamarLocked && $targetTipe === TipeRuang::Mudabbir->value) {
                        $kamarLocked = $findKamar(null);
                    }

                    if (!$kamarLocked) {
                        throw new Exception("Tidak ada kamar kosong yang tersedia untuk gender {$santri->gender->value}.");
                    }

                    $penghuniCount = PenempatanKamar::where('kamar_id', $kamarLocked->id)
                        ->where('status', PenempatanStatus::Aktif->value)
                        ->where('periode_id', $periode->id)->count();

                    if ($penghuniCount >= $kamarLocked->kapasitas_maks) {
                        throw new Exception("Kamar {$kamarLocked->nomor_kamar} sudah penuh.");
                    }

                    PenempatanKamar::create([
                        'periode_id' => $periode->id,
                        'santri_id' => $santri->id,
                        'kamar_id' => $kamarLocked->id,
                        'tanggal_masuk' => $tanggalMasuk,
                        'status' => PenempatanStatus::Aktif->value,
                        'catatan' => 'Penempatan otomatis sistem.',
                        'assigned_by' => $assignedById,
                    ]);

                    if (($penghuniCount + 1) >= $kamarLocked->kapasitas_maks) {
                        $kamarLocked->update(['status' => KamarStatus::Penuh->value]);
                    }

                    $berhasil++;
                });
            } catch (Exception $e) {
                $gagal++;
                $pesanGagal[] = ($santri->user->name ?? $santri->nomor_induk) . ': ' . $e->getMessage();
            }
        }

        Cache::forget('penempatan_kpi_counts');
        Cache::forget('penempatan_kpi_counts_' . $periode->id);

        return ['berhasil' => $berhasil, 'gagal' => $gagal, 'pesan_gagal' => $pesanGagal];
    }
}

