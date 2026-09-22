<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();

        $items = Item::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $branchId)])
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('items.partials.table', ['items' => $items]);
        }

        return view('items.index', ['items' => $items, 'branchId' => $branchId]);
    }

    public function create(): View
    {
        return view('items.create');
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $openingQuantity = (float) ($data['opening_quantity'] ?? 0);
        unset($data['opening_quantity']);

        $item = DB::transaction(function () use ($data, $openingQuantity) {
            $item = Item::create($data);

            $branches = Branch::query()->pluck('id');

            foreach ($branches as $branchId) {
                $stock = ItemStock::create([
                    'branch_id' => $branchId,
                    'item_id' => $item->id,
                    'quantity' => $branchId === BranchContext::id() ? $openingQuantity : 0,
                ]);

                if ($openingQuantity > 0 && $branchId === BranchContext::id()) {
                    $movement = StockMovement::create([
                        'branch_id' => $branchId,
                        'item_id' => $item->id,
                        'user_id' => auth()->id(),
                        'type' => 'in',
                        'quantity' => $openingQuantity,
                        'reason' => 'Opening stock',
                    ]);

                    StockBatch::create([
                        'branch_id' => $branchId,
                        'item_id' => $item->id,
                        'stock_movement_id' => $movement->id,
                        'unit_cost' => $item->purchase_price,
                        'quantity_in' => $openingQuantity,
                        'quantity_remaining' => $openingQuantity,
                        'received_at' => $movement->created_at,
                    ]);
                }
            }

            return $item;
        });

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" added.");
    }

    public function show(Request $request, Item $item): View
    {
        $branchId = BranchContext::id();

        $currentStock = ItemStock::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $item->id)
            ->value('quantity') ?? 0;

        $movements = StockMovement::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $item->id)
            ->with('user')
            ->latest()
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('items.partials.history-table', ['movements' => $movements, 'item' => $item]);
        }

        $totals = StockMovement::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $item->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END), 0) as total_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END), 0) as total_out")
            ->first();

        return view('items.show', [
            'item' => $item,
            'currentStock' => $currentStock,
            'totalIn' => (float) $totals->total_in,
            'totalOut' => (float) $totals->total_out,
            'movements' => $movements,
        ]);
    }

    public function edit(Item $item): View
    {
        return view('items.edit', ['item' => $item]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $item->update(['is_active' => $request->boolean('is_active')] + $request->validated());

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" updated.");
    }

    public function search(Request $request): JsonResponse
    {
        $branchId = BranchContext::id();
        $term = '%'.$request->string('q').'%';

        $items = Item::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term))
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $branchId)])
            ->limit(10)
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'selling_price' => (float) $item->selling_price,
                'purchase_price' => (float) $item->purchase_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
            ]);

        return response()->json($items);
    }
}
