<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Razorpay\Api\Api;

/**
 * Lets an already-existing, already-logged-in company pay to reactivate a
 * lapsed subscription. Distinct from GetStartedController's checkout, which
 * only works mid-signup (keyed off a session "pending" value and an
 * INCOMPLETE subscription). This reuses GetStartedController's
 * activateSubscription() — and so the Razorpay webhook's backup activation
 * path — rather than duplicating that logic.
 */
class SubscriptionRenewalController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $company = $request->user()->company;
        $subscription = $company->subscription;

        if (! $subscription) {
            return redirect()->route('dashboard');
        }

        $currentPlan = $subscription->plan;

        // An INCOMPLETE subscription never got past checkout at all — the
        // company/user exist, but no payment ever went through, for
        // whatever paid plan they originally picked (the free trial is
        // ₹0 and never reaches checkout, so it can't end up INCOMPLETE —
        // this only matters for legacy rows from before the trial was
        // free). There is nothing to "choose" here: resume checkout for
        // that exact plan. The picker below is only for a trial that was
        // actually used and has since lapsed, which can't be "renewed"
        // since it isn't a recurring price to begin with.
        $isAbandonedCheckout = $subscription->status === Subscription::STATUS_INCOMPLETE;

        if ($currentPlan->isFreeTrial() && ! $isAbandonedCheckout && ! $request->filled('plan')) {
            return view('subscription.renew-plan', [
                'company' => $company,
                'plans' => Plan::where('is_active', true)
                    ->where('key', '!=', Plan::KEY_FREE_TRIAL)
                    ->orderBy('sort_order')
                    ->get(),
            ]);
        }

        $plan = ($currentPlan->isFreeTrial() && $request->filled('plan'))
            ? Plan::where('is_active', true)
                ->where('key', '!=', Plan::KEY_FREE_TRIAL)
                ->findOrFail($request->integer('plan'))
            : $currentPlan;

        $initialTerm = in_array($request->integer('years'), Plan::YEARS_OPTIONS, true) ? $request->integer('years') : 1;

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        // One order per term, created upfront — a Razorpay order is fixed to
        // the amount it was created with, so a term toggle on the checkout
        // page itself (letting someone renewing their current plan reach
        // the 3-year discount too, not just a trial picking their first
        // paid plan) needs both ready before the page renders, not one
        // created after the fact based on whichever the buyer ends up
        // choosing.
        $orderIds = [];
        $prices = [];
        foreach (Plan::YEARS_OPTIONS as $years) {
            $prices[$years] = $plan->priceForYears($years);
            $orderIds[$years] = $api->order->create([
                'receipt' => 'resub_'.$subscription->id.'_'.$years.'y_'.now()->timestamp,
                'amount' => $prices[$years] * 100,
                'currency' => 'INR',
                'notes' => [
                    'company_id' => (string) $company->id,
                    'subscription_id' => (string) $subscription->id,
                    'plan_id' => (string) $plan->id,
                    'years' => (string) $years,
                ],
            ])['id'];
        }

        return view('subscription.renew-checkout', [
            'plan' => $plan,
            'initialTerm' => $initialTerm,
            'prices' => $prices,
            'orderIds' => $orderIds,
            'company' => $company,
            'admin' => $request->user(),
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'years' => ['required', 'integer', Rule::in(Plan::YEARS_OPTIONS)],
        ]);

        $subscription = $request->user()->company?->subscription;

        if (! $subscription) {
            return redirect()->route('dashboard');
        }

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $api->utility->verifyPaymentSignature($data);
        } catch (\Exception $e) {
            return redirect()->route('subscription.renew')
                ->with('status', __('We could not verify that payment. Please try again.'))
                ->with('status_type', 'danger');
        }

        // activateSubscription() trusts $subscription->plan/term_years as-is
        // and never reassigns them itself, so a trial -> Silver/Gold move
        // (or any plan/term change on renewal) has to happen here, before
        // activating — term_years always needs setting, even when the plan
        // itself is unchanged, since a same-plan 3-year renewal still needs
        // it updated from whatever it was before.
        $subscription->update(['plan_id' => $data['plan_id'], 'term_years' => $data['years']]);
        $subscription->refresh();

        app(GetStartedController::class)->activateSubscription(
            $subscription,
            $data['razorpay_payment_id'],
            $data['razorpay_order_id'],
        );

        return redirect()->route('dashboard')->with('status', __('Payment received — welcome back!'));
    }
}
