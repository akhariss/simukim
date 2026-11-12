<?php

namespace App\Http\Controllers\Kelompok;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kelompok\AddKelompokAnggotaRequest;
use App\Http\Requests\Kelompok\BulkAddKelompokAnggotaRequest;
use App\Http\Requests\Kelompok\ImportKelompokAnggotaRequest;
use App\Http\Requests\Kelompok\PindahKelompokRequest;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\Santri;
use App\Services\KelompokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KelompokAnggotaController extends Controller
{
    public function __construct(private readonly KelompokService $service) {}

    public function create(Request $request, Kelompok $kelompok): View
    {
        $kelompok->load('anggota');
        $periodeId = $kelompok->periode_id;

        // Santri yang sudah terdaftar di kelompok pada periode ini
        $enrolledInPeriod = KelompokAnggota::whereHas('kelompok', fn ($q) => $q->where('periode_id', $periodeId))
            ->pluck('santri_id')
            ->all();

        $enrolledInCurrent = $kelompok->anggota->pluck('santri_id')->all();

        // Hanya tampilkan mahasantri aktif yang BELUM terdaftar di kelompok manapun pada periode ini
        $santriQuery = Santri::query()
            ->where('status', 'aktif')
            ->whereDoesntHave('user', function ($q) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'tutor']));
            })
            ->when(! empty($enrolledInPeriod), fn ($q) => $q->whereNotIn('id', $enrolledInPeriod))
            ->with(['user', 'penempatanAktif.kamar.lantai.gedung'])
            ->orderBy('nomor_induk');

        $santriList = $santriQuery->get();

        $santriData = $santriList->map(function ($s) {
            $kamarText = $s->penempatanAktif?->kamar
                ? (($s->penempatanAktif->kamar->lantai->gedung->nama ?? '') . ' - ' . $s->penempatanAktif->kamar->nomor_kamar)
                : 'Belum ada kamar';

            return [
                'id'     => $s->id,
                'nim'    => $s->nomor_induk,
                'name'   => $s->user->name ?? '-',
                'gender' => $s->gender?->value ?? '',
                'prodi'  => $s->prodi ?? 'Umum',
                'kamar'  => $kamarText,
            ];
        })->values();

        $sisaSlot = max(0, $kelompok->kapasitas - count($enrolledInCurrent));
        $isFreshVisit = ! $request->hasAny(['search', 'gender', 'page']);

        return view('kelompok.anggota-create', compact(
            'kelompok',
            'santriList',
            'santriData',
            'enrolledInCurrent',
            'sisaSlot',
            'isFreshVisit'
        ));
    }

    public function store(AddKelompokAnggotaRequest $request, Kelompok $kelompok): RedirectResponse
    {
        $data = $request->validated();

        try {
            if (!empty($data['nim'])) {
                $santri = Santri::where('nomor_induk', $data['nim'])
                    ->orWhereHas('user', fn ($q) => $q->where('nomor_induk', $data['nim']))
                    ->first();

                if (!$santri) {
                    return back()->with('error', "Mahasantri dengan NIM/Nomor Induk '{$data['nim']}' tidak ditemukan.");
                }
                if ($santri->user?->hasAnyRole(['admin', 'tutor'])) {
                    return back()->with('error', "Pengguna '{$santri->user?->name}' memiliki peran Staf/Admin/Tutor dan tidak dapat didaftarkan sebagai santri kelompok.");
                }
                $data['santri_id'] = $santri->id;
            }

            $this->service->addAnggota($kelompok, (int) $data['santri_id']);

            return redirect()->route('kelompok.show', $kelompok)
                ->with('success', 'Mahasantri berhasil didaftarkan ke kelompok.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeBulk(BulkAddKelompokAnggotaRequest $request, Kelompok $kelompok): RedirectResponse
    {
        $santriIds = $request->validated()['santri_ids'];

        try {
            $result = $this->service->addAnggotaBulk($kelompok, $santriIds);
            $msg = "{$result['added']} mahasantri berhasil didaftarkan ke kelompok {$kelompok->nama}.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} santri dilewati karena sudah terdaftar di kelompok lain atau kuota penuh).";
            }
            return redirect()->route('kelompok.show', $kelompok)->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Kelompok $kelompok, Santri $santri): RedirectResponse
    {
        try {
            $this->service->removeAnggota($kelompok, $santri->id);
            return redirect()->route('kelompok.show', $kelompok)
                ->with('success', 'Mahasantri berhasil dikeluarkan dari kelompok.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pindah(PindahKelompokRequest $request, Kelompok $kelompok, Santri $santri): RedirectResponse
    {
        $targetId = (int) $request->validated()['target_kelompok_id'];

        try {
            $targetKelompok = Kelompok::findOrFail($targetId);
            $santriName = $santri->user?->name ?? $santri->nomor_induk;

            $this->service->pindahKelompok($santri->id, $targetId);

            return redirect()->route('kelompok.show', $kelompok)
                ->with('success', "Mahasantri {$santriName} berhasil dipindahkan dari {$kelompok->nama} ke {$targetKelompok->nama}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function import(ImportKelompokAnggotaRequest $request, Kelompok $kelompok): RedirectResponse
    {
        $file = $request->file('file_import');
        $handle = fopen($file->getRealPath(), 'r');

        $added = 0;
        $skipped = 0;
        $rowNum = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNum++;
            if ($rowNum === 1) {
                continue; // Skip header
            }

            $nim = trim((string) ($row[0] ?? ''));
            if ($nim === '') {
                continue;
            }

            $santri = Santri::where('nomor_induk', $nim)->first();
            if (!$santri) {
                $skipped++;
                continue;
            }

            try {
                $this->service->addAnggota($kelompok, $santri->id);
                $added++;
            } catch (\Exception) {
                $skipped++;
            }
        }
        fclose($handle);

        $msg = "Import selesai: {$added} mahasantri berhasil dimasukkan ke kelompok.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} baris dilewati karena sudah memiliki kelompok atau NIM tidak valid).";
        }

        return redirect()->route('kelompok.show', $kelompok)->with('success', $msg);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $csvContent = "NIM,NAMA (Opsional)\n23101001,Ahmad Fulan\n23101002,Budi Santoso\n";

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, 'template-import-anggota-kelompok.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-import-anggota-kelompok.csv"',
        ]);
    }
}
