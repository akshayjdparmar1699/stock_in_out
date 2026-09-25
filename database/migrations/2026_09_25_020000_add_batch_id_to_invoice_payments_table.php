<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Set only when a single payment (recorded from the customer's
            // own page) was split across more than one of their invoices —
            // lets the customer ledger show it as the one amount that was
            // actually handed over, instead of one row per invoice it
            // happened to land on.
            $table->string('batch_id')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
