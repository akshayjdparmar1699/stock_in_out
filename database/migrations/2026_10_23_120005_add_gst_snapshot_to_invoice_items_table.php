<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshotted at the moment the line is created, the same way
     * unit_price/unit already are — so a later change to an item's GST
     * rate never silently rewrites a historical invoice's tax figures.
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('hsn_code', 20)->nullable()->after('total');
            $table->decimal('gst_rate', 5, 2)->nullable()->after('hsn_code');
            $table->decimal('cgst_amount', 10, 2)->nullable()->after('gst_rate');
            $table->decimal('sgst_amount', 10, 2)->nullable()->after('cgst_amount');
            $table->decimal('igst_amount', 10, 2)->nullable()->after('sgst_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['hsn_code', 'gst_rate', 'cgst_amount', 'sgst_amount', 'igst_amount']);
        });
    }
};
