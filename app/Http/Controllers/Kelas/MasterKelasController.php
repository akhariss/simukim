<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kelas\StoreKelasJadwalRequest;
use App\Http\Requests\Kelas\StoreMasterKelasRequest;
use App\Http\Requests\Kelas\UpdateMasterKelasRequest;
use App\Models\Kelas;
use App\Models\KelasJadwal;
use App\Models\Periode;
use App\Services\MasterKelasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MasterKelasController extends Controller
{
    public function __construct(private readonly MasterKelasService $service) {}

    public function index(): View
    {
        $periode = Periode::getActive();

        if (! $periode) {
            return view('kelas.master.index', [
                'periode'   => null,
                'kelasList' => collect(),
            ]);
        }

        $kelasList = $this->service->getMasterKelasWithJadwal($periode->id);

        return view('kelas.master.index', compact('periode', 'kelasList'));
    }

    public function create(): View
    {
        $periode = Periode::getActive();

        return view('kelas.master.create', compact('periode'));
    }

    public function store(StoreMasterKelasRequest $request): RedirectResponse
    {
        $periode = Periode::getActive() ?? Periode::firstOrFail();
        $data = array_merge($request->validated(), ['periode_id' => $periode->id]);

        $this->service->createKelas($data);

        return redirect()->route('kelas.master.index')
            ->with('success', 'Kajian berhasil ditambahkan beserta jadwal sesi pertemuannya.');
    }

    public function edit(Kelas $kelas): View
    {
        $periode = Periode::getActive();
        $kelas->load(['jadwal' => fn ($q) => $q->orderBy('hari')->orderBy('jam_mulai')]);

        return view('kelas.master.edit', compact('kelas', 'periode'));
    }

    public function update(UpdateMasterKelasRequest $request, Kelas $kelas): RedirectResponse
    {
        $this->service->updateKelas($kelas, $request->validated());

        return redirect()->route('kelas.master.index')
            ->with('success', 'Data kajian beserta jadwal pertemuannya berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        try {
            $this->service->deleteKelas($kelas);
            return redirect()->route('kelas.master.index')
                ->with('success', 'Data kajian berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeJadwal(StoreKelasJadwalRequest $request, Kelas $kelas): RedirectResponse
    {
        $this->service->addJadwal($kelas, $request->validated());

        return redirect()->route('kelas.master.index')
            ->with('success', "Sesi jadwal pertemuan untuk {$kelas->nama} berhasil ditambahkan.");
    }

    public function updateJadwal(StoreKelasJadwalRequest $request, KelasJadwal $jadwal): RedirectResponse
    {
        $this->service->updateJadwal($jadwal, $request->validated());

        return redirect()->route('kelas.master.index')
            ->with('success', 'Sesi jadwal pertemuan berhasil diperbarui.');
    }

    public function destroyJadwal(KelasJadwal $jadwal): RedirectResponse
    {
        $this->service->deleteJadwal($jadwal);

        return redirect()->route('kelas.master.index')
            ->with('success', 'Sesi jadwal pertemuan berhasil dihapus.');
    }
}
