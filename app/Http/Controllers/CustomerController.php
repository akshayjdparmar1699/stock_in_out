<?php

namespace App\Http\Controllers;

use App\Support\GstStateCodes;
use App\Support\Sql;

use App\Http\Requests\StoreCustomerPaymentRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceItem;
use App\Services\AdminAlertService;
use App\Services\BranchContext;
use App\Services\NotificationService;
use App\Services\PerPagePreference;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        ['all' => $all, 'due' => $dueByCustomerId, 'filter' => $filter] = $this->filteredCustomers($request);

        $stats = $this->statsFor($all, $dueByCustomerId);

        $filtered = $all->filter(fn (Customer $customer) => $this->matchesFilter($customer, $filter, $dueByCustomerId))->values();

        $perPage = PerPagePreference::get();
        $page = $request->integer('page', 1);

        $customers = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $lastBillByCustomerId = $this->lastBillDatesFor($customers->getCollection());

        if ($request->ajax()) {
            return view('customers.partials.table', ['customers' => $customers, 'dueByCustomerId' => $dueByCustomerId, 'lastBillByCustomerId' => $lastBillByCustomerId]);
        }

        return view('customers.index', [
            'customers' => $customers,
            'filter' => $filter,
            'dueByCustomerId' => $dueByCustomerId,
            'lastBillByCustomerId' => $lastBillByCustomerId,
            'stats' => $stats,
            'totalCount' => $all->count(),
            'branch' => BranchContext::current(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        ['all' => $all, 'due' => $dueByCustomerId, 'filter' => $filter] = $this->filteredCustomers($request);

        $filtered = $all->filter(fn (Customer $customer) => $this->matchesFilter($customer, $filter, $dueByCustomerId))->values();

        return response()->streamDownload(function () use ($filtered, $dueByCustomerId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Phone', 'Village / City', 'Branches', 'Balance Due', 'Credit Limit', 'Status']);

            foreach ($filtered as $customer) {
                fputcsv($out, [
                    $customer->name,
                    $customer->phone,
                    $customer->address,
                    $customer->branches->pluck('name')->implode(', '),
                    number_format($dueByCustomerId[$customer->id] ?? 0, 2),
                    number_format((float) $customer->credit_limit, 2),
                    $customer->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($out);
        }, 'customers.csv');
    }

    /**
     * Shared branch+search query behind both index() and export() — loads
     * every matching customer unpaginated (shop customer lists are small
     * enough that this is cheap) so the filter tabs/stat counts and CSV
     * export can be computed from the exact same due-amount snapshot.
     *
     * @return array{all: Collection<int, Customer>, due: array<int, float>, filter: string}
     */
    private function filteredCustomers(Request $request): array
    {
        $branchId = BranchContext::id();
        $search = $request->string('q')->toString();

        $all = Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->with('branches')
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(fn ($q) => $q->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('phone', Sql::caseInsensitiveLike(), $term));
            })
            ->orderBy('name')
            ->get();

        return [
            'all' => $all,
            'due' => $this->dueAmountsFor($all),
            'filter' => $request->string('filter', 'all')->toString(),
        ];
    }

    /**
     * @param  array<int, float>  $dueByCustomerId
     */
    private function matchesFilter(Customer $customer, string $filter, array $dueByCustomerId): bool
    {
        $due = $dueByCustomerId[$customer->id] ?? 0.0;

        return match ($filter) {
            'udhaar' => $customer->is_active && $due > 0,
            'over_limit' => $customer->is_active && (float) $customer->credit_limit > 0 && $due > (float) $customer->credit_limit,
            'settled' => $customer->is_active && $due <= 0,
            'inactive' => ! $customer->is_active,
            default => true,
        };
    }

    /**
     * @param  Collection<int, Customer>  $all
     * @param  array<int, float>  $dueByCustomerId
     * @return array{udhaar_count: int, udhaar_total: float, over_limit_count: int, settled_count: int, inactive_count: int}
     */
    private function statsFor(Collection $all, array $dueByCustomerId): array
    {
        $stats = ['udhaar_count' => 0, 'udhaar_total' => 0.0, 'over_limit_count' => 0, 'settled_count' => 0, 'inactive_count' => 0];

        foreach ($all as $customer) {
            if (! $customer->is_active) {
                $stats['inactive_count']++;

                continue;
            }

            $due = $dueByCustomerId[$customer->id] ?? 0.0;

            if ($due > 0) {
                $stats['udhaar_count']++;
                $stats['udhaar_total'] += $due;

                if ((float) $customer->credit_limit > 0 && $due > (float) $customer->credit_limit) {
                    $stats['over_limit_count']++;
                }
            } else {
                $stats['settled_count']++;
            }
        }

        return $stats;
    }

    /**
     * Works out every given customer's due in one aggregate query instead
     * of calling dueAmount() (two SUM queries, run twice over for anyone
     * with a credit limit set, via isOverCreditLimit()) once per row —
     * which made this list page slower the more customers were on it,
     * worse still with per-page bumped up to 100.
     */
    private function dueAmountsFor(Collection $customers): array
    {
        if ($customers->isEmpty()) {
            return [];
        }

        $totals = Invoice::query()
            ->whereIn('customer_id', $customers->pluck('id'))
            ->selectRaw('customer_id, COALESCE(SUM(total), 0) as invoiced, COALESCE(SUM(paid_amount), 0) as paid')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        return $customers->mapWithKeys(function (Customer $customer) use ($totals) {
            $row = $totals->get($customer->id);

            $due = round(
                (float) $customer->opening_balance
                    + (float) ($row->invoiced ?? 0)
                    - (float) ($row->paid ?? 0)
                    - (float) $customer->credit_balance,
                2
            );

            return [$customer->id => $due];
        })->all();
    }

    /**
     * Most recent invoice date per customer, for the "Last Bill" column —
     * one grouped query instead of an N+1 per row.
     *
     * @param  Collection<int, Customer>  $customers
     * @return array<int, \Illuminate\Support\Carbon>
     */
    private function lastBillDatesFor(Collection $customers): array
    {
        if ($customers->isEmpty()) {
            return [];
        }

        return Invoice::query()
            ->whereIn('customer_id', $customers->pluck('id'))
            ->selectRaw('customer_id, MAX(invoice_date) as last_date')
            ->groupBy('customer_id')
            ->pluck('last_date', 'customer_id')
            ->map(fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date) : null)
            ->all();
    }

    /**
     * A customer has no company_id of its own — it's only reachable
     * through the branches it's linked to — so route-model-bound methods
     * (show/edit/payments/etc.) check ownership this way, since binding a
     * customer by id alone doesn't rule out one from another company.
     */
    private function belongsToCurrentCompany(Customer $customer): bool
    {
        return $customer->branches()->where('branches.company_id', auth()->user()->company_id)->exists();
    }

    public function create(): View
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('customers.create', [
            'branches' => $branches,
            'gstEnabled' => (bool) auth()->user()->company?->gst_enabled,
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $data['state_code'] = GstStateCodes::code($data['state'] ?? null);
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $customer = Customer::create($data);

        $customer->branches()->sync(
            auth()->user()->isAdmin() && $branchIds ? $branchIds : [BranchContext::id()]
        );

        $this->notifyBranch();

        if ($request->wantsJson()) {
            $customer->due = $customer->dueAmount();

            return response()->json($customer, 201);
        }

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" added.");
    }

    public function show(Customer $customer): View
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $due = $customer->dueAmount();
        $entries = $this->buildLedger($customer);
        $thisMonth = now()->format('F');

        return view('customers.show', [
            'customer' => $customer,
            'entries' => $entries->reverse()->values(),
            'due' => $due,
            'thisMonthLabel' => $thisMonth,
            'billedThisMonth' => $entries->where('type', 'billed')->filter(fn (array $e) => $e['date']->isCurrentMonth())->sum('amount'),
            'receivedThisMonth' => $entries->where('type', 'received')->filter(fn (array $e) => $e['date']->isCurrentMonth())->sum('amount'),
            'lastBillDate' => $entries->where('type', 'billed')->sortByDesc('date')->first()['date'] ?? null,
            'lastPaymentDate' => $entries->where('type', 'received')->sortByDesc('date')->first()['date'] ?? null,
            'usualItems' => $this->usualItemsFor($customer),
        ]);
    }

    /**
     * The 2-3 items this customer buys most often, by total quantity
     * across all their invoices — purely informational context on their
     * profile, not stored anywhere of its own.
     */
    private function usualItemsFor(Customer $customer): string
    {
        return InvoiceItem::query()
            ->whereHas('invoice', fn ($q) => $q->where('customer_id', $customer->id))
            ->selectRaw('item_id, SUM(quantity) as qty')
            ->groupBy('item_id')
            ->orderByDesc('qty')
            ->limit(3)
            ->with('item:id,name')
            ->get()
            ->pluck('item.name')
            ->filter()
            ->implode(', ');
    }

    public function statementPdf(Customer $customer): Response
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $entries = $this->buildLedger($customer);
        $months = $entries->groupBy(fn (array $entry) => $entry['date']->format('F Y'));

        return Pdf::loadView('customers.statement-pdf', [
            'customer' => $customer,
            'months' => $months,
            'openingBalance' => (float) $customer->opening_balance,
            'totalDebit' => $entries->whereIn('type', ['billed', 'refund'])->sum('amount'),
            'totalCredit' => $entries->where('type', 'received')->sum('amount'),
            'due' => $customer->dueAmount(),
            'entryCount' => $entries->count(),
            'fromDate' => $entries->first()['date'] ?? now(),
            'toDate' => $entries->last()['date'] ?? now(),
        ])->setPaper('a4')->stream("{$customer->name} - Statement.pdf");
    }

    /**
     * Records one payment from the customer against their oldest
     * outstanding invoices first (a payment larger than one invoice's own
     * balance rolls over onto the next), so it isn't tied to a single
     * invoice the way "Record a Payment" on an invoice's own page is.
     * Anything left over after every invoice is fully paid settles their
     * opening balance, and if it's still more than that, the rest becomes
     * credit they can draw on for a future invoice.
     *
     * A "received" payment here actually means the reverse: money we gave
     * back to the customer (e.g. refunding part of an over-payment, or
     * cash handed back for returned goods). It isn't matched against any
     * invoice — it just reduces whatever credit we're holding for them
     * (and going past zero correctly shows up as them owing us again).
     */
    public function storePayment(StoreCustomerPaymentRequest $request, Customer $customer): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $data = $request->validated();

        if (($data['type'] ?? 'paid') === 'received') {
            DB::transaction(function () use ($customer, $data) {
                InvoicePayment::create([
                    'customer_id' => $customer->id,
                    'user_id' => auth()->id(),
                    'amount' => $data['amount'],
                    'type' => 'received',
                    'mode' => $data['mode'] ?? null,
                    'note' => $data['note'] ?? null,
                ]);

                $customer->decrement('credit_balance', $data['amount']);
            });

            $this->notifyBranch();

            return redirect()->back()
                ->with('status', "Refund of ₹".number_format($data['amount'], 2)." recorded for \"{$customer->name}\".");
        }

        $remaining = (float) $data['amount'];
        $batchId = (string) Str::uuid();

        DB::transaction(function () use ($customer, $data, &$remaining, $batchId) {
            $invoices = $customer->invoices()
                ->whereColumn('paid_amount', '<', 'total')
                ->orderBy('invoice_date')
                ->orderBy('created_at')
                ->get();

            foreach ($invoices as $invoice) {
                if ($remaining <= 0) {
                    break;
                }

                $due = round((float) $invoice->total - (float) $invoice->paid_amount, 2);
                $apply = min($due, $remaining);

                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => auth()->id(),
                    'amount' => $apply,
                    'note' => $data['note'] ?? null,
                    'mode' => $data['mode'] ?? null,
                    'batch_id' => $batchId,
                ]);

                $invoice->increment('paid_amount', $apply);
                $invoice->refresh();
                $invoice->update([
                    'status' => $invoice->paid_amount >= $invoice->total ? 'paid' : ($invoice->paid_amount > 0 ? 'partial' : 'unpaid'),
                ]);

                $remaining -= $apply;
            }

            // Nothing left owed on any invoice, but there's still money in
            // hand — it isn't tied to any invoice, so it directly settles
            // whatever's left of their opening balance and/or becomes
            // credit for a future invoice to auto-draw on.
            if ($remaining > 0) {
                InvoicePayment::create([
                    'customer_id' => $customer->id,
                    'user_id' => auth()->id(),
                    'amount' => $remaining,
                    'note' => $data['note'] ?? null,
                    'mode' => $data['mode'] ?? null,
                    'batch_id' => $batchId,
                ]);

                $customer->increment('credit_balance', $remaining);
            }
        });

        $receiptUrl = AdminAlertService::paymentReceiptUrl($customer, (float) $data['amount'], $customer->refresh()->dueAmount());

        // back() rather than always customers.show, so this same action
        // works equally from the customer's own page and from the New Bill
        // screen's "receive payment against previous due" section — each
        // lands back where it was triggered from.
        return redirect()->back()
            ->with('status', "Payment of ₹".number_format($data['amount'], 2)." recorded for \"{$customer->name}\".")
            ->with('open_whatsapp_url', $receiptUrl);
    }

    /**
     * Deletes a statement entry for a wrongly recorded payment or refund —
     * identified by the batch_id a multi-invoice payment shares, or by its
     * own id when it's a single row (e.g. one made from an invoice's own
     * page). Reverses exactly what storePayment() did: gives back any
     * invoice's paid_amount it was applied to, or undoes whichever way it
     * moved the credit balance.
     */
    public function destroyPayment(Customer $customer, string $key): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $payments = InvoicePayment::query()
            ->where('from_credit_balance', false)
            ->where(function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)
                    ->orWhereHas('invoice', fn ($q) => $q->where('customer_id', $customer->id));
            })
            ->when(
                str_starts_with($key, 'id-'),
                fn ($query) => $query->where('id', (int) substr($key, 3)),
                fn ($query) => $query->where('batch_id', $key)
            )
            ->with('invoice')
            ->get();

        abort_if($payments->isEmpty(), 404);

        DB::transaction(function () use ($payments, $customer) {
            foreach ($payments as $payment) {
                if ($payment->invoice_id) {
                    $invoice = $payment->invoice;
                    $invoice->decrement('paid_amount', $payment->amount);
                    $invoice->refresh();
                    $invoice->update([
                        'status' => $invoice->paid_amount >= $invoice->total ? 'paid' : ($invoice->paid_amount > 0 ? 'partial' : 'unpaid'),
                    ]);
                } elseif ($payment->type === 'received') {
                    $customer->increment('credit_balance', $payment->amount);
                } else {
                    $customer->decrement('credit_balance', $payment->amount);
                }

                $payment->delete();
            }
        });

        return redirect()->route('customers.show', $customer)->with('status', 'Statement entry deleted.');
    }

    /**
     * Every invoice (debit — billed to the customer) and invoice payment
     * (credit — received from them) as one running-balance timeline,
     * oldest first, starting from their opening balance.
     */
    private function buildLedger(Customer $customer): Collection
    {
        $invoices = $customer->invoices()->with('payments')->orderBy('created_at')->get();

        $entries = collect();

        foreach ($invoices as $invoice) {
            $entries->push([
                'date' => $invoice->created_at,
                'type' => 'billed',
                'label' => "Invoice {$invoice->invoice_number}",
                'amount' => (float) $invoice->total,
                'url' => route('invoices.show', $invoice),
                'payment_key' => null,
            ]);
        }

        // A payment recorded from the customer's own page (not tied to one
        // invoice) can land on several invoices at once via FIFO — grouped
        // here by batch_id so it shows as the one amount actually handed
        // over, not fragmented into one row per invoice it happened to
        // cover. A plain invoice-level payment has no batch_id, so it's
        // its own group of one. A row with from_credit_balance is skipped
        // here — it's an invoice auto-drawing on credit that was already
        // counted as received when that credit was built up, not new cash.
        $paymentRows = $invoices->flatMap(fn (Invoice $invoice) => $invoice->payments
            ->reject(fn (InvoicePayment $payment) => $payment->from_credit_balance)
            ->map(fn (InvoicePayment $payment) => [
                'payment' => $payment,
                'invoice' => $invoice,
            ]));

        // Money handed over that wasn't matched to any invoice at the time
        // it was received — it settled the opening balance, or went
        // straight to credit.
        $unmatchedPayments = $customer->payments()->whereNull('invoice_id')->get()
            ->map(fn (InvoicePayment $payment) => ['payment' => $payment, 'invoice' => null]);

        $paymentRows->concat($unmatchedPayments)
            ->groupBy(fn (array $row) => $row['payment']->batch_id ?: 'single-'.$row['payment']->id)
            ->each(function (Collection $rows) use ($entries, $customer) {
                $first = $rows->first();
                $isRefund = $first['payment']->type === 'received';

                $entries->push([
                    'date' => $first['payment']->created_at,
                    'type' => $isRefund ? 'refund' : 'received',
                    'label' => $isRefund
                        ? ($first['payment']->note ? "Refund paid ({$first['payment']->note})" : 'Refund paid')
                        : ($first['payment']->note
                            ? "Payment ({$first['payment']->note})"
                            : ($first['invoice'] ? "Payment for {$first['invoice']->invoice_number}" : 'Payment')),
                    'amount' => (float) $rows->sum(fn (array $row) => (float) $row['payment']->amount),
                    'url' => $first['invoice'] ? route('invoices.show', $first['invoice']) : route('customers.show', $customer),
                    'payment_key' => $first['payment']->batch_id ?: 'id-'.$first['payment']->id,
                ]);
            });

        $balance = (float) $customer->opening_balance;

        return $entries->sortBy('date')->values()
            ->map(function (array $entry) use (&$balance) {
                // A refund works out the same way as an invoice bill does
                // for this running total — both increase what they owe us.
                $balance += in_array($entry['type'], ['billed', 'refund'], true) ? $entry['amount'] : -$entry['amount'];
                $entry['balance_after'] = round($balance, 2);

                return $entry;
            });
    }

    public function edit(Customer $customer): View
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $customer->load('branches');

        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('customers.edit', [
            'customer' => $customer,
            'branches' => $branches,
            'gstEnabled' => (bool) auth()->user()->company?->gst_enabled,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $data['state_code'] = GstStateCodes::code($data['state'] ?? null);
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $customer->update($data);

        if (auth()->user()->isAdmin() && $branchIds) {
            $customer->branches()->sync($branchIds);
        }

        $this->notifyBranch();

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" updated.");
    }

    /**
     * Lowering a credit limit (or raising an opening balance) here can
     * newly cross the over-limit line the same way an invoice can, so this
     * re-syncs/pushes the same way those do.
     */
    private function notifyBranch(): void
    {
        if ($branch = BranchContext::current()) {
            NotificationService::sync($branch);
        }
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        if ($customer->invoices()->exists()) {
            return back()
                ->with('status', "Cannot delete \"{$customer->name}\": they have billing history. Mark them inactive instead.")
                ->with('status_type', 'danger');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" deleted.");
    }

    public function toggleActive(Customer $customer): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $customer->update(['is_active' => ! $customer->is_active]);

        $status = $customer->is_active ? 'active' : 'inactive';

        return redirect()->back()
            ->with('status', "Customer \"{$customer->name}\" marked {$status}.")
            ->with('status_type', $customer->is_active ? 'success' : 'danger');
    }

    public function search(Request $request): JsonResponse
    {
        $branchId = BranchContext::id();
        $term = '%'.$request->string('q').'%';

        $customers = Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->where(fn ($q) => $q->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('phone', Sql::caseInsensitiveLike(), $term))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'address', 'opening_balance', 'credit_balance', 'is_active'])
            ->map(function (Customer $customer) {
                $customer->due = $customer->dueAmount();

                return $customer;
            });

        return response()->json($customers);
    }
}
