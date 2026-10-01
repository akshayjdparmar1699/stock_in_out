<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Support\Collection;

/**
 * Builds click-to-send WhatsApp links (wa.me) that notify the shop owner/admin
 * about low stock or customers who have gone quiet. There is no WhatsApp
 * Business API account configured, so these are opened by an admin clicking
 * a button rather than sent automatically.
 */
class AdminAlertService
{
    private const ADMIN_WHATSAPP_NUMBER = '919664604781';

    /**
     * @param  Collection<int, array{name: string, quantity: float|int, unit: string}>  $lowStockLines
     */
    public static function lowStockUrl(Branch $branch, Collection $lowStockLines): string
    {
        $lines = $lowStockLines
            ->map(fn (array $line) => "- {$line['name']}: {$line['quantity']} {$line['unit']} left")
            ->implode("\n");

        $message = "Low stock alert ({$branch->name}):\n\n{$lines}\n\nPlease reorder these items.";

        return self::url($message);
    }

    public static function inactiveCustomerUrl(Branch $branch, Customer $customer, ?string $lastPurchaseDate): string
    {
        $lastPurchaseLine = $lastPurchaseDate
            ? "Last purchase: {$lastPurchaseDate}."
            : 'No purchase on record.';

        $message = "Customer alert ({$branch->name}):\n{$customer->name} ({$customer->phone}) has not purchased in the last 3 days. {$lastPurchaseLine}";

        return self::url($message);
    }

    public static function creditLimitUrl(Branch $branch, Customer $customer, float $due, float $limit): string
    {
        $message = "Credit limit alert ({$branch->name}):\n{$customer->name} ({$customer->phone}) owes ₹".number_format($due, 2)
            .", over their ₹".number_format($limit, 2)." limit. Time to collect payment.";

        return self::url($message);
    }

    /**
     * Opens a chat with the customer themselves (not the admin), addressed
     * to them directly, so a tap can go straight to asking them for the
     * money rather than just reminding the shop owner to. Returns null
     * when the stored phone number isn't usable — customer phone numbers
     * get typed in a lot of different ways (spaces, +91, or sometimes not
     * a phone number at all), so this is deliberately conservative rather
     * than ever handing back a dead wa.me link.
     */
    public static function creditLimitCustomerUrl(Customer $customer, float $due, float $limit): ?string
    {
        $number = self::normalizeIndianPhone($customer->phone);

        if (! $number) {
            return null;
        }

        $message = "Hi {$customer->name}, your outstanding balance with us is ₹".number_format($due, 2)
            .", which is over your approved credit limit of ₹".number_format($limit, 2)
            .". Please clear this at your earliest. Thank you.";

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    private static function normalizeIndianPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return null;
    }

    private static function url(string $message): string
    {
        return 'https://wa.me/'.self::ADMIN_WHATSAPP_NUMBER.'?text='.rawurlencode($message);
    }
}
