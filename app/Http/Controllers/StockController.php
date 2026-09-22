<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddStockRequest;
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

        if ($request->ajax()) {
            return view('stock.partials.table', ['movements' => $movements]);
        }

        $items = Item::query()->where('is_active', true)->orderBy('name')->get();

        return view('stock.index', ['movements' => $movements, 'items' => $items]);
    }

    public function store(AddStockRequest $request): RedirectResponse
    {
        $branchId = BranchContext::id();
        $data = $request->validated();

        DB::transaction(function () use ($data, $branchId) {
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
}
