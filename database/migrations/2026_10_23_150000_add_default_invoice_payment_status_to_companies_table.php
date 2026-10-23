<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Null means "paid" — the same default the New Bill screen has
            // always hardcoded, so a company that never touches this
            // setting sees no change in behaviour.
            $table->string('default_invoice_payment_status')->nullable()->after('invoice_terms');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('default_invoice_payment_status');
        });
    }
};
