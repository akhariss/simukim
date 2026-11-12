<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kelas\StoreKomponenNilaiRequest;
use App\Http\Requests\Kelas\UpdatePenilaianRequest;
use App\Models\Kelas;
use App\Models\KelasKomponenNilai;
use App\Models\Kelompok;
use App\Models\Penilaian;
use App\Models\Periode;
use App\Services\PenilaianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class KelolaKelasNilaiController extends Controller
{
    public function __construct(private readonly PenilaianService $penilaianService) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $isSaya = ($user->hasRole('tutor') && !$user->hasRole('admin')) || $request->query('mode') === 'saya';
        $hasSelected = $request->filled('kelompok_id') && $request->filled('kelas_id');
        $periodeAktif = Periode::getActive() ?? Periode::orderByDesc('is_active')->first();
        $allPeriode = Periode::orderByDesc('is_active')->orderByDesc('id')->get();

        $selectedKelompok = null;
        $selectedKelas = null;
        $anggotaList = collect();
        $penilaianMap = collect();
        $komponenList = collect();
        $autoAbsensiMap = collect();
        $totalSesi = 0;
        $periode = $periodeAktif;

        if ($hasSelected) {
            $selectedKelompok = Kelompok::findOrFail($request->kelompok_id);
            $selectedKelas = Kelas::findOrFail($request->kelas_id);
            $periode = $selectedKelompok->periode ?? $periodeAktif;

            $sheet = $this->penilaianService->getSheetData($selectedKelompok, $selectedKelas, $user->id, $isSaya);
            $anggotaList = $sheet['anggotaList'];
            $komponenList = $sheet['komponenList'];
            $totalSesi = $sheet['totalSesi'];
            $autoAbsensiMap = $sheet['autoAbsensiMap'];
            $penilaianMap = $sheet['penilaianMap'];
            $myKajianList = collect();
        } else {
            $query = DB::table('mahad_kelompok_pengampu')
                ->join('mahad_kelompok', 'mahad_kelompok_pengampu.kelompok_id', '=', 'mahad_kelompok.id')
                ->join('mahad_kelas', 'mahad_kelompok_pengampu.kelas_id', '=', 'mahad_kelas.id')
                ->join('mahad_periode', 'mahad_kelompok.periode_id', '=', 'mahad_periode.id');

            if ($isSaya) {
                $query->where('mahad_kelompok_pengampu.tutor_id', $user->id)
                      ->where('mahad_kelompok.periode_id', $periodeAktif?->id);
            }

            $rawItems = $query->select(
                'mahad_kelompok_pengampu.id as pengampu_id',
                'mahad_kelompok.id as kelompok_id',
                'mahad_kelompok.nama as kelompok_nama',
                'mahad_kelompok.periode_id as periode_id',
                'mahad_periode.nama as periode_nama',
                'mahad_kelas.id as kelas_id',
                'mahad_kelas.nama as kelas_nama',
                'mahad_kelas.tipe as kelas_tipe'
            )->orderBy('mahad_kelompok.nama')->get();

            $kelompokIds = $rawItems->pluck('kelompok_id')->unique()->toArray();
            $kelasIds = $rawItems->pluck('kelas_id')->unique()->toArray();

            $santriCounts = DB::table('mahad_kelompok_anggota')
                ->whereIn('kelompok_id', $kelompokIds)
                ->groupBy('kelompok_id')
                ->select('kelompok_id', DB::raw('count(*) as total'))
                ->pluck('total', 'kelompok_id');

            $nilaiCounts = DB::table('mahad_penilaian')
                ->whereIn('kelompok_id', $kelompokIds)
                ->whereIn('kelas_id', $kelasIds)
                ->whereNotNull('nilai_angka')
                ->groupBy('kelompok_id', 'kelas_id')
                ->selectRaw('kelompok_id, kelas_id, count(*) as total')
                ->get()->keyBy(fn ($r) => $r->kelompok_id . '_' . $r->kelas_id);

            $jadwalGrouped = DB::table('mahad_kelas_jadwal')
                ->whereIn('kelas_id', $kelasIds)
                ->get()->groupBy('kelas_id');

            $santriKeywords = DB::table('mahad_kelompok_anggota')
                ->join('mahad_santri', 'mahad_kelompok_anggota.santri_id', '=', 'mahad_santri.id')
                ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                ->whereIn('mahad_kelompok_anggota.kelompok_id', $kelompokIds)
                ->select('mahad_kelompok_anggota.kelompok_id', 'users.name', 'mahad_santri.nomor_induk')
                ->get()
                ->groupBy('kelompok_id')
                ->map(fn($g) => $g->map(fn($s) => strtolower($s->name . ' ' . $s->nomor_induk))->join(' '));

            $myKajianList = $rawItems->map(function ($item) use ($santriCounts, $nilaiCounts, $jadwalGrouped, $santriKeywords) {
                $totalSantri = $santriCounts->get($item->kelompok_id, 0);
                $key = $item->kelompok_id . '_' . $item->kelas_id;
                $sudahDinilai = $nilaiCounts->has($key) ? $nilaiCounts->get($key)->total : 0;
                $jadwals = $jadwalGrouped->get($item->kelas_id, collect());
                $jadwalText = $jadwals->isNotEmpty()
                    ? $jadwals->map(fn ($j) => $j->hari . ' (' . substr($j->jam_mulai, 0, 5) . ')')->unique()->join(', ')
                    : 'Sesuai Jadwal';

                return (object) [
                    'kelompok_id'     => $item->kelompok_id,
                    'kelompok_nama'   => $item->kelompok_nama,
                    'periode_id'      => $item->periode_id,
                    'periode_nama'    => $item->periode_nama,
                    'kelas_id'        => $item->kelas_id,
                    'kelas_nama'      => $item->kelas_nama,
                    'kelas_tipe'      => $item->kelas_tipe,
                    'total_santri'    => $totalSantri,
                    'sudah_dinilai'   => $sudahDinilai,
                    'jadwal_text'     => $jadwalText,
                    'santri_keywords' => $santriKeywords->get($item->kelompok_id, ''),
                ];
            });
        }

        $santriRaporList = (!$isSaya && !$hasSelected) ? $this->penilaianService->getSantriRaporList() : collect();

        return view('kelas.nilai.index', compact(
            'hasSelected', 'myKajianList', 'santriRaporList', 'selectedKelompok', 'selectedKelas',
            'anggotaList', 'penilaianMap', 'komponenList', 'autoAbsensiMap',
            'totalSesi', 'periode', 'allPeriode', 'isSaya'
        ));
    }

    public function update(UpdatePenilaianRequest $request, Penilaian $penilaian): RedirectResponse
    {
        $validated = $request->validated();

        $nilaiAngka = isset($validated['nilai_angka']) && $validated['nilai_angka'] !== ''
            ? (float) $validated['nilai_angka']
            : null;

        $nilaiHuruf = $validated['nilai_huruf'] ?? null;
        if ($nilaiAngka !== null && !$nilaiHuruf) {
            $nilaiHuruf = match (true) {
                $nilaiAngka >= 85 => 'A',
                $nilaiAngka >= 75 => 'B',
                $nilaiAngka >= 60 => 'C',
                $nilaiAngka >= 50 => 'D',
                default           => 'E',
            };
        }

        $penilaian->update([
            'nilai_angka' => $nilaiAngka,
            'nilai_huruf' => $nilaiHuruf,
            'catatan'     => $validated['catatan'] ?? $penilaian->catatan,
            'input_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Nilai santri berhasil diperbarui.');
    }

    public function bulkUpdate(Request $request, Kelompok $kelompok, Kelas $kelas): RedirectResponse
    {
        $this->penilaianService->saveBulk($kelompok, $kelas, $request->input('nilai', []), auth()->id());

        return redirect()->route('kelas.nilai.index', array_filter([
            'kelompok_id' => $kelompok->id,
            'kelas_id'    => $kelas->id,
            'mode'        => $request->query('mode'),
        ]))->with('success', "Seluruh nilai santri kelompok {$kelompok->nama} berhasil disimpan.");
    }

    public function storeKomponen(StoreKomponenNilaiRequest $request): RedirectResponse
    {
        $v = $request->validated();

        $maxUrutan = KelasKomponenNilai::where('kelas_id', $v['kelas_id'])
            ->where(fn ($q) => $q->where('kelompok_id', $v['kelompok_id'] ?? null)->orWhereNull('kelompok_id'))
            ->max('urutan') ?? 0;

        KelasKomponenNilai::create([
            'kelas_id'    => $v['kelas_id'],
            'kelompok_id' => $v['kelompok_id'] ?? null,
            'nama'        => $v['nama'],
            'bobot'       => $v['bobot'],
            'is_absensi'  => false,
            'is_fixed'    => false,
            'urutan'      => $maxUrutan + 1,
        ]);

        return back()->with('success', "Komponen nilai '{$v['nama']}' berhasil ditambahkan.");
    }

    public function updateKomponen(Request $request, KelasKomponenNilai $komponen): RedirectResponse
    {
        $v = $request->validate([
            'nama'  => 'required|string|max:100',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $komponen->update([
            'nama'  => trim($v['nama']),
            'bobot' => (float) $v['bobot'],
        ]);

        return back()->with('success', "Komponen penilaian '{$komponen->nama}' berhasil diperbarui.");
    }

    public function destroyKomponen(KelasKomponenNilai $komponen): RedirectResponse
    {
        if ($komponen->is_absensi || $komponen->is_fixed) {
            return back()->with('error', 'Komponen Absensi wajib ada dan tidak dapat dihapus.');
        }
        $nama = $komponen->nama;
        $komponen->delete();
        return back()->with('success', "Komponen nilai '{$nama}' berhasil dihapus.");
    }

    public function export(Request $request, string $format = 'excel'): Response
    {
        $periode = Periode::getActive() ?? Periode::firstOrFail();
        return $this->penilaianService->export(
            $periode,
            $request->query('kelompok_id'),
            $request->query('kelas_id'),
            $format
        );
    }
}
