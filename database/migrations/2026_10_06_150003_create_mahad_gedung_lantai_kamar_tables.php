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
        Schema::create('mahad_gedung', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->enum('gender_khusus', ['L', 'P', 'campur'])->default('campur');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('mahad_lantai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gedung_id')->constrained('mahad_gedung')->restrictOnDelete();
            $table->unsignedTinyInteger('nomor_lantai');
            $table->unsignedTinyInteger('kapasitas_kamar_standar')->default(24);
            $table->timestamps();

            $table->unique(['gedung_id', 'nomor_lantai']);
        });

        Schema::create('mahad_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lantai_id')->constrained('mahad_lantai')->restrictOnDelete();
            $table->string('nomor_kamar', 20);
            $table->enum('tipe_ruang', [
                'kamar', 'wc', 'tangga', 'kantor', 'dapur', 'kantin', 'jemuran', 'koridor'
            ])->default('kamar');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->unsignedTinyInteger('kapasitas_maks')->default(4);
            $table->enum('status', ['tersedia', 'penuh', 'perbaikan', 'nonaktif'])->default('tersedia')->index();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['lantai_id', 'nomor_kamar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_kamar');
        Schema::dropIfExists('mahad_lantai');
        Schema::dropIfExists('mahad_gedung');
    }
};
