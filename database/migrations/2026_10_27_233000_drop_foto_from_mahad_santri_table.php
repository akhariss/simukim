<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('mahad_santri', 'foto')) {
            Schema::table('mahad_santri', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('mahad_santri', 'foto')) {
            Schema::table('mahad_santri', function (Blueprint $table) {
                $table->string('foto')->nullable()->after('semester');
            });
        }
    }
};
