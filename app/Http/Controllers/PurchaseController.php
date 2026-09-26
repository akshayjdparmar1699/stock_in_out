<?php

namespace App\Http\Controllers;

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
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();

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

        $activeQuickFilter = match (true) {
            $request->filled('from') || $request->filled('to') => null,
            $period !== '' => $period,
            default => 'all',
        };

        $purchases = Purchase::query()
            ->where('branch_id', $branchId)
            ->with('supplier')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->whereHas('supplier', fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->when($from && $to, fn ($q) => $q->whereBetween('purchase_date', [$from, $to]))
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('purchases.partials.table', ['purchases' => $purchases]);
        }

        return view('purchases.index', ['purchases' => $purchases, 'activeQuickFilter' => $activeQuickFilter]);
    }

    public function create(): View
    {
        $branchId = BranchContext::id();

        $items = Item::query()
            ->where('is_active', true)
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $branchId)])
            ->orderBy('name')
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'purchase_price' => (float) $item->purchase_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
            ]);

        return view('purchases.create', ['items' => $items]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();

        $purchase = DB::transaction(function () use ($data, $branchId) {
            $subtotal = 0;
            $lines = [];

            foreach ($data['items'] as $line) {
                $lineTotal = round($line['quantity'] * $line['unit_cost'], 2);
                $subtotal += $lineTotal;
                $lines[] = $line + ['total' => $lineTotal];
            }

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $total = round($subtotal - $discount + $tax, 2);
            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            $purchase = Purchase::create([
                'purchase_number' => $this->nextPurchaseNumber($branchId),
                'branch_id' => $branchId,
                'supplier_id' => $data['supplier_id'],
                'user_id' => auth()->id(),
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'status' => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $purchase->items()->create($line);

                $stock = ItemStock::query()->firstOrCreate(
                    ['branch_id' => $branchId, 'item_id' => $line['item_id']],
                    ['quantity' => 0]
                );
                $stock->increment('quantity', $line['quantity']);

                // Keep the item's cost basis current for profit calculations.
                Item::whereKey($line['item_id'])->update(['purchase_price' => $line['unit_cost']]);

                $movement = StockMovement::create([
                    'branch_id' => $branchId,
                    'item_id' => $line['item_id'],
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $line['quantity'],
                    'reason' => "Purchase - {$purchase->purchase_number}",
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                ]);

                StockBatch::create([
                    'branch_id' => $branchId,
                    'item_id' => $line['item_id'],
                    'stock_movement_id' => $movement->id,
                    'unit_cost' => $line['unit_cost'],
                    'quantity_in' => $line['quantity'],
                    'quantity_remaining' => $line['quantity'],
                    'received_at' => $movement->created_at,
                ]);
            }

            if ($paidAmount > 0) {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->id(),
                    'amount' => $paidAmount,
                    'note' => 'Payment made at purchase',
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

        return redirect()->route('purchases.show', $purchase)->with('status', "Purchase {$purchase->purchase_number} recorded.");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'branch', 'user', 'items.item', 'payments.user']);

        return view('purchases.show', [
            'purchase' => $purchase,
            'supplierDue' => $purchase->supplier->dueAmount(),
            'shareMessage' => $purchase->shareMessage(),
        ]);
    }

    public function pdf(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'branch', 'items.item']);

        return $this->renderPdf($purchase)->stream("{$purchase->purchase_number}.pdf");
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $movements = StockMovement::query()
            ->where('reference_type', Purchase::class)
            ->where('reference_id', $purchase->id)
            ->with(['batch', 'item'])
            ->get();

        $alreadySold = [];
        foreach ($movements as $movement) {
            $batch = $movement->batch;
            if ($batch && (float) $batch->quantity_remaining < (float) $batch->quantity_in) {
                $sold = (float) $batch->quantity_in - (float) $batch->quantity_remaining;
                $alreadySold[] = "{$movement->item?->name}: {$sold} {$movement->item?->unit} already sold";
            }
        }

        if (! empty($alreadySold)) {
            return back()
                ->with('status', 'Cannot delete: '.implode(', ', $alreadySold).'. Remove those sales first.')
                ->with('status_type', 'danger');
        }

        DB::transaction(function () use ($purchase, $movements) {
            foreach ($movements as $movement) {
                ItemStock::query()
                    ->where('branch_id', $purchase->branch_id)
                    ->where('item_id', $movement->item_id)
                    ->decrement('quantity', $movement->quantity);

                $movement->delete();
            }

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
        $data = $request->validated();

        DB::transaction(function () use ($data, $purchase) {
            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'note' => $data['note'] ?? null,
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
