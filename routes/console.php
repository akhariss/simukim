<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mahad:cek-santri {nim?}', function (?string $nim = null) {
    $query = \App\Models\User::with(['santri', 'roles']);
    if ($nim) {
        $query->where('nomor_induk', trim($nim));
    } else {
        $query->whereHas('santri');
    }
    $data = $query->latest('id')->take(10)->get();
    $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
})->purpose('Cek data JSON lengkap pengguna dan santri yang tersimpan di database');

/*
|--------------------------------------------------------------------------
| Scheduled Maintenance Tasks & Cron Jobs
|--------------------------------------------------------------------------
*/

// Pemangkasan otomatis berkas file cache yang sudah expired setiap hari pukul 02:00
Schedule::command('app:clean-cache --prune')->dailyAt('02:00');

// Optimasi query cache tags dan session database
Schedule::command('cache:prune-stale-tags')->hourly();
