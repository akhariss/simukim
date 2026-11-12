<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mahad_santri') && !Schema::hasColumn('mahad_santri', 'periode_id')) {
            Schema::table('mahad_santri', function (Blueprint $table) {
                $table->foreignId('periode_id')->nullable()->after('user_id')->constrained('mahad_periode')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mahad_santri') && Schema::hasColumn('mahad_santri', 'periode_id')) {
            Schema::table('mahad_santri', function (Blueprint $table) {
                $table->dropForeign(['periode_id']);
                $table->dropColumn('periode_id');
            });
        }
    }
};

