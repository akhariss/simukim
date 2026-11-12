<?php

namespace App\Http\Controllers\Kegiatan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kegiatan\StoreKegiatanRequest;
use App\Http\Requests\Kegiatan\UpdateKegiatanRequest;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Periode;
use App\Services\KegiatanScheduleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function index(Request $request, KegiatanScheduleService $scheduleService): View
    {
        $activePeriode = Periode::getActive();

        $jenisFilter = $request->query('jenis');
        $search = trim((string) $request->query('search'));
        $bulan = (int) $request->query('bulan', Carbon::now()->month);
        $tahun = (int) $request->query('tahun', Carbon::now()->year);
        $viewMode = $request->query('view', 'calendar'); // Default to calendar view

        // 1. Single Aggregated KPI Query untuk Kegiatan Asrama
        $stats = DB::table('mahad_kegiatan')
            ->when($activePeriode, fn($q) => $q->where('periode_id', $activePeriode->id)->orWhereNull('periode_id'))
            ->selectRaw('COUNT(*) as total,
                COALESCE(SUM(CASE WHEN jenis = "rutin" THEN 1 ELSE 0 END), 0) as rutin,
                COALESCE(SUM(CASE WHEN jenis = "khusus" THEN 1 ELSE 0 END), 0) as khusus,
                COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as aktif')
            ->first() ?? (object) ['total' => 0, 'rutin' => 0, 'khusus' => 0, 'aktif' => 0];

        // 2. Query Kelas Akademik Sesuai Konteks Pengguna (Mahasantri, Tutor, atau Admin)
        $kelasItems = $scheduleService->buildKelasSchedule(auth()->user(), $activePeriode, $search);
        $totalKelas = $kelasItems->unique('kelas_id')->count();

        // Tentukan apakah list kelas ditampilkan sesuai filter jenis
        $activeKelasList = in_array($jenisFilter, ['rutin', 'khusus']) ? collect() : $kelasItems;

        // 4. Query Kegiatan Asrama
        $kegiatanQuery = Kegiatan::query()
            ->with(['createdBy', 'periode'])
            ->where(function ($q) use ($activePeriode) {
                if ($activePeriode) {
                    $q->where('periode_id', $activePeriode->id)->orWhereNull('periode_id');
                }
            });

        if ($jenisFilter === 'kelas') {
            $kegiatanQuery->whereRaw('1 = 0');
        } elseif ($jenisFilter && in_array($jenisFilter, ['rutin', 'khusus'])) {
            $kegiatanQuery->where('jenis', $jenisFilter);
        }

        if ($search !== '') {
            $kegiatanQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kegiatanList = $kegiatanQuery->orderBy('is_active', 'desc')
            ->orderByRaw("CASE WHEN jenis = 'khusus' THEN tanggal_mulai ELSE NULL END ASC")
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 5. Gabungkan Semua Event Aktif untuk Kalender
        $kegiatanItems = Kegiatan::active()
            ->where(function ($q) use ($activePeriode) {
                if ($activePeriode) {
                    $q->where('periode_id', $activePeriode->id)->orWhereNull('periode_id');
                }
            })
            ->get()
            ->map(function ($kg) {
                return (object) [
                    'id' => 'kegiatan-' . $kg->id,
                    'kegiatan_id' => $kg->id,
                    'nama' => $kg->nama,
                    'tipe' => 'kegiatan',
                    'jenis_code' => $kg->jenis->value,
                    'jenis_label' => $kg->jenis->label(),
                    'is_kelas' => false,
                    'is_active' => $kg->is_active,
                    'hari' => $kg->hari,
                    'jam_mulai' => $kg->jam_mulai ? substr($kg->jam_mulai, 0, 5) : null,
                    'jam_selesai' => $kg->jam_selesai ? substr($kg->jam_selesai, 0, 5) : null,
                    'lokasi' => $kg->lokasi,
                    'tutor_name' => null,
                    'kapasitas' => null,
                    'deskripsi' => $kg->deskripsi,
                    'tanggal_mulai' => $kg->tanggal_mulai ? $kg->tanggal_mulai->format('Y-m-d') : null,
                    'tanggal_selesai' => $kg->tanggal_selesai ? $kg->tanggal_selesai->format('Y-m-d') : null,
                ];
            });

        // Kalender memuat kedua sumber: Kelas Akademik & Kegiatan Asrama
        $allEvents = $kegiatanItems->concat($kelasItems);

        $schedule = $scheduleService->buildWeeklySchedule($activeKelasList, $kegiatanList);
        $dayList = $schedule['dayList'];
        $scheduleByDay = $schedule['scheduleByDay'];
        $khususList = $schedule['khususList'];

        return view('kegiatan.index', compact(
            'kegiatanList',
            'allEvents',
            'activeKelasList',
            'totalKelas',
            'stats',
            'activePeriode',
            'jenisFilter',
            'search',
            'bulan',
            'tahun',
            'viewMode',
            'dayList',
            'scheduleByDay',
            'khususList'
        ));
    }

    public function store(StoreKegiatanRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $activePeriode = Periode::getActive();

        DB::transaction(function () use ($validated, $activePeriode) {
            Kegiatan::create([
                'periode_id'      => $activePeriode?->id,
                'nama'            => $validated['nama'],
                'jenis'           => $validated['jenis'],
                'hari'            => $validated['hari'] ?? null,
                'jam_mulai'       => $validated['jam_mulai'] ?? null,
                'jam_selesai'     => $validated['jam_selesai'] ?? null,
                'lokasi'          => $validated['lokasi'] ?? null,
                'deskripsi'       => $validated['deskripsi'] ?? null,
                'tanggal_mulai'   => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'is_active'       => $validated['is_active'] ?? true,
                'created_by'      => Auth::id(),
            ]);
        });

        return redirect()->route('kegiatan.index')->with('success', 'Agenda kegiatan berhasil ditambahkan.');
    }

    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($kegiatan, $validated) {
            $kegiatan->update([
                'nama'            => $validated['nama'],
                'jenis'           => $validated['jenis'],
                'hari'            => $validated['hari'] ?? null,
                'jam_mulai'       => $validated['jam_mulai'] ?? null,
                'jam_selesai'     => $validated['jam_selesai'] ?? null,
                'lokasi'          => $validated['lokasi'] ?? null,
                'deskripsi'       => $validated['deskripsi'] ?? null,
                'tanggal_mulai'   => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'is_active'       => $validated['is_active'] ?? $kegiatan->is_active,
            ]);
        });

        return redirect()->route('kegiatan.index')->with('success', 'Agenda kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan): RedirectResponse
    {
        DB::transaction(fn() => $kegiatan->delete());
        return redirect()->route('kegiatan.index')->with('success', 'Agenda kegiatan berhasil dihapus.');
    }

    public function toggleStatus(Kegiatan $kegiatan): RedirectResponse
    {
        DB::transaction(fn() => $kegiatan->update(['is_active' => !$kegiatan->is_active]));
        $statusStr = $kegiatan->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status kegiatan '{$kegiatan->nama}' berhasil {$statusStr}.");
    }
}
