<?php

namespace App\Http\Controllers\Fasilitas;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KelolaController extends Controller
{
    /**
     * Tampilan Kelola Asrama (Tab-scoped Lazy Querying: HANYA memuat tab aktif demi kecepatan maksimal)
     */
    public function index(Request $request): \Illuminate\View\View
    {
        // 1. Sanitasi & Whitelist Input Filter (Anti-URL Manipulation & Parameter Tampering)
        $activeTab = in_array($request->query('tab'), ['gedung', 'lantai', 'kamar']) ? $request->query('tab') : 'gedung';
        $viewMode = in_array($request->query('view'), ['per_lantai', 'table', 'daftar']) ? $request->query('view') : 'per_lantai';
        $selectedGender = in_array($request->query('gender'), ['L', 'P']) ? $request->query('gender') : null;
        $selectedTipeRuang = in_array($request->query('tipe_ruang'), ['kamar', 'mudabbir']) ? $request->query('tipe_ruang') : null;
        $selectedStatus = in_array($request->query('status'), ['tersedia', 'penuh', 'perbaikan', 'kosong']) ? $request->query('status') : null;
        $search = mb_substr(trim(strip_tags((string) $request->query('search'))), 0, 50);

        // 2. Validasi Integritas ID & Hierarki Relasional (Parent-Child Integrity Guardrail)
        $selectedGedungId = filter_var($request->query('gedung_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $selectedLantaiId = filter_var($request->query('lantai_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        if ($selectedGedungId && !Gedung::where('id', $selectedGedungId)->exists()) {
            $selectedGedungId = null;
        }

        if ($selectedLantaiId) {
            $lantai = Lantai::find($selectedLantaiId);
            if (!$lantai) {
                $selectedLantaiId = null;
            } elseif ($selectedGedungId && (int) $lantai->gedung_id !== (int) $selectedGedungId) {
                // Manipulasi URL terdeteksi: lantai_id bukan merupakan anak dari gedung_id!
                // Netralkan lantai_id demi menjaga integritas data kueri
                $selectedLantaiId = null;
            } elseif (!$selectedGedungId) {
                // Auto-sinkron parent jika lantai_id sah tapi gedung_id kosong
                $selectedGedungId = $lantai->gedung_id;
            }
        }

        $activePeriode = \App\Models\Periode::getActive();

        // 1. Data referensi dropdown (Ringan & di-cache)
        $allGedung = Cache::remember('all_gedung_tree', 3600, function () {
            return Gedung::where('is_active', true)->with(['lantai' => fn($q) => $q->orderBy('nomor_lantai', 'asc')])->get();
        });

        $totalLantaiCount = Cache::remember('total_lantai_count', 3600, fn() => Lantai::whereHas('gedung', fn($q) => $q->where('is_active', true))->count());
        $totalKamarCount = Cache::remember('total_kamar_count', 3600, fn() => Kamar::whereHas('lantai.gedung', fn($q) => $q->where('is_active', true))->count());

        $gedungList = collect();
        $lantaiList = collect();
        $kamarList = new LengthAwarePaginator([], 0, 24);
        $roomStats = (object) [
            'total_kamar' => 0,
            'total_bed' => 0,
            'total_terisi' => 0,
            'total_perbaikan' => 0,
            'total_penuh' => 0,
            'total_kosong' => 0,
            'total_tersedia' => 0,
        ];
        $totalPenghuni = 0;

        // 2. Query Tab 1: Gedung (Hanya dieksekusi jika tab gedung dibuka)
        if ($activeTab === 'gedung') {
            $gedungQuery = Gedung::where('is_active', true)
                ->with(['lantai' => fn($lq) => $lq->orderBy('nomor_lantai', 'asc')->withCount('kamar')])
                ->withCount('lantai');

            if ($selectedGedungId) {
                $gedungQuery->where('id', $selectedGedungId);
            }

            $gedungList = $gedungQuery->get();
        }

        // 3. Query Tab 2: Lantai (Hanya dieksekusi jika tab lantai dibuka)
        elseif ($activeTab === 'lantai') {
            $lantaiQuery = Lantai::whereHas('gedung', fn($q) => $q->where('is_active', true))
                ->with(['gedung', 'kamar' => function ($kq) {
                    $kq->with('penempatanAktif');
                }])->withCount('kamar');

            if ($selectedGedungId) {
                $lantaiQuery->where('gedung_id', $selectedGedungId);
            }

            $lantaiList = $lantaiQuery->orderBy('gedung_id', 'asc')->orderBy('nomor_lantai', 'asc')->get();
        }

        // 4. Query Tab 3: Kamar (Hanya dieksekusi jika tab kamar dibuka)
        elseif ($activeTab === 'kamar') {
            $occupantsSub = DB::table('mahad_penempatan_kamar')
                ->where('status', 'aktif')
                ->when($activePeriode, fn($q) => $q->where('periode_id', $activePeriode->id))
                ->groupBy('kamar_id')
                ->select('kamar_id', DB::raw('COUNT(*) as occupants'));

            if ($viewMode === 'per_lantai') {
                $gedungQuery = Gedung::where('is_active', true)->with(['lantai' => function ($lq) use ($selectedLantaiId, $selectedGender, $selectedTipeRuang, $selectedStatus, $search) {
                    $lq->orderBy('nomor_lantai', 'asc');
                    if ($selectedLantaiId) {
                        $lq->where('id', $selectedLantaiId);
                    }
                    $lq->with(['kamar' => function ($kq) use ($selectedGender, $selectedTipeRuang, $selectedStatus, $search) {
                        $kq->orderBy('nomor_kamar', 'asc')->with('penempatanAktif.santri.user');
                        if ($selectedGender) $kq->where('gender', $selectedGender);
                        if ($selectedTipeRuang) $kq->where('tipe_ruang', $selectedTipeRuang);
                        if ($selectedStatus === 'perbaikan') {
                            $kq->where('status', 'perbaikan');
                        } elseif ($selectedStatus === 'tersedia') {
                            $kq->where('status', '!=', 'perbaikan')
                               ->has('penempatanAktif', '<', DB::raw('kapasitas_maks'));
                        } elseif ($selectedStatus === 'penuh') {
                            $kq->where('status', '!=', 'perbaikan')
                               ->has('penempatanAktif', '>=', DB::raw('kapasitas_maks'));
                        } elseif ($selectedStatus === 'kosong') {
                            $kq->where('status', '!=', 'perbaikan')
                               ->doesntHave('penempatanAktif');
                        }
                        if ($search !== '') {
                            $kq->where('nomor_kamar', 'like', "%{$search}%");
                        }
                    }]);
                }])->withCount('lantai');

                if ($selectedGedungId) {
                    $gedungQuery->where('id', $selectedGedungId);
                }

                $gedungList = $gedungQuery->get();
            } else {
                $kamarQuery = Kamar::query()
                    ->select('mahad_kamar.*')
                    ->whereHas('lantai.gedung', fn($q) => $q->where('is_active', true))
                    ->leftJoinSub($occupantsSub, 'occ', 'occ.kamar_id', '=', 'mahad_kamar.id')
                    ->with(['lantai.gedung', 'penempatanAktif.santri.user']);

                if ($selectedGedungId) {
                    $kamarQuery->whereHas('lantai', fn($q) => $q->where('gedung_id', $selectedGedungId));
                }
                if ($selectedLantaiId) {
                    $kamarQuery->where('mahad_kamar.lantai_id', $selectedLantaiId);
                }
                if ($selectedGender) {
                    $kamarQuery->where('mahad_kamar.gender', $selectedGender);
                }
                if ($selectedTipeRuang) {
                    $kamarQuery->where('mahad_kamar.tipe_ruang', $selectedTipeRuang);
                }
                if ($selectedStatus) {
                    if ($selectedStatus === 'perbaikan') {
                        $kamarQuery->where('mahad_kamar.status', 'perbaikan');
                    } elseif ($selectedStatus === 'tersedia') {
                        $kamarQuery->where('mahad_kamar.status', '!=', 'perbaikan')
                            ->whereRaw('COALESCE(occ.occupants, 0) < mahad_kamar.kapasitas_maks');
                    } elseif ($selectedStatus === 'penuh') {
                        $kamarQuery->where('mahad_kamar.status', '!=', 'perbaikan')
                            ->whereRaw('COALESCE(occ.occupants, 0) >= mahad_kamar.kapasitas_maks');
                    } elseif ($selectedStatus === 'kosong') {
                        $kamarQuery->where('mahad_kamar.status', '!=', 'perbaikan')
                            ->whereRaw('COALESCE(occ.occupants, 0) = 0');
                    }
                }
                if ($search !== '') {
                    $kamarQuery->where('mahad_kamar.nomor_kamar', 'like', "%{$search}%");
                }

                $kamarList = $kamarQuery->orderBy('mahad_kamar.nomor_kamar', 'asc')->paginate(24)->withQueryString();
            }

            // Single Aggregated KPI Query khusus Tab Kamar (Optimized via single-pass leftJoinSub)
            $statsQuery = DB::table('mahad_kamar as k')
                ->join('mahad_lantai as l', 'k.lantai_id', '=', 'l.id')
                ->join('mahad_gedung as g', 'l.gedung_id', '=', 'g.id')
                ->where('g.is_active', true)
                ->leftJoinSub(
                    DB::table('mahad_penempatan_kamar')
                        ->where('status', 'aktif')
                        ->when($activePeriode, fn($q) => $q->where('periode_id', $activePeriode->id))
                        ->groupBy('kamar_id')
                        ->select('kamar_id', DB::raw('COUNT(*) as occupants')),
                    'p',
                    'p.kamar_id',
                    '=',
                    'k.id'
                );

            if ($selectedGedungId) $statsQuery->where('l.gedung_id', $selectedGedungId);
            if ($selectedLantaiId) $statsQuery->where('k.lantai_id', $selectedLantaiId);
            if ($selectedGender) $statsQuery->where('k.gender', $selectedGender);
            if ($selectedTipeRuang) $statsQuery->where('k.tipe_ruang', $selectedTipeRuang);

            $roomStats = $statsQuery->selectRaw('
                COUNT(*) as total_kamar,
                COALESCE(SUM(k.kapasitas_maks), 0) as total_bed,
                COALESCE(SUM(p.occupants), 0) as total_terisi,
                COALESCE(SUM(CASE WHEN k.status = "perbaikan" THEN 1 ELSE 0 END), 0) as total_perbaikan,
                COALESCE(SUM(CASE WHEN k.status != "perbaikan" AND COALESCE(p.occupants, 0) >= k.kapasitas_maks THEN 1 ELSE 0 END), 0) as total_penuh,
                COALESCE(SUM(CASE WHEN k.status != "perbaikan" AND COALESCE(p.occupants, 0) = 0 THEN 1 ELSE 0 END), 0) as total_kosong,
                COALESCE(SUM(CASE WHEN k.status != "perbaikan" AND COALESCE(p.occupants, 0) < k.kapasitas_maks THEN 1 ELSE 0 END), 0) as total_tersedia
            ')->first();

            $totalPenghuni = (int) ($roomStats->total_terisi ?? 0);
        }

        $stats = $roomStats;

        return view('fasilitas.kelola.index', compact(
            'activeTab',
            'viewMode',
            'gedungList',
            'allGedung',
            'totalLantaiCount',
            'totalKamarCount',
            'lantaiList',
            'kamarList',
            'roomStats',
            'stats',
            'totalPenghuni',
            'selectedGedungId',
            'selectedLantaiId',
            'selectedGender',
            'selectedTipeRuang',
            'selectedStatus',
            'search'
        ));
    }
}
