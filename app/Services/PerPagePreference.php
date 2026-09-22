<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * A single "rows per page" preference shared across every list page
 * (Invoices, Customers, Suppliers, Items, Stock, Purchases). Changing it
 * on any one page changes it everywhere, for the current session.
 */
class PerPagePreference
{
    private const SESSION_KEY = 'per_page';

    public const OPTIONS = [20, 50, 100];

    private const DEFAULT = 20;

    public static function get(): int
    {
        $value = (int) Session::get(self::SESSION_KEY, self::DEFAULT);

        return in_array($value, self::OPTIONS, true) ? $value : self::DEFAULT;
    }

    public static function set(int $value): void
    {
        if (in_array($value, self::OPTIONS, true)) {
            Session::put(self::SESSION_KEY, $value);
        }
    }
}
