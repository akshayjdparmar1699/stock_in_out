<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per (company, module). A module with no row here is
     * unrestricted — every existing company keeps today's behaviour
     * (staff can do everything they already could) until an admin
     * explicitly turns something off in Settings > Role permission.
     * Admins and super admins always bypass this entirely; it only ever
     * limits the "staff" role.
     */
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
