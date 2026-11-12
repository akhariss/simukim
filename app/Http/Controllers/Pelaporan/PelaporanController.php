<?php

namespace App\Http\Controllers\Pelaporan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelaporan\ExportPelaporanRequest;
use App\Models\Gedung;
use App\Models\Kelas;
use App\Models\Periode;
use App\Services\MasterPelaporanService;
use App\Services\PelaporanExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PelaporanController extends Controller
{
    public function __construct(
        protected PelaporanExportService $exportService,
        protected MasterPelaporanService $masterService
    ) {}

    public function index(Request $request): View
    {
        $activePeriode = Periode::getActive();
        $selectedPeriodeId = filter_var($request->query('periode_id'), FILTER_VALIDATE_INT) ?: ($activePeriode?->id ?? Periode::first()?->id);
        $selectedPeriode = Periode::find($selectedPeriodeId) ?? $activePeriode;

        $periodeList = Cache::remember('all_periodes_desc', 300, fn() => Periode::orderBy('tanggal_mulai', 'desc')->get());
        $kelasList = Kelas::where('periode_id', $selectedPeriode?->id)->where('is_active', true)->get();
        $gedungList = Cache::remember('active_gedung_simple', 300, fn() => Gedung::where('is_active', true)->orderBy('nama', 'asc')->get());

        $rentang = in_array($request->query('rentang'), ['periode', 'bulanan']) ? $request->query('rentang') : 'periode';
        $bulan = $request->query('bulan') ?: date('Y-m');
        $kategori = in_array($request->query('kategori'), ['semua', 'fasilitas', 'akademik', 'kedisiplinan', 'perizinan']) ? $request->query('kategori') : 'semua';

        return view('pelaporan.index', compact(
            'activePeriode',
            'selectedPeriode',
            'periodeList',
            'kelasList',
            'gedungList',
            'rentang',
            'bulan',
            'kategori'
        ));
    }

    public function export(ExportPelaporanRequest $request): Response|RedirectResponse
    {
        $validated = $request->validated();

        $activePeriodeId = (int) ($validated['periode_id'] ?? (Periode::getActiveId() ?? Periode::first()?->id ?? 0));
        $format = $validated['format'] ?? 'pdf';
        $rentang = $validated['rentang'] ?? 'periode';
        $bulan = ($rentang === 'bulanan' && !empty($validated['bulan'])) ? $validated['bulan'] : null;
        $kategori = $validated['kategori'] ?? null;
        $kelasId = !empty($validated['kelas_id']) ? (int) $validated['kelas_id'] : null;
        $gedungId = !empty($validated['gedung_id']) ? (int) $validated['gedung_id'] : null;
        $status = $validated['status'] ?? null;

        if (in_array($validated['tipe'], ['master', 'semua'])) {
            return $this->masterService->exportMaster($activePeriodeId, $rentang, $bulan, $format, $kategori);
        }

        return match ($validated['tipe']) {
            'kehadiran'   => $this->exportService->exportKehadiran($activePeriodeId, $kelasId, $format, $bulan),
            'okupansi'    => $this->exportService->exportOkupansi($gedungId, $format),
            'pelanggaran' => $this->exportService->exportPelanggaran($activePeriodeId, $status, $format, $bulan),
            'perizinan'   => $this->exportService->exportPerizinan($activePeriodeId, $status, $format, $bulan),
            'nilai'       => $this->exportService->exportNilai($activePeriodeId, $kelasId, $format),
            default       => back()->with('error', 'Tipe laporan tidak dikenali.'),
        };
    }
}
