<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applied per module in routes/web.php as ->middleware('module:customers')
 * etc. — maps the actual controller method being hit to a view/add/edit/
 * delete action automatically, so adding this to a resource route needs no
 * per-action route splitting. See App\Models\RolePermission.
 */
class CheckModulePermission
{
    private const ACTION_FOR_METHOD = [
        'index' => 'view',
        'show' => 'view',
        'search' => 'view',
        'export' => 'view',
        'statementPdf' => 'view',
        'pdf' => 'view',
        'create' => 'add',
        'store' => 'add',
        'edit' => 'edit',
        'update' => 'edit',
        'toggleActive' => 'edit',
        'storePayment' => 'edit',
        'destroyPayment' => 'edit',
        'destroy' => 'delete',
    ];

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $method = $request->route()?->getActionMethod();
        $action = self::ACTION_FOR_METHOD[$method] ?? null;

        if ($action && ! $request->user()?->canAccess($module, $action)) {
            abort(403, "You don't have permission to do this.");
        }

        return $next($request);
    }
}
