<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Unmatched cash held for this customer — grows when a payment
            // (against invoices, the opening balance, or neither) exceeds
            // everything currently owed, shrinks when a later invoice
            // auto-draws on it. Fungible: it isn't tagged to what it was
            // originally collected against.
            $table->decimal('credit_balance', 12, 2)->default(0)->after('opening_balance');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
};
