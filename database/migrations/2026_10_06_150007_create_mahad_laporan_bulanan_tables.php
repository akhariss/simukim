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
        Schema::create('mahad_laporan_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('mahad_periode')->restrictOnDelete();
            $table->foreignId('kelompok_id')->nullable()->constrained('mahad_kelompok')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('mahad_kelas')->nullOnDelete();
            $table->foreignId('tutor_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->text('catatan');
            $table->enum('status', ['draft', 'menunggu_review', 'direview', 'selesai'])->default('menunggu_review')->index();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['periode_id', 'tutor_id', 'bulan', 'tahun']);
            $table->index(['kelompok_id', 'kelas_id']);
        });

        Schema::create('mahad_laporan_bulanan_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('mahad_laporan_bulanan')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('file_name', 255);
            $table->unsignedInteger('file_size');
            $table->string('caption', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_laporan_bulanan_foto');
        Schema::dropIfExists('mahad_laporan_bulanan');
    }
};
