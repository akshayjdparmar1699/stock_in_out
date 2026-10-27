<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Razorpay\Api\Api;

class GetStartedController extends Controller
{
    private const SESSION_KEY = 'get_started.pending';

    /**
     * Step 1 of the public signup flow: plan selection.
     */
    public function index(): View
    {
        return view('get-started.plan', [
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Step 2: business + admin details for the plan picked in step 1.
     */
    public function details(Request $request): View
    {
        $plan = Plan::where('is_active', true)->findOrFail($request->integer('plan'));

        $years = in_array($request->integer('years'), Plan::YEARS_OPTIONS, true) ? $request->integer('years') : 1;

        return view('get-started.details', ['plan' => $plan, 'years' => $years]);
    }

    public function storeDetails(Request $request): RedirectResponse
    {
        $plan = Plan::where('is_active', true)->findOrFail($request->integer('plan_id'));

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email'],
            'admin_phone' => ['nullable', 'string', 'max:20'],
            'admin_password' => ['required', 'confirmed', Rules\Password::defaults()],
            'years' => ['nullable', 'integer', Rule::in(Plan::YEARS_OPTIONS)],
        ]);

        // The trial has no multi-year concept at all (see plan.blade.php) —
        // force 1 regardless of what was submitted, rather than trusting a
        // tampered request to shorten/lengthen its fixed trial period.
        $years = $plan->isFreeTrial() ? 1 : ($data['years'] ?? 1);

        if ($plan->price === 0) {
            $company = $this->createCompany($data, $plan);

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_TRIALING,
                'trial_ends_at' => now()->addDays($plan->trial_days),
            ]);

            Auth::login($company->users()->where('role', User::ROLE_ADMIN)->first());

            return redirect()->route('dashboard')
                ->with('status', __('Welcome! Your trial has started.'));
        }

        // Every plan right now carries a price (even the trial, as a small
        // anti-abuse/payment-method-verification charge), so it goes
        // through checkout. The company is created now (status
        // "incomplete" — no access yet) so the Razorpay order's notes only
        // need to carry IDs; access turns on once payment is verified.
        $company = $this->createCompany($data, $plan);

        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'term_years' => $years,
            'status' => Subscription::STATUS_INCOMPLETE,
        ]);

        $request->session()->put(self::SESSION_KEY, ['company_id' => $company->id]);

        return redirect()->route('get-started.billing');
    }

    /**
     * Step 3: Razorpay checkout for a paid plan.
     */
    public function billing(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        $company = $pending ? Company::with(['subscription.plan', 'users'])->find($pending['company_id']) : null;

        if (! $company || ! $company->subscription || $company->subscription->status !== Subscription::STATUS_INCOMPLETE) {
            return redirect()->route('get-started');
        }

        $plan = $company->subscription->plan;
        $admin = $company->users()->where('role', User::ROLE_ADMIN)->first();
        $years = $company->subscription->term_years ?: 1;
        $amount = $plan->priceForYears($years);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $order = $api->order->create([
            'receipt' => 'sub_'.$company->subscription->id.'_'.now()->timestamp,
            'amount' => $amount * 100,
            'currency' => 'INR',
            'notes' => [
                'company_id' => (string) $company->id,
                'subscription_id' => (string) $company->subscription->id,
            ],
        ]);

        return view('get-started.billing', [
            'plan' => $plan,
            'years' => $years,
            'amount' => $amount,
            'company' => $company,
            'admin' => $admin,
            'razorpayKey' => config('services.razorpay.key'),
            'orderId' => $order['id'],
        ]);
    }

    /**
     * The browser lands back here once Razorpay's checkout succeeds. The
     * webhook (RazorpayWebhookController) does the same activation
     * independently, so whichever arrives first wins and the other is a
     * no-op — see SubscriptionPayment's unique razorpay_payment_id.
     */
    public function verifyPayment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $pending = $request->session()->get(self::SESSION_KEY);
        $company = $pending ? Company::with('subscription')->find($pending['company_id']) : null;

        if (! $company || ! $company->subscription) {
            return redirect()->route('get-started')->with('status', __('Something went wrong. Please start again.'));
        }

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $api->utility->verifyPaymentSignature($data);
        } catch (\Exception $e) {
            return redirect()->route('get-started.billing')
                ->with('status', __('We could not verify that payment. Please try again.'));
        }

        $this->activateSubscription(
            $company->subscription,
            $data['razorpay_payment_id'],
            $data['razorpay_order_id'],
        );

        $request->session()->forget(self::SESSION_KEY);

        Auth::login($company->users()->where('role', User::ROLE_ADMIN)->first());

        return redirect()->route('dashboard')->with('status', __('Payment received — welcome aboard!'));
    }

    /**
     * Shared by the browser-redirect path above and the webhook — activates
     * a subscription from a confirmed Razorpay payment exactly once.
     */
    public function activateSubscription(Subscription $subscription, string $paymentId, string $orderId): void
    {
        if (SubscriptionPayment::where('razorpay_payment_id', $paymentId)->exists()) {
            return;
        }

        $plan = $subscription->plan;
        // Set on the subscription by the caller before activation (trial
        // signup never touches this, so it's always 1 there) — see
        // SubscriptionRenewalController::verify().
        $years = $subscription->term_years ?: 1;

        SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $orderId,
            'amount' => $plan->priceForYears($years) * 100,
            'status' => SubscriptionPayment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        if ($plan->isFreeTrial()) {
            // The trial's price is just a payment-method verification charge
            // — it still runs as a time-boxed trial, not a paid period.
            $subscription->update([
                'status' => Subscription::STATUS_TRIALING,
                'trial_ends_at' => now()->addDays($plan->trial_days),
            ]);

            return;
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end' => $plan->billing_period === 'yearly' ? now()->addYears($years) : now()->addMonth(),
        ]);
    }

    private function createCompany(array $data, Plan $plan): Company
    {
        $company = Company::create(['name' => $data['company_name']]);

        Branch::create([
            'company_id' => $company->id,
            'name' => $data['branch_name'],
            'code' => $this->uniqueBranchCode($data['branch_name']),
        ]);

        User::create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        return $company;
    }

    private function uniqueBranchCode(string $branchName): string
    {
        $base = strtoupper(Str::slug($branchName, '-')) ?: 'BR';
        $code = $base;
        $suffix = 1;

        while (Branch::where('code', $code)->exists()) {
            $code = $base.'-'.(++$suffix);
        }

        return $code;
    }
}
