<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah tipe 'mudabbir' pada mahad_kamar
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE mahad_kamar MODIFY COLUMN tipe_ruang ENUM('kamar', 'mudabbir', 'wc', 'tangga', 'kantor', 'dapur', 'kantin', 'jemuran', 'koridor') DEFAULT 'kamar'");
        }

        // 2. Tabel mahad_pengaturan
        if (!Schema::hasTable('mahad_pengaturan')) {
            Schema::create('mahad_pengaturan', function (Blueprint $table) {
                $table->id();
                $table->string('key', 60)->unique();
                $table->text('value')->nullable();
                $table->string('kategori', 50)->default('umum');
                $table->timestamps();
            });

            // Default settings
            DB::table('mahad_pengaturan')->insert([
                ['key' => 'nama_mahad', 'value' => "Ma'had Al-Jami'ah Terpadu", 'kategori' => 'profil', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'kepala_mahad', 'value' => 'Dr. KH. Abdullah Shiddiq, M.Ag.', 'kategori' => 'profil', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'nip_kepala_mahad', 'value' => '197808152003121003', 'kategori' => 'profil', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'alamat_mahad', 'value' => "Kompleks Asrama Ma'had Al-Jami'ah Kampus Terpadu, Jl. Pendidikan No. 45", 'kategori' => 'profil', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'kontak_mahad', 'value' => '081122334455 / mahad@kampus.ac.id', 'kategori' => 'profil', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'jam_malam', 'value' => '21:30', 'kategori' => 'keamanan', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahad_pengaturan');
    }
};
