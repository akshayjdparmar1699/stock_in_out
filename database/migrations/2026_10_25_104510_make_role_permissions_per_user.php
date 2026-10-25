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
     * there's no data to preserve — the table is dropped and recreated
     * outright rather than altered. (An earlier version of this migration
     * tried to alter it in place and dropped company_id without first
     * dropping its foreign key, which MySQL refuses to do — recreating the
     * table sidesteps that entirely, and is safe to run again even if that
     * broken version already ran partway on a server.)
     */
    public function up(): void
    {
        Schema::dropIfExists('role_permissions');

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->boolean('can_view')->default(true);
            $table->boolean('can_add')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_delete')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->boolean('can_view')->default(true);
            $table->boolean('can_add')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_delete')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'module']);
        });
    }
};
