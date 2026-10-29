<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The trial used to charge a nominal ₹2 (a payment-method-verification
     * / anti-abuse charge) for 7 days, which meant signing up for a trial
     * went through full Razorpay checkout. Dropping the price to ₹0 makes
     * GetStartedController::storeDetails() take its existing zero-price
     * branch instead, skipping checkout entirely, and stretches the trial
     * to 14 days so new users have enough time to actually adopt the app.
     */
    public function up(): void
    {
        Plan::where('key', Plan::KEY_FREE_TRIAL)->update([
            'price' => 0,
            'trial_days' => 14,
        ]);
    }

    public function down(): void
    {
        Plan::where('key', Plan::KEY_FREE_TRIAL)->update([
            'price' => 2,
            'trial_days' => 7,
        ]);
    }
};
