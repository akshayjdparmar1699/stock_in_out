<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ItemStock;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns the same "needs attention" conditions the dashboard already computes
 * live (low stock, customers gone quiet, customers over their credit limit)
 * into persisted, read/unread notification rows — synced on every fetch
 * rather than on a schedule, since there's no cron running in production.
 * A condition that's no longer true (item restocked, customer paid down)
 * has its notification removed on the next sync, so the bell only ever
 * shows what's still actually wrong.
 */
class NotificationService
{
    private const INACTIVE_DAYS = 3;

    /**
     * Syncs the branch's notification rows and pushes a real device
     * notification for whatever's genuinely new this call — called lazily
     * when the bell is opened, but also called straight from
     * invoice/stock/payment saves so low-stock and credit-limit alerts
     * reach a phone the instant they become true, not just whenever
     * someone next opens the app.
     */
    public static function sync(Branch $branch): void
    {
        $current = collect()
            ->merge(self::lowStockNotifications($branch))
            ->merge(self::inactiveCustomerNotifications($branch))
            ->merge(self::overLimitNotifications($branch));

        $currentKeys = $current->map(fn (array $row) => $row['type'].'|'.$row['dedupe_key'])->flip();

        $existing = AppNotification::query()->where('branch_id', $branch->id)->get();

        $toRemove = $existing->reject(
            fn (AppNotification $n) => $currentKeys->has($n->type.'|'.$n->dedupe_key)
        );
        if ($toRemove->isNotEmpty()) {
            AppNotification::destroy($toRemove->pluck('id'));
        }

        $existingKeys = $existing->map(fn (AppNotification $n) => $n->type.'|'.$n->dedupe_key)->flip();

        $newRows = $current->reject(fn (array $row) => $existingKeys->has($row['type'].'|'.$row['dedupe_key']));

        if ($newRows->isEmpty()) {
            return;
        }

        AppNotification::insert($newRows->map(fn (array $row) => [
            'branch_id' => $branch->id,
            'type' => $row['type'],
            'dedupe_key' => $row['dedupe_key'],
            'title' => $row['title'],
            'body' => $row['body'] ?? null,
            'url' => $row['url'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        self::pushNew($branch, $newRows);
    }

    /**
     * Pushed to every admin of the company (they oversee all branches) plus
     * whichever staff member is assigned to this specific branch — not to
     * every staff member company-wide, since a condition at one branch
     * isn't that branch's business.
     */
    private static function pushNew(Branch $branch, Collection $newRows): void
    {
        $recipients = User::query()
            ->where('company_id', $branch->company_id)
            ->where(fn ($q) => $q->where('role', User::ROLE_ADMIN)->orWhere('branch_id', $branch->id))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($newRows as $row) {
            PushNotificationService::sendToUsers($recipients, $row['title'], $row['body'] ?? '', $row['url'] ?? null);
        }
    }

    /**
     * @return Collection<int, array>
     */
    private static function lowStockNotifications(Branch $branch): Collection
    {
        return ItemStock::query()
            ->where('branch_id', $branch->id)
            ->with('item')
            ->whereHas('item', fn ($q) => $q->whereColumn('item_stocks.quantity', '<=', 'items.low_stock_threshold'))
            ->get()
            ->map(fn (ItemStock $stock) => [
                'type' => 'low_stock',
                'dedupe_key' => (string) $stock->item_id,
                'title' => "Low stock: {$stock->item->name}",
                'body' => rtrim(rtrim((string) $stock->quantity, '0'), '.')." {$stock->item->unit} left",
                'url' => route('stock.index'),
            ]);
    }

    /**
     * @return Collection<int, array>
     */
    private static function inactiveCustomerNotifications(Branch $branch): Collection
    {
        return Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branch->id))
            ->where('is_active', true)
            ->with(['invoices' => fn ($q) => $q->where('branch_id', $branch->id)->latest('invoice_date')->limit(1)])
            ->get()
            ->map(fn (Customer $customer) => [
                'customer' => $customer,
                'last_date' => $customer->invoices->first()?->invoice_date,
            ])
            ->filter(fn (array $row) => $row['last_date'] !== null && $row['last_date']->lt(now()->subDays(self::INACTIVE_DAYS)))
            ->map(fn (array $row) => [
                'type' => 'inactive_customer',
                'dedupe_key' => (string) $row['customer']->id,
                'title' => "No purchase from {$row['customer']->name} in ".self::INACTIVE_DAYS.'+ days',
                'body' => 'Last purchase: '.$row['last_date']->format('d M Y'),
                'url' => route('customers.show', $row['customer']),
            ])
            ->values();
    }

    /**
     * Computes each customer's due amount (opening balance + invoiced -
     * paid - credit balance) via correlated subqueries in one query,
     * instead of Customer::dueAmount()'s two queries run per customer —
     * with a few hundred customers on a remote (Supabase) connection,
     * the per-customer version took over ten seconds just for this one
     * category.
     *
     * @return Collection<int, array>
     */
    private static function overLimitNotifications(Branch $branch): Collection
    {
        $invoicedSub = DB::table('invoices')
            ->selectRaw('COALESCE(SUM(total), 0)')
            ->whereColumn('invoices.customer_id', 'customers.id');

        $paidSub = DB::table('invoices')
            ->selectRaw('COALESCE(SUM(paid_amount), 0)')
            ->whereColumn('invoices.customer_id', 'customers.id');

        return Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branch->id))
            ->where('is_active', true)
            ->where('credit_limit', '>', 0)
            ->select('customers.*')
            ->selectSub($invoicedSub, 'invoiced_total')
            ->selectSub($paidSub, 'paid_total')
            ->get()
            ->map(fn (Customer $customer) => [
                'customer' => $customer,
                'due' => round(
                    (float) $customer->opening_balance
                    + (float) $customer->invoiced_total
                    - (float) $customer->paid_total
                    - (float) $customer->credit_balance,
                    2
                ),
            ])
            ->filter(fn (array $row) => $row['due'] > (float) $row['customer']->credit_limit)
            ->map(fn (array $row) => [
                'type' => 'credit_limit',
                'dedupe_key' => (string) $row['customer']->id,
                'title' => "{$row['customer']->name} is over their credit limit",
                'body' => '₹'.number_format($row['due'], 2).' owed, time to collect',
                'url' => route('customers.show', $row['customer']),
            ])
            ->values();
    }
}
