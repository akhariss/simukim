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
        // 1. Ubah tipe column ke string VARCHAR(50) agar fleksibel dengan PHP Enum (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_kelas MODIFY COLUMN tipe VARCHAR(50) NOT NULL DEFAULT 'kelompok'");
        }

        // 2. Migrasikan data lama (quran / kitab) menjadi kelompok
        DB::statement("UPDATE mahad_kelas SET tipe = 'kelompok' WHERE tipe IN ('quran', 'kitab')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_kelas MODIFY COLUMN tipe ENUM('quran', 'kitab', 'masal') NOT NULL DEFAULT 'kitab'");
        }
    }
};
