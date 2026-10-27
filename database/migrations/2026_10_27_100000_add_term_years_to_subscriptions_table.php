<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How many years the subscription's current paid period actually
     * covers — 1 by default, or 3 for the discounted multi-year purchase.
     * Read by GetStartedController::activateSubscription() to decide how
     * far to push current_period_end out, and to charge the right total.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedTinyInteger('term_years')->default(1)->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('term_years');
        });
    }
};
