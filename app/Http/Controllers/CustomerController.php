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

        if ($request->ajax()) {
            return view('customers.partials.table', ['customers' => $customers]);
        }

        return view('customers.index', ['customers' => $customers, 'status' => $status]);
    }

    public function create(): View
    {
        return view('customers.create', ['branches' => Branch::orderBy('name')->get()]);
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
        $entries = $this->buildLedger($customer);
        $months = $entries->groupBy(fn (array $entry) => $entry['date']->format('F Y'));

        return Pdf::loadView('customers.statement-pdf', [
            'customer' => $customer,
            'months' => $months,
            'openingBalance' => (float) $customer->opening_balance,
            'totalDebit' => $entries->where('type', 'billed')->sum('amount'),
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
     */
    public function storePayment(StoreCustomerPaymentRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
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

                $entries->push([
                    'date' => $first['payment']->created_at,
                    'type' => 'received',
                    'label' => $first['payment']->note
                        ? "Payment ({$first['payment']->note})"
                        : ($first['invoice'] ? "Payment for {$first['invoice']->invoice_number}" : 'Payment'),
                    'amount' => (float) $rows->sum(fn (array $row) => (float) $row['payment']->amount),
                    'url' => $first['invoice'] ? route('invoices.show', $first['invoice']) : route('customers.show', $customer),
                ]);
            });

        $balance = (float) $customer->opening_balance;

        return $entries->sortBy('date')->values()
            ->map(function (array $entry) use (&$balance) {
                $balance += $entry['type'] === 'billed' ? $entry['amount'] : -$entry['amount'];
                $entry['balance_after'] = round($balance, 2);

                return $entry;
            });
    }

    public function edit(Customer $customer): View
    {
        $customer->load('branches');

        return view('customers.edit', ['customer' => $customer, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
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
            ->get(['id', 'name', 'phone', 'email', 'address', 'opening_balance', 'is_active'])
            ->map(function (Customer $customer) {
                $customer->due = $customer->dueAmount();

                return $customer;
            });

        return response()->json($customers);
    }
}
