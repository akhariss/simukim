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
        // 1. Standarisasi Data Periode (Format: "2026 Ganjil", "2026 Genap")
        if (Schema::hasTable('mahad_periode')) {
            DB::table('mahad_periode')
                ->where('id', 1)
                ->update([
                    'kode' => '2026-GENAP',
                    'nama' => '2026 Genap',
                ]);

            // Pastikan periode pelengkap ada
            $periods = [
                ['kode' => '2025-GANJIL', 'nama' => '2025 Ganjil', 'tanggal_mulai' => '2025-08-01', 'tanggal_selesai' => '2026-01-31', 'is_active' => false],
                ['kode' => '2025-GENAP',  'nama' => '2025 Genap',  'tanggal_mulai' => '2025-02-01', 'tanggal_selesai' => '2025-07-31', 'is_active' => false],
                ['kode' => '2026-GANJIL', 'nama' => '2026 Ganjil', 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'is_active' => false],
            ];

            foreach ($periods as $p) {
                if (!DB::table('mahad_periode')->where('kode', $p['kode'])->exists()) {
                    DB::table('mahad_periode')->insert(array_merge($p, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }

        $activePeriodeId = DB::table('mahad_periode')->where('is_active', true)->value('id') ?? 1;

        // 2. Tambah periode_id ke mahad_perizinan
        if (Schema::hasTable('mahad_perizinan') && !Schema::hasColumn('mahad_perizinan', 'periode_id')) {
            Schema::table('mahad_perizinan', function (Blueprint $table) use ($activePeriodeId) {
                $table->foreignId('periode_id')->after('id')->default($activePeriodeId)->constrained('mahad_periode')->restrictOnDelete();
                $table->index(['periode_id', 'status']);
            });
        }

        // 3. Tambah periode_id ke mahad_pelanggaran
        if (Schema::hasTable('mahad_pelanggaran') && !Schema::hasColumn('mahad_pelanggaran', 'periode_id')) {
            Schema::table('mahad_pelanggaran', function (Blueprint $table) use ($activePeriodeId) {
                $table->foreignId('periode_id')->after('id')->default($activePeriodeId)->constrained('mahad_periode')->restrictOnDelete();
                $table->index(['periode_id', 'status']);
            });
        }

        // 4. Tambah periode_id ke mahad_kegiatan
        if (Schema::hasTable('mahad_kegiatan') && !Schema::hasColumn('mahad_kegiatan', 'periode_id')) {
            Schema::table('mahad_kegiatan', function (Blueprint $table) {
                $table->foreignId('periode_id')->nullable()->after('id')->constrained('mahad_periode')->nullOnDelete();
                $table->index(['periode_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mahad_kegiatan', 'periode_id')) {
            Schema::table('mahad_kegiatan', function (Blueprint $table) {
                $table->dropForeign(['periode_id']);
                $table->dropIndex(['periode_id', 'is_active']);
                $table->dropColumn('periode_id');
            });
        }

        if (Schema::hasColumn('mahad_pelanggaran', 'periode_id')) {
            Schema::table('mahad_pelanggaran', function (Blueprint $table) {
                $table->dropForeign(['periode_id']);
                $table->dropIndex(['periode_id', 'status']);
                $table->dropColumn('periode_id');
            });
        }

        if (Schema::hasColumn('mahad_perizinan', 'periode_id')) {
            Schema::table('mahad_perizinan', function (Blueprint $table) {
                $table->dropForeign(['periode_id']);
                $table->dropIndex(['periode_id', 'status']);
                $table->dropColumn('periode_id');
            });
        }
    }
};
