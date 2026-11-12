<?php

namespace App\Http\Controllers\Fasilitas;

use App\Enums\KamarStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fasilitas\BatchKamarRequest;
use App\Http\Requests\Fasilitas\StoreKamarRequest;
use App\Http\Requests\Fasilitas\UpdateKamarRequest;
use App\Models\Kamar;
use App\Models\Lantai;
use App\Models\PenempatanKamar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KamarController extends Controller
{
    /**
     * Simpan Kamar Tunggal
     */
    public function store(StoreKamarRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $exists = Kamar::where('lantai_id', $validated['lantai_id'])
            ->where('nomor_kamar', trim($validated['nomor_kamar']))
            ->exists();

        if ($exists) {
            return back()->with('error', "Nomor Kamar '{$validated['nomor_kamar']}' sudah ada pada lantai ini.");
        }

        Kamar::create([
            'lantai_id' => $validated['lantai_id'],
            'nomor_kamar' => trim($validated['nomor_kamar']),
            'gender' => $validated['gender'],
            'kapasitas_maks' => $validated['kapasitas_maks'],
            'tipe_ruang' => $validated['tipe_ruang'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        $this->clearKamarCache();

        return back()->with('success', "Kamar {$validated['nomor_kamar']} berhasil ditambahkan.");
    }

    /**
     * Update Data Kamar
     */
    public function update(UpdateKamarRequest $request, Kamar $kamar): RedirectResponse
    {
        $validated = $request->validated();

        $exists = Kamar::where('lantai_id', $kamar->lantai_id)
            ->where('nomor_kamar', trim($validated['nomor_kamar']))
            ->where('id', '!=', $kamar->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "Nomor Kamar '{$validated['nomor_kamar']}' sudah digunakan kamar lain pada lantai ini.");
        }

        $kamar->update([
            'nomor_kamar' => trim($validated['nomor_kamar']),
            'gender' => $validated['gender'],
            'kapasitas_maks' => $validated['kapasitas_maks'],
            'tipe_ruang' => $validated['tipe_ruang'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        $this->clearKamarCache();

        return back()->with('success', "Data Kamar {$kamar->nomor_kamar} berhasil diperbarui.");
    }

    /**
     * Hapus Kamar (Anti-Data Yatim / Cascade)
     */
    public function destroy(Kamar $kamar): RedirectResponse
    {
        $nomor = $kamar->nomor_kamar;
        DB::transaction(function () use ($kamar) {
            PenempatanKamar::where('kamar_id', $kamar->id)->delete();
            $kamar->delete();
        });

        $this->clearKamarCache();

        return back()->with('success', "Kamar {$nomor} berhasil dihapus dari sistem.");
    }

    /**
     * Batch Generator Kamar Massal
     */
    public function batch(BatchKamarRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $lantai = Lantai::with('gedung')->findOrFail($validated['lantai_id']);
        $prefix = trim($validated['prefix']);
        $start = (int) $validated['nomor_mulai'];
        $end = (int) $validated['nomor_selesai'];
        $pad = (int) $validated['padding'];

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($lantai, $prefix, $start, $end, $pad, $validated, &$created, &$skipped) {
            for ($i = $start; $i <= $end; $i++) {
                $nomorKamar = $prefix . str_pad($i, $pad, '0', STR_PAD_LEFT);

                $exists = Kamar::where('lantai_id', $lantai->id)
                    ->where('nomor_kamar', $nomorKamar)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Kamar::create([
                    'lantai_id' => $lantai->id,
                    'nomor_kamar' => $nomorKamar,
                    'gender' => $validated['gender'],
                    'kapasitas_maks' => $validated['kapasitas_maks'],
                    'tipe_ruang' => $validated['tipe_ruang'],
                    'status' => KamarStatus::Tersedia->value,
                    'catatan' => 'Dibuat via Batch Generator',
                ]);
                $created++;
            }
        });

        $msg = "Batch Generator: {$created} unit kamar berhasil digenerate di {$lantai->gedung->nama} Lantai {$lantai->nomor_lantai}.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} nomor dilewati karena sudah ada sebelumnya).";
        }

        $this->clearKamarCache();

        return redirect()->route('fasilitas.kelola.index', [
            'tab' => 'kamar',
            'gedung_id' => $lantai->gedung_id,
            'lantai_id' => $lantai->id
        ])->with('success', $msg);
    }

    private function clearKamarCache(): void
    {
        Cache::forget('all_gedung_tree');
        Cache::forget('total_kamar_count');
        Cache::forget('total_lantai_count');
        Cache::forget('dashboard_stats');
    }
}
