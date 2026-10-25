<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Switches role_permissions from one row per (company, module) — every
     * staff login sharing the same access — to one row per (user, module),
     * so each person can be given their own set of modules when they're
     * added or edited. Nothing had been set yet on any live company, so
     * this is a clean structural change rather than a data migration.
     */
    public function up(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'module']);
            $table->dropColumn('company_id');
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'module']);
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('company_id')->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['company_id', 'module']);
        });
    }
};
