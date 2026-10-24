<?php

namespace App\Http\Controllers;

use App\Support\Sql;

use App\Http\Requests\StorePurchasePaymentRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseController extends Controller
{
    /**
     * Shared by index() and export() — date range + text search, scoped to
     * the current branch. Status filtering is applied separately so the
     * status-pill counts can be computed against the same base set.
     */
    private function filteredPurchasesQuery(Request $request, int $branchId)
    {
        $period = $request->string('period')->toString();
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;

        if ($period && ! $from && ! $to) {
            [$from, $to] = match ($period) {
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'week' => [now()->startOfWeek(), now()->endOfWeek()],
                'month' => [now()->startOfMonth(), now()->endOfMonth()],
                default => [null, null],
            };
        }

        return Purchase::query()
            ->where('branch_id', $branchId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('supplier', fn ($sq) => $sq->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('phone', Sql::caseInsensitiveLike(), $term))
                        ->orWhere('purchase_number', Sql::caseInsensitiveLike(), $term);
                });
            })
            ->when($from && $to, fn ($q) => $q->whereBetween('purchase_date', [$from, $to]));
    }

    public function index(Request $request): View
    {
        $branchId = BranchContext::id();

        $period = $request->string('period')->toString();
        $activeQuickFilter = match (true) {
            $request->filled('from') || $request->filled('to') => null,
            $period !== '' => $period,
            default => 'all',
        };

        $status = $request->string('status')->toString();
        $baseQuery = $this->filteredPurchasesQuery($request, $branchId);

        // Aggregated with SQL (not an in-memory collection) so a branch
        // with years of purchase history under "All time" still loads fast —
        // only the current page's rows are ever actually fetched below.
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $totals = (clone $baseQuery)
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_purchased,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(CASE WHEN total > paid_amount THEN total - paid_amount ELSE 0 END), 0) as total_pending,
                COUNT(*) as total_count,
                COUNT(DISTINCT supplier_id) as supplier_count
            ')
            ->first();

        $paidCount = (int) ($statusCounts['paid'] ?? 0);
        $unpaidCount = (int) ($statusCounts['unpaid'] ?? 0);
        $partialCount = (int) ($statusCounts['partial'] ?? 0);

        $stats = [
            'total_purchased' => (float) $totals->total_purchased,
            'total_paid' => (float) $totals->total_paid,
            'total_pending' => (float) $totals->total_pending,
            'total_count' => (int) $totals->total_count,
            'supplier_count' => (int) $totals->supplier_count,
            'paid_count' => $paidCount,
            'unpaid_count' => $unpaidCount,
            'partial_count' => $partialCount,
        ];

        $purchases = (clone $baseQuery)
            ->with('supplier')
            ->when(in_array($status, ['paid', 'unpaid', 'partial'], true), fn ($q) => $q->where('status', $status))
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('purchases.partials.table', ['purchases' => $purchases]);
        }

        return view('purchases.index', [
            'purchases' => $purchases,
            'activeQuickFilter' => $activeQuickFilter,
            'status' => $status ?: 'all',
            'stats' => $stats,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $branchId = BranchContext::id();
        $status = $request->string('status')->toString();

        $purchases = $this->filteredPurchasesQuery($request, $branchId)
            ->with('supplier')
            ->when(in_array($status, ['paid', 'unpaid', 'partial'], true), fn ($q) => $q->where('status', $status))
            ->latest('purchase_date')
            ->latest('id')
            ->get();

        $filename = 'purchases-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($purchases) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Purchase #', 'Date', 'Supplier', 'Total', 'Paid', 'Balance', 'Status']);

            foreach ($purchases as $purchase) {
                fputcsv($out, [
                    $purchase->purchase_number,
                    $purchase->purchase_date->format('d M Y'),
                    $purchase->supplier->name,
                    number_format((float) $purchase->total, 2),
                    number_format((float) $purchase->paid_amount, 2),
                    number_format($purchase->balanceDue(), 2),
                    ucfirst($purchase->status),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(Request $request): View
    {
        $branchId = BranchContext::id();

        $preselectSupplier = null;

        if ($request->filled('supplier_id')) {
            $supplier = Supplier::query()
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
                ->find($request->integer('supplier_id'));

            if ($supplier) {
                $preselectSupplier = [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'due' => $supplier->dueAmount(),
                ];
            }
        }

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $branchId)])
            ->orderBy('name')
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'alt_unit' => $item->alt_unit,
                'alt_unit_ratio' => $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null,
                'purchase_price' => (float) $item->purchase_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
            ]);

        return view('purchases.create', [
            'items' => $items,
            'nextPurchaseNumber' => $this->nextPurchaseNumber($branchId),
            'preselectSupplier' => $preselectSupplier,
            'defaultPaymentStatus' => auth()->user()->company?->default_purchase_payment_status ?? 'paid',
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();

        $this->assertSupplierInBranch((int) $data['supplier_id'], $branchId);

        $purchase = DB::transaction(function () use ($data, $branchId) {
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $transportation = (float) ($data['transportation'] ?? 0);
            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            $purchase = Purchase::create([
                'purchase_number' => $this->nextPurchaseNumber($branchId),
                'branch_id' => $branchId,
                'supplier_id' => $data['supplier_id'],
                'user_id' => auth()->id(),
                'purchase_date' => $data['purchase_date'],
                'subtotal' => 0,
                'discount' => $discount,
                'tax' => $tax,
                'transportation' => $transportation,
                'total' => 0,
                'paid_amount' => $paidAmount,
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = $this->applyLines($purchase, $branchId, $data['items']);
            $total = round($subtotal - $discount + $tax + $transportation, 2);

            $purchase->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            if ($paidAmount > 0) {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->id(),
                    'amount' => $paidAmount,
                    'note' => 'Payment made at purchase',
                    'mode' => $data['mode'] ?? null,
                ]);
            }

            // Any credit we're already holding with this supplier (from a
            // past payment that exceeded everything owed at the time)
            // auto-draws against what's still due on this new purchase.
            $supplier = Supplier::whereKey($data['supplier_id'])->lockForUpdate()->first();
            $stillDue = round($total - $paidAmount, 2);
            $creditToApply = min((float) $supplier->credit_balance, max(0, $stillDue));

            if ($creditToApply > 0) {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->id(),
                    'amount' => $creditToApply,
                    'note' => 'Applied from supplier credit balance',
                    'from_credit_balance' => true,
                ]);

                $supplier->decrement('credit_balance', $creditToApply);
                $paidAmount = round($paidAmount + $creditToApply, 2);

                $purchase->update([
                    'paid_amount' => $paidAmount,
                    'status' => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            return $purchase;
        });

        $redirect = redirect()->route('purchases.show', $purchase)->with('status', "Purchase {$purchase->purchase_number} recorded.");

        if ($request->boolean('share_after_save')) {
            $redirect->with('open_whatsapp_url', $this->whatsappShareUrl($purchase));
        }

        return $redirect;
    }

    private function whatsappShareUrl(Purchase $purchase): string
    {
        $digits = $purchase->supplier?->phone ? preg_replace('/\D/', '', $purchase->supplier->phone) : null;
        $number = $digits && strlen($digits) >= 10 ? '91'.substr($digits, -10) : null;

        return 'https://wa.me/'.$number.'?text='.rawurlencode($purchase->shareMessage());
    }

    /**
     * Purchase has no company_id of its own — only a branch_id — so
     * route-model-bound methods (show/edit/payments/pdf/etc.) check
     * ownership through the branch's own company, since binding a
     * purchase by id alone doesn't rule out one from another company.
     */
    private function belongsToCurrentCompany(Purchase $purchase): bool
    {
        return $purchase->branch?->company_id === auth()->user()->company_id;
    }

    private function assertSupplierInBranch(int $supplierId, int $branchId): void
    {
        $belongsToBranch = Supplier::query()
            ->where('id', $supplierId)
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->exists();

        if (! $belongsToBranch) {
            throw ValidationException::withMessages([
                'supplier_id' => 'This supplier does not belong to the current branch.',
            ]);
        }
    }

    public function edit(Purchase $purchase): View
    {
        abort_unless($this->belongsToCurrentCompany($purchase), 404);

        $purchase->load('items.item', 'supplier');

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $purchase->branch_id)])
            ->orderBy('name')
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'alt_unit' => $item->alt_unit,
                'alt_unit_ratio' => $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null,
                'purchase_price' => (float) $item->purchase_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
            ]);

        $itemsById = $items->keyBy('id');

        $initialLines = $purchase->items->map(fn ($line) => [
            'id' => $line->id,
            'item_id' => $line->item_id,
            'name' => $line->item->name,
            'unit' => $line->item->unit,
            'stock' => $itemsById->get($line->item_id)['stock'] ?? 0,
            'quantity' => (float) $line->quantity,
            'unit_cost' => (float) $line->unit_cost,
        ])->values();

        $selectedSupplier = [
            'id' => $purchase->supplier->id,
            'name' => $purchase->supplier->name,
            'phone' => $purchase->supplier->phone,
            'address' => $purchase->supplier->address,
            'due' => $purchase->supplier->dueAmount(),
        ];

        return view('purchases.edit', [
            'purchase' => $purchase,
            'items' => $items,
            'initialLines' => $initialLines,
            'selectedSupplier' => $selectedSupplier,
        ]);
    }

    public function update(StorePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($purchase), 404);

        $data = $request->validated();

        $this->assertReversible($purchase);

        DB::transaction(function () use ($data, $purchase) {
            $this->reverseStockEffects($purchase);

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $transportation = (float) ($data['transportation'] ?? 0);

            $subtotal = $this->applyLines($purchase, $purchase->branch_id, $data['items']);
            $total = round($subtotal - $discount + $tax + $transportation, 2);

            // The payment status buttons here correct the purchase's own
            // recorded state — they don't create a new PurchasePayment row,
            // since no real payment actually happened just now. A genuine
            // new payment still goes through the payment form on the
            // purchase page, which does create one. Falls back to the old
            // derive-from-existing-paid_amount behaviour if payment_status
            // isn't present, so this stays safe even if ever hit without it.
            $paymentStatus = $data['payment_status']
                ?? ($purchase->paid_amount >= $total ? 'paid' : ($purchase->paid_amount > 0 ? 'partial' : 'unpaid'));
            $chosenAmount = match ($paymentStatus) {
                'paid' => $total,
                'partial' => min((float) ($data['paid_amount'] ?? $purchase->paid_amount), $total),
                default => 0.0,
            };

            // Never let these buttons set paid_amount below what real
            // PurchasePayment rows already show as paid on this purchase —
            // that money genuinely went out, even if the admin picks (by
            // mistake or otherwise) a status that implies less. The status
            // is then derived fresh from whichever amount wins.
            $actuallyPaid = (float) $purchase->payments()->sum('amount');
            $paidAmount = max($chosenAmount, $actuallyPaid);
            $paymentStatus = $total > 0 && $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'transportation' => $transportation,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'status' => $paymentStatus,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('purchases.show', $purchase)->with('status', "Purchase {$purchase->purchase_number} updated.");
    }

    /**
     * Validates and creates every item line for a purchase that currently
     * has none (assumes $purchase already exists and has no items — for an
     * edit, call reverseStockEffects() first), incrementing stock and
     * recording a stock-in movement + FIFO batch for each. Returns the
     * subtotal.
     */
    private function applyLines(Purchase $purchase, int $branchId, array $items): float
    {
        $subtotal = 0;

        foreach ($items as $line) {
            $item = Item::whereKey($line['item_id'])->lockForUpdate()->first();

            // The alt-unit config can be set/updated right from the
            // purchase form instead of a separate trip to edit the item.
            if (array_key_exists('alt_unit', $line)) {
                $item->update([
                    'alt_unit' => $line['alt_unit'],
                    'alt_unit_ratio' => $line['alt_unit'] ? ($line['alt_unit_ratio'] ?? null) : null,
                ]);
            }

            // The quantity/cost actually typed can be in the item's
            // alternate unit (e.g. 35 pcs instead of 0.7 bag) — the amount
            // billed comes out the same either way, so it's worked out
            // before converting down to the base unit everything else
            // (stock, batches) is stored in.
            $lineTotal = round($line['quantity'] * $line['unit_cost'], 2);
            $subtotal += $lineTotal;

            $ratio = $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null;
            $useAlt = ($line['quantity_unit'] ?? 'base') === 'alt' && $ratio;

            $quantity = $useAlt ? round((float) $line['quantity'] / $ratio, 4) : (float) $line['quantity'];
            $unitCost = $useAlt ? round((float) $line['unit_cost'] * $ratio, 2) : (float) $line['unit_cost'];

            $purchase->items()->create([
                'item_id' => $line['item_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total' => $lineTotal,
            ]);

            $stock = ItemStock::query()->firstOrCreate(
                ['branch_id' => $branchId, 'item_id' => $line['item_id']],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $quantity);

            // Keep the item's cost basis current for profit calculations.
            $item->update(['purchase_price' => $unitCost]);

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'item_id' => $line['item_id'],
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $quantity,
                'reason' => "Purchase - {$purchase->purchase_number}",
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
            ]);

            StockBatch::create([
                'branch_id' => $branchId,
                'item_id' => $line['item_id'],
                'stock_movement_id' => $movement->id,
                'unit_cost' => $unitCost,
                'quantity_in' => $quantity,
                'quantity_remaining' => $quantity,
                'received_at' => $movement->created_at,
            ]);
        }

        return $subtotal;
    }

    /**
     * Throws if any of this purchase's stock has already been sold — used
     * before both editing and deleting, since either one is about to wipe
     * out the batches that sale drew from.
     */
    private function assertReversible(Purchase $purchase): void
    {
        $alreadySold = [];

        foreach ($this->purchaseMovements($purchase) as $movement) {
            $batch = $movement->batch;
            if ($batch && (float) $batch->quantity_remaining < (float) $batch->quantity_in) {
                $sold = (float) $batch->quantity_in - (float) $batch->quantity_remaining;
                $alreadySold[] = "{$movement->item?->name}: {$sold} {$movement->item?->unit} already sold";
            }
        }

        if (! empty($alreadySold)) {
            throw ValidationException::withMessages([
                'items' => 'Cannot edit: '.implode(', ', $alreadySold).'. Remove those sales first.',
            ]);
        }
    }

    /**
     * Undoes applyLines(): restores the stock this purchase's current lines
     * added (deleting each stock-in movement cascades to the batch it
     * created) and removes those lines. Used by update() (which re-applies
     * fresh lines right after) — destroy() has its own copy since it also
     * needs the movements for its own guard message.
     */
    private function reverseStockEffects(Purchase $purchase): void
    {
        foreach ($this->purchaseMovements($purchase) as $movement) {
            ItemStock::query()
                ->where('branch_id', $purchase->branch_id)
                ->where('item_id', $movement->item_id)
                ->decrement('quantity', $movement->quantity);

            $movement->delete();
        }

        $purchase->items()->delete();
    }

    private function purchaseMovements(Purchase $purchase)
    {
        return StockMovement::query()
            ->where('reference_type', Purchase::class)
            ->where('reference_id', $purchase->id)
            ->with(['batch', 'item'])
            ->get();
    }

    public function show(Purchase $purchase): View
    {
        abort_unless($this->belongsToCurrentCompany($purchase), 404);

        $purchase->load(['supplier', 'branch', 'user', 'items.item', 'payments.user']);

        return view('purchases.show', [
            'purchase' => $purchase,
            'supplierDue' => $purchase->supplier->dueAmount(),
            'shareMessage' => $purchase->shareMessage(),
        ]);
    }

    public function pdf(Purchase $purchase): Response
    {
        abort_unless($this->belongsToCurrentCompany($purchase), 404);

        $purchase->load(['supplier', 'branch', 'items.item']);

        return $this->renderPdf($purchase)->stream("{$purchase->purchase_number}.pdf");
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($purchase), 404);

        try {
            $this->assertReversible($purchase);
        } catch (ValidationException $e) {
            return back()
                ->with('status', collect($e->errors())->flatten()->first())
                ->with('status_type', 'danger');
        }

        DB::transaction(function () use ($purchase) {
            $this->reverseStockEffects($purchase);

            // Any credit this purchase auto-drew from the supplier's
            // balance goes back to them — otherwise deleting the purchase
            // would simply erase credit we still owe them for.
            $creditUsed = (float) $purchase->payments()->where('from_credit_balance', true)->sum('amount');
            if ($creditUsed > 0) {
                $purchase->supplier()->increment('credit_balance', $creditUsed);
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('status', "Purchase {$purchase->purchase_number} deleted.");
    }

    private function renderPdf(Purchase $purchase)
    {
        return Pdf::loadView('purchases.pdf', [
            'purchase' => $purchase,
            'supplierDue' => $purchase->supplier->dueAmount(),
        ])->setPaper('a4');
    }

    public function storePayment(StorePurchasePaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($purchase), 404);

        $data = $request->validated();

        DB::transaction(function () use ($data, $purchase) {
            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'note' => $data['note'] ?? null,
                'mode' => $data['mode'] ?? null,
            ]);

            $purchase->increment('paid_amount', $data['amount']);
            $purchase->refresh();

            $purchase->update([
                'status' => $purchase->paid_amount >= $purchase->total ? 'paid' : ($purchase->paid_amount > 0 ? 'partial' : 'unpaid'),
            ]);
        });

        return redirect()->route('purchases.show', $purchase)->with('status', 'Payment recorded.');
    }

    /**
     * Based on the highest existing sequence number, not the row count —
     * a deleted purchase leaves a gap, and counting rows would eventually
     * regenerate a number that's still in use by one that survived.
     */
    private function nextPurchaseNumber(int $branchId): string
    {
        $branchCode = str_pad((string) $branchId, 2, '0', STR_PAD_LEFT);
        $prefix = "PUR-B{$branchCode}-";

        $nextSequence = Purchase::query()
            ->where('branch_id', $branchId)
            ->where('purchase_number', 'like', "{$prefix}%")
            ->pluck('purchase_number')
            ->map(fn (string $number) => (int) substr($number, strlen($prefix)))
            ->max() + 1;

        return $prefix.str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }
}
