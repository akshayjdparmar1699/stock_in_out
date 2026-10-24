<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per (company, module) toggling what the "staff" role can do
 * there. Admins and super admins never consult this — see
 * User::canAccess(). A module with no row at all is unrestricted, so a
 * company that's never opened Settings > Role permission keeps staff's
 * original, unrestricted access.
 */
class RolePermission extends Model
{
    protected $fillable = [
        'company_id',
        'module',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_add' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    /**
     * Every module staff can normally reach at all (Branches, Users and
     * Settings itself are already admin-only at the route level, so they
     * have no row here). Labels are what an admin sees in Settings.
     */
    public const MODULES = [
        'customers' => 'Customers',
        'items' => 'Items',
        'invoices' => 'Invoices',
        'stock' => 'Stock',
        'purchases' => 'Purchases',
        'suppliers' => 'Suppliers',
        'staff' => 'Staff & Partners',
        'expenses' => 'Expenses',
    ];

    /**
     * One query per company per request (not per check) — cached in a
     * static array that resets with every new request, so a page that
     * checks several modules (e.g. building the sidebar) doesn't re-query
     * for each one.
     */
    public static function forCompany(int $companyId): \Illuminate\Support\Collection
    {
        static $cache = [];

        return $cache[$companyId] ??= static::where('company_id', $companyId)->get()->keyBy('module');
    }

    public static function allows(int $companyId, string $module, string $action): bool
    {
        $row = static::forCompany($companyId)->get($module);

        if (! $row) {
            return true;
        }

        return (bool) $row->{"can_{$action}"};
    }
}
