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
        Schema::table('mahad_kamar', function (Blueprint $table) {
            $table->index('nomor_kamar');
            $table->index(['gender', 'tipe_ruang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahad_kamar', function (Blueprint $table) {
            $table->dropIndex(['nomor_kamar']);
            $table->dropIndex(['gender', 'tipe_ruang']);
        });
    }
};
