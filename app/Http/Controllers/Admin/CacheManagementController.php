<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClearCacheRequest;
use App\Http\Requests\Admin\SwitchPeriodeRequest;
use App\Http\Requests\Admin\SyncSahajaRequest;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use App\Services\CampusAuthService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class CacheManagementController extends Controller
{
    public function index(): View
    {
        $cachePath = storage_path('framework/cache/data');
        $viewsPath = storage_path('framework/views');

        // Ringkasan ukuran cache lokal (di-cache 10 menit agar instan 0ms)
        $cacheStats = Cache::remember('admin_cache_dir_stats', 600, fn() => $this->getDirectoryStats($cachePath));
        $viewsStats = Cache::remember('admin_views_dir_stats', 600, fn() => $this->getDirectoryStats($viewsPath));

        $activePeriode = Periode::getActive() ?? Periode::orderByDesc('is_active')->first();
        $activePeriodeId = $activePeriode?->id ?? 0;

        // Agregasi lokal cepat tanpa memblokir jaringan eksternal (< 5ms)
        $localStats = Cache::remember("admin_cache_local_stats_{$activePeriodeId}", 60, function () use ($activePeriodeId) {
            $totalSantri = Santri::where('periode_id', $activePeriodeId)->count();
            $assignedSantri = PenempatanKamar::where('status', 'aktif')->where('periode_id', $activePeriodeId)->count();
            $unassignedSantri = max(0, $totalSantri - $assignedSantri);
            $lunasSantri = Santri::where('periode_id', $activePeriodeId)->where('status_pembayaran', 'Lunas')->count();
            $belumLunasSantri = max(0, $totalSantri - $lunasSantri);
            $ikhwanSantri = Santri::where('periode_id', $activePeriodeId)->where('gender', 'L')->count();
            $akhwatSantri = Santri::where('periode_id', $activePeriodeId)->where('gender', 'P')->count();

            return [
                'total_santri'       => $totalSantri,
                'assigned_santri'    => $assignedSantri,
                'unassigned_santri'  => $unassignedSantri,
                'lunas_santri'       => $lunasSantri,
                'belum_lunas_santri' => $belumLunasSantri,
                'ikhwan_santri'      => $ikhwanSantri,
                'akhwat_santri'      => $akhwatSantri,
            ];
        });

        // Sahaja Summary: Snapshot cepat ter-cache (600 detik) untuk perbandingan pangkalan data
        $sahajaSummary = Cache::remember("sahaja_summary_snapshot_{$activePeriodeId}", 600, function () use ($activePeriode) {
            if (!$activePeriode?->kode) return null;
            return app(\App\Services\CampusApiService::class)->fetchSahajaSummary($activePeriode->kode);
        });

        $lastSyncTime = Cache::get('sahaja_last_sync_time');
        $lastSyncCount = Cache::get('sahaja_last_sync_count');
        $periodes = Periode::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.cache.index', compact(
            'cacheStats', 'viewsStats', 'activePeriode', 'localStats', 'sahajaSummary', 'lastSyncTime', 'lastSyncCount', 'periodes'
        ));
    }

    public function syncSahaja(SyncSahajaRequest $request, CampusAuthService $authService): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $kode = !empty($validated['kode_periode']) ? $validated['kode_periode'] : null;
            $filters = [];
            if (!empty($validated['status_pembayaran']) && $validated['status_pembayaran'] !== 'all') {
                $filters['status_pembayaran'] = $validated['status_pembayaran'];
            }
            if (!empty($validated['gender']) && $validated['gender'] !== 'all') {
                $filters['gender'] = $validated['gender'];
            }

            $result = $authService->syncAllMuqimMahasiswa($kode, $filters);
            Cache::forget("sahaja_students_{$kode}");
            Cache::forget('admin_cache_dir_stats');
            Cache::forget('admin_views_dir_stats');

            $activePeriode = Periode::getActive();
            if ($activePeriode) {
                Cache::forget("admin_cache_local_stats_{$activePeriode->id}");
                Cache::forget("sahaja_summary_snapshot_{$activePeriode->id}");
            }

            session()->flash('reconciliation_report', $result);

            $nowStr = now()->translatedFormat('d M Y, H:i');
            Cache::put('sahaja_last_sync_time', $nowStr, 86400 * 30);
            Cache::put('sahaja_last_sync_count', $result['synced'] ?? 0, 86400 * 30);

            $pName = $result['periode']->nama ?? 'Semester Aktif';
            $pKode = $result['periode']->kode ?? '';

            return back()->with('status', "Sinkronisasi berhasil! {$result['synced']} data mahasantri kampus berhasil diperbarui ({$result['new']} baru, {$result['updated']} diperbarui) pada semester {$pName} ({$pKode}).");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyinkronkan data mahasantri kampus: ' . $e->getMessage());
        }
    }

    public function clear(ClearCacheRequest $request): RedirectResponse
    {
        $type = $request->validated('type');

        switch ($type) {
            case 'all':
            case 'quick_optimize':
                Artisan::call('app:clean-cache', ['--all' => true]);
                Cache::flush();
                $msg = 'Aplikasi berhasil dipercepat! Seluruh memori sementara dan berkas kompilasi telah dibersihkan.';
                break;
            case 'data':
                Cache::flush();
                $msg = 'Data memori sementara berhasil dibersihkan.';
                break;
            case 'views':
                Artisan::call('view:clear');
                $msg = 'Tampilan aplikasi berhasil disegarkan.';
                break;
            case 'routes':
                Artisan::call('route:clear');
                $msg = 'Navigasi sistem berhasil disegarkan.';
                break;
            case 'config':
                Artisan::call('config:clear');
                $msg = 'Konfigurasi aplikasi berhasil disegarkan.';
                break;
            case 'key':
                $key = $request->validated('key');
                if ($key) Cache::forget($key);
                $msg = "Cache key '{$key}' berhasil dihapus.";
                break;
            case 'prune':
            default:
                Artisan::call('app:clean-cache', ['--prune' => true]);
                $msg = 'Berkas sementara yang sudah usang berhasil dibersihkan.';
        }

        Cache::forget('admin_cache_dir_stats');
        Cache::forget('admin_views_dir_stats');

        $target = !str_starts_with($request->path(), 'dashboard') ? url('/admin/cache') : route('admin.cache.index');
        return redirect($target)->with('success', $msg);
    }

    public function switchPeriode(SwitchPeriodeRequest $request): RedirectResponse
    {
        $id = (int) $request->validated('periode_id');
        Periode::where('id', $id)->update(['is_active' => true]);
        Periode::where('id', '!=', $id)->update(['is_active' => false]);
        Cache::forget('active_periode');
        Cache::forget('penempatan_kpi_counts');

        $p = Periode::find($id);
        return back()->with('status', "Semester aktif berhasil dialihkan ke: {$p->nama} ({$p->kode}).");
    }

    private function getDirectoryStats(string $path): array
    {
        if (!File::isDirectory($path)) return ['count' => 0, 'size_formatted' => '0 KB', 'bytes' => 0];
        $files = File::allFiles($path);
        $totalBytes = 0;
        foreach ($files as $file) { $totalBytes += $file->getSize(); }
        return [
            'count' => count($files),
            'bytes' => $totalBytes,
            'size_formatted' => $this->formatBytes($totalBytes),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 2) . ' MB' : ($bytes >= 1024 ? number_format($bytes / 1024, 2) . ' KB' : "{$bytes} B");
    }
}
