<?php

namespace App\Http\Controllers;

use App\Support\Sql;

use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Branch;
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
use App\Services\NotificationService;
use App\Services\PerPagePreference;
use App\Support\InvoiceFormat;
use App\Support\InvoiceFormatData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    /**
     * Shared by index() and export() — date range + text search, scoped to
     * the current branch. Status filtering is applied separately so the
     * status-pill counts can be computed against the same base set.
     */
    private function filteredInvoicesQuery(Request $request, int $branchId)
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

        return Invoice::query()
            ->where('branch_id', $branchId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('customer', fn ($cq) => $cq->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('phone', Sql::caseInsensitiveLike(), $term))
                        ->orWhere('guest_customer_name', Sql::caseInsensitiveLike(), $term)
                        ->orWhere('invoice_number', Sql::caseInsensitiveLike(), $term);
                });
            })
            ->when($from && $to, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]));
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
        $baseQuery = $this->filteredInvoicesQuery($request, $branchId);

        // Aggregated with SQL (not an in-memory collection) so a branch
        // with years of invoice history under "All time" still loads fast —
        // only the current page's rows are ever actually fetched below.
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $totals = (clone $baseQuery)
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_billed,
                COALESCE(SUM(paid_amount), 0) as total_received,
                COALESCE(SUM(CASE WHEN total > paid_amount THEN total - paid_amount ELSE 0 END), 0) as total_pending,
                COUNT(*) as total_count,
                SUM(CASE WHEN customer_id IS NULL THEN 1 ELSE 0 END) as walkin_count
            ')
            ->first();

        $paidCount = (int) ($statusCounts['paid'] ?? 0);
        $unpaidCount = (int) ($statusCounts['unpaid'] ?? 0);
        $partialCount = (int) ($statusCounts['partial'] ?? 0);

        $stats = [
            'total_billed' => (float) $totals->total_billed,
            'total_received' => (float) $totals->total_received,
            'total_pending' => (float) $totals->total_pending,
            'total_count' => (int) $totals->total_count,
            'paid_count' => $paidCount,
            'unpaid_count' => $unpaidCount,
            'partial_count' => $partialCount,
            'not_paid_count' => $unpaidCount + $partialCount,
            'walkin_count' => (int) $totals->walkin_count,
        ];

        $invoices = (clone $baseQuery)
            ->with('customer', 'branch')
            ->when(in_array($status, ['paid', 'unpaid', 'partial'], true), fn ($q) => $q->where('status', $status))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        // Batched once for the whole page, not per row — the share text
        // needs each customer's running due amount, and calling
        // Customer::dueAmount() per invoice would add two SUM queries for
        // every row just to build a WhatsApp message nobody may even tap
        // "Share" for.
        $customerDues = Customer::dueAmountsFor($invoices->pluck('customer_id'));

        if ($request->ajax()) {
            return view('invoices.partials.table', ['invoices' => $invoices, 'customerDues' => $customerDues]);
        }

        return view('invoices.index', [
            'invoices' => $invoices,
            'activeQuickFilter' => $activeQuickFilter,
            'status' => $status ?: 'all',
            'stats' => $stats,
            'customerDues' => $customerDues,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $branchId = BranchContext::id();
        $status = $request->string('status')->toString();

        $invoices = $this->filteredInvoicesQuery($request, $branchId)
            ->with('customer', 'branch')
            ->when(in_array($status, ['paid', 'unpaid', 'partial'], true), fn ($q) => $q->where('status', $status))
            ->latest('invoice_date')
            ->latest('id')
            ->get();

        $filename = 'invoices-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice #', 'Date', 'Customer', 'Total', 'Paid', 'Balance', 'Status']);

            foreach ($invoices as $invoice) {
                fputcsv($out, [
                    $invoice->invoice_number,
                    $invoice->invoice_date->format('d M Y'),
                    $invoice->displayCustomerName(),
                    number_format((float) $invoice->total, 2),
                    number_format((float) $invoice->paid_amount, 2),
                    number_format($invoice->balanceDue(), 2),
                    ucfirst($invoice->status),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(): View
    {
        $branchId = BranchContext::id();

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->whereHas('stocks', fn ($q) => $q->where('branch_id', $branchId))
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
                'selling_price' => (float) $item->selling_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
                'hsn_code' => $item->hsn_code,
                'gst_rate' => $item->gst_rate !== null ? (float) $item->gst_rate : null,
            ]);

        return view('invoices.create', [
            'items' => $items,
            'nextInvoiceNumber' => $this->nextInvoiceNumber($branchId),
            'gstEnabled' => (bool) auth()->user()->company?->gst_enabled,
            'defaultPaymentStatus' => auth()->user()->company?->default_invoice_payment_status ?? 'paid',
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();
        $customerId = $data['customer_id'] ?? null;

        if ($customerId) {
            $this->assertCustomerInBranch($customerId, $branchId);
        }

        $invoice = DB::transaction(function () use ($data, $branchId, $customerId) {
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $transportation = (float) ($data['transportation'] ?? 0);
            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            // Re-checked against the company's own setting (not just the
            // submitted flag) so a request can't turn on GST invoicing for a
            // company that never enabled it.
            $isGstInvoice = (bool) ($data['is_gst_invoice'] ?? false) && (bool) auth()->user()->company?->gst_enabled;

            $invoice = Invoice::create([
                'invoice_number' => $this->nextInvoiceNumber($branchId),
                // Snapshot of the branch's format at the moment of billing,
                // so changing it later never repaints a bill already shared
                // with a customer.
                'invoice_format' => BranchContext::current()?->invoice_format,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'guest_customer_name' => $customerId ? null : $data['guest_customer_name'],
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
                'is_gst_invoice' => $isGstInvoice,
                'transport_note' => $data['transport_note'] ?? null,
            ]);

            ['subtotal' => $subtotal, 'gst_tax' => $gstTax] = $this->applyLines($invoice, $branchId, $data['items'], $isGstInvoice, $customerId);

            // GST tax is never trusted from the client on a compliance
            // document — it's always the server-computed sum of each line's
            // own HSN/rate, replacing whatever "tax" the form submitted.
            if ($isGstInvoice) {
                $tax = $gstTax;
            }

            $total = round($subtotal - $discount + $tax + $transportation, 2);

            if ($paidAmount > 0) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => auth()->id(),
                    'amount' => $paidAmount,
                    'note' => 'Paid at billing',
                    'mode' => $data['mode'] ?? null,
                ]);
            }

            // A walk-in billed by name only has no Customer record to hold
            // a credit balance on, so there's nothing to auto-draw against.
            if ($customerId) {
                // Any credit this customer is already holding (from a past
                // payment that exceeded everything they owed at the time)
                // auto-draws against what's still due on this new invoice,
                // oldest credit first — they shouldn't have to remember to
                // ask for it to be applied.
                $customer = Customer::whereKey($customerId)->lockForUpdate()->first();
                $stillDue = round($total - $paidAmount, 2);
                $creditToApply = min((float) $customer->credit_balance, max(0, $stillDue));

                if ($creditToApply > 0) {
                    InvoicePayment::create([
                        'invoice_id' => $invoice->id,
                        'user_id' => auth()->id(),
                        'amount' => $creditToApply,
                        'note' => 'Applied from customer credit balance',
                        'from_credit_balance' => true,
                    ]);

                    $customer->decrement('credit_balance', $creditToApply);
                    $paidAmount = round($paidAmount + $creditToApply, 2);
                }
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'status' => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            return $invoice;
        });

        $this->notifyBranch();

        $redirect = redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->invoice_number} created.");

        // Only a saved Customer carries a balance between visits — a
        // walk-in billed by name has nowhere for that to live, so the
        // shop has to remember the credit themselves. Flagged here rather
        // than silently, since it's easy to forget this bill was ever
        // partly unpaid once the page is closed.
        if ($invoice->isGuestCustomer() && $invoice->balanceDue() > 0.004) {
            $redirect->with('guest_credit_warning', true);
        }

        if ($request->boolean('share_after_save')) {
            $redirect->with('open_whatsapp_url', $this->whatsappShareUrl($invoice));
        }

        return $redirect;
    }

    /**
     * Mirrors AdminAlertService's own Indian-number normalization, but this
     * one is sent as (or on behalf of) the shop to its own customer rather
     * than to the admin, so it lives here instead of being reused from there.
     */
    private function whatsappShareUrl(Invoice $invoice): string
    {
        $digits = $invoice->customer?->phone ? preg_replace('/\D/', '', $invoice->customer->phone) : null;
        $number = $digits && strlen($digits) >= 10 ? '91'.substr($digits, -10) : null;

        return 'https://wa.me/'.$number.'?text='.rawurlencode($invoice->whatsappMessage());
    }

    /**
     * Invoice has no company_id of its own — only a branch_id — so
     * route-model-bound methods (show/edit/payments/pdf/etc.) check
     * ownership through the branch's own company, since binding an
     * invoice by id alone doesn't rule out one from another company.
     */
    private function belongsToCurrentCompany(Invoice $invoice): bool
    {
        return $invoice->branch?->company_id === auth()->user()->company_id;
    }

    public function edit(Invoice $invoice): View
    {
        abort_unless($this->belongsToCurrentCompany($invoice), 404);

        $invoice->load('items.item', 'customer', 'payments.user');

        $alreadyOnInvoice = $invoice->items->groupBy('item_id')
            ->map(fn ($lines) => $lines->sum('base_quantity'));

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            // A line already on this invoice stays selectable even if the
            // item isn't (or is no longer) stocked at this branch, so an
            // existing line can still be seen/edited/removed.
            ->where(fn ($q) => $q->whereHas('stocks', fn ($q2) => $q2->where('branch_id', $invoice->branch_id))
                ->orWhereIn('id', $alreadyOnInvoice->keys()))
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $invoice->branch_id)])
            ->orderBy('name')
            ->get()
            ->map(function (Item $item) use ($alreadyOnInvoice) {
                $onThisInvoice = (float) ($alreadyOnInvoice->get($item->id) ?? 0);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'unit' => $item->unit,
                    'alt_unit' => $item->alt_unit,
                    'alt_unit_ratio' => $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null,
                    'selling_price' => (float) $item->selling_price,
                    // What would be available (in the item's base unit) if
                    // this invoice's own current lines were released back to
                    // stock first — otherwise editing a line down to the same
                    // quantity it already has would look like it's short on
                    // stock. $onThisInvoice is already in base units since
                    // that's what's stored/deducted from stock.
                    'stock' => (float) ($item->stocks->first()->quantity ?? 0) + $onThisInvoice,
                ];
            });

        $itemsById = $items->keyBy('id');

        $initialLines = $invoice->items->map(fn ($line) => [
            'id' => $line->id,
            'item_id' => $line->item_id,
            'name' => $line->item->name,
            'unit' => $line->displayUnit(),
            'stock' => $itemsById->get($line->item_id)['stock'] ?? 0,
            'quantity' => (float) $line->quantity,
            'unit_price' => (float) $line->unit_price,
        ])->values();

        $selectedCustomer = $invoice->customer ? [
            'id' => $invoice->customer->id,
            'name' => $invoice->customer->name,
            'phone' => $invoice->customer->phone,
            'address' => $invoice->customer->address,
            'due' => $invoice->customer->dueAmount(),
        ] : null;

        return view('invoices.edit', [
            'invoice' => $invoice,
            'items' => $items,
            'initialLines' => $initialLines,
            'selectedCustomer' => $selectedCustomer,
            'guestCustomerName' => $invoice->guest_customer_name,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($invoice), 404);

        $data = $request->validated();
        $customerId = $data['customer_id'] ?? null;

        if ($customerId) {
            $this->assertCustomerInBranch($customerId, $invoice->branch_id);
        }

        DB::transaction(function () use ($data, $invoice, $customerId) {
            $this->reverseStockEffects($invoice);

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $transportation = (float) ($data['transportation'] ?? 0);

            // Whether this invoice is a GST invoice is fixed at creation —
            // editing only re-runs the same GST computation against the
            // (possibly changed) line items and customer.
            $isGstInvoice = (bool) $invoice->is_gst_invoice;

            ['subtotal' => $subtotal, 'gst_tax' => $gstTax] = $this->applyLines($invoice, $invoice->branch_id, $data['items'], $isGstInvoice, $customerId);

            if ($isGstInvoice) {
                $tax = $gstTax;
            }

            $total = round($subtotal - $discount + $tax + $transportation, 2);

            // The payment status buttons here correct the bill's own
            // recorded state (e.g. it was billed as "Unpaid" by mistake and
            // should say "Paid") — they don't create a new InvoicePayment
            // row, since no real payment actually happened just now. A
            // genuine new payment still goes through "Record a payment" on
            // the invoice page, which does create one.
            $paymentStatus = $data['payment_status'];
            $chosenAmount = match ($paymentStatus) {
                'paid' => $total,
                'partial' => min((float) ($data['paid_amount'] ?? 0), $total),
                default => 0.0,
            };

            // Never let these buttons set paid_amount below what real
            // InvoicePayment rows already show as received on this bill —
            // that money genuinely came in, even if the admin picks (by
            // mistake or otherwise) a status that implies less, e.g.
            // "Unpaid" on a bill that already has a real payment recorded.
            // The status is then derived fresh from whichever amount wins,
            // the same way a brand-new bill's status is derived.
            $actuallyReceived = (float) $invoice->payments()->sum('amount');
            $paidAmount = max($chosenAmount, $actuallyReceived);
            $paymentStatus = $total > 0 && $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

            $invoice->update([
                'customer_id' => $customerId,
                'guest_customer_name' => $customerId ? null : $data['guest_customer_name'],
                'invoice_date' => $data['invoice_date'],
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

        $this->notifyBranch();

        return redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->invoice_number} updated.");
    }

    /**
     * Re-syncs the current branch's notifications right after a save that
     * could newly cross a low-stock or over-credit-limit line, so a push
     * goes out the moment it happens rather than whenever someone next
     * opens the bell.
     */
    private function notifyBranch(): void
    {
        if ($branch = BranchContext::current()) {
            NotificationService::sync($branch);
        }
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
     * subtotal and (for a GST invoice) the server-computed total GST.
     * Assumes $invoice already exists (has an id) and currently has no
     * items — for an edit, call reverseStockEffects() first.
     */
    private function applyLines(Invoice $invoice, int $branchId, array $items, bool $isGstInvoice = false, ?int $customerId = null): array
    {
        $itemIds = collect($items)->pluck('item_id');

        $stocks = ItemStock::query()
            ->where('branch_id', $branchId)
            ->whereIn('item_id', $itemIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('item_id');

        $itemModels = Item::query()->whereIn('id', $itemIds)->get()->keyBy('id');

        $remaining = $stocks->map(fn (ItemStock $stock) => (float) $stock->quantity);

        // Same state (branch vs customer) means CGST+SGST; different states
        // mean a single IGST line instead — the total tax amount is the
        // same either way, only how it's split for filing differs. When
        // either side's state isn't on file, intra-state is assumed, since
        // most sales here are local.
        $sameState = true;
        if ($isGstInvoice) {
            $branchStateCode = Branch::whereKey($branchId)->value('state_code');
            $customerStateCode = $customerId ? Customer::whereKey($customerId)->value('state_code') : null;
            if ($branchStateCode && $customerStateCode) {
                $sameState = $branchStateCode === $customerStateCode;
            }
        }

        $subtotal = 0;
        $gstTax = 0;
        $lines = [];

        foreach ($items as $line) {
            $item = $itemModels->get($line['item_id']);
            $unit = $line['unit'] ?? $item->unit;

            // A line billed in the item's alt unit (e.g. kg out of a bag)
            // still deducts stock in the item's base/stock unit (the bag),
            // so it's converted here via the item's configured ratio.
            $isAltUnit = $item->hasAltUnit() && $unit === $item->alt_unit;
            $baseQuantity = $isAltUnit
                ? round((float) $line['quantity'] / (float) $item->alt_unit_ratio, 4)
                : (float) $line['quantity'];

            $available = $remaining->get($line['item_id'], 0.0);

            if ($available < $baseQuantity) {
                throw ValidationException::withMessages([
                    'items' => "Not enough stock for \"{$item?->name}\". Available: {$available} {$item?->unit}.",
                ]);
            }

            $remaining[$line['item_id']] = $available - $baseQuantity;

            $lineTotal = round($line['quantity'] * $line['unit_price'], 2);
            $subtotal += $lineTotal;

            $hsnCode = null;
            $gstRate = null;
            $cgstAmount = null;
            $sgstAmount = null;
            $igstAmount = null;

            if ($isGstInvoice) {
                $hsnCode = $item->hsn_code;
                $gstRate = $item->gst_rate !== null ? (float) $item->gst_rate : null;
                $lineGst = round($lineTotal * ($gstRate ?? 0) / 100, 2);
                $gstTax += $lineGst;

                if ($sameState) {
                    $cgstAmount = round($lineGst / 2, 2);
                    $sgstAmount = round($lineGst - $cgstAmount, 2);
                } else {
                    $igstAmount = $lineGst;
                }
            }

            $lines[] = [
                'item_id' => $line['item_id'],
                'quantity' => $line['quantity'],
                'unit' => $unit,
                'base_quantity' => $baseQuantity,
                'unit_price' => $line['unit_price'],
                'total' => $lineTotal,
                'hsn_code' => $hsnCode,
                'gst_rate' => $gstRate,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'igst_amount' => $igstAmount,
            ];
        }

        foreach ($lines as $line) {
            $invoiceItem = $invoice->items()->create($line);

            $stocks->get($line['item_id'])->decrement('quantity', $line['base_quantity']);

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'item_id' => $line['item_id'],
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $line['base_quantity'],
                'reason' => "Sale - Invoice {$invoice->invoice_number}",
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
            ]);

            // Links this line to the movement that actually consumed the
            // stock, so profit can later be worked out from the real cost
            // of the batch(es) it was FIFO-allocated from below, not just
            // the item's current/latest purchase price.
            $invoiceItem->update(['stock_movement_id' => $movement->id]);

            $this->allocateFromBatches($branchId, $line['item_id'], (float) $line['base_quantity'], $movement->id);
        }

        return ['subtotal' => $subtotal, 'gst_tax' => round($gstTax, 2)];
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
        abort_unless($this->belongsToCurrentCompany($invoice), 404);

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
            'customerDue' => $invoice->customer?->dueAmount(),
            'shareMessage' => $invoice->whatsappMessage(),
            'lowStockLines' => $lowStockLines,
            'lowStockAdminUrl' => $lowStockLines->isNotEmpty()
                ? AdminAlertService::lowStockUrl($invoice->branch, $lowStockLines)
                : null,
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($invoice), 404);

        DB::transaction(function () use ($invoice) {
            $this->reverseStockEffects($invoice);

            // Any credit this invoice auto-drew from the customer's balance
            // goes back to them — otherwise deleting the invoice would
            // simply erase money they're still owed credit for.
            $creditUsed = (float) $invoice->payments()->where('from_credit_balance', true)->sum('amount');
            if ($creditUsed > 0) {
                $invoice->customer()->increment('credit_balance', $creditUsed);
            }

            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('status', "Invoice {$invoice->invoice_number} deleted.");
    }

    public function pdf(Invoice $invoice): Response
    {
        abort_unless($this->belongsToCurrentCompany($invoice), 404);

        $invoice->load(['customer', 'branch.company', 'items.item', 'payments']);

        return $this->renderPdf($invoice)->stream("{$invoice->invoice_number}.pdf");
    }

    private function renderPdf(Invoice $invoice)
    {
        $formatView = InvoiceFormat::viewFor($invoice, $invoice->branch);

        // Only reachable if the configured default format's own view is
        // somehow missing — falls back to the app's original template
        // rather than a hard error.
        if (! $formatView) {
            return Pdf::loadView('invoices.tax-invoice-pdf', [
                'invoice' => $invoice,
                'customerDue' => $invoice->customer?->dueAmount(),
            ])->setPaper('a4');
        }

        return Pdf::loadView($formatView, InvoiceFormatData::build($invoice, forPdf: true))
            ->setPaper(InvoiceFormat::paperFor($invoice, $invoice->branch));
    }

    public function storePayment(StoreInvoicePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($invoice), 404);

        $data = $request->validated();

        DB::transaction(function () use ($data, $invoice) {
            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'note' => $data['note'] ?? 'Payment recorded',
                'mode' => $data['mode'] ?? null,
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
