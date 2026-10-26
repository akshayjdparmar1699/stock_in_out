<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every dashboard widget (and the invoices/purchases list pages) filters
     * by branch_id and then a date range or "latest first" on invoice_date /
     * purchase_date — branch_id alone was already indexed (its foreign key),
     * but invoice_date/purchase_date had no index at all, so MySQL could
     * only narrow down to "this branch" before scanning every row to filter
     * or sort by date. A composite index with the date column after
     * branch_id covers both the range filter and the ORDER BY in one pass.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['branch_id', 'invoice_date']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['branch_id', 'purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'invoice_date']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'purchase_date']);
        });
    }
};
