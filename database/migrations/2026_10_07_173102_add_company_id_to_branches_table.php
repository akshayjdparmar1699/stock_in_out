<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // restrictOnDelete, not cascade: a company with real branches
            // (and everything cascading from those — stock, invoices via
            // their own restrict, expenses) should never disappear via a
            // side effect of deleting the company row.
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
