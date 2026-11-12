<?php

namespace App\Services;

use App\Enums\KelasType;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\KelompokPengampu;
use App\Models\Kelas;
use App\Models\Santri;
use Illuminate\Support\Facades\DB;

class KelompokService
{
    /**
     * Tambah santri ke kelompok dengan pessimistic locking.
     * Invariant: 1 Mahasantri HANYA bisa terdaftar di 1 kelompok per periode aktif.
     */
    public function addAnggota(Kelompok $kelompok, int $santriId): KelompokAnggota
    {
        return DB::transaction(function () use ($kelompok, $santriId) {
            $kelompokFresh = Kelompok::lockForUpdate()->findOrFail($kelompok->id);
            $santri = Santri::with('user.roles')->findOrFail($santriId);
            if ($santri->user?->hasAnyRole(['admin', 'tutor'])) {
                throw new \DomainException("Pengguna '{$santri->nama_lengkap}' memiliki peran Staf/Admin/Tutor sehingga tidak dapat didaftarkan sebagai santri kelompok.");
            }
            if (KelompokAnggota::where('kelompok_id', $kelompokFresh->id)->count() >= $kelompokFresh->kapasitas) {
                throw new \DomainException("Kapasitas kelompok {$kelompokFresh->nama} sudah penuh ({$kelompokFresh->kapasitas} anggota).");
            }
            $exists = KelompokAnggota::where('santri_id', $santriId)
                ->whereHas('kelompok', fn ($q) => $q->where('periode_id', $kelompokFresh->periode_id))
                ->exists();
            if ($exists) {
                throw new \DomainException('Mahasantri sudah terdaftar di kelompok lain pada periode ini.');
            }
            return KelompokAnggota::create(['kelompok_id' => $kelompokFresh->id, 'santri_id' => $santriId]);
        });
    }

    /**
     * Tambah banyak santri sekaligus ke kelompok secara atomic.
     */
    public function addAnggotaBulk(Kelompok $kelompok, array $santriIds): array
    {
        return DB::transaction(function () use ($kelompok, $santriIds) {
            $kelompokFresh = Kelompok::lockForUpdate()->findOrFail($kelompok->id);
            $currentCount = KelompokAnggota::where('kelompok_id', $kelompokFresh->id)->count();
            $sisaSlot = max(0, $kelompokFresh->kapasitas - $currentCount);
            if ($sisaSlot <= 0) {
                throw new \DomainException("Kapasitas kelompok sudah penuh ({$kelompokFresh->kapasitas} anggota).");
            }

            $registeredSantriIds = KelompokAnggota::whereIn('santri_id', $santriIds)
                ->whereHas('kelompok', fn ($q) => $q->where('periode_id', $kelompokFresh->periode_id))
                ->pluck('santri_id')->all();

            $candidates = array_values(array_diff($santriIds, $registeredSantriIds));
            $invalidIds = Santri::whereIn('id', $candidates)
                ->whereHas('user', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'tutor'])))
                ->pluck('id')->all();
            $candidates = array_values(array_diff($candidates, $invalidIds));
            $added = 0;
            $skipped = count($registeredSantriIds) + count($invalidIds);
            $insertData = [];
            $now = now();

            foreach ($candidates as $sid) {
                if ($added >= $sisaSlot) { $skipped++; continue; }
                $insertData[] = ['kelompok_id' => $kelompokFresh->id, 'santri_id' => (int) $sid, 'created_at' => $now, 'updated_at' => $now];
                $added++;
            }

            if (!empty($insertData)) KelompokAnggota::insert($insertData);
            return compact('added', 'skipped');
        });
    }

    public function removeAnggota(Kelompok $kelompok, int $santriId): void
    {
        KelompokAnggota::where('kelompok_id', $kelompok->id)->where('santri_id', $santriId)->delete();
    }

    public function pindahKelompok(int $santriId, int $targetKelompokId): void
    {
        DB::transaction(function () use ($santriId, $targetKelompokId) {
            $target = Kelompok::lockForUpdate()->findOrFail($targetKelompokId);
            if (KelompokAnggota::where('kelompok_id', $target->id)->count() >= $target->kapasitas) {
                throw new \DomainException("Kapasitas kelompok tujuan {$target->nama} sudah penuh.");
            }
            KelompokAnggota::where('santri_id', $santriId)
                ->whereHas('kelompok', fn ($q) => $q->where('periode_id', $target->periode_id))->delete();
            KelompokAnggota::create(['kelompok_id' => $target->id, 'santri_id' => $santriId]);
        });
    }

    /**
     * Atur Tutor Pengampu per Kelas Kajian di Kelompok.
     * Aturan Bisnis:
     * - Untuk Qur'an dan Kitab: 1 Tutor hanya bisa mengampu 1 kelompok di kelas/mapel yang sama.
     * - Untuk Masal: 1 Tutor bisa mengampu banyak kelompok di kelas/mapel yang sama.
     */
    public function assignTutor(Kelompok $kelompok, int $kelasId, int $tutorId): KelompokPengampu
    {
        return DB::transaction(function () use ($kelompok, $kelasId, $tutorId) {
            $kelas = Kelas::findOrFail($kelasId);
            $tipe = $kelas->tipe instanceof KelasType ? $kelas->tipe->value : (string) $kelas->tipe;

            if (in_array($tipe, ['kelompok', 'quran', 'kitab'])) {
                // Cek apakah tutor sudah mengampu kajian ini di kelompok lain pada periode yang sama
                $conflict = KelompokPengampu::where('kelas_id', $kelasId)
                    ->where('tutor_id', $tutorId)
                    ->where('kelompok_id', '!=', $kelompok->id)
                    ->whereHas('kelompok', fn ($q) => $q->where('periode_id', $kelompok->periode_id))
                    ->with('kelompok')
                    ->first();

                if ($conflict) {
                    $kelompokLain = $conflict->kelompok?->nama ?? 'kelompok lain';
                    $tipeLabel = $kelas->tipe instanceof KelasType ? $kelas->tipe->label() : 'Kajian Kelompok';
                    throw new \DomainException("Tutor ini sudah ditugaskan mengampu {$kelas->nama} di {$kelompokLain}. Untuk {$tipeLabel}, 1 tutor hanya dapat mengampu 1 kelompok.");
                }
            }

            return KelompokPengampu::updateOrCreate(
                [
                    'kelompok_id' => $kelompok->id,
                    'kelas_id'    => $kelasId,
                ],
                [
                    'tutor_id' => $tutorId,
                ]
            );
        });
    }

    public function createKelompok(array $data): Kelompok
    {
        return DB::transaction(function () use ($data) {
            return Kelompok::create($data);
        });
    }

    /**
     * Batch-generate sejumlah kelompok sekaligus secara atomik.
     * Format nama otomatis: "{prefix} {nomor_urut}" — skip jika nama sudah ada di periode.
     *
     * @return array{created: int, skipped: int}
     */
    public function batchCreateKelompok(int $periodeId, string $prefix, int $jumlah, int $nomorMulai, int $kapasitas): array
    {
        return DB::transaction(function () use ($periodeId, $prefix, $jumlah, $nomorMulai, $kapasitas) {
            $created = 0;
            $skipped = 0;
            $now     = now();

            for ($i = 0; $i < $jumlah; $i++) {
                $nomor = $nomorMulai + $i;
                $nama  = trim($prefix) . ' ' . $nomor;

                $exists = Kelompok::where('periode_id', $periodeId)
                    ->where('nama', $nama)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Kelompok::create([
                    'periode_id' => $periodeId,
                    'nama'       => $nama,
                    'kode'       => null,
                    'kapasitas'  => $kapasitas,
                    'is_active'  => true,
                ]);
                $created++;
            }

            return compact('created', 'skipped');
        });
    }

    /**
     * Hitung jumlah santri aktif yang belum terdaftar di kelompok manapun pada periode ini.
     */
    public function getSantriTidakBerkelompokCount(int $periodeId): int
    {
        $enrolledIds = KelompokAnggota::whereHas(
            'kelompok',
            fn ($q) => $q->where('periode_id', $periodeId)
        )->pluck('santri_id');

        $query = Santri::where('status', 'aktif')
            ->whereDoesntHave('user', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'tutor'])));
        if ($enrolledIds->isNotEmpty()) {
            $query->whereNotIn('id', $enrolledIds);
        }
        return $query->count();
    }

    public function updateKelompok(Kelompok $kelompok, array $data): Kelompok
    {
        return DB::transaction(function () use ($kelompok, $data) {
            $kelompok->update($data);
            return $kelompok->fresh();
        });
    }

    public function deleteKelompok(Kelompok $kelompok): void
    {
        DB::transaction(function () use ($kelompok) {
            if ($kelompok->anggota()->exists()) {
                throw new \DomainException('Kelompok masih memiliki santri terdaftar. Kosongkan anggota kelompok terlebih dahulu.');
            }
            $kelompok->delete();
        });
    }

    public function getKelompokStats(int $periodeId): object
    {
        $stats = DB::table('mahad_kelompok')
            ->where('periode_id', $periodeId)
            ->selectRaw('COUNT(*) as total_kelompok, COALESCE(SUM(kapasitas), 0) as total_kapasitas')
            ->first();

        $totalSantriTerbagi = DB::table('mahad_kelompok_anggota as ka')
            ->join('mahad_kelompok as k', 'ka.kelompok_id', '=', 'k.id')
            ->where('k.periode_id', $periodeId)
            ->count();

        return (object) [
            'total_kelompok' => (int) ($stats->total_kelompok ?? 0),
            'total_kapasitas' => (int) ($stats->total_kapasitas ?? 0),
            'total_santri_terbagi' => $totalSantriTerbagi,
        ];
    }
}
