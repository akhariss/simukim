<?php

namespace App\Http\Controllers\Fasilitas;

use App\Enums\KamarStatus;
use App\Enums\TipeRuang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fasilitas\StoreGedungRequest;
use App\Http\Requests\Fasilitas\UpdateGedungRequest;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use App\Models\PenempatanKamar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GedungController extends Controller
{
    /**
     * Simpan Gedung Baru (dengan opsi shortcut buat lantai & kamar standar)
     */
    public function store(StoreGedungRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $gedung = Gedung::create([
                'kode' => strtoupper(trim($validated['kode'])),
                'nama' => trim($validated['nama']),
                'gender_khusus' => $validated['gender_khusus'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'is_active' => true,
            ]);

            $jumlahLantai = (int) ($validated['jumlah_lantai'] ?? 0);
            $isCampur = ($validated['gender_khusus'] === 'campur');
            $genderBlocks = [];
            if ($isCampur && !empty($validated['gender_blocks'])) {
                $raw = is_array($validated['gender_blocks'])
                    ? $validated['gender_blocks']
                    : json_decode((string) $validated['gender_blocks'], true);
                if (is_array($raw)) {
                    $genderBlocks = $raw;
                }
            }

            for ($i = 2; $i <= ($jumlahLantai + 1); $i++) {
                $lantai = Lantai::create([
                    'gedung_id' => $gedung->id,
                    'nomor_lantai' => $i,
                    'kapasitas_kamar_standar' => 24,
                ]);

                for ($k = 1; $k <= 24; $k++) {
                    $kamarGender = $isCampur ? 'L' : $validated['gender_khusus'];

                    if ($isCampur && !empty($genderBlocks)) {
                        foreach ($genderBlocks as $block) {
                            $dari = (int) ($block['dari'] ?? 1);
                            $sampai = (int) ($block['sampai'] ?? 24);
                            if ($k >= $dari && $k <= $sampai) {
                                $kamarGender = in_array($block['gender'] ?? '', ['L', 'P']) ? $block['gender'] : 'L';
                                break;
                            }
                        }
                    }

                    Kamar::create([
                        'lantai_id' => $lantai->id,
                        'nomor_kamar' => sprintf("%s-%d%02d", $gedung->kode, $i, $k),
                        'gender' => $kamarGender,
                        'kapasitas_maks' => 4,
                        'tipe_ruang' => TipeRuang::Kamar->value,
                        'status' => KamarStatus::Tersedia->value,
                    ]);
                }
            }
        });

        $this->clearGedungCache();

        return redirect()->route('fasilitas.kelola.index')
            ->with('success', "Gedung '{$validated['nama']}' berhasil ditambahkan ke fasilitas asrama.");
    }

    /**
     * Update Data Gedung
     */
    public function update(UpdateGedungRequest $request, Gedung $gedung): RedirectResponse
    {
        $validated = $request->validated();

        $gedung->update([
            'kode' => strtoupper(trim($validated['kode'])),
            'nama' => trim($validated['nama']),
            'gender_khusus' => $validated['gender_khusus'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        $this->clearGedungCache();

        return redirect()->route('fasilitas.kelola.index')
            ->with('success', "Data Gedung '{$gedung->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus Gedung beserta seluruh lantai, kamar, dan penempatan (Anti-Data Yatim / Cascade)
     */
    public function destroy(Gedung $gedung): RedirectResponse
    {
        $namaGedung = $gedung->nama;

        DB::transaction(function () use ($gedung) {
            $lantaiIds = Lantai::where('gedung_id', $gedung->id)->pluck('id');
            $kamarIds = Kamar::whereIn('lantai_id', $lantaiIds)->pluck('id');

            // 1. Hapus riwayat & penempatan kamar santri pada gedung ini
            if ($kamarIds->isNotEmpty()) {
                PenempatanKamar::whereIn('kamar_id', $kamarIds)->delete();
                // 2. Hapus seluruh unit kamar
                Kamar::whereIn('id', $kamarIds)->delete();
            }

            // 3. Hapus seluruh lantai
            if ($lantaiIds->isNotEmpty()) {
                Lantai::whereIn('id', $lantaiIds)->delete();
            }

            // 4. Hapus gedung
            $gedung->delete();
        });

        $this->clearGedungCache();

        return redirect()->route('fasilitas.kelola.index')
            ->with('success', "Gedung '{$namaGedung}' beserta seluruh lantai dan kamarnya berhasil dihapus.");
    }

    private function clearGedungCache(): void
    {
        Cache::forget('all_gedung_tree');
        Cache::forget('total_lantai_count');
        Cache::forget('total_kamar_count');
        Cache::forget('dashboard_stats');
    }
}
