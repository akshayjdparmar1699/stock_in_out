<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A payment recorded from the customer's own page can be entirely
        // (or partly) unmatched to any invoice — e.g. paying down their
        // opening balance, or paying more than everything currently owed.
        // Such a row has no invoice_id and points at the customer directly.
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->change();
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();

            // True only for the row created when a new invoice auto-draws
            // on the customer's existing credit balance — that money was
            // already counted as received when the credit was built up, so
            // this row must be excluded from the ledger's "received" sum
            // to avoid counting the same cash twice.
            $table->boolean('from_credit_balance')->default(false)->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn('from_credit_balance');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable(false)->change();
        });
    }
};
