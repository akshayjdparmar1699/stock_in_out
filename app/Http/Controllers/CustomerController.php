<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerPaymentRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\AdminAlertService;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();
        $status = $request->string('status', 'active')->toString();

        $customers = Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->with('branches')
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        $dueByCustomerId = $this->dueAmountsFor($customers->getCollection());

        if ($request->ajax()) {
            return view('customers.partials.table', ['customers' => $customers, 'dueByCustomerId' => $dueByCustomerId]);
        }

        return view('customers.index', ['customers' => $customers, 'status' => $status, 'dueByCustomerId' => $dueByCustomerId]);
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

        return view('customers.create', ['branches' => $branches]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $customer = Customer::create($data);

        $customer->branches()->sync(
            auth()->user()->isAdmin() && $branchIds ? $branchIds : [BranchContext::id()]
        );

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
        $branch = BranchContext::current();

        return view('customers.show', [
            'customer' => $customer,
            'entries' => $this->buildLedger($customer)->reverse()->values(),
            'due' => $due,
            'creditLimitAdminUrl' => ($branch && $customer->isOverCreditLimit())
                ? AdminAlertService::creditLimitUrl($branch, $customer, $due, (float) $customer->credit_limit)
                : null,
        ]);
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
                    'note' => $data['note'] ?? null,
                ]);

                $customer->decrement('credit_balance', $data['amount']);
            });

            return redirect()->route('customers.show', $customer)
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
                    'batch_id' => $batchId,
                ]);

                $customer->increment('credit_balance', $remaining);
            }
        });

        return redirect()->route('customers.show', $customer)
            ->with('status', "Payment of ₹".number_format($data['amount'], 2)." recorded for \"{$customer->name}\".");
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

        return view('customers.edit', ['customer' => $customer, 'branches' => $branches]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($customer), 404);

        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $customer->update($data);

        if (auth()->user()->isAdmin() && $branchIds) {
            $customer->branches()->sync($branchIds);
        }

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" updated.");
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
            ->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term))
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
