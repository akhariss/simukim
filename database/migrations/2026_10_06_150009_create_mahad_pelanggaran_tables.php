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
        Schema::create('mahad_pelanggaran_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->unsignedSmallInteger('poin_default')->default(0);
            $table->string('sanksi_default', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mahad_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('mahad_santri')->restrictOnDelete();
            $table->foreignId('jenis_pelanggaran_id')->constrained('mahad_pelanggaran_jenis')->restrictOnDelete();
            $table->date('tanggal')->index();
            $table->unsignedSmallInteger('poin')->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('hukuman', 255)->nullable();
            $table->enum('status', ['terdaftar', 'banding', 'selesai'])->default('terdaftar')->index();
            $table->text('catatan_banding')->nullable();
            $table->foreignId('input_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_pelanggaran');
        Schema::dropIfExists('mahad_pelanggaran_jenis');
    }
};
