<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Resolves which of the 5 selectable formats (if any) an invoice renders
 * with. Returning null from keyFor() means "none of these" — the branch
 * hasn't picked one, so the invoice renders with the app's own original
 * design instead, exactly as it always has.
 *
 * Priority for a chosen format: the one saved on the invoice itself (set
 * once, at creation, from the branch's setting at that time) first, then
 * the branch's current setting — so changing a branch's format later never
 * repaints a bill already shared with a customer.
 */
class InvoiceFormat
{
    /**
     * Formats whose Blade view exists, so Settings never offers a format
     * that cannot render.
     */
    public static function all(): array
    {
        return array_filter(
            config('invoice_formats.formats', []),
            fn (array $format) => view()->exists($format['view'])
        );
    }

    public static function keyFor(mixed $invoice, mixed $branch): ?string
    {
        $key = data_get($invoice, 'invoice_format') ?: data_get($branch, 'invoice_format');

        return $key && array_key_exists((string) $key, self::all()) ? (string) $key : null;
    }

    public static function viewFor(mixed $invoice, mixed $branch): ?string
    {
        $key = self::keyFor($invoice, $branch);

        return $key ? Arr::get(self::all(), $key.'.view') : null;
    }

    public static function accentFor(mixed $invoice, mixed $branch): string
    {
        $key = self::keyFor($invoice, $branch);

        return data_get($branch, 'invoice_accent')
            ?: ($key ? Arr::get(self::all(), $key.'.accent', '#6A00D9') : '#6A00D9');
    }
}
