<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Ma'had Al-Jami'ah
|--------------------------------------------------------------------------
*/

// Landing Page Publik (Halaman Utama)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Gatekeeper Berkas Sensitif Terproteksi (Foto Santri, Surat Bukti Izin, Dokumentasi)
Route::get('/secure-file/{category}/{filename}', [SecureFileController::class, 'show'])
    ->name('secure.file')
    ->middleware(['auth']);

// Authenticated Routes (Harus Login)
Route::middleware(['auth'])->group(function () {

    // Profil Pengguna (Edit, Update, Destroy)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── DASHBOARD UNIFIED PORTAL HIERARCHY ─────────────────────────────────
    Route::prefix('dashboard')->group(function () {
        // Beranda Dashboard Ringkasan
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Manajemen Pengguna (Khusus Role Admin)
        Route::prefix('admin/users')->name('users.')->middleware(['role:admin'])->group(function () {
            Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\UserController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('store');
            Route::post('/store-local', [\App\Http\Controllers\UserController::class, 'storeLocal'])->name('store.local');
            Route::get('/campus-lookup', [\App\Http\Controllers\UserCampusSyncController::class, 'lookupCampus'])->name('campus.lookup');
            Route::post('/assign-santri', [\App\Http\Controllers\UserCampusSyncController::class, 'assignCampusSantri'])->name('assign.santri');
            Route::post('/assign-mudabbir', [\App\Http\Controllers\UserCampusSyncController::class, 'assignCampusMudabbir'])->name('assign.mudabbir');
            Route::post('/assign-tutor', [\App\Http\Controllers\UserCampusSyncController::class, 'assignCampusTutor'])->name('assign.tutor');
            Route::post('/sync-santri', [\App\Http\Controllers\UserCampusSyncController::class, 'syncAllMuqimMahasiswa'])->name('sync.santri');
            Route::post('/import', [\App\Http\Controllers\UserImportController::class, 'import'])->name('import');
            Route::get('/import/template', [\App\Http\Controllers\UserImportController::class, 'downloadTemplate'])->name('import.template');
            Route::patch('/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleSantriStatus'])->name('toggle.status');
            Route::get('/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('edit');
            Route::get('/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('show');
            Route::put('/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
        });

        // Manajemen Sistem & Cache (Khusus Role Admin)
        Route::prefix('admin/cache')->name('admin.cache.')->middleware(['role:admin'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CacheManagementController::class, 'index'])->name('index');
            Route::post('/clear', [\App\Http\Controllers\Admin\CacheManagementController::class, 'clear'])->name('clear');
            Route::post('/switch-periode', [\App\Http\Controllers\Admin\CacheManagementController::class, 'switchPeriode'])->name('periode.switch');
            Route::post('/sync-sahaja', [\App\Http\Controllers\Admin\CacheManagementController::class, 'syncSahaja'])->name('sahaja.sync');
            Route::post('/reset-database', [\App\Http\Controllers\Admin\DatabaseResetController::class, 'reset'])->name('reset.database');
        });

        // Fasilitas Asrama (Monitoring, Kelola, Penempatan)
        Route::prefix('fasilitas')->name('fasilitas.')->group(function () {
            // Monitoring Asrama
            Route::prefix('monitoring')->name('monitoring.')->middleware(['role:admin|mudabbir|tutor'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Fasilitas\MonitoringController::class, 'index'])->name('index');
                Route::get('/room/{kamar}', [\App\Http\Controllers\Fasilitas\MonitoringController::class, 'roomDetail'])->name('room');
            });

            // Kelola Asrama (Khusus Role Admin)
            Route::prefix('kelola')->name('kelola.')->middleware(['role:admin'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Fasilitas\KelolaController::class, 'index'])->name('index');
                Route::post('/gedung', [\App\Http\Controllers\Fasilitas\GedungController::class, 'store'])->name('gedung.store');
                Route::put('/gedung/{gedung}', [\App\Http\Controllers\Fasilitas\GedungController::class, 'update'])->name('gedung.update');
                Route::delete('/gedung/{gedung}', [\App\Http\Controllers\Fasilitas\GedungController::class, 'destroy'])->name('gedung.destroy');
                Route::post('/lantai', [\App\Http\Controllers\Fasilitas\LantaiController::class, 'store'])->name('lantai.store');
                Route::put('/lantai/{lantai}', [\App\Http\Controllers\Fasilitas\LantaiController::class, 'update'])->name('lantai.update');
                Route::delete('/lantai/{lantai}', [\App\Http\Controllers\Fasilitas\LantaiController::class, 'destroy'])->name('lantai.destroy');
                Route::post('/kamar', [\App\Http\Controllers\Fasilitas\KamarController::class, 'store'])->name('kamar.store');
                Route::put('/kamar/{kamar}', [\App\Http\Controllers\Fasilitas\KamarController::class, 'update'])->name('kamar.update');
                Route::delete('/kamar/{kamar}', [\App\Http\Controllers\Fasilitas\KamarController::class, 'destroy'])->name('kamar.destroy');
                Route::post('/kamar-batch', [\App\Http\Controllers\Fasilitas\KamarController::class, 'batch'])->name('kamar.batch');
            });

            // Penempatan Mahasantri (Admin & Mudabbir)
            Route::prefix('penempatan')->name('penempatan.')->middleware(['role:admin|mudabbir'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'index'])->name('index');
                Route::get('/available-rooms', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'getAvailableRooms'])->name('available-rooms');
                Route::get('/unassigned-santri', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'getUnassignedSantri'])->name('unassigned-santri');
                Route::post('/assign', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'assign'])->name('assign');
                Route::post('/assign-batch', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'assignBatch'])->name('assign-batch');
                Route::post('/auto-assign', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'autoAssign'])->name('auto-assign');
                Route::post('/{penempatan}/pindah', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'pindah'])->name('pindah');
                Route::post('/{penempatan}/keluar', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'keluar'])->name('keluar');
                Route::get('/{penempatan}/kartu', [\App\Http\Controllers\Fasilitas\PenempatanController::class, 'cetakKartu'])->name('kartu');
            });
        });

        // ─── KELOMPOK MAHASANTRI (ROMBEL PENEMPATAN TES) ────────────────────
        Route::prefix('kelompok')->name('kelompok.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Kelompok\KelompokController::class, 'index'])->name('index')->middleware(['role:admin|tutor|mudabbir']);
            Route::post('/', [\App\Http\Controllers\Kelompok\KelompokController::class, 'store'])->name('store')->middleware(['role:admin']);
            Route::post('/batch', [\App\Http\Controllers\Kelompok\KelompokController::class, 'batch'])->name('batch')->middleware(['role:admin']);
            Route::get('/export/{format}', [\App\Http\Controllers\Kelompok\KelompokController::class, 'export'])->name('export')->middleware(['role:admin']);
            Route::get('/import/template', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'downloadTemplate'])->name('import.template')->middleware(['role:admin']);
            Route::get('/{kelompok}', [\App\Http\Controllers\Kelompok\KelompokController::class, 'show'])->name('show')->middleware(['role:admin|tutor|mudabbir']);
            Route::put('/{kelompok}', [\App\Http\Controllers\Kelompok\KelompokController::class, 'update'])->name('update')->middleware(['role:admin']);
            Route::delete('/{kelompok}', [\App\Http\Controllers\Kelompok\KelompokController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);

            // Detail Kajian & Kelola Tutor Pengampu per Kelompok (Halaman Khusus)
            Route::get('/{kelompok}/kajian/{kelas}', [\App\Http\Controllers\Kelompok\KelompokKajianController::class, 'show'])->name('kajian.show')->middleware(['role:admin|tutor|mudabbir']);
            Route::post('/{kelompok}/kajian/{kelas}/assign-tutor', [\App\Http\Controllers\Kelompok\KelompokKajianController::class, 'assignTutor'])->name('kajian.assign.tutor')->middleware(['role:admin']);
            Route::delete('/{kelompok}/kajian/{kelas}/unassign-tutor', [\App\Http\Controllers\Kelompok\KelompokKajianController::class, 'unassignTutor'])->name('kajian.unassign.tutor')->middleware(['role:admin']);

            // Penugasan Tutor Pengampu per Kajian di Kelompok (Direct)
            Route::post('/{kelompok}/assign-tutor', [\App\Http\Controllers\Kelompok\KelompokController::class, 'assignTutor'])->name('assign.tutor')->middleware(['role:admin']);

            // Anggota Kelompok
            Route::get('/{kelompok}/anggota/tambah', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'create'])->name('anggota.create')->middleware(['role:admin']);
            Route::post('/{kelompok}/anggota/bulk', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'storeBulk'])->name('anggota.bulk')->middleware(['role:admin']);
            Route::post('/{kelompok}/anggota', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'store'])->name('anggota.store')->middleware(['role:admin']);
            Route::post('/{kelompok}/anggota/import', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'import'])->name('anggota.import')->middleware(['role:admin']);
            Route::post('/{kelompok}/anggota/{santri}/pindah', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'pindah'])->name('anggota.pindah')->middleware(['role:admin']);
            Route::delete('/{kelompok}/anggota/{santri}', [\App\Http\Controllers\Kelompok\KelompokAnggotaController::class, 'destroy'])->name('anggota.destroy')->middleware(['role:admin']);
        });

        // ─── KELAS & AKADEMIK KAJIAN ─────────────────────────────────────────
        Route::prefix('kelas')->name('kelas.')->group(function () {
            // Portal Akademik Santri (Jadwal, Presensi, Nilai Mahasantri)
            Route::get('/santri', [\App\Http\Controllers\Kelas\SantriKelasController::class, 'index'])
                ->name('santri.index')
                ->middleware(['role:mahasantri|admin|mudabbir']);

            // Master Kajian & Jadwal Sesi Pertemuan (Khusus Admin)
            Route::prefix('master')->name('master.')->middleware(['role:admin'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'store'])->name('store');
                Route::get('/{kelas}/edit', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'edit'])->name('edit');
                Route::put('/{kelas}', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'update'])->name('update');
                Route::delete('/{kelas}', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'destroy'])->name('destroy');
                Route::post('/{kelas}/jadwal', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'storeJadwal'])->name('jadwal.store');
                Route::put('/jadwal/{jadwal}', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'updateJadwal'])->name('jadwal.update');
                Route::delete('/jadwal/{jadwal}', [\App\Http\Controllers\Kelas\MasterKelasController::class, 'destroyJadwal'])->name('jadwal.destroy');
            });

            // Presensi Harian Santri per Kelompok & Sesi Kelas
            Route::get('/absensi', [\App\Http\Controllers\Kelas\AbsensiController::class, 'index'])->name('absensi.index')->middleware(['role:admin|tutor']);
            Route::post('/absensi', [\App\Http\Controllers\Kelas\AbsensiController::class, 'store'])->name('absensi.store')->middleware(['role:admin|tutor']);
            Route::post('/absensi/copy', [\App\Http\Controllers\Kelas\AbsensiController::class, 'copyYesterday'])->name('absensi.copy')->middleware(['role:admin|tutor']);
            Route::get('/absensi/rekap', [\App\Http\Controllers\Kelas\AbsensiController::class, 'rekap'])->name('absensi.rekap')->middleware(['role:admin|tutor']);

            // Evaluasi & Penilaian
            Route::get('/nilai', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'index'])->name('nilai.index')->middleware(['role:admin|tutor']);
            Route::get('/nilai/export/{format}', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'export'])->name('nilai.export')->middleware(['role:admin|tutor']);
            Route::put('/nilai/{penilaian}', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'update'])->name('nilai.update')->middleware(['role:admin|tutor']);
            Route::post('/nilai/{kelompok}/{kelas}/bulk', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'bulkUpdate'])->name('nilai.bulk')->middleware(['role:admin|tutor']);
            Route::post('/nilai/komponen', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'storeKomponen'])->name('nilai.komponen.store')->middleware(['role:admin|tutor']);
            Route::put('/nilai/komponen/{komponen}', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'updateKomponen'])->name('nilai.komponen.update')->middleware(['role:admin|tutor']);
            Route::delete('/nilai/komponen/{komponen}', [\App\Http\Controllers\Kelas\KelolaKelasNilaiController::class, 'destroyKomponen'])->name('nilai.komponen.destroy')->middleware(['role:admin|tutor']);

            // Laporan Bulanan Tutor
            Route::get('/laporan', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'index'])->name('laporan.index')->middleware(['role:admin|tutor']);
            Route::get('/laporan/kelas/{kelompok}/{kelas}', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'showKelas'])->name('laporan.kelas')->middleware(['role:admin|tutor']);
            Route::get('/laporan/kelas/{kelompok}/{kelas}/create', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'create'])->name('laporan.create')->middleware(['role:admin|tutor']);
            Route::post('/laporan', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'store'])->name('laporan.store')->middleware(['role:admin|tutor']);
            Route::get('/laporan/{laporan}', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'show'])->name('laporan.show')->middleware(['role:admin|tutor']);
            Route::get('/laporan/{laporan}/edit', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'edit'])->name('laporan.edit')->middleware(['role:admin|tutor']);
            Route::put('/laporan/{laporan}', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'update'])->name('laporan.update')->middleware(['role:admin|tutor']);
            Route::delete('/laporan/{laporan}', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'destroy'])->name('laporan.destroy')->middleware(['role:admin|tutor']);
            Route::patch('/laporan/{laporan}/status', [\App\Http\Controllers\Kelas\KelolaLaporanController::class, 'updateStatus'])->name('laporan.status')->middleware(['role:admin']);
        });

        // Agenda & Kegiatan Asrama
        Route::prefix('kegiatan')->name('kegiatan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Kegiatan\KegiatanController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Kegiatan\KegiatanController::class, 'store'])->name('store')->middleware(['role:admin|mudabbir']);
            Route::put('/{kegiatan}', [\App\Http\Controllers\Kegiatan\KegiatanController::class, 'update'])->name('update')->middleware(['role:admin|mudabbir']);
            Route::delete('/{kegiatan}', [\App\Http\Controllers\Kegiatan\KegiatanController::class, 'destroy'])->name('destroy')->middleware(['role:admin|mudabbir']);
            Route::patch('/{kegiatan}/toggle', [\App\Http\Controllers\Kegiatan\KegiatanController::class, 'toggleStatus'])->name('toggle')->middleware(['role:admin|mudabbir']);
        });

        // Kedisiplinan & Perizinan Mahasantri
        Route::prefix('kedisiplinan')->name('kedisiplinan.')->group(function () {
            // Perizinan (Hanya Admin, Mudabbir, dan Mahasantri)
            Route::prefix('perizinan')->name('perizinan.')->middleware(['role:admin|mudabbir|mahasantri'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Kedisiplinan\PerizinanController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Kedisiplinan\PerizinanController::class, 'store'])->name('store');
                Route::get('/export', [\App\Http\Controllers\Kedisiplinan\PerizinanController::class, 'export'])->name('export')->middleware(['role:admin|mudabbir']);
                Route::patch('/{perizinan}/status', [\App\Http\Controllers\Kedisiplinan\PerizinanController::class, 'updateStatus'])->name('status')->middleware(['role:admin|mudabbir']);
                Route::delete('/{perizinan}', [\App\Http\Controllers\Kedisiplinan\PerizinanController::class, 'destroy'])->name('destroy');
                Route::get('/{perizinan}/cetak', [\App\Http\Controllers\Kedisiplinan\PerizinanController::class, 'cetak'])->name('cetak');
            });

            // Pelanggaran (Admin & Mudabbir)
            Route::prefix('pelanggaran')->name('pelanggaran.')->middleware(['role:admin|mudabbir'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Kedisiplinan\PelanggaranController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Kedisiplinan\PelanggaranController::class, 'store'])->name('store');
                Route::get('/export', [\App\Http\Controllers\Kedisiplinan\PelanggaranController::class, 'export'])->name('export');
                Route::put('/{pelanggaran}', [\App\Http\Controllers\Kedisiplinan\PelanggaranController::class, 'update'])->name('update');
                Route::patch('/{pelanggaran}/status', [\App\Http\Controllers\Kedisiplinan\PelanggaranController::class, 'updateStatus'])->name('status');
                Route::delete('/{pelanggaran}', [\App\Http\Controllers\Kedisiplinan\PelanggaranController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);
            });
        });

        // Portal Khusus Tutor (Kajian Saya & Kedisiplinan Terpadu)
        Route::prefix('tutor')->name('tutor.')->middleware(['role:admin|tutor'])->group(function () {
            Route::get('/kajian', [\App\Http\Controllers\Tutor\TutorKajianController::class, 'index'])->name('kajian.index');
            Route::get('/kajian/{kelompok}/{kelas}', [\App\Http\Controllers\Tutor\TutorKajianController::class, 'show'])->name('kajian.show');
            Route::get('/kedisiplinan', [\App\Http\Controllers\Tutor\TutorKedisiplinanController::class, 'index'])->name('kedisiplinan.index');
        });

        // Pusat Pelaporan & Ekspor Terpadu (Khusus Role Admin)
        Route::prefix('pelaporan')->name('pelaporan.')->middleware(['role:admin'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Pelaporan\PelaporanController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Pelaporan\PelaporanController::class, 'export'])->name('export');
        });
    });

    // ─── LEGACY URL COMPATIBILITY ALIASES (Bebas Error untuk Test & Bookmark) ───
    Route::get('/admin/users', fn() => redirect()->route('users.index'));
    Route::get('/admin/cache', [\App\Http\Controllers\Admin\CacheManagementController::class, 'index'])->middleware(['role:admin']);
    Route::post('/admin/cache/clear', [\App\Http\Controllers\Admin\CacheManagementController::class, 'clear'])->middleware(['role:admin']);
    Route::post('/admin/cache/switch-periode', [\App\Http\Controllers\Admin\CacheManagementController::class, 'switchPeriode'])->middleware(['role:admin']);
    Route::post('/admin/cache/sync-sahaja', [\App\Http\Controllers\Admin\CacheManagementController::class, 'syncSahaja'])->middleware(['role:admin']);
    Route::post('/admin/cache/reset-database', [\App\Http\Controllers\Admin\DatabaseResetController::class, 'reset'])->middleware(['role:admin']);
    Route::get('/fasilitas/monitoring', fn() => redirect()->route('fasilitas.monitoring.index'));
    Route::get('/fasilitas/kelola', fn() => redirect()->route('fasilitas.kelola.index'));
    Route::get('/fasilitas/penempatan', fn() => redirect()->route('fasilitas.penempatan.index'));
    Route::get('/kegiatan', [\App\Http\Controllers\Kegiatan\KegiatanController::class, 'index']);
    Route::get('/kedisiplinan/perizinan', fn() => redirect()->route('kedisiplinan.perizinan.index'));
    Route::get('/kedisiplinan/pelanggaran', fn() => redirect()->route('kedisiplinan.pelanggaran.index'));
    Route::get('/pelaporan', fn() => redirect()->route('pelaporan.index'));
});

// Laravel Breeze Auth Routes (login, register, forgot-password, logout, dll)
require __DIR__.'/auth.php';
