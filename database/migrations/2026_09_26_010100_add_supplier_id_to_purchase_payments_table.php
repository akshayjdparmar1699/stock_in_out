<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A payment recorded from the supplier's own page can be entirely
        // (or partly) unmatched to any purchase — e.g. paying down their
        // opening balance, or paying more than everything currently owed.
        // Such a row has no purchase_id and points at the supplier directly.
        DB::statement('ALTER TABLE purchase_payments ALTER COLUMN purchase_id DROP NOT NULL');

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('purchase_id')->constrained()->cascadeOnDelete();

            // Set only when a single payment (recorded from the supplier's
            // own page) was split across more than one purchase — lets the
            // supplier ledger show it as the one amount actually handed
            // over, instead of one row per purchase it happened to land on.
            $table->string('batch_id')->nullable()->after('note');

            // True only for the row created when a new purchase auto-draws
            // on the supplier's existing credit balance — that money was
            // already counted as paid when the credit was built up, so this
            // row must be excluded from the ledger's "paid" sum to avoid
            // counting the same cash twice.
            $table->boolean('from_credit_balance')->default(false)->after('batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn(['batch_id', 'from_credit_balance']);
        });

        DB::statement('ALTER TABLE purchase_payments ALTER COLUMN purchase_id SET NOT NULL');
    }
};
