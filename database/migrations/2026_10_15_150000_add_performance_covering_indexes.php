<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mahad_penempatan_kamar', function (Blueprint $table) {
            $table->index(['status', 'kamar_id'], 'idx_pk_status_kamar');
        });

        Schema::table('mahad_absensi', function (Blueprint $table) {
            $table->index(['kelas_id', 'tanggal', 'status'], 'idx_absensi_kelas_tgl_status');
        });

        Schema::table('mahad_santri', function (Blueprint $table) {
            $table->index(['status', 'nomor_induk'], 'idx_santri_status_noinduk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahad_penempatan_kamar', function (Blueprint $table) {
            $table->dropIndex('idx_pk_status_kamar');
        });

        Schema::table('mahad_absensi', function (Blueprint $table) {
            $table->dropIndex('idx_absensi_kelas_tgl_status');
        });

        Schema::table('mahad_santri', function (Blueprint $table) {
            $table->dropIndex('idx_santri_status_noinduk');
        });
    }
};
