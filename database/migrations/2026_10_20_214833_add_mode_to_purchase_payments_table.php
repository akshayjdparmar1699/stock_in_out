<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->string('mode', 10)->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
