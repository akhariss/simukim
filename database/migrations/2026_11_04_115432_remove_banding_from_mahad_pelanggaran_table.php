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
        Schema::table('mahad_pelanggaran', function (Blueprint $table) {
            if (Schema::hasColumn('mahad_pelanggaran', 'catatan_banding')) {
                $table->dropColumn('catatan_banding');
            }
        });

        // Update enum to remove 'banding'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_pelanggaran MODIFY COLUMN status ENUM('terdaftar', 'selesai') NOT NULL DEFAULT 'terdaftar'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_pelanggaran MODIFY COLUMN status ENUM('terdaftar', 'banding', 'selesai') NOT NULL DEFAULT 'terdaftar'");
        }

        Schema::table('mahad_pelanggaran', function (Blueprint $table) {
            if (!Schema::hasColumn('mahad_pelanggaran', 'catatan_banding')) {
                $table->text('catatan_banding')->nullable()->after('status');
            }
        });
    }
};
