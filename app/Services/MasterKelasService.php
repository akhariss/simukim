<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\KelasJadwal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MasterKelasService
{
    public function getMasterKelasWithJadwal(int $periodeId): Collection
    {
        return Kelas::where('periode_id', $periodeId)
            ->with(['jadwal' => fn ($q) => $q->orderBy('hari')->orderBy('jam_mulai')])
            ->withCount('jadwal')
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();
    }

    public function createKelas(array $data): Kelas
    {
        return DB::transaction(function () use ($data) {
            $sesiList = $data['sesi'] ?? [];
            unset($data['sesi'], $data['siklus'], $data['jumlah_pertemuan']);

            $kelas = Kelas::create($data);

            if (!empty($sesiList)) {
                foreach ($sesiList as $sesi) {
                    if (empty($sesi['hari'])) continue;
                    $kelas->jadwal()->create([
                        'nama_sesi'   => $sesi['nama_sesi'] ?? 'Pertemuan',
                        'hari'        => $sesi['hari'],
                        'jam_mulai'   => $sesi['jam_mulai'] ?? '05:30',
                        'jam_selesai' => $sesi['jam_selesai'] ?? '06:30',
                        'ruangan'     => $sesi['ruangan'] ?? null,
                    ]);
                }
            }

            return $kelas->load('jadwal');
        });
    }

    public function updateKelas(Kelas $kelas, array $data): Kelas
    {
        return DB::transaction(function () use ($kelas, $data) {
            $sesiList = $data['sesi'] ?? null;
            unset($data['sesi'], $data['siklus'], $data['jumlah_pertemuan']);

            $data['is_active'] = !empty($data['is_active']);
            $kelas->update($data);

            if ($sesiList !== null) {
                $keptIds = [];
                foreach ($sesiList as $sesi) {
                    if (empty($sesi['hari'])) continue;

                    $sesiData = [
                        'nama_sesi'   => $sesi['nama_sesi'] ?? 'Pertemuan',
                        'hari'        => $sesi['hari'],
                        'jam_mulai'   => $sesi['jam_mulai'] ?? '05:30',
                        'jam_selesai' => $sesi['jam_selesai'] ?? '06:30',
                        'ruangan'     => $sesi['ruangan'] ?? null,
                    ];

                    if (!empty($sesi['id'])) {
                        $jadwal = $kelas->jadwal()->where('id', $sesi['id'])->first();
                        if ($jadwal) {
                            $jadwal->update($sesiData);
                            $keptIds[] = $jadwal->id;
                            continue;
                        }
                    }

                    $newJadwal = $kelas->jadwal()->create($sesiData);
                    $keptIds[] = $newJadwal->id;
                }

                if (!empty($keptIds)) {
                    $kelas->jadwal()->whereNotIn('id', $keptIds)->delete();
                } else {
                    $kelas->jadwal()->delete();
                }
            }

            return $kelas->fresh('jadwal');
        });
    }

    public function deleteKelas(Kelas $kelas): void
    {
        DB::transaction(function () use ($kelas) {
            $kelas->delete();
        });
    }

    public function addJadwal(Kelas $kelas, array $data): KelasJadwal
    {
        return DB::transaction(function () use ($kelas, $data) {
            return $kelas->jadwal()->create($data);
        });
    }

    public function updateJadwal(KelasJadwal $jadwal, array $data): KelasJadwal
    {
        return DB::transaction(function () use ($jadwal, $data) {
            $jadwal->update($data);
            return $jadwal->fresh();
        });
    }

    public function deleteJadwal(KelasJadwal $jadwal): void
    {
        DB::transaction(function () use ($jadwal) {
            $jadwal->delete();
        });
    }
}
