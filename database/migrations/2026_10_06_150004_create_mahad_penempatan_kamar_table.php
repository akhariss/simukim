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
        Schema::create('mahad_penempatan_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('mahad_periode')->restrictOnDelete();
            $table->foreignId('santri_id')->constrained('mahad_santri')->restrictOnDelete();
            $table->foreignId('kamar_id')->constrained('mahad_kamar')->restrictOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'pindah', 'keluar'])->default('aktif')->index();
            $table->text('catatan')->nullable();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'status']);
            $table->index(['kamar_id', 'status']);
            $table->index(['periode_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_penempatan_kamar');
    }
};
