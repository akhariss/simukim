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
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            if (!Schema::hasColumn('users', 'auth_source')) {
                $table->enum('auth_source', ['local', 'campus'])->default('local')->after('email')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'auth_source')) {
                $table->dropColumn('auth_source');
            }
            $table->string('password')->nullable(false)->change();
        });
    }
};
