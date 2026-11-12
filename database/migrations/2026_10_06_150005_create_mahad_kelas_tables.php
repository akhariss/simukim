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
        // 1. Kelompok Mahasantri (Rombel / Penempatan Hasil Tes)
        Schema::create('mahad_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('mahad_periode')->restrictOnDelete();
            $table->string('nama', 100);
            $table->string('kode', 30)->nullable();
            $table->unsignedSmallInteger('kapasitas')->default(30);
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['periode_id', 'is_active']);
            $table->unique(['periode_id', 'nama']);
        });

        // 2. Anggota Kelompok (1 Santri hanya berada di 1 Kelompok)
        Schema::create('mahad_kelompok_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_id')->constrained('mahad_kelompok')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('mahad_santri')->restrictOnDelete();
            $table->date('tanggal_masuk')->nullable();
            $table->timestamps();

            $table->unique(['kelompok_id', 'santri_id']);
            $table->index('santri_id');
        });

        // 3. Master Mata Pelajaran / Kelas Kajian (Qur'an, Kitab, Masal)
        Schema::create('mahad_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('mahad_periode')->restrictOnDelete();
            $table->string('nama', 100);
            $table->string('tipe', 50)->default('kelompok')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index(['periode_id', 'tipe', 'is_active']);
            $table->unique(['periode_id', 'nama']);
        });

        // 4. Sesi Jadwal Pertemuan Mingguan di Master Kelas
        Schema::create('mahad_kelas_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('mahad_kelas')->cascadeOnDelete();
            $table->string('nama_sesi', 100); // e.g. "Qur'an Pagi", "Qur'an Malam", "Kajian Dhuha"
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan', 100)->nullable();
            $table->timestamps();

            $table->index(['kelas_id', 'hari']);
        });

        // 5. Penugasan Tutor Pengampu per Kelas Kajian di Tiap Kelompok
        Schema::create('mahad_kelompok_pengampu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_id')->constrained('mahad_kelompok')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('mahad_kelas')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['kelompok_id', 'kelas_id']);
            $table->index(['kelas_id', 'tutor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_kelompok_pengampu');
        Schema::dropIfExists('mahad_kelas_jadwal');
        Schema::dropIfExists('mahad_kelas');
        Schema::dropIfExists('mahad_kelompok_anggota');
        Schema::dropIfExists('mahad_kelompok');
    }
};
