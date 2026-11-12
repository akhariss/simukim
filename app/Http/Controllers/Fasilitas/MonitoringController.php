<?php

namespace App\Http\Controllers\Fasilitas;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Tampilan Monitoring Asrama (Read-only Watch: isi/kosong, cari kamar & monitoring kapasitas)
     */
    public function index(Request $request): View
    {
        // 1. Sanitasi & Whitelist Input Filter (Anti-URL Manipulation)
        $genderFilter = in_array($request->query('gender'), ['L', 'P']) ? $request->query('gender') : null;
        $rawTipeRuang = $request->query('tipe_ruang');
        if ($rawTipeRuang === 'reguler') {
            $rawTipeRuang = 'kamar';
        }
        $validTipeRuang = array_map(fn($case) => $case->value, \App\Enums\TipeRuang::cases());
        $tipeRuangFilter = in_array($rawTipeRuang, $validTipeRuang, true) ? $rawTipeRuang : null;
        $statusFilter = in_array($request->query('status'), ['tersedia', 'penuh', 'perbaikan', 'kosong']) ? $request->query('status') : null;
        $search = mb_substr(trim(strip_tags((string) $request->query('search'))), 0, 50);

        // 2. Validasi Integritas ID & Hierarki Relasional (Parent-Child Integrity Guardrail)
        $gedungId = filter_var($request->query('gedung_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $lantaiId = filter_var($request->query('lantai_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        if ($gedungId && !Gedung::where('id', $gedungId)->exists()) {
            $gedungId = null;
        }

        if ($lantaiId) {
            $lantai = Lantai::find($lantaiId);
            if (!$lantai) {
                $lantaiId = null;
            } elseif ($gedungId && (int) $lantai->gedung_id !== (int) $gedungId) {
                // Manipulasi URL terdeteksi: lantai_id bukan merupakan anak dari gedung_id!
                $lantaiId = null;
            } elseif (!$gedungId) {
                $gedungId = $lantai->gedung_id;
            }
        }

        // 1. Data Gedung & Lantai untuk filter & navigasi (Cached)
        $gedungList = \Illuminate\Support\Facades\Cache::remember('active_gedung_with_lantai', 3600, function () {
            return Gedung::where('is_active', true)->with(['lantai' => fn($q) => $q->orderBy('nomor_lantai', 'asc')])->get();
        });

        // 2. Metrik Utama Kapasitas & Okupansi (Optimized via single-pass leftJoinSub)
        $statsKamarQuery = DB::table('mahad_kamar as k')
            ->join('mahad_lantai as l', 'k.lantai_id', '=', 'l.id')
            ->join('mahad_gedung as g', 'l.gedung_id', '=', 'g.id')
            ->leftJoinSub(
                DB::table('mahad_penempatan_kamar')->where('status', 'aktif')->groupBy('kamar_id')->select('kamar_id', DB::raw('COUNT(*) as occupants')),
                'p',
                'p.kamar_id',
                '=',
                'k.id'
            )
            ->where('g.is_active', true);

        if ($gedungId) $statsKamarQuery->where('l.gedung_id', $gedungId);
        if ($lantaiId) $statsKamarQuery->where('k.lantai_id', $lantaiId);
        if ($genderFilter) $statsKamarQuery->where('k.gender', $genderFilter);
        if ($tipeRuangFilter) $statsKamarQuery->where('k.tipe_ruang', $tipeRuangFilter);

        $this->applyStatusFilter($statsKamarQuery, $statusFilter, 'k');

        if ($search !== '') {
            $statsKamarQuery->where(function ($q) use ($search) {
                $q->where('k.nomor_kamar', 'like', "%{$search}%")
                  ->orWhereExists(function ($sub) use ($search) {
                      $sub->select(DB::raw(1))
                          ->from('mahad_penempatan_kamar')
                          ->join('mahad_santri', 'mahad_penempatan_kamar.santri_id', '=', 'mahad_santri.id')
                          ->join('users', 'mahad_santri.user_id', '=', 'users.id')
                          ->whereColumn('mahad_penempatan_kamar.kamar_id', 'k.id')
                          ->where('mahad_penempatan_kamar.status', 'aktif')
                          ->where(fn($uq) => $uq->where('users.name', 'like', "%{$search}%")->orWhere('mahad_santri.nomor_induk', 'like', "%{$search}%"));
                  });
            });
        }

        $stats = $statsKamarQuery->selectRaw('
            COUNT(*) as total_kamar,
            COUNT(DISTINCT l.gedung_id) as total_gedung,
            COALESCE(SUM(k.kapasitas_maks), 0) as total_kapasitas,
            COALESCE(SUM(CASE WHEN k.status = "perbaikan" THEN 1 ELSE 0 END), 0) as kamar_perbaikan,
            COALESCE(SUM(p.occupants), 0) as total_terisi
        ')->first();

        $totalGedung = (int) ($stats->total_gedung ?? 0);
        $totalKamar = (int) ($stats->total_kamar ?? 0);
        $totalKapasitas = (int) ($stats->total_kapasitas ?? 0);
        $totalTerisi = (int) ($stats->total_terisi ?? 0);
        $sisaBed = max(0, $totalKapasitas - $totalTerisi);
        $okupansiPersen = $totalKapasitas > 0 ? round(($totalTerisi / $totalKapasitas) * 100) : 0;
        $kamarPerbaikan = (int) ($stats->kamar_perbaikan ?? 0);
        $hasActiveFilter = (bool) ($gedungId || $lantaiId || $genderFilter || $tipeRuangFilter || $statusFilter || $search !== '');

        // 3. Query Kamar untuk Visualisasi & Tabel Monitoring
        $kamarQuery = Kamar::query()->with(['lantai.gedung', 'penempatanAktif.santri.user']);

        if ($gedungId) $kamarQuery->whereHas('lantai', fn($q) => $q->where('gedung_id', $gedungId));
        if ($lantaiId) $kamarQuery->where('lantai_id', $lantaiId);
        if ($genderFilter) $kamarQuery->where('gender', $genderFilter);
        if ($tipeRuangFilter) $kamarQuery->where('tipe_ruang', $tipeRuangFilter);

        $this->applyStatusFilter($kamarQuery, $statusFilter);

        if ($search !== '') {
            $kamarQuery->where(fn($q) => $q->where('nomor_kamar', 'like', "%{$search}%")
                ->orWhereHas('penempatanAktif.santri.user', fn($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('nomor_induk', 'like', "%{$search}%")));
        }

        $perPage = in_array((int) $request->query('per_page'), [12, 24, 48, 96]) ? (int) $request->query('per_page') : 24;
        $kamarList = $kamarQuery->orderBy('nomor_kamar', 'asc')->paginate($perPage)->withQueryString();

        return view('fasilitas.monitoring.index', compact(
            'gedungList', 'totalGedung', 'totalKamar', 'totalKapasitas', 'totalTerisi',
            'sisaBed', 'okupansiPersen', 'kamarPerbaikan', 'hasActiveFilter', 'kamarList',
            'gedungId', 'lantaiId', 'genderFilter', 'tipeRuangFilter',
            'statusFilter', 'search', 'perPage'
        ));
    }

    /**
     * Endpoint JSON detail kamar untuk modal interaktif
     */
    public function roomDetail(Kamar $kamar): JsonResponse
    {
        $kamar->load(['lantai.gedung', 'penempatanAktif.santri.user']);

        $penghuni = $kamar->penempatanAktif->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->santri->user->name,
            'nomor_induk' => $p->santri->nomor_induk,
            'prodi' => $p->santri->prodi,
            'angkatan' => $p->santri->angkatan,
            'no_hp' => $p->santri->no_hp,
            'tanggal_masuk' => $p->tanggal_masuk ? $p->tanggal_masuk->format('d M Y') : '-',
        ]);

        $terisi = $kamar->penempatanAktif->count();
        $kapasitas = (int) ($kamar->kapasitas_maks ?: 4);
        $sisa = max(0, $kapasitas - $terisi);

        $genderVal = $kamar->gender->value ?? $kamar->gender;
        $statusVal = $kamar->status->value ?? (string) $kamar->status;
        $tipeRuangLabel = $kamar->tipe_ruang instanceof \App\Enums\TipeRuang
            ? $kamar->tipe_ruang->label()
            : ($kamar->tipe_ruang ? ucfirst((string) $kamar->tipe_ruang) : 'Kamar Santri');

        return response()->json([
            'id' => $kamar->id,
            'nomor_kamar' => $kamar->nomor_kamar,
            'gedung' => $kamar->lantai->gedung->nama ?? 'Gedung Asrama',
            'lantai' => $kamar->lantai->nomor_lantai ?? 1,
            'gender' => $genderVal === 'L' ? 'Ikhwan' : 'Akhwat',
            'kapasitas_maks' => $kapasitas,
            'terisi' => $terisi,
            'sisa' => $sisa,
            'sisa_bed' => $sisa,
            'status' => $statusVal,
            'tipe_ruang' => $tipeRuangLabel,
            'catatan' => $kamar->catatan,
            'penghuni' => $penghuni,
        ]);
    }

    /**
     * Helper reusable untuk filter status okupansi kamar
     */
    private function applyStatusFilter($query, ?string $status, string $table = 'mahad_kamar'): void
    {
        if (!$status) return;

        $isAlias = ($table === 'k');
        $statusCol = $isAlias ? 'k.status' : 'mahad_kamar.status';

        if ($status === 'perbaikan') {
            $query->where($statusCol, 'perbaikan');
        } elseif ($status === 'tersedia') {
            if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
                $query->where($statusCol, '!=', 'perbaikan')
                      ->has('penempatanAktif', '<', DB::raw('kapasitas_maks'));
            } else {
                $query->where($statusCol, '!=', 'perbaikan')
                      ->whereRaw($isAlias ? 'COALESCE(p.occupants, 0) < k.kapasitas_maks' : 'COALESCE(p.occupants, 0) < mahad_kamar.kapasitas_maks');
            }
        } elseif ($status === 'penuh') {
            if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
                $query->where($statusCol, '!=', 'perbaikan')
                      ->has('penempatanAktif', '>=', DB::raw('kapasitas_maks'));
            } else {
                $query->where($statusCol, '!=', 'perbaikan')
                      ->whereRaw($isAlias ? 'COALESCE(p.occupants, 0) >= k.kapasitas_maks' : 'COALESCE(p.occupants, 0) >= mahad_kamar.kapasitas_maks');
            }
        } elseif ($status === 'kosong') {
            if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
                $query->where($statusCol, '!=', 'perbaikan')
                      ->doesntHave('penempatanAktif');
            } else {
                $query->where($statusCol, '!=', 'perbaikan')
                      ->whereRaw('COALESCE(p.occupants, 0) = 0');
            }
        }
    }
}
