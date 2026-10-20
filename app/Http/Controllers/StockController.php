<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\StockBatch;
use App\Models\StockBatchAllocation;
use App\Models\StockMovement;
use App\Models\Purchase;
use App\Services\BranchContext;
use App\Services\NotificationService;
use App\Services\PerPagePreference;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();
        $filter = $request->string('filter', 'all')->toString();
        $filter = in_array($filter, ['in', 'out', 'manual'], true) ? $filter : 'all';

        $base = StockMovement::query()->where('branch_id', $branchId);

        $movements = (clone $base)
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))
            ->when($filter === 'in', fn ($q) => $q->where('type', 'in'))
            ->when($filter === 'out', fn ($q) => $q->where('type', 'out'))
            ->when($filter === 'manual', fn ($q) => $q->whereNull('reference_type'))
            ->with(['item', 'user', 'reference'])
            ->latest()
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        $remainingByMovement = $this->remainingStockByMovement($branchId, $movements->pluck('item_id')->unique());

        if ($request->ajax()) {
            return view('stock.partials.table', ['movements' => $movements, 'remainingByMovement' => $remainingByMovement]);
        }

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->whereHas('stocks', fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => (clone $base)->count(),
            'in' => (clone $base)->where('type', 'in')->count(),
            'out' => (clone $base)->where('type', 'out')->count(),
            'from_purchases' => (clone $base)->where('reference_type', Purchase::class)->count(),
            'manual' => (clone $base)->whereNull('reference_type')->count(),
        ];

        return view('stock.index', [
            'movements' => $movements,
            'items' => $items,
            'remainingByMovement' => $remainingByMovement,
            'filterItemId' => $request->integer('item_id') ?: null,
            'filter' => $filter,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $branchId = BranchContext::id();

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->whereHas('stocks', fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $stockByItem = ItemStock::query()
            ->where('branch_id', $branchId)
            ->whereIn('item_id', $items->pluck('id'))
            ->pluck('quantity', 'item_id');

        return view('stock.create', [
            'items' => $items,
            'stockByItem' => $stockByItem,
        ]);
    }

    /**
     * For each of the given items, walks every one of their movements in
     * this branch newest-first, starting from the item's actual current
     * stock (ItemStock.quantity) and undoing each movement in turn — rather
     * than summing forward from zero. Editing an invoice or purchase deletes
     * its old stock-in/out entries without leaving any trace behind (see
     * reverseStockEffects() on those controllers), so a forward sum can
     * drift below the real total once an item has had any edited document;
     * anchoring on the current quantity instead guarantees the newest row
     * always agrees with what the Items page shows. Keyed by movement id so
     * a paginated page can look up just the rows it's showing.
     */
    private function remainingStockByMovement(int $branchId, $itemIds): array
    {
        if ($itemIds->isEmpty()) {
            return [];
        }

        $currentQuantities = ItemStock::query()
            ->where('branch_id', $branchId)
            ->whereIn('item_id', $itemIds)
            ->pluck('quantity', 'item_id');

        $allMovements = StockMovement::query()
            ->where('branch_id', $branchId)
            ->whereIn('item_id', $itemIds)
            ->orderBy('item_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'item_id', 'type', 'quantity']);

        $running = [];
        $balances = [];

        foreach ($allMovements as $movement) {
            if (! array_key_exists($movement->item_id, $running)) {
                $running[$movement->item_id] = (float) ($currentQuantities[$movement->item_id] ?? 0);
            }

            $balances[$movement->id] = round($running[$movement->item_id], 2);

            $running[$movement->item_id] -= $movement->type === 'in' ? (float) $movement->quantity : -(float) $movement->quantity;
        }

        return $balances;
    }

    public function store(AddStockRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();
        $type = $data['type'] ?? 'in';

        // Lets a correction be logged as of when it actually happened (e.g.
        // an opening-stock count taken a few days ago) instead of always
        // "now" — kept same-day by default so entries made today still sort
        // after anything else from today regardless of exact second.
        $occurredAt = ! empty($data['movement_date'])
            ? Carbon::parse($data['movement_date'])->setTimeFrom(now())
            : null;

        DB::transaction(function () use ($data, $branchId, $type, $occurredAt) {
            $item = Item::whereKey($data['item_id'])->lockForUpdate()->first();

            if (array_key_exists('alt_unit', $data)) {
                $item->update([
                    'alt_unit' => $data['alt_unit'],
                    'alt_unit_ratio' => $data['alt_unit'] ? $data['alt_unit_ratio'] : null,
                ]);
            }

            [$quantity, $unitCost] = $this->toBaseUnit($data, $item);

            $stock = ItemStock::query()->firstOrCreate(
                ['branch_id' => $branchId, 'item_id' => $data['item_id']],
                ['quantity' => 0]
            );

            if ($type === 'out') {
                abort_if($quantity > (float) $stock->quantity, 422, 'Cannot remove more than the current stock.');

                $stock->decrement('quantity', $quantity);

                $movement = StockMovement::create([
                    'branch_id' => $branchId,
                    'item_id' => $data['item_id'],
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $quantity,
                    'reason' => $data['reason'] ?? 'Stock removed',
                ]);

                if ($occurredAt) {
                    $movement->forceFill(['created_at' => $occurredAt, 'updated_at' => $occurredAt])->save();
                }

                $this->allocateFromBatches($branchId, (int) $data['item_id'], $quantity, $movement->id);

                return;
            }

            $stock->increment('quantity', $quantity);

            // Keep the item's cost basis current for profit calculations,
            // same as a purchase bill does.
            $item->update(['purchase_price' => $unitCost]);

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'item_id' => $data['item_id'],
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $quantity,
                'reason' => $data['reason'] ?? 'Stock added',
            ]);

            if ($occurredAt) {
                $movement->forceFill(['created_at' => $occurredAt, 'updated_at' => $occurredAt])->save();
            }

            StockBatch::create([
                'branch_id' => $branchId,
                'item_id' => $data['item_id'],
                'stock_movement_id' => $movement->id,
                'unit_cost' => $unitCost,
                'quantity_in' => $quantity,
                'quantity_remaining' => $quantity,
                'received_at' => $movement->created_at,
            ]);
        });

        $this->notifyBranch();

        $status = $type === 'out' ? 'Stock removed successfully.' : 'Stock added successfully.';

        // The embedded form on an item's own page wants "back()" (stay put,
        // show the updated ledger right there); the standalone Adjust Stock
        // page isn't somewhere to land back on, so it asks to go to the
        // ledger instead via this hidden field.
        if ($request->filled('redirect_to_index')) {
            return redirect()->route('stock.index')->with('status', $status);
        }

        return redirect()->back()->with('status', $status);
    }

    /**
     * Deplete this item's oldest batches first (FIFO) to cover a manual
     * stock removal, the same way a sale does — so cost-basis tracking
     * doesn't silently drift when stock leaves outside of an invoice.
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

    /**
     * Lets the quantity/price actually typed on the form be in the item's
     * alternate unit (e.g. 25 kg or 35 pcs) instead of always the base
     * stock unit (bag) — converted here using whichever ratio applies:
     * one just set on this same submission, or the item's existing one.
     * Everything downstream (ItemStock, StockMovement, StockBatch) keeps
     * storing base-unit quantity and base-unit cost exactly as before.
     *
     * @return array{0: float, 1: float} [base quantity, base unit cost]
     */
    private function toBaseUnit(array $data, Item $item): array
    {
        $ratio = $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null;

        $unitCost = (float) ($data['unit_cost'] ?? 0);

        if (($data['quantity_unit'] ?? 'base') !== 'alt' || ! $ratio) {
            return [(float) $data['quantity'], $unitCost];
        }

        return [
            round((float) $data['quantity'] / $ratio, 4),
            round($unitCost * $ratio, 2),
        ];
    }

    /**
     * Only manual entries (no reference_type) can be deleted here — a
     * movement created by an invoice or purchase must be undone by deleting
     * that document instead, so its own totals stay consistent.
     */
    public function destroy(StockMovement $movement): RedirectResponse
    {
        abort_unless($movement->branch_id === BranchContext::id(), 404);

        if ($movement->reference_type !== null) {
            return back()
                ->with('status', 'This stock entry is tied to an invoice or purchase. Delete that document instead.')
                ->with('status_type', 'danger');
        }

        if ($movement->type !== 'in') {
            return back()
                ->with('status', 'This stock entry cannot be deleted here.')
                ->with('status_type', 'danger');
        }

        $batch = $movement->batch;

        if ($batch && $batch->quantitySold() > 0) {
            return back()
                ->with('status', "Cannot delete: {$batch->quantitySold()} {$movement->item->unit} from this entry has already been sold. Remove those sales first.")
                ->with('status_type', 'danger');
        }

        DB::transaction(function () use ($movement, $batch) {
            ItemStock::query()
                ->where('branch_id', $movement->branch_id)
                ->where('item_id', $movement->item_id)
                ->decrement('quantity', $movement->quantity);

            $batch?->delete();
            $movement->delete();
        });

        $this->notifyBranch();

        return redirect()->route('stock.index')->with('status', 'Stock entry deleted.');
    }

    /**
     * A manual entry edited down or deleted can newly cross the low-stock
     * line just like a sale can, so the same re-sync/push runs here.
     */
    private function notifyBranch(): void
    {
        if ($branch = BranchContext::current()) {
            NotificationService::sync($branch);
        }
    }

    public function edit(StockMovement $movement): View
    {
        abort_unless($movement->branch_id === BranchContext::id(), 404);
        abort_unless($movement->reference_type === null && $movement->type === 'in', 404);

        $movement->load('item', 'batch');

        return view('stock.edit', ['movement' => $movement]);
    }

    /**
     * Only manual entries can be edited here, same as destroy() — and only
     * up to what's already been sold from the batch this entry created, so
     * a correction can't undercut stock that's genuinely gone out the door.
     */
    public function update(UpdateStockRequest $request, StockMovement $movement): RedirectResponse
    {
        abort_unless($movement->branch_id === BranchContext::id(), 404);

        if ($movement->reference_type !== null || $movement->type !== 'in') {
            return back()
                ->with('status', 'This stock entry cannot be edited here.')
                ->with('status_type', 'danger');
        }

        $data = $request->validated();
        $batch = $movement->batch;
        $sold = $batch ? $batch->quantitySold() : 0;

        if ($data['quantity'] < $sold) {
            return back()
                ->with('status', "Cannot reduce below {$sold} {$movement->item->unit}: that much from this entry has already been sold.")
                ->with('status_type', 'danger');
        }

        DB::transaction(function () use ($movement, $batch, $data) {
            if (array_key_exists('alt_unit', $data)) {
                Item::whereKey($movement->item_id)->update([
                    'alt_unit' => $data['alt_unit'],
                    'alt_unit_ratio' => $data['alt_unit'] ? $data['alt_unit_ratio'] : null,
                ]);
            }

            Item::whereKey($movement->item_id)->update(['purchase_price' => $data['unit_cost']]);

            $delta = $data['quantity'] - (float) $movement->quantity;

            ItemStock::query()
                ->where('branch_id', $movement->branch_id)
                ->where('item_id', $movement->item_id)
                ->increment('quantity', $delta);

            $batch?->update([
                'unit_cost' => $data['unit_cost'],
                'quantity_in' => $data['quantity'],
                'quantity_remaining' => (float) $batch->quantity_remaining + $delta,
            ]);

            $movement->update([
                'quantity' => $data['quantity'],
                'reason' => $data['reason'] ?? null,
            ]);
        });

        $this->notifyBranch();

        return redirect()->route('stock.index')->with('status', 'Stock entry updated.');
    }
}
