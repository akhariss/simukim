<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_absensi MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alfa', 'terlambat', 'sakit_tambahan') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_absensi MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alfa', 'terlambat') NOT NULL");
        }
    }
};

