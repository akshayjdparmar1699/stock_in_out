<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\StockBatch;
use App\Models\StockBatchAllocation;
use App\Models\StockMovement;
use App\Services\AdminAlertService;
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

class InvoiceController extends Controller
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

        $invoices = Invoice::query()
            ->where('branch_id', $branchId)
            ->with('customer')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->whereHas('customer', fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->when($from && $to, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('invoices.partials.table', ['invoices' => $invoices]);
        }

        return view('invoices.index', [
            'invoices' => $invoices,
            'activeQuickFilter' => $activeQuickFilter,
        ]);
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
                'selling_price' => (float) $item->selling_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
            ]);

        return view('invoices.create', ['items' => $items]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();

        $this->assertCustomerInBranch($data['customer_id'], $branchId);

        $invoice = DB::transaction(function () use ($data, $branchId) {
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $transportation = (float) ($data['transportation'] ?? 0);
            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            $invoice = Invoice::create([
                'invoice_number' => $this->nextInvoiceNumber($branchId),
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'user_id' => auth()->id(),
                'invoice_date' => $data['invoice_date'],
                'subtotal' => 0,
                'discount' => $discount,
                'tax' => $tax,
                'transportation' => $transportation,
                'total' => 0,
                'paid_amount' => $paidAmount,
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = $this->applyLines($invoice, $branchId, $data['items']);
            $total = round($subtotal - $discount + $tax + $transportation, 2);

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            if ($paidAmount > 0) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => auth()->id(),
                    'amount' => $paidAmount,
                    'note' => 'Payment received at billing',
                ]);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->invoice_number} created.");
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('items.item', 'customer');

        $alreadyOnInvoice = $invoice->items->keyBy('item_id');

        $items = Item::query()
            ->where('is_active', true)
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $invoice->branch_id)])
            ->orderBy('name')
            ->get()
            ->map(function (Item $item) use ($alreadyOnInvoice) {
                $onThisInvoice = (float) ($alreadyOnInvoice->get($item->id)?->quantity ?? 0);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'unit' => $item->unit,
                    'selling_price' => (float) $item->selling_price,
                    // What would be available if this invoice's own current
                    // lines were released back to stock first — otherwise
                    // editing a line down to the same quantity it already
                    // has would look like it's short on stock.
                    'stock' => (float) ($item->stocks->first()->quantity ?? 0) + $onThisInvoice,
                ];
            });

        $itemsById = $items->keyBy('id');

        $initialLines = $invoice->items->map(fn ($line) => [
            'id' => $line->id,
            'item_id' => $line->item_id,
            'name' => $line->item->name,
            'unit' => $line->item->unit,
            'stock' => $itemsById->get($line->item_id)['stock'] ?? 0,
            'quantity' => (float) $line->quantity,
            'unit_price' => (float) $line->unit_price,
        ])->values();

        $selectedCustomer = [
            'id' => $invoice->customer->id,
            'name' => $invoice->customer->name,
            'phone' => $invoice->customer->phone,
            'address' => $invoice->customer->address,
            'due' => $invoice->customer->dueAmount(),
        ];

        return view('invoices.edit', [
            'invoice' => $invoice,
            'items' => $items,
            'initialLines' => $initialLines,
            'selectedCustomer' => $selectedCustomer,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validated();

        $this->assertCustomerInBranch($data['customer_id'], $invoice->branch_id);

        DB::transaction(function () use ($data, $invoice) {
            $this->reverseStockEffects($invoice);

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $transportation = (float) ($data['transportation'] ?? 0);

            $subtotal = $this->applyLines($invoice, $invoice->branch_id, $data['items']);
            $total = round($subtotal - $discount + $tax + $transportation, 2);

            $invoice->update([
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'transportation' => $transportation,
                'total' => $total,
                'status' => $invoice->paid_amount >= $total ? 'paid' : ($invoice->paid_amount > 0 ? 'partial' : 'unpaid'),
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->invoice_number} updated.");
    }

    private function assertCustomerInBranch(int $customerId, int $branchId): void
    {
        $belongsToBranch = Customer::query()
            ->where('id', $customerId)
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->exists();

        if (! $belongsToBranch) {
            throw ValidationException::withMessages([
                'customer_id' => 'This customer does not belong to the current branch.',
            ]);
        }
    }

    /**
     * Validates stock for each line (tracking a running remainder so two
     * lines for the same item are checked against their combined quantity),
     * then creates the invoice_items, decrements stock, and records the
     * stock-out movement + FIFO batch allocation for each. Returns the
     * subtotal. Assumes $invoice already exists (has an id) and currently
     * has no items — for an edit, call reverseStockEffects() first.
     */
    private function applyLines(Invoice $invoice, int $branchId, array $items): float
    {
        $stocks = ItemStock::query()
            ->where('branch_id', $branchId)
            ->whereIn('item_id', collect($items)->pluck('item_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('item_id');

        $remaining = $stocks->map(fn (ItemStock $stock) => (float) $stock->quantity);

        $subtotal = 0;
        $lines = [];

        foreach ($items as $line) {
            $available = $remaining->get($line['item_id'], 0.0);

            if ($available < $line['quantity']) {
                $item = Item::find($line['item_id']);
                throw ValidationException::withMessages([
                    'items' => "Not enough stock for \"{$item?->name}\". Available: {$available} {$item?->unit}.",
                ]);
            }

            $remaining[$line['item_id']] = $available - (float) $line['quantity'];

            $lineTotal = round($line['quantity'] * $line['unit_price'], 2);
            $subtotal += $lineTotal;
            $lines[] = $line + ['total' => $lineTotal];
        }

        foreach ($lines as $line) {
            $invoice->items()->create($line);

            $stocks->get($line['item_id'])->decrement('quantity', $line['quantity']);

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'item_id' => $line['item_id'],
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $line['quantity'],
                'reason' => "Sale - Invoice {$invoice->invoice_number}",
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
            ]);

            $this->allocateFromBatches($branchId, $line['item_id'], (float) $line['quantity'], $movement->id);
        }

        return $subtotal;
    }

    /**
     * Undoes applyLines(): restores the stock and FIFO batches this
     * invoice's current lines consumed, and removes those lines. Used by
     * both destroy() and update() (which re-applies fresh lines right after).
     */
    private function reverseStockEffects(Invoice $invoice): void
    {
        $movements = StockMovement::query()
            ->where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->with('batchAllocations')
            ->get();

        foreach ($movements as $movement) {
            foreach ($movement->batchAllocations as $allocation) {
                StockBatch::whereKey($allocation->stock_batch_id)->increment('quantity_remaining', $allocation->quantity);
            }

            ItemStock::query()
                ->where('branch_id', $invoice->branch_id)
                ->where('item_id', $movement->item_id)
                ->increment('quantity', $movement->quantity);

            $movement->delete();
        }

        $invoice->items()->delete();
    }

    /**
     * Deplete this item's oldest batches first (FIFO) to cover the sold quantity,
     * so each stock-in lot can be traced through to what it was sold as.
     */
    private function allocateFromBatches(int $branchId, int $itemId, float $quantity, int $movementId): void
    {
        $batches = StockBatch::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $itemId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('received_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($quantity <= 0) {
                break;
            }

            $take = min((float) $batch->quantity_remaining, $quantity);

            $batch->decrement('quantity_remaining', $take);

            StockBatchAllocation::create([
                'stock_batch_id' => $batch->id,
                'stock_movement_id' => $movementId,
                'quantity' => $take,
            ]);

            $quantity -= $take;
        }
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'branch', 'user', 'items.item', 'payments.user']);

        $lowStockLines = ItemStock::query()
            ->where('branch_id', $invoice->branch_id)
            ->whereIn('item_id', $invoice->items->pluck('item_id'))
            ->with('item')
            ->get()
            ->filter(fn (ItemStock $stock) => $stock->quantity <= $stock->item->low_stock_threshold)
            ->map(fn (ItemStock $stock) => [
                'name' => $stock->item->name,
                'quantity' => $stock->quantity,
                'unit' => $stock->item->unit,
            ]);

        return view('invoices.show', [
            'invoice' => $invoice,
            'customerDue' => $invoice->customer->dueAmount(),
            'shareMessage' => $invoice->whatsappMessage(),
            'lowStockLines' => $lowStockLines,
            'lowStockAdminUrl' => $lowStockLines->isNotEmpty()
                ? AdminAlertService::lowStockUrl($invoice->branch, $lowStockLines)
                : null,
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($invoice) {
            $this->reverseStockEffects($invoice);
            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('status', "Invoice {$invoice->invoice_number} deleted.");
    }

    public function pdf(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'branch', 'items.item']);

        return $this->renderPdf($invoice)->stream("{$invoice->invoice_number}.pdf");
    }

    private function renderPdf(Invoice $invoice)
    {
        return Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'customerDue' => $invoice->customer->dueAmount(),
        ])->setPaper('a4');
    }

    public function storePayment(StoreInvoicePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $invoice) {
            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'note' => $data['note'] ?? null,
            ]);

            $invoice->increment('paid_amount', $data['amount']);
            $invoice->refresh();

            $invoice->update([
                'status' => $invoice->paid_amount >= $invoice->total ? 'paid' : ($invoice->paid_amount > 0 ? 'partial' : 'unpaid'),
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Payment recorded.');
    }

    /**
     * Based on the highest existing sequence number, not the row count —
     * a deleted invoice leaves a gap, and counting rows would eventually
     * regenerate a number that's still in use by one that survived.
     */
    private function nextInvoiceNumber(int $branchId): string
    {
        $branchCode = str_pad((string) $branchId, 2, '0', STR_PAD_LEFT);
        $prefix = "INV-B{$branchCode}-";

        $nextSequence = Invoice::query()
            ->where('branch_id', $branchId)
            ->where('invoice_number', 'like', "{$prefix}%")
            ->pluck('invoice_number')
            ->map(fn (string $number) => (int) substr($number, strlen($prefix)))
            ->max() + 1;

        return $prefix.str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }
}
