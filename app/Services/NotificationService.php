<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ItemStock;
use App\Models\Subscription;
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
                'body' => rtrim(rtrim((string) $stock->quantity, '0'), '.')." {$stock->item->unit} left — {$branch->name}",
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
                'body' => 'Last purchase: '.$row['last_date']->format('d M Y')." — {$branch->name}",
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
                'body' => '₹'.number_format($row['due'], 2).' owed, time to collect — '.$branch->name,
                'url' => route('customers.show', $row['customer']),
            ])
            ->values();
    }

    /**
     * Warns a company whose trial or paid period is about to lapse, a few
     * days ahead of the hard paywall (CheckSubscriptionActive) kicking in.
     * Called from that same middleware on every request while the
     * subscription is still active — there's no cron running in production
     * (see this class's own docblock), so this rides on real request
     * traffic exactly like every other notification here, instead of
     * depending on a scheduled job that may never actually fire.
     *
     * AppNotification rows are branch_id-scoped (NotificationController's
     * bell always reads the currently-selected branch), so a company-wide
     * event like this is written once per branch rather than needing a
     * schema change — the existing (branch_id, type, dedupe_key) unique
     * constraint already makes that safe to call repeatedly.
     */
    public static function syncSubscriptionExpiry(Company $company): void
    {
        $subscription = $company->subscription;

        if (! $subscription || ! $subscription->isActive() || $subscription->is_lifetime) {
            return;
        }

        $targetDate = $subscription->status === Subscription::STATUS_TRIALING
            ? $subscription->trial_ends_at
            : $subscription->current_period_end;

        if ($targetDate === null || $targetDate->isPast()) {
            return;
        }

        $daysLeft = (int) now()->startOfDay()->diffInDays($targetDate->copy()->startOfDay());

        if ($daysLeft > 4) {
            return;
        }

        $dedupeKey = (string) $subscription->id.'_'.$targetDate->format('Y-m-d');
        $type = 'subscription_expiring';

        $branchIds = $company->branches()->pluck('id');

        if ($branchIds->isEmpty()) {
            return;
        }

        // Checked per-branch, not globally — a global exists() check would
        // wrongly skip a branch that doesn't have its row yet just because
        // some other branch of the same company already does (the unique
        // constraint is per-branch, so "already handled" has to be too).
        $alreadyHave = AppNotification::query()
            ->where('type', $type)
            ->where('dedupe_key', $dedupeKey)
            ->whereIn('branch_id', $branchIds)
            ->pluck('branch_id');

        $missingBranchIds = $branchIds->diff($alreadyHave);

        if ($missingBranchIds->isEmpty()) {
            return;
        }

        $isTrial = $subscription->status === Subscription::STATUS_TRIALING;
        $planName = $subscription->plan->name ?? 'your plan';
        $whenText = $daysLeft <= 0 ? 'today' : ($daysLeft === 1 ? 'in 1 day' : "in {$daysLeft} days");

        $title = $isTrial
            ? "Your free trial ends {$whenText}"
            : "Your {$planName} plan ends {$whenText}";

        $body = $isTrial
            ? 'Subscribe within 2 days to keep billing, managing stock and seeing reports without interruption.'
            : "Renew your {$planName} plan to avoid losing access to billing and reports.";

        $url = route('subscription.renew');

        $rows = $missingBranchIds->map(fn (int $branchId) => [
            'branch_id' => $branchId,
            'type' => $type,
            'dedupe_key' => $dedupeKey,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        AppNotification::insertOrIgnore($rows);

        // Reaching here means at least one branch just got its row for the
        // first time, so a push is warranted — it won't fire again for this
        // dedupe key once every branch already has one.
        $admins = $company->users()->where('role', User::ROLE_ADMIN)->get();

        if ($admins->isNotEmpty()) {
            PushNotificationService::sendToUsers($admins, $title, $body, $url);
        }
    }
}
