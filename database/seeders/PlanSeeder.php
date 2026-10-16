<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'key' => Plan::KEY_FREE_TRIAL,
                'name' => 'Free trial',
                'tagline' => 'Try every feature and see how fast billing becomes.',
                'price' => 2,
                'billing_period' => 'trial',
                'trial_days' => 14,
                'branch_limit' => 1,
                'features' => [
                    'Billing and invoices',
                    'Stock, items and batches',
                    'Customers, suppliers and credit limits',
                    'Purchases and expenses',
                    'Alerts and WhatsApp reminders',
                ],
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'key' => Plan::KEY_SILVER,
                'name' => 'Silver',
                'tagline' => 'For a single shop or godown that wants simple, fast billing.',
                'price' => 699,
                'billing_period' => 'yearly',
                'trial_days' => null,
                'branch_limit' => 1,
                'features' => [
                    'Everything in the trial, no time limit',
                    'Unlimited bills and items',
                    'Customer follow up and credit alerts',
                    'Daily sales, profit and stock reports',
                    'WhatsApp payment reminders',
                ],
                'is_popular' => false,
                'sort_order' => 2,
            ],
            [
                'key' => Plan::KEY_GOLD,
                'name' => 'Gold',
                'tagline' => 'For growing wholesalers running more than one branch.',
                'price' => 1199,
                'billing_period' => 'yearly',
                'trial_days' => null,
                'branch_limit' => null,
                'features' => [
                    'Everything in Silver',
                    'Unlimited branches',
                    'Separate live stock for each branch',
                    'Switch branches in one tap',
                    'Users and staff for every branch',
                ],
                'is_popular' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
