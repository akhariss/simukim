<?php

namespace App\Console\Commands;

use App\Models\PenempatanKamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResetSahajaSync extends Command
{
    protected $signature = 'sahaja:reset-sync {--force : Lewati konfirmasi}';
    protected $description = 'Bersihkan seluruh data mahasantri hasil sinkronisasi SAHAJA untuk pengujian ulang';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Apakah Anda yakin ingin menghapus seluruh data santri hasil sinkronisasi SAHAJA? Akun Admin dan Master Kamar tetap aman.')) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        $this->info('Memulai pembersihan data sinkronisasi SAHAJA...');

        DB::transaction(function () {
            // Ambil ID user dari kampus
            $campusUserIds = User::where('auth_source', 'campus')->pluck('id')->toArray();
            $campusSantriIds = Santri::whereIn('user_id', $campusUserIds)->pluck('id')->toArray();

            // 1. Hapus riwayat penempatan kamar santri kampus
            if (!empty($campusSantriIds)) {
                PenempatanKamar::whereIn('santri_id', $campusSantriIds)->delete();
            }

            // 2. Hapus data santri kampus
            Santri::whereIn('user_id', $campusUserIds)->delete();

            // 3. Hapus user kampus (kecuali admin)
            User::whereIn('id', $campusUserIds)
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                ->delete();

            // 4. Bersihkan cache
            Cache::forget('users_role_counts');
            Cache::forget('active_periode');
        });

        $this->info('Data santri SAHAJA berhasil dibersihkan!');
        $this->info('Kondisi sistem saat ini: Bersih (0 Santri Kampus).');
        $this->info('Anda sekarang bisa membuka Menu Cache / Rekonsiliasi dan menguji tombol "Tarik & Sinkronkan SAHAJA" kembali.');

        return self::SUCCESS;
    }
}

