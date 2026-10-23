<?php

namespace App\Http\Controllers;

use App\Support\Sql;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\InvoiceItem;
use App\Models\PurchaseItem;
use App\Models\StockBatch;
use App\Models\StockBatchAllocation;
use App\Models\StockMovement;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        ['all' => $all, 'filter' => $filter] = $this->filteredItems($request);

        $stats = $this->statsFor($all);

        $filtered = $all->filter(fn (Item $item) => $this->matchesFilter($item, $filter))->values();

        $perPage = PerPagePreference::get();
        $page = $request->integer('page', 1);

        $items = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return view('items.partials.table', ['items' => $items]);
        }

        return view('items.index', [
            'items' => $items,
            'filter' => $filter,
            'stats' => $stats,
            'totalCount' => $all->count(),
            'branch' => BranchContext::current(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        ['all' => $all, 'filter' => $filter] = $this->filteredItems($request);

        $filtered = $all->filter(fn (Item $item) => $this->matchesFilter($item, $filter))->values();

        return response()->streamDownload(function () use ($filtered) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'SKU', 'Unit', 'Selling Price', 'Purchase Price', 'Stock', 'Loose Stock', 'Status']);

            foreach ($filtered as $item) {
                $qty = $item->stocks->first()->quantity ?? 0;

                fputcsv($out, [
                    $item->name,
                    $item->sku,
                    $item->unit,
                    number_format($item->selling_price, 2),
                    number_format($item->purchase_price, 2),
                    $qty,
                    $item->hasAltUnit() ? number_format($item->altUnitQuantity($qty), 2).' '.$item->alt_unit : '',
                    $item->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($out);
        }, 'items.csv');
    }

    /**
     * Shared branch+search query behind both index() and export() — loads
     * every matching item unpaginated (shop item lists are small enough
     * that this is cheap) so the filter tabs/stat counts and CSV export
     * can be computed from the exact same stock snapshot.
     *
     * @return array{all: Collection<int, Item>, filter: string}
     */
    private function filteredItems(Request $request): array
    {
        $branchId = BranchContext::id();
        $search = $request->string('q')->toString();

        $all = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->whereHas('stocks', fn ($q) => $q->where('branch_id', $branchId))
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(fn ($q) => $q->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('sku', Sql::caseInsensitiveLike(), $term));
            })
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $branchId)])
            ->orderBy('name')
            ->get();

        return [
            'all' => $all,
            'filter' => $request->string('filter', 'all')->toString(),
        ];
    }

    private function matchesFilter(Item $item, string $filter): bool
    {
        $qty = (float) ($item->stocks->first()->quantity ?? 0);

        return match ($filter) {
            'low_stock' => $qty <= (float) $item->low_stock_threshold,
            'in_stock' => $qty > (float) $item->low_stock_threshold,
            'out_of_stock' => $qty <= 0,
            default => true,
        };
    }

    /**
     * @param  Collection<int, Item>  $all
     * @return array{total_items: int, stock_value: float, low_stock_count: int, in_stock_count: int, out_of_stock_count: int}
     */
    private function statsFor(Collection $all): array
    {
        $stats = ['total_items' => $all->count(), 'stock_value' => 0.0, 'low_stock_count' => 0, 'in_stock_count' => 0, 'out_of_stock_count' => 0];

        foreach ($all as $item) {
            $qty = (float) ($item->stocks->first()->quantity ?? 0);
            $stats['stock_value'] += $qty * (float) $item->selling_price;

            if ($qty <= (float) $item->low_stock_threshold) {
                $stats['low_stock_count']++;
            } else {
                $stats['in_stock_count']++;
            }

            if ($qty <= 0) {
                $stats['out_of_stock_count']++;
            }
        }

        return $stats;
    }

    public function create(): View
    {
        return view('items.create', [
            'branch' => BranchContext::current(),
            'gstEnabled' => (bool) auth()->user()->company?->gst_enabled,
        ]);
    }

    /**
     * A new item only belongs to the branch it's created in — it used to
     * get an (empty) stock row on every branch regardless, which made it
     * silently show up everywhere once branches could carry different
     * items from each other. It'll show up for another branch the same
     * way any item does: once that branch purchases or manually stocks it.
     */
    public function store(StoreItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sku'] = $data['sku'] ?: null;
        $openingQuantity = (float) ($data['opening_quantity'] ?? 0);
        unset($data['opening_quantity']);
        $branchId = BranchContext::id();

        $companyId = auth()->user()->company_id;

        $item = DB::transaction(function () use ($data, $openingQuantity, $branchId, $companyId) {
            $item = Item::create([...$data, 'company_id' => $companyId]);

            ItemStock::create([
                'branch_id' => $branchId,
                'item_id' => $item->id,
                'quantity' => $openingQuantity,
            ]);

            if ($openingQuantity > 0) {
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

            return $item;
        });

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" added.");
    }

    public function show(Request $request, Item $item): View
    {
        abort_unless($item->company_id === auth()->user()->company_id, 404);

        $branchId = BranchContext::id();

        $currentStock = ItemStock::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $item->id)
            ->value('quantity') ?? 0;

        $movementType = $request->string('type', 'all')->toString();

        $movements = StockMovement::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $item->id)
            ->when(in_array($movementType, ['in', 'out'], true), fn ($q) => $q->where('type', $movementType))
            ->with('user')
            ->latest()
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        // Walks every movement for this item newest-first, starting from the
        // item's actual current stock and undoing each movement in turn —
        // rather than summing forward from zero. Editing an invoice or
        // purchase deletes its old stock entries without a trace (see
        // reverseStockEffects()), so a forward sum can drift below the real
        // total once an item's had any edited document; anchoring on the
        // current quantity keeps the newest row in agreement with it.
        $running = (float) $currentStock;
        $remainingByMovement = StockMovement::query()
            ->where('branch_id', $branchId)
            ->where('item_id', $item->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'type', 'quantity'])
            ->mapWithKeys(function (StockMovement $movement) use (&$running) {
                $balance = round($running, 2);
                $running -= $movement->type === 'in' ? (float) $movement->quantity : -(float) $movement->quantity;

                return [$movement->id => $balance];
            })
            ->all();

        if ($request->ajax()) {
            return view('items.partials.history-table', ['movements' => $movements, 'item' => $item, 'remainingByMovement' => $remainingByMovement, 'movementType' => $movementType]);
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
            'remainingByMovement' => $remainingByMovement,
            'movementType' => $movementType,
            'profitPerUnit' => (float) $item->selling_price - (float) $item->purchase_price,
        ]);
    }

    public function edit(Item $item): View
    {
        abort_unless($item->company_id === auth()->user()->company_id, 404);

        return view('items.edit', [
            'item' => $item,
            'gstEnabled' => (bool) auth()->user()->company?->gst_enabled,
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        abort_unless($item->company_id === auth()->user()->company_id, 404);

        $data = $request->validated();
        $data['sku'] = $data['sku'] ?: null;

        $item->update(['is_active' => $request->boolean('is_active')] + $data);

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" updated.");
    }

    public function destroy(Item $item): RedirectResponse
    {
        abort_unless($item->company_id === auth()->user()->company_id, 404);

        $hasBeenTraded = InvoiceItem::where('item_id', $item->id)->exists()
            || PurchaseItem::where('item_id', $item->id)->exists();

        if ($hasBeenTraded) {
            return back()
                ->with('status', "Cannot delete \"{$item->name}\": it has billing or purchase history. Mark it inactive instead.")
                ->with('status_type', 'danger');
        }

        DB::transaction(function () use ($item) {
            $movementIds = StockMovement::where('item_id', $item->id)->pluck('id');
            StockBatchAllocation::whereIn('stock_movement_id', $movementIds)->delete();
            StockBatch::where('item_id', $item->id)->delete();
            StockMovement::where('item_id', $item->id)->delete();
            ItemStock::where('item_id', $item->id)->delete();
            $item->delete();
        });

        return redirect()->route('items.index')->with('status', "Item \"{$item->name}\" deleted.");
    }

    /**
     * Shared between the Invoice and Purchase item-search boxes. A
     * purchase has to be able to find any item, including one this branch
     * doesn't stock yet — that's exactly how it ends up stocking it — so
     * only the invoice side passes in_stock_only to limit results to what
     * this branch can actually sell.
     */
    public function search(Request $request): JsonResponse
    {
        $branchId = BranchContext::id();
        $term = '%'.$request->string('q').'%';

        $items = Item::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('sku', Sql::caseInsensitiveLike(), $term))
            ->when($request->boolean('in_stock_only'), fn ($q) => $q->whereHas('stocks', fn ($q2) => $q2->where('branch_id', $branchId)))
            ->with(['stocks' => fn ($q) => $q->where('branch_id', $branchId)])
            ->limit(10)
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'alt_unit' => $item->alt_unit,
                'alt_unit_ratio' => $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null,
                'selling_price' => (float) $item->selling_price,
                'purchase_price' => (float) $item->purchase_price,
                'stock' => (float) ($item->stocks->first()->quantity ?? 0),
                'hsn_code' => $item->hsn_code,
                'gst_rate' => $item->gst_rate !== null ? (float) $item->gst_rate : null,
            ]);

        return response()->json($items);
    }
}
