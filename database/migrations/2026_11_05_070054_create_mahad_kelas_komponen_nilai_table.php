<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahad_kelas_komponen_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('mahad_kelas')->cascadeOnDelete();
            $table->foreignId('kelompok_id')->nullable()->constrained('mahad_kelompok')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->decimal('bobot', 5, 2)->default(25.00);
            $table->boolean('is_absensi')->default(false);
            $table->boolean('is_fixed')->default(false);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['kelas_id', 'kelompok_id']);
        });

        Schema::table('mahad_penilaian', function (Blueprint $table) {
            $table->json('nilai_komponen')->nullable()->after('santri_id');
        });
    }

    public function down(): void
    {
        Schema::table('mahad_penilaian', function (Blueprint $table) {
            $table->dropColumn('nilai_komponen');
        });

        Schema::dropIfExists('mahad_kelas_komponen_nilai');
    }
};
