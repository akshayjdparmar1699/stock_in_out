<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\ItemStock;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Company-wide reporting over data every other controller already owns —
 * this never writes to invoices/purchases/expenses itself, it only
 * aggregates them, scoped to whichever set of the company's own branches
 * the admin has selected (all of them by default).
 */
class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $branches = Branch::where('company_id', $user->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, 'all', ['today', '7days', 'month', 'all']);
        [$from, $to] = $this->periodRange($period);

        $salesTotal = $this->salesTotal($branchIds, $from, $to);
        $profit = $this->totalProfit($branchIds, $from, $to);
        $expensesTotal = $this->expensesTotal($branchIds, $from, $to);
        $netProfit = round($profit - $expensesTotal, 2);

        return view('sales-reports.index', [
            'branches' => $branches,
            'selectedBranchId' => $request->string('branch_id', 'all')->toString(),
            'period' => $period,
            'salesTotal' => $salesTotal,
            'grossProfit' => $profit,
            'netProfit' => $netProfit,
            'customerDueTotal' => $this->customerDueTotal($branchIds),
            'supplierDueTotal' => $this->supplierDueTotal($branchIds),
            'stockValue' => $this->stockValue($branchIds),
            'purchasesTotal' => $this->purchasesTotal($branchIds, $from, $to),
            'expensesTotal' => $expensesTotal,
            'bestItem' => $this->bestItemName($branchIds, $from, $to),
            'whatsappEnabled' => (bool) $user->company?->daily_whatsapp_reports_enabled,
        ]);
    }

    public function sales(Request $request): View
    {
        $branches = Branch::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, '7days', ['7days', 'month']);
        [$from, $to] = $this->periodRange($period);

        return view('sales-reports.sales', $this->salesReportData($branchIds, $from, $to) + [
            'branches' => $branches,
            'selectedBranchId' => $request->string('branch_id', 'all')->toString(),
            'period' => $period,
        ]);
    }

    public function salesPdf(Request $request): Response
    {
        $branches = Branch::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, '7days', ['7days', 'month']);
        [$from, $to] = $this->periodRange($period);

        $data = $this->salesReportData($branchIds, $from, $to);
        $data['periodLabel'] = $period === 'month' ? 'This month' : 'Last 7 days';
        $data['companyName'] = $request->user()->company?->name ?? 'Company';

        return Pdf::loadView('sales-reports.pdf.sales', $data)->stream('sales-report.pdf');
    }

    public function salesExcel(Request $request): StreamedResponse
    {
        $branches = Branch::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, '7days', ['7days', 'month']);
        [$from, $to] = $this->periodRange($period);

        $topItems = $this->topItems($branchIds, $from, $to, 1000);

        return response()->streamDownload(function () use ($topItems) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Item', 'Sold Qty', 'Unit', 'Sales', 'Profit']);

            foreach ($topItems as $row) {
                fputcsv($out, [
                    $row->name,
                    rtrim(rtrim(number_format((float) $row->sold_qty, 2), '0'), '.'),
                    $row->unit,
                    number_format((float) $row->sales_total, 2),
                    number_format((float) $row->profit, 2),
                ]);
            }

            fclose($out);
        }, 'sales-report-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function profitLoss(Request $request): View
    {
        $branches = Branch::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, 'all', ['month', 'all']);
        [$from, $to] = $this->periodRange($period);

        return view('sales-reports.profit-loss', $this->profitLossData($branchIds, $from, $to) + [
            'branches' => $branches,
            'selectedBranchId' => $request->string('branch_id', 'all')->toString(),
            'period' => $period,
        ]);
    }

    public function profitLossPdf(Request $request): Response
    {
        $branches = Branch::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, 'all', ['month', 'all']);
        [$from, $to] = $this->periodRange($period);

        $data = $this->profitLossData($branchIds, $from, $to);
        $data['periodLabel'] = $period === 'month' ? 'This month' : 'All time';
        $data['companyName'] = $request->user()->company?->name ?? 'Company';

        return Pdf::loadView('sales-reports.pdf.profit-loss', $data)->stream('profit-and-loss.pdf');
    }

    public function itemWiseProfit(Request $request): View
    {
        $branches = Branch::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        $branchIds = $this->resolveBranchIds($request, $branches);
        $period = $this->resolvePeriod($request, 'all', ['today', '7days', 'month', 'all']);
        [$from, $to] = $this->periodRange($period);

        $sort = $request->string('sort', 'profit')->toString();
        $sortColumn = in_array($sort, ['profit', 'sales_total', 'sold_qty'], true) ? $sort : 'profit';

        $all = $this->topItems($branchIds, $from, $to, 100000)
            ->sortByDesc($sortColumn)
            ->values();

        $perPage = 20;
        $page = (int) $request->input('page', 1);
        $items = new LengthAwarePaginator(
            $all->slice(($page - 1) * $perPage, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('sales-reports.item-wise-profit', [
            'items' => $items,
            'branches' => $branches,
            'selectedBranchId' => $request->string('branch_id', 'all')->toString(),
            'period' => $period,
            'sort' => $sortColumn,
        ]);
    }

    public function toggleWhatsapp(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        $company->update(['daily_whatsapp_reports_enabled' => ! $company->daily_whatsapp_reports_enabled]);

        return back();
    }

    // -----------------------------------------------------------------
    // Shared data builders
    // -----------------------------------------------------------------

    private function salesReportData(array $branchIds, ?Carbon $from, ?Carbon $to): array
    {
        $salesTotal = $this->salesTotal($branchIds, $from, $to);
        $billCount = $this->invoiceQuery($branchIds, $from, $to)->count();
        $profit = $this->totalProfit($branchIds, $from, $to);
        $received = (float) $this->invoiceQuery($branchIds, $from, $to)->sum('paid_amount');
        $onCredit = max(0, round($salesTotal - $received, 2));

        return [
            'salesTotal' => $salesTotal,
            'billCount' => $billCount,
            'grossProfit' => $profit,
            'margin' => $salesTotal > 0 ? round($profit / $salesTotal * 100, 1) : 0,
            'averageBill' => $billCount > 0 ? round($salesTotal / $billCount) : 0,
            'received' => $received,
            'onCredit' => $onCredit,
            'dailyTrend' => $this->dailyTrend($branchIds, $from, $to),
            'topItems' => $this->topItems($branchIds, $from, $to, 6),
            'topCustomers' => $this->topCustomers($branchIds, $from, $to, 6),
            'paymentBreakdown' => $this->paymentBreakdown($branchIds, $from, $to, $salesTotal, $received),
            'salesByBranch' => $this->salesByBranch($branchIds, $from, $to),
            'billsMadeBy' => $this->billsMadeBy($branchIds, $from, $to),
        ];
    }

    private function profitLossData(array $branchIds, ?Carbon $from, ?Carbon $to): array
    {
        $salesTotal = $this->salesTotal($branchIds, $from, $to);
        $grossProfit = $this->totalProfit($branchIds, $from, $to);
        $cogs = round($salesTotal - $grossProfit, 2);
        $expensesByCategory = $this->expensesByCategory($branchIds, $from, $to);
        $expensesTotal = (float) $expensesByCategory->sum('total');
        $netProfit = round($grossProfit - $expensesTotal, 2);

        $per100 = fn (float $amount) => $salesTotal > 0 ? round($amount / $salesTotal * 100, 2) : 0;

        $customerDue = $this->customerDueTotal($branchIds);

        return [
            'salesTotal' => $salesTotal,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'grossMargin' => $salesTotal > 0 ? round($grossProfit / $salesTotal * 100, 1) : 0,
            'expensesByCategory' => $expensesByCategory,
            'expensesTotal' => $expensesTotal,
            'netProfit' => $netProfit,
            'cogsPer100' => $per100($cogs),
            'expensesPer100' => $per100($expensesTotal),
            'profitPer100' => $per100($netProfit),
            'customerDueTotal' => $customerDue,
            'supplierDueTotal' => $this->supplierDueTotal($branchIds),
            'stockValue' => $this->stockValue($branchIds),
            'insight' => $this->insightText($customerDue, $netProfit),
        ];
    }

    private function resolveBranchIds(Request $request, $branches): array
    {
        $branchId = $request->string('branch_id', 'all')->toString();

        if ($branchId !== 'all' && $branches->contains('id', (int) $branchId)) {
            return [(int) $branchId];
        }

        return $branches->pluck('id')->all() ?: [0];
    }

    private function resolvePeriod(Request $request, string $default, array $allowed): string
    {
        $period = $request->string('period', $default)->toString();

        return in_array($period, $allowed, true) ? $period : $default;
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfDay()],
            default => [null, null],
        };
    }

    private function invoiceQuery(array $branchIds, ?Carbon $from, ?Carbon $to)
    {
        return Invoice::query()->whereIn('branch_id', $branchIds)
            ->when($from, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]));
    }

    /**
     * Cost of goods sold per line comes from the real FIFO batch cost that
     * sale was allocated from (via stock_movement_id), falling back to the
     * item's flat purchase price for older rows saved before that was
     * tracked — the same calculation as the Dashboard's own profit figure,
     * just parameterised over a set of branches and a date range instead
     * of one branch and a fixed period, so the two never quietly disagree.
     */
    private function profitQuery(array $branchIds, ?Carbon $from, ?Carbon $to)
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->whereIn('invoices.branch_id', $branchIds)
            ->when($from, fn ($q) => $q->whereBetween('invoices.invoice_date', [$from, $to]));
    }

    private function profitSelectExpression(): string
    {
        return '
            COALESCE(SUM(
                invoice_items.total - COALESCE(
                    (SELECT SUM(stock_batch_allocations.quantity * stock_batches.unit_cost)
                     FROM stock_batch_allocations
                     JOIN stock_batches ON stock_batches.id = stock_batch_allocations.stock_batch_id
                     WHERE stock_batch_allocations.stock_movement_id = invoice_items.stock_movement_id),
                    invoice_items.base_quantity * items.purchase_price
                )
            ), 0)
        ';
    }

    private function salesTotal(array $branchIds, ?Carbon $from, ?Carbon $to): float
    {
        return (float) $this->invoiceQuery($branchIds, $from, $to)->sum('total');
    }

    private function totalProfit(array $branchIds, ?Carbon $from, ?Carbon $to): float
    {
        return round((float) $this->profitQuery($branchIds, $from, $to)
            ->selectRaw($this->profitSelectExpression().' as profit')
            ->value('profit'), 2);
    }

    private function bestItemName(array $branchIds, ?Carbon $from, ?Carbon $to): ?string
    {
        return $this->profitQuery($branchIds, $from, $to)
            ->select('items.name')
            ->selectRaw($this->profitSelectExpression().' as profit')
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('profit')
            ->value('items.name');
    }

    private function topItems(array $branchIds, ?Carbon $from, ?Carbon $to, int $limit)
    {
        return $this->profitQuery($branchIds, $from, $to)
            ->select('items.id', 'items.name', 'items.unit')
            ->selectRaw('SUM(invoice_items.base_quantity) as sold_qty')
            ->selectRaw('SUM(invoice_items.total) as sales_total')
            ->selectRaw($this->profitSelectExpression().' as profit')
            ->groupBy('items.id', 'items.name', 'items.unit')
            ->orderByDesc('sales_total')
            ->limit($limit)
            ->get();
    }

    private function topCustomers(array $branchIds, ?Carbon $from, ?Carbon $to, int $limit)
    {
        return $this->invoiceQuery($branchIds, $from, $to)
            ->whereNotNull('customer_id')
            ->select('customer_id')
            ->selectRaw('SUM(total) as sales_total')
            ->selectRaw('COUNT(*) as bill_count')
            ->groupBy('customer_id')
            ->orderByDesc('sales_total')
            ->limit($limit)
            ->with('customer')
            ->get()
            ->filter(fn ($row) => $row->customer !== null)
            ->map(fn ($row) => [
                'customer' => $row->customer,
                'sales_total' => (float) $row->sales_total,
                'bill_count' => (int) $row->bill_count,
                'due' => $row->customer->dueAmount(),
            ])
            ->values();
    }

    /**
     * Grouped by mode, plus an "Other" bucket for the real amount of rows
     * recorded before the `mode` column existed on invoice_payments (older
     * data has it null) — without this, that money would just silently
     * vanish from the bar instead of still accounting for it.
     */
    private function paymentBreakdown(array $branchIds, ?Carbon $from, ?Carbon $to, float $salesTotal, float $received): array
    {
        $byMode = InvoicePayment::query()
            ->whereHas('invoice', fn ($q) => $q->whereIn('branch_id', $branchIds)
                ->when($from, fn ($qq) => $qq->whereBetween('invoice_date', [$from, $to])))
            ->selectRaw('COALESCE(mode, \'other\') as mode_key, COALESCE(SUM(amount), 0) as total')
            ->groupBy('mode_key')
            ->pluck('total', 'mode_key');

        $onCredit = max(0, round($salesTotal - $received, 2));

        $rows = [];
        foreach ([
            'cash' => ['Cash', 'bg-green-500'],
            'upi' => ['UPI', 'bg-blue-500'],
            'bank' => ['Bank', 'bg-indigo-500'],
            'other' => ['Other / unspecified', 'bg-gray-400'],
        ] as $mode => [$label, $color]) {
            $amount = (float) ($byMode[$mode] ?? 0);
            if ($amount > 0) {
                $rows[] = ['label' => $label, 'amount' => $amount, 'color' => $color];
            }
        }
        if ($onCredit > 0) {
            $rows[] = ['label' => 'On credit', 'amount' => $onCredit, 'color' => 'bg-red-500'];
        }

        $total = array_sum(array_column($rows, 'amount')) ?: 1;

        return array_map(fn ($row) => $row + ['percent' => round($row['amount'] / $total * 100)], $rows);
    }

    private function salesByBranch(array $branchIds, ?Carbon $from, ?Carbon $to)
    {
        $rows = $this->invoiceQuery($branchIds, $from, $to)
            ->select('branch_id')
            ->selectRaw('SUM(total) as total')
            ->groupBy('branch_id')
            ->orderByDesc('total')
            ->with('branch')
            ->get();

        $grandTotal = (float) $rows->sum('total') ?: 1;
        $palette = ['bg-brand-600', 'bg-purple-300', 'bg-blue-400', 'bg-teal-400', 'bg-pink-400'];

        return $rows->values()->map(fn ($row, $i) => [
            'name' => $row->branch->name ?? 'Branch',
            'total' => (float) $row->total,
            'percent' => round((float) $row->total / $grandTotal * 100),
            'color' => $palette[$i % count($palette)],
        ]);
    }

    private function billsMadeBy(array $branchIds, ?Carbon $from, ?Carbon $to)
    {
        $rows = $this->invoiceQuery($branchIds, $from, $to)
            ->select('user_id')
            ->selectRaw('SUM(total) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->get();

        $grandTotal = (float) $rows->sum('total') ?: 1;
        $palette = ['bg-orange-500', 'bg-amber-300', 'bg-rose-400', 'bg-cyan-400'];

        return $rows->values()->map(fn ($row, $i) => [
            'name' => $row->user->name ?? 'Unknown',
            'total' => (float) $row->total,
            'percent' => round((float) $row->total / $grandTotal * 100),
            'color' => $palette[$i % count($palette)],
        ]);
    }

    private function dailyTrend(array $branchIds, Carbon $from, Carbon $to): array
    {
        $salesByDate = $this->invoiceQuery($branchIds, $from, $to)
            ->selectRaw('DATE(invoice_date) as d, COALESCE(SUM(total), 0) as total, COUNT(*) as bills')
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $profitByDate = $this->profitQuery($branchIds, $from, $to)
            ->selectRaw('DATE(invoices.invoice_date) as d, '.$this->profitSelectExpression().' as profit')
            ->groupBy('d')
            ->pluck('profit', 'd');

        $days = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m-d');
            $row = $salesByDate->get($key);
            $days[] = [
                'date' => $key,
                'label' => $cursor->format('j M'),
                'short' => $cursor->format('j'),
                'sales' => round((float) ($row->total ?? 0), 2),
                'bills' => (int) ($row->bills ?? 0),
                'profit' => round((float) ($profitByDate[$key] ?? 0), 2),
            ];
            $cursor->addDay();
        }

        return $days;
    }

    private function customerDueTotal(array $branchIds): float
    {
        return (float) Customer::query()
            ->whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds))
            ->selectRaw('COALESCE(SUM(
                opening_balance
                + (SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.customer_id = customers.id)
                - (SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id)
                - credit_balance
            ), 0) as total_due')
            ->value('total_due');
    }

    private function supplierDueTotal(array $branchIds): float
    {
        return (float) Purchase::query()->whereIn('branch_id', $branchIds)
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as due')
            ->value('due');
    }

    private function stockValue(array $branchIds): float
    {
        return (float) (ItemStock::query()->whereIn('branch_id', $branchIds)
            ->join('items', 'items.id', '=', 'item_stocks.item_id')
            ->selectRaw('SUM(item_stocks.quantity * items.selling_price) as value')
            ->value('value') ?? 0);
    }

    private function purchasesTotal(array $branchIds, ?Carbon $from, ?Carbon $to): float
    {
        return (float) Purchase::query()->whereIn('branch_id', $branchIds)
            ->when($from, fn ($q) => $q->whereBetween('purchase_date', [$from, $to]))
            ->sum('total');
    }

    private function expensesTotal(array $branchIds, ?Carbon $from, ?Carbon $to): float
    {
        return (float) Expense::query()->whereIn('branch_id', $branchIds)
            ->when($from, fn ($q) => $q->whereBetween('expense_date', [$from, $to]))
            ->sum('amount');
    }

    private function expensesByCategory(array $branchIds, ?Carbon $from, ?Carbon $to)
    {
        $rows = Expense::query()->whereIn('branch_id', $branchIds)
            ->when($from, fn ($q) => $q->whereBetween('expense_date', [$from, $to]))
            ->select('category')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $max = (float) ($rows->max('total') ?: 1);

        return $rows->map(fn ($row) => [
            'label' => Expense::categories()[$row->category] ?? ucfirst($row->category),
            'total' => (float) $row->total,
            'percent' => round((float) $row->total / $max * 100),
        ]);
    }

    private function insightText(float $customerDue, float $netProfit): string
    {
        if ($customerDue <= 0) {
            return 'Customers have no outstanding dues right now, so nothing extra is tied up in credit.';
        }

        $tenPercent = round($customerDue * 0.1);

        return 'Profit is small because a lot of your money is with customers on credit. Collecting even 10% of dues brings in about ₹'.number_format($tenPercent).'.';
    }
}
