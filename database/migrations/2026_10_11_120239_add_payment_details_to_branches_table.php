<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('qr_code_path')->nullable()->after('phone');
            $table->string('bank_name')->nullable()->after('qr_code_path');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('account_holder_name')->nullable()->after('account_number');
            $table->string('ifsc_code')->nullable()->after('account_holder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['qr_code_path', 'bank_name', 'account_number', 'account_holder_name', 'ifsc_code']);
        });
    }
};
