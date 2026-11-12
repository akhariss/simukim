<?php

namespace App\Http\Controllers\Kelompok;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kelompok\AssignTutorRequest;
use App\Http\Requests\Kelompok\BatchKelompokRequest;
use App\Http\Requests\Kelompok\StoreKelompokRequest;
use App\Http\Requests\Kelompok\UpdateKelompokRequest;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Periode;
use App\Models\User;
use App\Services\KelompokExportService;
use App\Services\KelompokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class KelompokController extends Controller
{
    public function __construct(
        private readonly KelompokService $service,
        private readonly KelompokExportService $exportService
    ) {}

    public function index(Request $request): View
    {
        $periode = Periode::getActive();

        if (! $periode) {
            return view('kelompok.index', [
                'periode'                    => null,
                'kelompokList'               => collect(),
                'stats'                      => (object) ['total_kelompok' => 0, 'total_kapasitas' => 0, 'total_santri_terbagi' => 0],
                'santriTidakBerkelompokCount' => 0,
                'search'                     => '',
                'filterKapasitas'            => '',
                'filterTutor'                => '',
                'totalKajian'                => 0,
            ]);
        }

        $search          = trim((string) $request->input('search'));
        $filterKapasitas = trim((string) ($request->input('filter_kapasitas') ?? $request->input('filter_kuota')));
        $filterTutor     = trim((string) $request->input('filter_tutor'));
        $totalKajian     = Kelas::where('periode_id', $periode->id)->where('is_active', true)->count();

        $query = Kelompok::query()
            ->where('periode_id', $periode->id)
            ->with(['pengampu.kelas', 'pengampu.tutor'])
            ->withCount('anggota');

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Smart Filter Kapasitas Santri
        if ($filterKapasitas === 'penuh') {
            $query->has('anggota', '>=', DB::raw('mahad_kelompok.kapasitas'));
        } elseif ($filterKapasitas === 'tersedia') {
            $query->has('anggota', '<', DB::raw('mahad_kelompok.kapasitas'))->has('anggota', '>', 0);
        } elseif ($filterKapasitas === 'kosong') {
            $query->doesntHave('anggota');
        }

        // Smart Filter Penugasan Tutor
        if ($filterTutor === 'lengkap') {
            if ($totalKajian > 0) {
                $query->has('pengampu', '>=', $totalKajian);
            } else {
                $query->has('pengampu', '>', 0);
            }
        } elseif ($filterTutor === 'belum_lengkap') {
            if ($totalKajian > 0) {
                $query->has('pengampu', '>', 0)->has('pengampu', '<', $totalKajian);
            }
        } elseif ($filterTutor === 'kosong') {
            $query->doesntHave('pengampu');
        }

        $kelompokList = $query->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $stats                       = $this->service->getKelompokStats($periode->id);
        $santriTidakBerkelompokCount = $this->service->getSantriTidakBerkelompokCount($periode->id);

        return view('kelompok.index', compact(
            'periode', 'kelompokList', 'stats', 'santriTidakBerkelompokCount', 'search', 'filterKapasitas', 'filterTutor', 'totalKajian'
        ));
    }

    public function store(StoreKelompokRequest $request): RedirectResponse
    {
        $periode = Periode::getActive() ?? Periode::firstOrFail();
        $data    = array_merge($request->validated(), ['periode_id' => $periode->id]);

        $this->service->createKelompok($data);

        return redirect()->route('kelompok.index')
            ->with('success', 'Kelompok baru berhasil ditambahkan.');
    }

    public function batch(BatchKelompokRequest $request): RedirectResponse
    {
        $periode = Periode::getActive() ?? Periode::firstOrFail();
        $v       = $request->validated();

        $result = $this->service->batchCreateKelompok(
            periodeId:  $periode->id,
            prefix:     $v['prefix'],
            jumlah:     (int) $v['jumlah'],
            nomorMulai: (int) $v['nomor_mulai'],
            kapasitas:  (int) $v['kapasitas'],
        );

        $msg = "Batch Generator: {$result['created']} kelompok berhasil dibuat.";
        if ($result['skipped'] > 0) {
            $msg .= " ({$result['skipped']} nama dilewati karena sudah ada sebelumnya).";
        }

        return redirect()->route('kelompok.index')->with('success', $msg);
    }

    public function show(Kelompok $kelompok): View
    {
        $kelompok->load([
            'periode',
            'anggota.santri.user',
            'anggota.santri.penempatanAktif.kamar.lantai.gedung',
            'pengampu.kelas.jadwal',
            'pengampu.tutor',
        ]);

        // Daftar Master Kelas aktif untuk periode ini
        $masterKelasList = Kelas::where('periode_id', $kelompok->periode_id)
            ->where('is_active', true)
            ->with('jadwal')
            ->orderBy('tipe')
            ->get();

        // Daftar Tutor Dosen yang tersedia
        $tutors = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['tutor', 'admin']))
            ->orderBy('name')
            ->get(['id', 'name', 'nomor_induk']);

        // Kelompok lain untuk opsi pindah santri
        $otherKelompokList = Kelompok::where('periode_id', $kelompok->periode_id)
            ->where('id', '!=', $kelompok->id)
            ->where('is_active', true)
            ->withCount('anggota')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'kapasitas']);

        return view('kelompok.show', compact('kelompok', 'masterKelasList', 'tutors', 'otherKelompokList'));
    }

    public function update(UpdateKelompokRequest $request, Kelompok $kelompok): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        if ($request->filled('deskripsi') && empty($data['keterangan'])) {
            $data['keterangan'] = $request->input('deskripsi');
        }

        $this->service->updateKelompok($kelompok, $data);

        return redirect()->route('kelompok.show', $kelompok)
            ->with('success', 'Pengaturan kelompok berhasil diperbarui.');
    }

    public function destroy(Kelompok $kelompok): RedirectResponse
    {
        try {
            $this->service->deleteKelompok($kelompok);
            return redirect()->route('kelompok.index')
                ->with('success', 'Kelompok berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function assignTutor(AssignTutorRequest $request, Kelompok $kelompok): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->service->assignTutor($kelompok, (int) $validated['kelas_id'], (int) $validated['tutor_id']);
            return back()->with('success', 'Tutor pengampu kajian berhasil ditugaskan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function export(string $format = 'excel'): Response
    {
        $periode = Periode::getActive() ?? Periode::firstOrFail();

        $kelompokList = Kelompok::where('periode_id', $periode->id)
            ->with(['anggota.santri.user', 'pengampu.kelas', 'pengampu.tutor'])
            ->orderBy('nama')
            ->get();

        return $this->exportService->exportExcel($kelompokList, $periode);
    }
}
