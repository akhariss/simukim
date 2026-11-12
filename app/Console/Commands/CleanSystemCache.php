<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CleanSystemCache extends Command
{
    protected $signature = 'app:clean-cache 
                            {--all : Bersihkan seluruh cache aplikasi, view, route, dan config}
                            {--prune : Pangkas file cache yang sudah kedaluwarsa}
                            {--views : Bersihkan cache view yang terkompilasi}';

    protected $description = 'Pembersihan dan pemangkasan cache sistem aplikasi Ma\'had';

    public function handle(): int
    {
        $this->info('Memulai pembersihan cache...');

        if ($this->option('all')) {
            Cache::flush();
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            $this->info('Seluruh cache aplikasi, view, route, dan config berhasil dibersihkan.');
            return self::SUCCESS;
        }

        if ($this->option('views')) {
            Artisan::call('view:clear');
            $this->info('Cache Blade view terkompilasi berhasil dibersihkan.');
            return self::SUCCESS;
        }

        if ($this->option('prune')) {
            $this->pruneExpiredCacheFiles();
            $this->info('Pemangkasan file cache kedaluwarsa selesai.');
            return self::SUCCESS;
        }

        // Default: bersihkan data cache
        Cache::flush();
        $this->info('Data cache aplikasi berhasil dibersihkan.');
        return self::SUCCESS;
    }

    /**
     * Pangkas berkas cache driver "file" yang sudah expired berdasarkan timestamp payload
     */
    protected function pruneExpiredCacheFiles(): void
    {
        $cachePath = storage_path('framework/cache/data');
        if (!File::isDirectory($cachePath)) {
            return;
        }

        $now = time();
        $prunedCount = 0;

        $files = File::allFiles($cachePath);
        foreach ($files as $file) {
            try {
                $content = @file_get_contents($file->getRealPath());
                if ($content !== false && strlen($content) >= 10) {
                    $expire = (int) substr($content, 0, 10);
                    if ($expire > 0 && $expire < $now) {
                        @unlink($file->getRealPath());
                        $prunedCount++;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore transient locked files
            }
        }

        $this->line("Dihapus {$prunedCount} berkas cache yang kedaluwarsa.");
    }
}
