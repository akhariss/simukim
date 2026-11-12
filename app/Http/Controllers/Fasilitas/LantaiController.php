<?php

namespace App\Http\Controllers\Fasilitas;

use App\Enums\KamarStatus;
use App\Enums\TipeRuang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fasilitas\StoreLantaiRequest;
use App\Http\Requests\Fasilitas\UpdateLantaiRequest;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use App\Models\PenempatanKamar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LantaiController extends Controller
{
    /**
     * Tambah Lantai pada Gedung & Otomatis Generate Kamar
     */
    public function store(StoreLantaiRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $gedung = Gedung::findOrFail($validated['gedung_id']);

        $exists = Lantai::where('gedung_id', $gedung->id)
            ->where('nomor_lantai', $validated['nomor_lantai'])
            ->exists();

        if ($exists) {
            return back()->with('error', "Lantai {$validated['nomor_lantai']} sudah ada pada gedung {$gedung->nama}.");
        }

        $jumlahKamar = isset($validated['jumlah_kamar']) ? (int) $validated['jumlah_kamar'] : 24;
        $gender = $validated['gender'] ?? ($gedung->gender_khusus === 'campur' ? 'L' : $gedung->gender_khusus);
        $kapasitasMaks = (int) ($validated['kapasitas_maks'] ?? 4);

        $createdKamar = 0;
        DB::transaction(function () use ($gedung, $validated, $jumlahKamar, $gender, $kapasitasMaks, &$createdKamar) {
            $lantai = Lantai::create([
                'gedung_id' => $gedung->id,
                'nomor_lantai' => $validated['nomor_lantai'],
                'kapasitas_kamar_standar' => $jumlahKamar > 0 ? $jumlahKamar : 24,
            ]);

            if ($jumlahKamar > 0) {
                for ($i = 1; $i <= $jumlahKamar; $i++) {
                    $nomorKamar = sprintf("%s-%d%02d", $gedung->kode, $validated['nomor_lantai'], $i);
                    $tipeRuang = TipeRuang::Kamar->value;

                    Kamar::create([
                        'lantai_id' => $lantai->id,
                        'nomor_kamar' => $nomorKamar,
                        'gender' => $gender,
                        'kapasitas_maks' => $kapasitasMaks,
                        'tipe_ruang' => $tipeRuang,
                        'status' => KamarStatus::Tersedia->value,
                    ]);
                    $createdKamar++;
                }
            }
        });

        $this->clearLantaiCache();

        return redirect()->route('fasilitas.kelola.index', ['tab' => 'lantai', 'gedung_id' => $gedung->id])
            ->with('success', "Lantai {$validated['nomor_lantai']} ({$gedung->nama}) berhasil dibuat beserta {$createdKamar} unit kamar.");
    }

    /**
     * Update Nomor Lantai / Data Lantai
     */
    public function update(UpdateLantaiRequest $request, Lantai $lantai): RedirectResponse
    {
        $validated = $request->validated();

        $exists = Lantai::where('gedung_id', $lantai->gedung_id)
            ->where('nomor_lantai', $validated['nomor_lantai'])
            ->where('id', '!=', $lantai->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "Nomor lantai {$validated['nomor_lantai']} sudah ada di gedung ini.");
        }

        $lantai->update($validated);

        $this->clearLantaiCache();

        return redirect()->route('fasilitas.kelola.index', ['tab' => 'lantai', 'gedung_id' => $lantai->gedung_id])
            ->with('success', "Data Lantai {$lantai->nomor_lantai} berhasil diperbarui.");
    }

    /**
     * Hapus Lantai beserta seluruh kamarnya (Anti-Data Yatim / Cascade)
     */
    public function destroy(Lantai $lantai): RedirectResponse
    {
        $gedungId = $lantai->gedung_id;
        $nomorLantai = $lantai->nomor_lantai;

        DB::transaction(function () use ($lantai) {
            $kamarIds = Kamar::where('lantai_id', $lantai->id)->pluck('id');
            if ($kamarIds->isNotEmpty()) {
                PenempatanKamar::whereIn('kamar_id', $kamarIds)->delete();
                Kamar::whereIn('id', $kamarIds)->delete();
            }
            $lantai->delete();
        });

        $this->clearLantaiCache();

        return redirect()->route('fasilitas.kelola.index', ['tab' => 'lantai', 'gedung_id' => $gedungId])
            ->with('success', "Lantai {$nomorLantai} beserta seluruh kamarnya berhasil dihapus.");
    }

    private function clearLantaiCache(): void
    {
        Cache::forget('all_gedung_tree');
        Cache::forget('total_lantai_count');
        Cache::forget('total_kamar_count');
        Cache::forget('dashboard_stats');
    }
}
