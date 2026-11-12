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
        Schema::create('mahad_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_id')->constrained('mahad_kelompok')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('mahad_kelas')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('mahad_kelas_jadwal')->nullOnDelete();
            $table->foreignId('santri_id')->constrained('mahad_santri')->restrictOnDelete();
            $table->date('tanggal')->index();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa', 'terlambat', 'sakit_tambahan'])->index();
            $table->string('catatan', 255)->nullable();
            $table->foreignId('input_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['kelompok_id', 'kelas_id', 'jadwal_id', 'santri_id', 'tanggal'], 'uq_absensi_klp_kls_jdw_st_tgl');
            $table->index(['kelompok_id', 'kelas_id', 'tanggal', 'status'], 'idx_absensi_klp_kls_tgl_st');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_absensi');
    }
};
