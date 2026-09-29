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
            ->with(['item', 'user'])
            ->latest()
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        $remainingByMovement = $this->remainingStockByMovement($branchId, $movements->pluck('item_id')->unique());

        if ($request->ajax()) {
            return view('stock.partials.table', ['movements' => $movements, 'remainingByMovement' => $remainingByMovement]);
        }

        $items = Item::query()->where('is_active', true)->orderBy('name')->get();

        return view('stock.index', ['movements' => $movements, 'items' => $items, 'remainingByMovement' => $remainingByMovement]);
    }

    /**
     * For each of the given items, walks every one of their movements in
     * this branch in chronological order and keeps a running total, so each
     * movement can show what stock was actually left right after it — not
     * just the movement's own quantity. Keyed by movement id so a paginated
     * page can look up just the rows it's showing.
     */
    private function remainingStockByMovement(int $branchId, $itemIds): array
    {
        if ($itemIds->isEmpty()) {
            return [];
        }

        $allMovements = StockMovement::query()
            ->where('branch_id', $branchId)
            ->whereIn('item_id', $itemIds)
            ->orderBy('item_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'item_id', 'type', 'quantity']);

        $running = [];
        $balances = [];

        foreach ($allMovements as $movement) {
            $running[$movement->item_id] = ($running[$movement->item_id] ?? 0)
                + ($movement->type === 'in' ? (float) $movement->quantity : -(float) $movement->quantity);

            $balances[$movement->id] = round($running[$movement->item_id], 2);
        }

        return $balances;
    }

    public function store(AddStockRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();

        DB::transaction(function () use ($data, $branchId) {
            if (array_key_exists('alt_unit', $data)) {
                Item::whereKey($data['item_id'])->update([
                    'alt_unit' => $data['alt_unit'],
                    'alt_unit_ratio' => $data['alt_unit'] ? $data['alt_unit_ratio'] : null,
                ]);
            }

            $stock = ItemStock::query()->firstOrCreate(
                ['branch_id' => $branchId, 'item_id' => $data['item_id']],
                ['quantity' => 0]
            );

            $stock->increment('quantity', $data['quantity']);

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'item_id' => $data['item_id'],
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $data['quantity'],
                'reason' => $data['reason'] ?? 'Stock added',
            ]);

            StockBatch::create([
                'branch_id' => $branchId,
                'item_id' => $data['item_id'],
                'stock_movement_id' => $movement->id,
                'unit_cost' => Item::whereKey($data['item_id'])->value('purchase_price'),
                'quantity_in' => $data['quantity'],
                'quantity_remaining' => $data['quantity'],
                'received_at' => $movement->created_at,
            ]);
        });

        return redirect()->route('stock.index')->with('status', 'Stock added successfully.');
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

        $movement->load('item');

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

            $delta = $data['quantity'] - (float) $movement->quantity;

            ItemStock::query()
                ->where('branch_id', $movement->branch_id)
                ->where('item_id', $movement->item_id)
                ->increment('quantity', $delta);

            $batch?->update([
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
