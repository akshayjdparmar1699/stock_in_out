<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('gstin', 20)->nullable()->after('ifsc_code');
            $table->string('state')->nullable()->after('gstin');
            $table->string('state_code', 2)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'state', 'state_code']);
        });
    }
};
