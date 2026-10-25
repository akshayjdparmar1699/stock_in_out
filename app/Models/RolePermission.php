<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per (user, module) toggling what that specific person can do
 * there — set when they're added or edited, on the Users page. Admins and
 * super admins never consult this at all — see User::canAccess(). A module
 * with no row for a user is unrestricted, so a staff login nobody has
 * configured yet keeps full access until an admin explicitly limits them.
 */
class RolePermission extends Model
{
    protected $fillable = [
        'user_id',
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
     * have no row here). Labels are what an admin sees when adding/editing
     * a user.
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
     * One query per user per request (not per check) — cached in a static
     * array that resets with every new request, so a page that checks
     * several modules (e.g. building the sidebar) doesn't re-query for
     * each one.
     */
    public static function forUser(int $userId): \Illuminate\Support\Collection
    {
        static $cache = [];

        return $cache[$userId] ??= static::where('user_id', $userId)->get()->keyBy('module');
    }

    public static function allows(int $userId, string $module, string $action): bool
    {
        $row = static::forUser($userId)->get($module);

        if (! $row) {
            return true;
        }

        return (bool) $row->{"can_{$action}"};
    }

    /**
     * Saves every module's permissions for one user in one go — called
     * right after that user is created or updated. $modules is the raw
     * `modules` array from the request: ['customers' => ['view', 'edit'],
     * ...], only present for a module/action the checkbox was actually
     * ticked.
     */
    public static function syncForUser(int $userId, array $modules): void
    {
        foreach (array_keys(self::MODULES) as $module) {
            $flags = $modules[$module] ?? [];

            static::updateOrCreate(
                ['user_id' => $userId, 'module' => $module],
                [
                    'can_view' => in_array('view', $flags, true),
                    'can_add' => in_array('add', $flags, true),
                    'can_edit' => in_array('edit', $flags, true),
                    'can_delete' => in_array('delete', $flags, true),
                ]
            );
        }
    }
}
