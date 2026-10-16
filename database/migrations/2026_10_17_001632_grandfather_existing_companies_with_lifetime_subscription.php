<?php

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Every company that already existed before paid subscriptions were
     * introduced gets a permanent free ride on the top-tier plan — we were
     * never charging them, and we're not going to start now. New signups
     * from here on go through the normal trial/paid flow instead.
     */
    public function up(): void
    {
        $goldPlanId = Plan::where('key', Plan::KEY_GOLD)->value('id');

        if (! $goldPlanId) {
            return;
        }

        Company::query()->whereDoesntHave('subscription')->each(function (Company $company) use ($goldPlanId) {
            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $goldPlanId,
                'status' => Subscription::STATUS_ACTIVE,
                'is_lifetime' => true,
            ]);
        });
    }

    public function down(): void
    {
        Subscription::where('is_lifetime', true)->delete();
    }
};
