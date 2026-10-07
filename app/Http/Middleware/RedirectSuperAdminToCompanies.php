<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The super-admin (platform owner) belongs to no company, so every
 * company-scoped page (dashboard, invoices, stock, ...) would either
 * break or leak across companies if they landed on it. Wraps the whole
 * normal app so they're bounced to Companies instead, rather than
 * needing every controller to guard against a user with no company_id.
 */
class RedirectSuperAdminToCompanies
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuperAdmin()) {
            return redirect()->route('companies.index');
        }

        return $next($request);
    }
}
