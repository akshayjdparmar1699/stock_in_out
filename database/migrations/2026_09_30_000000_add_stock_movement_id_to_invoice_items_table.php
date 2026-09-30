<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Links this line to the exact stock-out movement it created,
            // so profit can be worked out from the real cost of whichever
            // batch(es) it was actually sold from (via that movement's
            // stock_batch_allocations) instead of the item's current/latest
            // purchase price. Nullable — rows created before this existed
            // fall back to the old flat-rate estimate.
            $table->foreignId('stock_movement_id')->nullable()->after('base_quantity')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_movement_id');
        });
    }
};
