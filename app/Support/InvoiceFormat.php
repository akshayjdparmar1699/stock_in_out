<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Resolves which of the selectable formats an invoice renders with — every
 * invoice always renders with one of these; there's no separate "original"
 * design any more. A branch that's never touched this setting falls back to
 * config('invoice_formats.default') (GST Tax Invoice).
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

    public static function keyFor(mixed $invoice, mixed $branch): string
    {
        $key = data_get($invoice, 'invoice_format') ?: data_get($branch, 'invoice_format');

        return $key && array_key_exists((string) $key, self::all())
            ? (string) $key
            : config('invoice_formats.default');
    }

    public static function viewFor(mixed $invoice, mixed $branch): ?string
    {
        return Arr::get(self::all(), self::keyFor($invoice, $branch).'.view');
    }

    public static function accentFor(mixed $invoice, mixed $branch): string
    {
        return data_get($branch, 'invoice_accent')
            ?: Arr::get(self::all(), self::keyFor($invoice, $branch).'.accent', '#6A00D9');
    }

    /**
     * Paper for Pdf::setPaper(). Every format prints on A4 except the
     * thermal receipt, which is 80mm wide (226.77pt) with a height
     * estimated from the item count so the whole slip stays on one page
     * instead of being cut off or splitting onto a second one.
     */
    public static function paperFor(mixed $invoice, mixed $branch): string|array
    {
        $paper = Arr::get(self::all(), self::keyFor($invoice, $branch).'.paper', 'a4');

        if ($paper !== 'thermal-80') {
            return $paper;
        }

        $itemCount = data_get($invoice, 'items') ? count(data_get($invoice, 'items')) : 0;
        $isGst = (bool) data_get($invoice, 'is_gst_invoice', false);
        $hasQr = method_exists($branch, 'hasPaymentDetails') && $branch->hasPaymentDetails();
        $heightPt = 360 + ($itemCount * ($isGst ? 40 : 30)) + ($isGst ? 120 : 0) + ($hasQr ? 110 : 0);

        return [0, 0, 226.77, $heightPt];
    }
}
