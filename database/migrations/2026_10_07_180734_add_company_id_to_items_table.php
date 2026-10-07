<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Items had no company/branch relationship at all — a flat-out
        // shared catalog, which was fine with a single company but let any
        // company's Items page, invoice/purchase pickers, and search see
        // and sell every other company's products the moment a second
        // company existed for real.
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained()->restrictOnDelete();
        });

        // sku only has to be unique within one company's own catalog, not
        // across every company on the platform.
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->unique(['company_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'sku']);
            $table->unique('sku');
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
