<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\ItemStock;
use App\Models\Purchase;
use App\Services\AdminAlertService;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PER_PAGE = 5;

    /**
     * Renders only the page shell (period switcher, quick-add buttons, and
     * one placeholder per section) — none of the actual figures are
     * queried here. Each section only runs its own query when its refresh
     * icon is clicked, via the widget branch below, so a page load never
     * pays for every section's query at once, and a section nobody looks
     * at today never runs at all.
     */
    public function __invoke(Request $request): View
    {
        $period = $this->resolvePeriod($request);

        if ($widget = $request->string('widget')->toString()) {
            return $this->renderWidget($widget, $request, $period);
        }

        return view('dashboard', ['period' => $period]);
    }

    private function resolvePeriod(Request $request): string
    {
        $period = $request->string('period', 'today')->toString();

        return in_array($period, ['today', 'week', 'month', 'all'], true) ? $period : 'today';
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'all' => [null, null],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function renderWidget(string $widget, Request $request, string $period): View
    {
        $branchId = BranchContext::id();
        $branch = BranchContext::current();
        [$from, $to] = $this->periodRange($period);

        $salesQuery = Invoice::query()->where('branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]));

        $purchasesQuery = Purchase::query()->where('branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('purchase_date', [$from, $to]));

        return match ($widget) {
            'stats' => view('dashboard.partials.stats', [
                'period' => $period,
                'salesTotal' => (clone $salesQuery)->sum('total'),
                'salesCount' => (clone $salesQuery)->count(),
                'purchasesTotal' => (clone $purchasesQuery)->sum('total'),
                'purchasesCount' => (clone $purchasesQuery)->count(),
                'profit' => $this->calculateProfit($branchId, $from, $to),
                'totalStockValue' => $this->totalStockValue($branchId),
                'lowStockCount' => $this->lowStockItemsQuery($branchId)->count(),
            ]),
            'dues' => view('dashboard.partials.dues', [
                'customerDueTotal' => $this->customerDueTotal($branchId),
                'supplierDueTotal' => $this->supplierDueTotal($branchId),
                'totalUpad' => $this->totalUpad($branchId),
            ]),
            'invoices' => view('dashboard.partials.invoices', [
                'recentInvoices' => (clone $salesQuery)->with('customer')->latest('invoice_date')->latest('id')
                    ->paginate(self::PER_PAGE, ['*'], 'invoices_page')->withQueryString(),
            ]),
            'purchases' => view('dashboard.partials.purchases', [
                'recentPurchases' => (clone $purchasesQuery)->with('supplier')->latest('purchase_date')->latest('id')
                    ->paginate(self::PER_PAGE, ['*'], 'purchases_page')->withQueryString(),
            ]),
            'low-stock' => (function () use ($branch, $branchId) {
                $lowStockItems = $this->lowStockItemsQuery($branchId)
                    ->paginate(self::PER_PAGE, ['*'], 'stock_page')->withQueryString();

                $lowStockAdminUrl = null;
                if ($branch && $lowStockItems->isNotEmpty()) {
                    $lowStockAdminUrl = AdminAlertService::lowStockUrl($branch, $lowStockItems->getCollection()->map(fn (ItemStock $stock) => [
                        'name' => $stock->item->name,
                        'quantity' => $stock->quantity,
                        'unit' => $stock->item->unit,
                    ]));
                }

                return view('dashboard.partials.low-stock', [
                    'lowStockItems' => $lowStockItems,
                    'lowStockAdminUrl' => $lowStockAdminUrl,
                ]);
            })(),
            'followup' => view('dashboard.partials.followup', [
                'followUpCustomers' => $this->paginateCollection($this->buildFollowUpCustomers($branch, $branchId), $request, 'followup_page'),
            ]),
            'over-limit' => view('dashboard.partials.over-limit', [
                'overLimitCustomers' => $this->paginateCollection($this->buildOverLimitCustomers($branch, $branchId), $request, 'over_limit_page'),
            ]),
            'expense-breakdown' => (function () use ($branchId, $from, $to, $period) {
                $expenseBreakdown = Expense::query()
                    ->where('branch_id', $branchId)
                    ->when($from, fn ($q) => $q->whereBetween('expense_date', [$from, $to]))
                    ->selectRaw('category, COALESCE(SUM(amount), 0) as total')
                    ->groupBy('category')
                    ->pluck('total', 'category');

                return view('dashboard.partials.expense-breakdown', [
                    'period' => $period,
                    'expenseBreakdown' => $expenseBreakdown,
                    'expenseTotal' => (float) $expenseBreakdown->sum(),
                ]);
            })(),
            'expenses' => view('dashboard.partials.expenses', [
                'recentExpenses' => Expense::query()->where('branch_id', $branchId)->with(['staffMember', 'user'])
                    ->latest('expense_date')->latest('id')
                    ->paginate(self::PER_PAGE, ['*'], 'expenses_page')->withQueryString(),
            ]),
            default => abort(404),
        };
    }

    /**
     * Cost of goods sold per line comes from the real cost of whichever
     * batch(es) that specific sale was FIFO-allocated from (via the line's
     * own stock_movement_id), not the item's current/latest purchase
     * price — so a rate change between two purchases of the same item
     * shows up as a genuinely different margin on old stock versus new,
     * instead of every past sale silently re-pricing itself to today's
     * rate. Lines saved before this was tracked (stock_movement_id is
     * null) fall back to the old flat-rate estimate.
     */
    private function calculateProfit(int $branchId, $from, $to): float
    {
        return (float) DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->where('invoices.branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('invoices.invoice_date', [$from, $to]))
            ->selectRaw('
                COALESCE(SUM(
                    invoice_items.total - COALESCE(
                        (SELECT SUM(stock_batch_allocations.quantity * stock_batches.unit_cost)
                         FROM stock_batch_allocations
                         JOIN stock_batches ON stock_batches.id = stock_batch_allocations.stock_batch_id
                         WHERE stock_batch_allocations.stock_movement_id = invoice_items.stock_movement_id),
                        invoice_items.base_quantity * items.purchase_price
                    )
                ), 0) as profit
            ')
            ->value('profit');
    }

    private function totalStockValue(int $branchId): float
    {
        return (float) (ItemStock::query()
            ->where('branch_id', $branchId)
            ->join('items', 'items.id', '=', 'item_stocks.item_id')
            ->select(DB::raw('SUM(item_stocks.quantity * items.selling_price) as value'))
            ->value('value') ?? 0);
    }

    private function customerDueTotal(int $branchId): float
    {
        return (float) Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->get()
            ->sum(fn (Customer $c) => $c->dueAmount());
    }

    private function supplierDueTotal(int $branchId): float
    {
        return (float) Purchase::query()
            ->where('branch_id', $branchId)
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as due')
            ->value('due');
    }

    private function totalUpad(int $branchId): float
    {
        return (float) Expense::query()
            ->where('branch_id', $branchId)
            ->where('category', 'upad')
            ->sum('amount');
    }

    private function lowStockItemsQuery(int $branchId)
    {
        return ItemStock::query()
            ->where('branch_id', $branchId)
            ->with('item')
            ->whereHas('item', fn ($q) => $q->whereColumn('item_stocks.quantity', '<=', 'items.low_stock_threshold'));
    }

    private function buildFollowUpCustomers(?Branch $branch, int $branchId)
    {
        if (! $branch) {
            return collect();
        }

        return Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->where('is_active', true)
            ->with(['invoices' => fn ($q) => $q->where('branch_id', $branchId)->latest('invoice_date')->limit(1)])
            ->get()
            ->map(fn (Customer $customer) => [
                'customer' => $customer,
                'last_date' => $customer->invoices->first()?->invoice_date,
            ])
            ->filter(fn (array $row) => $row['last_date'] !== null && $row['last_date']->lt(now()->subDays(3)))
            ->sortBy('last_date')
            ->values()
            ->map(fn (array $row) => [
                'customer' => $row['customer'],
                'last_date' => $row['last_date'],
                'whatsapp_url' => AdminAlertService::inactiveCustomerUrl($branch, $row['customer'], $row['last_date']->format('d M Y')),
            ]);
    }

    private function buildOverLimitCustomers(?Branch $branch, int $branchId)
    {
        if (! $branch) {
            return collect();
        }

        return Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->where('is_active', true)
            ->where('credit_limit', '>', 0)
            ->get()
            ->filter(fn (Customer $customer) => $customer->isOverCreditLimit())
            ->map(fn (Customer $customer) => [
                'customer' => $customer,
                'due' => $customer->dueAmount(),
            ])
            ->sortByDesc('due')
            ->values()
            ->map(fn (array $row) => [
                'customer' => $row['customer'],
                'due' => $row['due'],
                'whatsapp_url' => AdminAlertService::creditLimitUrl($branch, $row['customer'], $row['due'], (float) $row['customer']->credit_limit),
            ]);
    }

    private function paginateCollection($items, Request $request, string $pageName): LengthAwarePaginator
    {
        $items = $items instanceof Collection ? $items : collect($items);
        $page = (int) $request->input($pageName, 1);
        $slice = $items->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        return new LengthAwarePaginator($slice, $items->count(), self::PER_PAGE, $page, [
            'path' => $request->url(),
            'pageName' => $pageName,
            'query' => $request->query(),
        ]);
    }
}
