<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        // The free-trial plan's price is a one-time anti-abuse charge, not
        // a recurring price — once the trial lapses there is nothing
        // sensible to "renew" at that price, so the company must choose a
        // real plan first.
        if ($currentPlan->isFreeTrial() && ! $request->filled('plan')) {
            return view('subscription.renew-plan', [
                'company' => $company,
                'plans' => Plan::where('is_active', true)
                    ->where('key', '!=', Plan::KEY_FREE_TRIAL)
                    ->orderBy('sort_order')
                    ->get(),
            ]);
        }

        $plan = $currentPlan->isFreeTrial()
            ? Plan::where('is_active', true)
                ->where('key', '!=', Plan::KEY_FREE_TRIAL)
                ->findOrFail($request->integer('plan'))
            : $currentPlan;

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $order = $api->order->create([
            'receipt' => 'resub_'.$subscription->id.'_'.now()->timestamp,
            'amount' => $plan->price * 100,
            'currency' => 'INR',
            'notes' => [
                'company_id' => (string) $company->id,
                'subscription_id' => (string) $subscription->id,
                'plan_id' => (string) $plan->id,
            ],
        ]);

        return view('subscription.renew-checkout', [
            'plan' => $plan,
            'company' => $company,
            'admin' => $request->user(),
            'razorpayKey' => config('services.razorpay.key'),
            'orderId' => $order['id'],
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
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

        // activateSubscription() trusts $subscription->plan as-is and never
        // reassigns plan_id itself, so a trial -> Silver/Gold move (or any
        // plan change on renewal) has to happen here, before activating.
        if ((int) $subscription->plan_id !== (int) $data['plan_id']) {
            $subscription->update(['plan_id' => $data['plan_id']]);
            $subscription->refresh();
        }

        app(GetStartedController::class)->activateSubscription(
            $subscription,
            $data['razorpay_payment_id'],
            $data['razorpay_order_id'],
        );

        return redirect()->route('dashboard')->with('status', __('Payment received — welcome back!'));
    }
}
