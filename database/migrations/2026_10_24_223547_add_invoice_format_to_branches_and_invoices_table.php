<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Null means "the app's original design" — not one of the 5
            // selectable formats, so a branch that's never touched this
            // setting keeps looking exactly as it always has.
            $table->string('invoice_format', 40)->nullable()->after('name');
            $table->string('invoice_accent', 7)->nullable()->after('invoice_format');
        });

        // Set once at creation from the branch's setting at that time, so
        // changing the branch's format later never repaints an invoice
        // already shared with a customer.
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_format', 40)->nullable()->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_format');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['invoice_format', 'invoice_accent']);
        });
    }
};
