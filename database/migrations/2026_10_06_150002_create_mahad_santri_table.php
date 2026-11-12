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
        Schema::create('mahad_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('nomor_induk', 50)->unique()->index();
            $table->enum('gender', ['L', 'P'])->index();
            $table->string('prodi', 100)->nullable();
            $table->string('angkatan', 10)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->string('nama_wali', 100)->nullable();
            $table->string('no_hp_wali', 25)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_santri');
    }
};
