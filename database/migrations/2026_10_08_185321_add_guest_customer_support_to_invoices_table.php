<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a bill be made for just a typed name instead of a saved
     * Customer record — for a one-off walk-in nobody wants to go through
     * full customer creation for. customer_id becomes optional, and
     * guest_customer_name holds the typed name when there's no customer
     * record behind the bill at all.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('guest_customer_name')->nullable()->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('guest_customer_name');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
