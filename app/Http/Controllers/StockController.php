<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();

        $movements = StockMovement::query()
            ->where('branch_id', $branchId)
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))
            ->with(['item', 'user'])
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

        return view('stock.index', [
            'movements' => $movements,
            'items' => $items,
            'remainingByMovement' => $remainingByMovement,
            'filterItemId' => $request->integer('item_id') ?: null,
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

        DB::transaction(function () use ($data, $branchId) {
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

        return redirect()->route('stock.index')->with('status', 'Stock added successfully.');
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

        if (($data['quantity_unit'] ?? 'base') !== 'alt' || ! $ratio) {
            return [(float) $data['quantity'], (float) $data['unit_cost']];
        }

        return [
            round((float) $data['quantity'] / $ratio, 4),
            round((float) $data['unit_cost'] * $ratio, 2),
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

        return redirect()->route('stock.index')->with('status', 'Stock entry deleted.');
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

        return redirect()->route('stock.index')->with('status', 'Stock entry updated.');
    }
}
