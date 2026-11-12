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
        Schema::create('mahad_perizinan_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->boolean('butuh_bukti')->default(false);
            $table->unsignedSmallInteger('batas_hari_maks')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mahad_perizinan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('mahad_santri')->restrictOnDelete();
            $table->foreignId('jenis_perizinan_id')->constrained('mahad_perizinan_jenis')->restrictOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('alasan');
            $table->string('file_bukti', 255)->nullable();
            $table->enum('status', ['berlaku', 'selesai', 'dibatalkan'])->default('berlaku')->index();
            $table->foreignId('input_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_perizinan');
        Schema::dropIfExists('mahad_perizinan_jenis');
    }
};
