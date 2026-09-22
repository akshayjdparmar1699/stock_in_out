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

    public function __invoke(Request $request): View
    {
        $branchId = BranchContext::id();
        $branch = BranchContext::current();
        $period = $request->string('period', 'today')->toString();

        if (! in_array($period, ['today', 'week', 'month', 'all'], true)) {
            $period = 'today';
        }

        [$from, $to] = match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'all' => [null, null],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        $salesQuery = Invoice::query()->where('branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]));

        $purchasesQuery = Purchase::query()->where('branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('purchase_date', [$from, $to]));

        // AJAX partial refresh for a single widget's pagination — skip the
        // rest of the (heavier) dashboard computation entirely.
        if ($widget = $request->string('widget')->toString()) {
            return match ($widget) {
                'invoices' => view('dashboard.partials.invoices', [
                    'recentInvoices' => (clone $salesQuery)->with('customer')->latest('invoice_date')->latest('id')
                        ->paginate(self::PER_PAGE, ['*'], 'invoices_page')->withQueryString(),
                ]),
                'purchases' => view('dashboard.partials.purchases', [
                    'recentPurchases' => (clone $purchasesQuery)->with('supplier')->latest('purchase_date')->latest('id')
                        ->paginate(self::PER_PAGE, ['*'], 'purchases_page')->withQueryString(),
                ]),
                'low-stock' => view('dashboard.partials.low-stock', [
                    'lowStockItems' => $this->lowStockItemsQuery($branchId)
                        ->paginate(self::PER_PAGE, ['*'], 'stock_page')->withQueryString(),
                ]),
                'followup' => view('dashboard.partials.followup', [
                    'followUpCustomers' => $this->paginateCollection($this->buildFollowUpCustomers($branch, $branchId), $request, 'followup_page'),
                ]),
                'expenses' => view('dashboard.partials.expenses', [
                    'recentExpenses' => Expense::query()->where('branch_id', $branchId)->with(['staffMember', 'user'])
                        ->latest('expense_date')->latest('id')
                        ->paginate(self::PER_PAGE, ['*'], 'expenses_page')->withQueryString(),
                ]),
                default => abort(404),
            };
        }

        $salesTotal = (clone $salesQuery)->sum('total');
        $salesCount = (clone $salesQuery)->count();

        $purchasesTotal = (clone $purchasesQuery)->sum('total');
        $purchasesCount = (clone $purchasesQuery)->count();

        $profit = (float) DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->where('invoices.branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('invoices.invoice_date', [$from, $to]))
            ->selectRaw('COALESCE(SUM((invoice_items.unit_price - items.purchase_price) * invoice_items.quantity), 0) as profit')
            ->value('profit');

        $lowStockCount = $this->lowStockItemsQuery($branchId)->count();

        $totalStockValue = ItemStock::query()
            ->where('branch_id', $branchId)
            ->join('items', 'items.id', '=', 'item_stocks.item_id')
            ->select(DB::raw('SUM(item_stocks.quantity * items.selling_price) as value'))
            ->value('value') ?? 0;

        $recentInvoices = (clone $salesQuery)
            ->with('customer')
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(self::PER_PAGE, ['*'], 'invoices_page')
            ->withQueryString();

        $recentPurchases = (clone $purchasesQuery)
            ->with('supplier')
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(self::PER_PAGE, ['*'], 'purchases_page')
            ->withQueryString();

        $lowStockItems = $this->lowStockItemsQuery($branchId)
            ->paginate(self::PER_PAGE, ['*'], 'stock_page')
            ->withQueryString();

        $lowStockAdminUrl = null;
        if ($branch && $lowStockItems->isNotEmpty()) {
            $lowStockAdminUrl = AdminAlertService::lowStockUrl($branch, $lowStockItems->getCollection()->map(fn (ItemStock $stock) => [
                'name' => $stock->item->name,
                'quantity' => $stock->quantity,
                'unit' => $stock->item->unit,
            ]));
        }

        $followUpCustomers = $this->paginateCollection($this->buildFollowUpCustomers($branch, $branchId), $request, 'followup_page');

        $customerDueTotal = Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->get()
            ->sum(fn (Customer $c) => $c->dueAmount());

        $supplierDueTotal = (float) Purchase::query()
            ->where('branch_id', $branchId)
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as due')
            ->value('due');

        $totalUpad = (float) Expense::query()
            ->where('branch_id', $branchId)
            ->where('category', 'upad')
            ->sum('amount');

        $expenseBreakdown = Expense::query()
            ->where('branch_id', $branchId)
            ->when($from, fn ($q) => $q->whereBetween('expense_date', [$from, $to]))
            ->selectRaw('category, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $expenseTotal = (float) $expenseBreakdown->sum();

        $recentExpenses = Expense::query()
            ->where('branch_id', $branchId)
            ->with(['staffMember', 'user'])
            ->latest('expense_date')
            ->latest('id')
            ->paginate(self::PER_PAGE, ['*'], 'expenses_page')
            ->withQueryString();

        return view('dashboard', [
            'period' => $period,
            'salesTotal' => $salesTotal,
            'salesCount' => $salesCount,
            'purchasesTotal' => $purchasesTotal,
            'purchasesCount' => $purchasesCount,
            'profit' => $profit,
            'lowStockCount' => $lowStockCount,
            'totalStockValue' => $totalStockValue,
            'recentInvoices' => $recentInvoices,
            'recentPurchases' => $recentPurchases,
            'lowStockItems' => $lowStockItems,
            'lowStockAdminUrl' => $lowStockAdminUrl,
            'followUpCustomers' => $followUpCustomers,
            'customerDueTotal' => $customerDueTotal,
            'supplierDueTotal' => $supplierDueTotal,
            'totalUpad' => $totalUpad,
            'expenseBreakdown' => $expenseBreakdown,
            'expenseTotal' => $expenseTotal,
            'recentExpenses' => $recentExpenses,
        ]);
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
            ->filter(fn (array $row) => $row['last_date'] !== null && $row['last_date']->lt(now()->subDays(7)))
            ->sortBy('last_date')
            ->values()
            ->map(fn (array $row) => [
                'customer' => $row['customer'],
                'last_date' => $row['last_date'],
                'whatsapp_url' => AdminAlertService::inactiveCustomerUrl($branch, $row['customer'], $row['last_date']->format('d M Y')),
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
