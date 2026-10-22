<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a company whose subscription has lapsed from doing anything that
 * changes data, while still letting GET requests render normally — the
 * blocking modal (layouts/partials/subscription-blocked-modal) is overlaid
 * on top of that real page by layouts/app.blade.php via the shared
 * `subscriptionBlocked` variable set below. Super admins belong to no
 * company and are already routed away before this runs; a company with no
 * subscription row at all is let through rather than locked out, since
 * that's an unexpected data state this middleware has no business guessing
 * about.
 */
class CheckSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $user->loadMissing('company.subscription.plan');
        $subscription = $user->company?->subscription;

        if (! $subscription || $subscription->isActive()) {
            return $next($request);
        }

        if (! $request->isMethod('get')) {
            // Redirecting (rather than letting the write happen) means no
            // data gets mutated while unpaid; the dashboard GET that follows
            // re-runs this same check and shows the modal again.
            return redirect()->route('dashboard');
        }

        View::share('subscriptionBlocked', [
            'company' => $user->company,
            'subscription' => $subscription,
            'plan' => $subscription->plan,
        ]);

        return $next($request);
    }
}
