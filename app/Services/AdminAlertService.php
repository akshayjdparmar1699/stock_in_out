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

    private static function url(string $message): string
    {
        return 'https://wa.me/'.self::ADMIN_WHATSAPP_NUMBER.'?text='.rawurlencode($message);
    }
}
