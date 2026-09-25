<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // The unit this line was actually billed in (may be the
            // item's base unit or its alt selling unit — items can
            // change their configured units later, so each line records
            // what was true at the time).
            $table->string('unit')->nullable()->after('quantity');
            // `quantity` converted into the item's base/stock unit — what
            // actually gets deducted from stock and used for FIFO batch
            // allocation and profit, regardless of which unit the line
            // was billed in.
            $table->decimal('base_quantity', 12, 4)->nullable()->after('unit');
        });

        // Backfill existing rows: they were always billed and stocked in
        // the same (base) unit, so base_quantity == quantity for them.
        DB::table('invoice_items')->update([
            'base_quantity' => DB::raw('quantity'),
        ]);
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'base_quantity']);
        });
    }
};
