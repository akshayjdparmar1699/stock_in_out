<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierPaymentRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Services\PerPagePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('suppliers.partials.table', ['suppliers' => $suppliers]);
        }

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier = Supplier::create($data);

        if ($request->wantsJson()) {
            $supplier->due = $supplier->dueAmount();

            return response()->json($supplier, 201);
        }

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" added.");
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', [
            'supplier' => $supplier,
            'entries' => $this->buildLedger($supplier)->reverse()->values(),
            'due' => $supplier->dueAmount(),
        ]);
    }

    /**
     * Records one payment to the supplier against their oldest outstanding
     * purchases first (a payment larger than one purchase's own balance
     * rolls over onto the next). Anything left over after every purchase is
     * fully paid settles their opening balance, and if it's still more than
     * that, the rest becomes credit we can draw on for a future purchase.
     */
    public function storePayment(StoreSupplierPaymentRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();
        $remaining = (float) $data['amount'];
        $batchId = (string) Str::uuid();

        DB::transaction(function () use ($supplier, $data, &$remaining, $batchId) {
            $purchases = $supplier->purchases()
                ->whereColumn('paid_amount', '<', 'total')
                ->orderBy('purchase_date')
                ->orderBy('created_at')
                ->get();

            foreach ($purchases as $purchase) {
                if ($remaining <= 0) {
                    break;
                }

                $due = round((float) $purchase->total - (float) $purchase->paid_amount, 2);
                $apply = min($due, $remaining);

                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->id(),
                    'amount' => $apply,
                    'note' => $data['note'] ?? null,
                    'batch_id' => $batchId,
                ]);

                $purchase->increment('paid_amount', $apply);
                $purchase->refresh();
                $purchase->update([
                    'status' => $purchase->paid_amount >= $purchase->total ? 'paid' : ($purchase->paid_amount > 0 ? 'partial' : 'unpaid'),
                ]);

                $remaining -= $apply;
            }

            // Nothing left owed on any purchase, but there's still money
            // handed over — it isn't tied to any purchase, so it directly
            // settles whatever's left of the opening balance and/or becomes
            // credit for a future purchase to auto-draw on.
            if ($remaining > 0) {
                PurchasePayment::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => auth()->id(),
                    'amount' => $remaining,
                    'note' => $data['note'] ?? null,
                    'batch_id' => $batchId,
                ]);

                $supplier->increment('credit_balance', $remaining);
            }
        });

        return redirect()->route('suppliers.show', $supplier)
            ->with('status', "Payment of ₹".number_format($data['amount'], 2)." recorded for \"{$supplier->name}\".");
    }

    /**
     * Every purchase (debit — billed by the supplier) and purchase payment
     * (credit — paid to them) as one running-balance timeline, oldest
     * first, starting from the opening balance.
     */
    private function buildLedger(Supplier $supplier): Collection
    {
        $purchases = $supplier->purchases()->with('payments')->orderBy('created_at')->get();

        $entries = collect();

        foreach ($purchases as $purchase) {
            $entries->push([
                'date' => $purchase->created_at,
                'type' => 'billed',
                'label' => "Purchase {$purchase->purchase_number}",
                'amount' => (float) $purchase->total,
                'url' => route('purchases.show', $purchase),
            ]);
        }

        // A row with from_credit_balance is excluded — it's a purchase
        // auto-drawing on credit that was already counted as paid when that
        // credit was built up, not new cash leaving our hands.
        $paymentRows = $purchases->flatMap(fn (Purchase $purchase) => $purchase->payments
            ->reject(fn (PurchasePayment $payment) => $payment->from_credit_balance)
            ->map(fn (PurchasePayment $payment) => [
                'payment' => $payment,
                'purchase' => $purchase,
            ]));

        // Money handed over that wasn't matched to any purchase at the time
        // it was paid — it settled the opening balance, or went straight to
        // credit.
        $unmatchedPayments = $supplier->payments()->whereNull('purchase_id')->get()
            ->map(fn (PurchasePayment $payment) => ['payment' => $payment, 'purchase' => null]);

        $paymentRows->concat($unmatchedPayments)
            ->groupBy(fn (array $row) => $row['payment']->batch_id ?: 'single-'.$row['payment']->id)
            ->each(function (Collection $rows) use ($entries, $supplier) {
                $first = $rows->first();

                $entries->push([
                    'date' => $first['payment']->created_at,
                    'type' => 'paid',
                    'label' => $first['payment']->note
                        ? "Payment ({$first['payment']->note})"
                        : ($first['purchase'] ? "Payment for {$first['purchase']->purchase_number}" : 'Payment'),
                    'amount' => (float) $rows->sum(fn (array $row) => (float) $row['payment']->amount),
                    'url' => $first['purchase'] ? route('purchases.show', $first['purchase']) : route('suppliers.show', $supplier),
                ]);
            });

        $balance = (float) $supplier->opening_balance;

        return $entries->sortBy('date')->values()
            ->map(function (array $entry) use (&$balance) {
                $balance += $entry['type'] === 'billed' ? $entry['amount'] : -$entry['amount'];
                $entry['balance_after'] = round($balance, 2);

                return $entry;
            });
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" updated.");
    }

    public function search(Request $request): JsonResponse
    {
        $term = '%'.$request->string('q').'%';

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'address'])
            ->map(function (Supplier $supplier) {
                $supplier->due = $supplier->dueAmount();

                return $supplier;
            });

        return response()->json($suppliers);
    }
}
