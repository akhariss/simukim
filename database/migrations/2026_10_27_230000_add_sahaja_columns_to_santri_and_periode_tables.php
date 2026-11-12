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
        // 1. Tambah Kolom Standar SAHAJA ke mahad_santri
        if (Schema::hasTable('mahad_santri')) {
            Schema::table('mahad_santri', function (Blueprint $table) {
                if (!Schema::hasColumn('mahad_santri', 'fakultas')) {
                    $table->string('fakultas', 100)->nullable()->after('prodi');
                }
                if (!Schema::hasColumn('mahad_santri', 'semester')) {
                    $table->unsignedTinyInteger('semester')->default(1)->after('fakultas');
                }
                if (!Schema::hasColumn('mahad_santri', 'wa_santri')) {
                    $table->string('wa_santri', 25)->nullable()->after('no_hp');
                }
                if (!Schema::hasColumn('mahad_santri', 'alamat')) {
                    $table->text('alamat')->nullable()->after('wa_santri');
                }
                if (!Schema::hasColumn('mahad_santri', 'hp_ayah')) {
                    $table->string('hp_ayah', 25)->nullable()->after('no_hp_wali');
                }
                if (!Schema::hasColumn('mahad_santri', 'email_ayah')) {
                    $table->string('email_ayah', 100)->nullable()->after('hp_ayah');
                }
                if (!Schema::hasColumn('mahad_santri', 'hp_ibu')) {
                    $table->string('hp_ibu', 25)->nullable()->after('email_ayah');
                }
                if (!Schema::hasColumn('mahad_santri', 'email_ibu')) {
                    $table->string('email_ibu', 100)->nullable()->after('hp_ibu');
                }
                if (!Schema::hasColumn('mahad_santri', 'briva_number')) {
                    $table->string('briva_number', 30)->nullable()->index()->after('email_ibu');
                }
                if (!Schema::hasColumn('mahad_santri', 'bsb_customer_id')) {
                    $table->string('bsb_customer_id', 30)->nullable()->after('briva_number');
                }
                if (!Schema::hasColumn('mahad_santri', 'status_pembayaran')) {
                    $table->string('status_pembayaran', 20)->default('Belum Bayar')->index()->after('bsb_customer_id');
                }
                if (!Schema::hasColumn('mahad_santri', 'nominal_tagihan')) {
                    $table->unsignedInteger('nominal_tagihan')->default(1500000)->after('status_pembayaran');
                }
            });
        }

        // 2. Tambah academic_period_id ke mahad_periode
        if (Schema::hasTable('mahad_periode')) {
            Schema::table('mahad_periode', function (Blueprint $table) {
                if (!Schema::hasColumn('mahad_periode', 'academic_period_id')) {
                    $table->unsignedBigInteger('academic_period_id')->nullable()->index()->after('kode');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mahad_santri')) {
            Schema::table('mahad_santri', function (Blueprint $table) {
                $columns = [
                    'fakultas', 'semester', 'wa_santri', 'alamat',
                    'hp_ayah', 'email_ayah', 'hp_ibu', 'email_ibu',
                    'briva_number', 'bsb_customer_id', 'status_pembayaran', 'nominal_tagihan'
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('mahad_santri', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('mahad_periode')) {
            Schema::table('mahad_periode', function (Blueprint $table) {
                if (Schema::hasColumn('mahad_periode', 'academic_period_id')) {
                    $table->dropColumn('academic_period_id');
                }
            });
        }
    }
};
