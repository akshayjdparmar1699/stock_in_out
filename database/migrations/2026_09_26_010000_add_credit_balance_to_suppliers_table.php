<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Unmatched cash paid to this supplier — grows when a payment
            // (against purchases, the opening balance, or neither) exceeds
            // everything currently owed, shrinks when a later purchase
            // auto-draws on it.
            $table->decimal('credit_balance', 12, 2)->default(0)->after('opening_balance');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
};
