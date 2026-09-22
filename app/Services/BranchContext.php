<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Resolves which branch the current request should operate on.
 *
 * Staff users are locked to the branch they are assigned to. Admins have no
 * fixed branch and pick one via the branch switcher, kept in the session.
 */
class BranchContext
{
    public const SESSION_KEY = 'current_branch_id';

    public static function id(): ?int
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if (! $user->isAdmin()) {
            return $user->branch_id;
        }

        return Session::get(self::SESSION_KEY) ?? Branch::query()->where('is_active', true)->value('id');
    }

    public static function current(): ?Branch
    {
        $id = self::id();

        return $id ? Branch::find($id) : null;
    }

    public static function set(int $branchId): void
    {
        Session::put(self::SESSION_KEY, $branchId);
    }
}
