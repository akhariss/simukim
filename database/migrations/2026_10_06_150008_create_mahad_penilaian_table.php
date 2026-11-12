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
        Schema::create('mahad_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_id')->constrained('mahad_kelompok')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('mahad_kelas')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('mahad_santri')->restrictOnDelete();
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('input_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['kelompok_id', 'kelas_id', 'santri_id'], 'uq_penilaian_klp_kls_st');
            $table->index(['kelompok_id', 'kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_penilaian');
    }
};
