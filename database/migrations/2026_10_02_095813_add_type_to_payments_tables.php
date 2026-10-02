<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'paid' (default, every existing row) is money handed to the
        // supplier/customer as before. 'received' is the reverse: money
        // they handed back to us (e.g. a refund for short-delivered goods,
        // or an over-payment they returned) — recorded as its own entry
        // instead of editing the original payment, so both sides of what
        // actually happened stay visible on the statement.
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->string('type')->default('paid')->after('amount');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->string('type')->default('paid')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
