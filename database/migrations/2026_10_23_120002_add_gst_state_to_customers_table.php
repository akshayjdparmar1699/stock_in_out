<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // gst_number already exists on this table (used as the GSTIN) —
            // only the state is new, needed to decide CGST+SGST vs IGST.
            $table->string('state')->nullable()->after('gst_number');
            $table->string('state_code', 2)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['state', 'state_code']);
        });
    }
};
