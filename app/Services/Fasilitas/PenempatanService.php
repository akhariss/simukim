<?php

namespace App\Services\Fasilitas;

use App\Enums\KamarStatus;
use App\Enums\PenempatanStatus;
use App\Models\Kamar;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PenempatanService
{
    /**
     * Assign Santri ke Kamar dengan Atomic Transaction dan lockForUpdate
     */
    public function assign(array $data, int $assignedById): PenempatanKamar
    {
        return DB::transaction(function () use ($data, $assignedById) {
            $periode = Periode::getActive();
            if (!$periode) throw new Exception("Tidak ada periode akademik aktif.");

            $santri = Santri::where('id', $data['santri_id'])->lockForUpdate()->firstOrFail();
            $hasActive = PenempatanKamar::where('santri_id', $santri->id)
                ->where('status', PenempatanStatus::Aktif->value)
                ->where('periode_id', $periode->id)
                ->exists();

            if ($hasActive) throw new Exception("Mahasantri {$santri->nomor_induk} sudah memiliki kamar aktif pada semester {$periode->nama}.");

            $kamar = Kamar::where('id', $data['kamar_id'])->lockForUpdate()->firstOrFail();
            if ($kamar->status === KamarStatus::Perbaikan || $kamar->status === KamarStatus::Nonaktif) {
                throw new Exception("Kamar {$kamar->nomor_kamar} sedang dalam perbaikan / nonaktif.");
            }

            if ($kamar->gender !== $santri->gender) {
                $gKamar = $kamar->gender->value === 'L' ? 'Ikhwan (L)' : 'Akhwat (P)';
                $gSantri = $santri->gender->value === 'L' ? 'Ikhwan (L)' : 'Akhwat (P)';
                throw new Exception("Ketidakcocokan Gender! Kamar {$kamar->nomor_kamar} adalah zona {$gKamar}, sedangkan santri adalah {$gSantri}.");
            }

            $penghuniAktifCount = PenempatanKamar::where('kamar_id', $kamar->id)
                ->where('status', PenempatanStatus::Aktif->value)
                ->where('periode_id', $periode->id)->count();

            if ($penghuniAktifCount >= $kamar->kapasitas_maks) {
                throw new Exception("Kamar {$kamar->nomor_kamar} sudah penuh ({$penghuniAktifCount}/{$kamar->kapasitas_maks} bed).");
            }

            $newPenempatan = PenempatanKamar::create([
                'periode_id' => $periode->id, 'santri_id' => $santri->id, 'kamar_id' => $kamar->id,
                'tanggal_masuk' => $data['tanggal_masuk'], 'status' => PenempatanStatus::Aktif->value,
                'catatan' => $data['catatan'] ?? null, 'assigned_by' => $assignedById,
            ]);

            if (($penghuniAktifCount + 1) >= $kamar->kapasitas_maks) {
                $kamar->update(['status' => KamarStatus::Penuh->value]);
            }

            Cache::forget('penempatan_kpi_counts');
            Cache::forget('penempatan_kpi_counts_' . $periode->id);
            return $newPenempatan;
        });
    }

    /**
     * Mutasi / Pindah Kamar Mahasantri
     */
    public function pindah(PenempatanKamar $penempatan, array $data, int $assignedById): void
    {
        if ($penempatan->status !== PenempatanStatus::Aktif) {
            throw new Exception('Hanya penempatan aktif yang dapat dipindahkan.');
        }

        DB::transaction(function () use ($penempatan, $data, $assignedById) {
            $santri = $penempatan->santri;
            $kamarLama = Kamar::where('id', $penempatan->kamar_id)->lockForUpdate()->first();
            $kamarBaru = Kamar::where('id', $data['kamar_tujuan_id'])->lockForUpdate()->firstOrFail();

            if ($kamarBaru->status === KamarStatus::Perbaikan || $kamarBaru->status === KamarStatus::Nonaktif) {
                throw new Exception("Kamar tujuan sedang dalam perbaikan / nonaktif.");
            }
            if ($kamarBaru->gender !== $santri->gender) {
                throw new Exception("Gender santri tidak cocok dengan zona kamar tujuan.");
            }

            $penghuniBaruCount = PenempatanKamar::where('kamar_id', $kamarBaru->id)
                ->where('status', PenempatanStatus::Aktif->value)
                ->where('periode_id', $penempatan->periode_id)->count();

            if ($penghuniBaruCount >= $kamarBaru->kapasitas_maks) {
                throw new Exception("Kamar tujuan {$kamarBaru->nomor_kamar} sudah penuh.");
            }

            $penempatan->update([
                'status' => PenempatanStatus::Pindah->value, 'tanggal_keluar' => $data['tanggal_pindah'],
                'catatan' => trim(($penempatan->catatan ?? '') . " [Pindah ke K.{$kamarBaru->nomor_kamar}: " . ($data['alasan_pindah'] ?? 'Tanpa keterangan') . "]"),
            ]);

            PenempatanKamar::create([
                'periode_id' => $penempatan->periode_id, 'santri_id' => $santri->id, 'kamar_id' => $kamarBaru->id,
                'tanggal_masuk' => $data['tanggal_pindah'], 'status' => PenempatanStatus::Aktif->value,
                'catatan' => "Pindahan dari K.{$kamarLama->nomor_kamar}. " . ($data['alasan_pindah'] ?? ''),
                'assigned_by' => $assignedById,
            ]);

            if ($kamarLama && $kamarLama->status === KamarStatus::Penuh) $kamarLama->update(['status' => KamarStatus::Tersedia->value]);
            if (($penghuniBaruCount + 1) >= $kamarBaru->kapasitas_maks) $kamarBaru->update(['status' => KamarStatus::Penuh->value]);

            Cache::forget('penempatan_kpi_counts');
            Cache::forget('penempatan_kpi_counts_' . $penempatan->periode_id);
        });
    }

    /**
     * Checkout Mahasantri dari Kamar
     */
    public function keluar(PenempatanKamar $penempatan): void
    {
        if ($penempatan->status !== PenempatanStatus::Aktif) throw new Exception('Penempatan ini sudah tidak aktif.');

        DB::transaction(function () use ($penempatan) {
            $penempatan->update(['status' => PenempatanStatus::Keluar->value, 'tanggal_keluar' => now()->toDateString()]);
            $kamar = $penempatan->kamar;
            if ($kamar && $kamar->status === KamarStatus::Penuh) $kamar->update(['status' => KamarStatus::Tersedia->value]);

            Cache::forget('penempatan_kpi_counts');
            Cache::forget('penempatan_kpi_counts_' . $penempatan->periode_id);
        });
    }

    /**
     * Penempatan Kolektif/Batch ke Kamar (Swap: Alokasi Berbasis Kamar)
     */
    public function assignBatch(array $data, int $assignedById): array
    {
        return DB::transaction(function () use ($data, $assignedById) {
            $periode = Periode::getActive();
            if (!$periode) throw new Exception("Tidak ada periode akademik aktif.");

            $kamar = Kamar::where('id', $data['kamar_id'])->lockForUpdate()->firstOrFail();
            if ($kamar->status === KamarStatus::Perbaikan || $kamar->status === KamarStatus::Nonaktif) {
                throw new Exception("Kamar {$kamar->nomor_kamar} sedang dalam perbaikan / nonaktif.");
            }

            $penghuniAktifCount = PenempatanKamar::where('kamar_id', $kamar->id)
                ->where('status', PenempatanStatus::Aktif->value)
                ->where('periode_id', $periode->id)
                ->count();

            $sisaSlot = max(0, $kamar->kapasitas_maks - $penghuniAktifCount);
            $santriIds = array_unique((array) $data['santri_ids']);
            $countToAssign = count($santriIds);

            if ($countToAssign > $sisaSlot) {
                throw new Exception("Jumlah mahasantri yang dipilih ({$countToAssign}) melebihi sisa kapasitas kamar {$kamar->nomor_kamar} ({$sisaSlot} slot tersisa).");
            }

            $santris = Santri::whereIn('id', $santriIds)->where('status', 'aktif')->lockForUpdate()->get();
            if ($santris->count() !== $countToAssign) {
                throw new Exception("Beberapa data mahasantri tidak valid atau tidak berstatus aktif.");
            }

            $tglMasuk = $data['tanggal_masuk'] ?? now()->toDateString();
            $catatan = $data['catatan'] ?? 'Penempatan kolektif kamar.';

            foreach ($santris as $santri) {
                if ($santri->gender !== $kamar->gender) {
                    throw new Exception("Ketidakcocokan gender! Mahasantri {$santri->nomor_induk} tidak sesuai dengan zona kamar {$kamar->nomor_kamar}.");
                }

                $hasActive = PenempatanKamar::where('santri_id', $santri->id)
                    ->where('status', PenempatanStatus::Aktif->value)
                    ->where('periode_id', $periode->id)
                    ->exists();

                if ($hasActive) {
                    throw new Exception("Mahasantri {$santri->nomor_induk} sudah memiliki kamar aktif.");
                }

                PenempatanKamar::create([
                    'periode_id' => $periode->id,
                    'santri_id' => $santri->id,
                    'kamar_id' => $kamar->id,
                    'tanggal_masuk' => $tglMasuk,
                    'status' => PenempatanStatus::Aktif->value,
                    'catatan' => $catatan,
                    'assigned_by' => $assignedById,
                ]);
            }

            if (($penghuniAktifCount + $countToAssign) >= $kamar->kapasitas_maks) {
                $kamar->update(['status' => KamarStatus::Penuh->value]);
            }

            Cache::forget('penempatan_kpi_counts');
            Cache::forget('penempatan_kpi_counts_' . $periode->id);

            return ['kamar' => $kamar, 'placed' => $countToAssign];
        });
    }

    /**
     * Penempatan Otomatis secara batch
     */
    public function autoAssign(string $tanggalMasuk, int $assignedById): array
    {
        return app(AutoPenempatanService::class)->autoAssign($tanggalMasuk, $assignedById);
    }

    public function getAvailableRoomsQuery(?int $gedungId = null, ?string $gender = null, ?string $search = null)
    {
        $periode = Periode::getActive();
        $occSub = DB::table('mahad_penempatan_kamar')
            ->select('kamar_id', DB::raw('COUNT(*) as occupants'))
            ->where('status', PenempatanStatus::Aktif->value)
            ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
            ->groupBy('kamar_id');

        $query = Kamar::query()
            ->select('mahad_kamar.*', DB::raw('COALESCE(occ.occupants, 0) as terisi_count'))
            ->leftJoinSub($occSub, 'occ', 'occ.kamar_id', '=', 'mahad_kamar.id')
            ->whereNotIn('mahad_kamar.status', [KamarStatus::Perbaikan->value, KamarStatus::Nonaktif->value])
            ->whereRaw('COALESCE(occ.occupants, 0) < mahad_kamar.kapasitas_maks')
            ->with([
                'lantai.gedung',
                'penempatanAktif' => fn($q) => $q->when($periode, fn($pq) => $pq->where('periode_id', $periode->id))->with('santri.user'),
            ]);

        if ($gender) $query->where('mahad_kamar.gender', $gender);
        if ($gedungId) $query->whereHas('lantai', fn($q) => $q->where('gedung_id', $gedungId));
        if ($search) {
            $query->where(fn($q) => $q->where('mahad_kamar.nomor_kamar', 'like', "%{$search}%")
                ->orWhereHas('lantai.gedung', fn($gq) => $gq->where('nama', 'like', "%{$search}%")));
        }

        return $query->orderBy('mahad_kamar.nomor_kamar', 'asc');
    }
}
