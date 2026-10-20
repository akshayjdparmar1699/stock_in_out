<?php

namespace App\Http\Controllers;

use App\Support\Sql;

use App\Http\Requests\StoreSupplierPaymentRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Services\PerPagePreference;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        ['all' => $all, 'due' => $dueBySupplierId, 'filter' => $filter] = $this->filteredSuppliers($request);

        $stats = $this->statsFor($all, $dueBySupplierId);

        $filtered = $all->filter(fn (Supplier $supplier) => $this->matchesFilter($supplier, $filter, $dueBySupplierId))->values();

        $sort = $request->string('sort', 'name_asc')->toString();
        $filtered = $this->sortSuppliers($filtered, $sort, $dueBySupplierId);

        $perPage = PerPagePreference::get();
        $page = $request->integer('page', 1);

        $suppliers = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return view('suppliers.partials.table', ['suppliers' => $suppliers, 'dueBySupplierId' => $dueBySupplierId]);
        }

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'filter' => $filter,
            'sort' => $sort,
            'dueBySupplierId' => $dueBySupplierId,
            'stats' => $stats,
            'totalCount' => $all->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        ['all' => $all, 'due' => $dueBySupplierId, 'filter' => $filter] = $this->filteredSuppliers($request);

        $filtered = $all->filter(fn (Supplier $supplier) => $this->matchesFilter($supplier, $filter, $dueBySupplierId))->values();

        return response()->streamDownload(function () use ($filtered, $dueBySupplierId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Phone', 'City', 'GST Number', 'We Owe Them']);

            foreach ($filtered as $supplier) {
                fputcsv($out, [
                    $supplier->name,
                    $supplier->phone,
                    $supplier->city,
                    $supplier->gst_number,
                    number_format($dueBySupplierId[$supplier->id] ?? 0, 2),
                ]);
            }

            fclose($out);
        }, 'suppliers.csv');
    }

    /**
     * Shared company+search query behind index()/export() — loads every
     * matching supplier unpaginated (a company's supplier list is small
     * enough that this is cheap) so the filter-tab counts, CSV export, and
     * the paginated list all derive from one consistent due-amount
     * snapshot.
     *
     * @return array{all: Collection<int, Supplier>, due: array<int, float>, filter: string}
     */
    private function filteredSuppliers(Request $request): array
    {
        $search = $request->string('q')->toString();

        $all = Supplier::query()
            ->where('company_id', auth()->user()->company_id)
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(fn ($q) => $q->where('name', Sql::caseInsensitiveLike(), $term)
                    ->orWhere('phone', Sql::caseInsensitiveLike(), $term)
                    ->orWhere('city', Sql::caseInsensitiveLike(), $term));
            })
            ->orderBy('name')
            ->get();

        return [
            'all' => $all,
            'due' => $this->dueAmountsFor($all),
            'filter' => $request->string('filter', 'all')->toString(),
        ];
    }

    private function matchesFilter(Supplier $supplier, string $filter, array $dueBySupplierId): bool
    {
        $due = $dueBySupplierId[$supplier->id] ?? 0.0;

        return match ($filter) {
            'we_owe' => $due > 0,
            'settled' => $due <= 0,
            default => true,
        };
    }

    /**
     * @param  Collection<int, Supplier>  $all
     * @param  array<int, float>  $dueBySupplierId
     */
    private function statsFor(Collection $all, array $dueBySupplierId): array
    {
        $weOweTotal = 0.0;
        $weOweCount = 0;
        $settledCount = 0;
        $biggestDueSupplier = null;
        $biggestDue = 0.0;

        foreach ($all as $supplier) {
            $due = $dueBySupplierId[$supplier->id] ?? 0.0;

            if ($due > 0) {
                $weOweCount++;
                $weOweTotal += $due;

                if ($due > $biggestDue) {
                    $biggestDue = $due;
                    $biggestDueSupplier = $supplier;
                }
            } else {
                $settledCount++;
            }
        }

        return [
            'we_owe_total' => $weOweTotal,
            'we_owe_count' => $weOweCount,
            'settled_count' => $settledCount,
            'biggest_due_supplier' => $biggestDueSupplier,
            'biggest_due' => $biggestDue,
        ];
    }

    /**
     * @param  Collection<int, Supplier>  $suppliers
     * @param  array<int, float>  $dueBySupplierId
     */
    private function sortSuppliers(Collection $suppliers, string $sort, array $dueBySupplierId): Collection
    {
        return match ($sort) {
            'due_desc' => $suppliers->sortByDesc(fn (Supplier $s) => $dueBySupplierId[$s->id] ?? 0.0)->values(),
            'name_desc' => $suppliers->sortByDesc(fn (Supplier $s) => mb_strtolower($s->name))->values(),
            default => $suppliers->sortBy(fn (Supplier $s) => mb_strtolower($s->name))->values(),
        };
    }

    /**
     * Works out every given supplier's due in one aggregate query instead
     * of calling dueAmount() (two SUM queries) once per row, which made
     * this list page slower the more suppliers were on it.
     */
    private function dueAmountsFor(Collection $suppliers): array
    {
        if ($suppliers->isEmpty()) {
            return [];
        }

        $totals = Purchase::query()
            ->whereIn('supplier_id', $suppliers->pluck('id'))
            ->selectRaw('supplier_id, COALESCE(SUM(total), 0) as purchased, COALESCE(SUM(paid_amount), 0) as paid')
            ->groupBy('supplier_id')
            ->get()
            ->keyBy('supplier_id');

        return $suppliers->mapWithKeys(function (Supplier $supplier) use ($totals) {
            $row = $totals->get($supplier->id);

            $due = round(
                (float) $supplier->opening_balance
                    + (float) ($row->purchased ?? 0)
                    - (float) ($row->paid ?? 0)
                    - (float) $supplier->credit_balance,
                2
            );

            return [$supplier->id => $due];
        })->all();
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['company_id'] = auth()->user()->company_id;

        $supplier = Supplier::create($data);

        if ($request->wantsJson()) {
            $supplier->due = $supplier->dueAmount();

            return response()->json($supplier, 201);
        }

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" added.");
    }

    public function show(Supplier $supplier): View
    {
        abort_unless($supplier->company_id === auth()->user()->company_id, 404);

        $entries = $this->buildLedger($supplier)->reverse()->values();
        $lastPayment = $entries->first(fn (array $entry) => $entry['type'] === 'paid');

        return view('suppliers.show', [
            'supplier' => $supplier,
            'entries' => $entries,
            'due' => $supplier->dueAmount(),
            'lastPaymentAmount' => $lastPayment['amount'] ?? null,
            'lastPaymentDate' => $lastPayment['date'] ?? null,
        ]);
    }

    public function statementPdf(Supplier $supplier): Response
    {
        abort_unless($supplier->company_id === auth()->user()->company_id, 404);

        $entries = $this->buildLedger($supplier);
        $months = $entries->groupBy(fn (array $entry) => $entry['date']->format('F Y'));

        return Pdf::loadView('suppliers.statement-pdf', [
            'supplier' => $supplier,
            'months' => $months,
            'openingBalance' => (float) $supplier->opening_balance,
            'totalDebit' => $entries->whereIn('type', ['billed', 'refund'])->sum('amount'),
            'totalCredit' => $entries->where('type', 'paid')->sum('amount'),
            'due' => $supplier->dueAmount(),
            'entryCount' => $entries->count(),
            'fromDate' => $entries->first()['date'] ?? now(),
            'toDate' => $entries->last()['date'] ?? now(),
        ])->setPaper('a4')->stream("{$supplier->name} - Statement.pdf");
    }

    /**
     * Records one payment to the supplier against their oldest outstanding
     * purchases first (a payment larger than one purchase's own balance
     * rolls over onto the next). Anything left over after every purchase is
     * fully paid settles their opening balance, and if it's still more than
     * that, the rest becomes credit we can draw on for a future purchase.
     *
     * A "received" payment is the reverse: money the supplier handed back
     * to us (e.g. a refund for goods billed but never delivered). It isn't
     * matched against any purchase — it just reduces whatever credit we're
     * holding with them (and going past zero correctly shows up as money
     * we now owe them again), since we can't know which purchase a refund
     * is "for" without them telling us.
     */
    public function storePayment(StoreSupplierPaymentRequest $request, Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->company_id === auth()->user()->company_id, 404);

        $data = $request->validated();

        if (($data['type'] ?? 'paid') === 'received') {
            DB::transaction(function () use ($supplier, $data) {
                PurchasePayment::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => auth()->id(),
                    'amount' => $data['amount'],
                    'type' => 'received',
                    'mode' => $data['mode'] ?? null,
                    'note' => $data['note'] ?? null,
                ]);

                $supplier->decrement('credit_balance', $data['amount']);
            });

            return redirect()->route('suppliers.show', $supplier)
                ->with('status', "Refund of ₹".number_format($data['amount'], 2)." recorded for \"{$supplier->name}\".");
        }

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
                    'mode' => $data['mode'] ?? null,
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
                    'mode' => $data['mode'] ?? null,
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
     * Deletes a statement entry for a wrongly recorded payment or refund —
     * identified by the batch_id a multi-purchase payment shares, or by its
     * own id when it's a single row (e.g. one made from a purchase's own
     * page). Reverses exactly what storePayment() did: gives back any
     * purchase's paid_amount it was applied to, or undoes whichever way it
     * moved the credit balance.
     */
    public function destroyPayment(Supplier $supplier, string $key): RedirectResponse
    {
        abort_unless($supplier->company_id === auth()->user()->company_id, 404);

        $payments = PurchasePayment::query()
            ->where('from_credit_balance', false)
            ->where(function ($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id)
                    ->orWhereHas('purchase', fn ($q) => $q->where('supplier_id', $supplier->id));
            })
            ->when(
                str_starts_with($key, 'id-'),
                fn ($query) => $query->where('id', (int) substr($key, 3)),
                fn ($query) => $query->where('batch_id', $key)
            )
            ->with('purchase')
            ->get();

        abort_if($payments->isEmpty(), 404);

        DB::transaction(function () use ($payments, $supplier) {
            foreach ($payments as $payment) {
                if ($payment->purchase_id) {
                    $purchase = $payment->purchase;
                    $purchase->decrement('paid_amount', $payment->amount);
                    $purchase->refresh();
                    $purchase->update([
                        'status' => $purchase->paid_amount >= $purchase->total ? 'paid' : ($purchase->paid_amount > 0 ? 'partial' : 'unpaid'),
                    ]);
                } elseif ($payment->type === 'received') {
                    $supplier->increment('credit_balance', $payment->amount);
                } else {
                    $supplier->decrement('credit_balance', $payment->amount);
                }

                $payment->delete();
            }
        });

        return redirect()->route('suppliers.show', $supplier)->with('status', 'Statement entry deleted.');
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
                'payment_key' => null,
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
                $isRefund = $first['payment']->type === 'received';

                $entries->push([
                    'date' => $first['payment']->created_at,
                    'type' => $isRefund ? 'refund' : 'paid',
                    'label' => $isRefund
                        ? ($first['payment']->note ? "Refund received ({$first['payment']->note})" : 'Refund received')
                        : ($first['payment']->note
                            ? "Payment ({$first['payment']->note})"
                            : ($first['purchase'] ? "Payment for {$first['purchase']->purchase_number}" : 'Payment')),
                    'amount' => (float) $rows->sum(fn (array $row) => (float) $row['payment']->amount),
                    'url' => $first['purchase'] ? route('purchases.show', $first['purchase']) : route('suppliers.show', $supplier),
                    'payment_key' => $first['payment']->batch_id ?: 'id-'.$first['payment']->id,
                ]);
            });

        $balance = (float) $supplier->opening_balance;

        return $entries->sortBy('date')->values()
            ->map(function (array $entry) use (&$balance) {
                // A refund works out the same way as a purchase bill does
                // for this running total — both increase what we owe them.
                $balance += in_array($entry['type'], ['billed', 'refund'], true) ? $entry['amount'] : -$entry['amount'];
                $entry['balance_after'] = round($balance, 2);

                return $entry;
            });
    }

    public function edit(Supplier $supplier): View
    {
        abort_unless($supplier->company_id === auth()->user()->company_id, 404);

        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->company_id === auth()->user()->company_id, 404);

        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" updated.");
    }

    public function search(Request $request): JsonResponse
    {
        $term = '%'.$request->string('q').'%';

        $suppliers = Supplier::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('phone', Sql::caseInsensitiveLike(), $term))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'address', 'opening_balance', 'credit_balance'])
            ->map(function (Supplier $supplier) {
                $supplier->due = $supplier->dueAmount();

                return $supplier;
            });

        return response()->json($suppliers);
    }
}
