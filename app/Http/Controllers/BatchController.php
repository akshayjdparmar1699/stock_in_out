<?php

namespace App\Http\Controllers;

use App\Support\Sql;

use App\Models\Purchase;
use App\Models\StockBatch;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();
        $filter = $request->string('filter', 'all')->toString();

        $stats = $this->statsFor($branchId);

        $batches = StockBatch::query()
            ->where('branch_id', $branchId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('item', fn ($iq) => $iq->where('name', Sql::caseInsensitiveLike(), $term)->orWhere('sku', Sql::caseInsensitiveLike(), $term))
                        ->orWhereHas('sourceMovement', function ($mq) use ($term) {
                            $mq->where('reference_type', Purchase::class)
                                ->whereHasMorph('reference', [Purchase::class], fn ($pq) => $pq->where('purchase_number', Sql::caseInsensitiveLike(), $term));
                        });
                });
            })
            ->when($filter === 'full', fn ($q) => $q->whereColumn('quantity_remaining', '=', 'quantity_in'))
            ->when($filter === 'selling', fn ($q) => $q->whereColumn('quantity_remaining', '<', 'quantity_in')->where('quantity_remaining', '>', 0))
            ->when($filter === 'sold_out', fn ($q) => $q->where('quantity_remaining', '<=', 0))
            ->with(['item', 'sourceMovement.reference'])
            ->latest('received_at')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('batches.partials.table', ['batches' => $batches, 'filter' => $filter, 'stats' => $stats]);
        }

        return view('batches.index', ['batches' => $batches, 'filter' => $filter, 'stats' => $stats]);
    }

    public function show(Request $request, StockBatch $batch): View
    {
        abort_unless($batch->branch_id === BranchContext::id(), 404);

        $batch->load(['item', 'sourceMovement.reference']);

        $allocations = $batch->allocations()
            ->with('movement.user', 'movement.reference')
            ->oldest()
            ->get();

        $soldSoFar = 0;
        $timeline = $allocations->map(function ($allocation) use ($batch, &$soldSoFar) {
            $soldSoFar += (float) $allocation->quantity;
            $remainingAfter = max(0, (float) $batch->quantity_in - $soldSoFar);

            return [
                'allocation' => $allocation,
                'remaining_after' => $remainingAfter,
                'lot_finished' => $remainingAfter <= 0,
            ];
        });

        $fullySoldAt = $batch->isDepleted() && $allocations->isNotEmpty()
            ? $allocations->last()->created_at
            : null;

        $fullySoldInDays = $fullySoldAt
            ? $batch->received_at->copy()->startOfDay()->diffInDays($fullySoldAt->copy()->startOfDay())
            : null;

        $oldestUnsoldLotOfItem = StockBatch::where('branch_id', $batch->branch_id)
            ->where('item_id', $batch->item_id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('received_at')
            ->first();

        $fifoTip = ($oldestUnsoldLotOfItem && $oldestUnsoldLotOfItem->id !== $batch->id) ? $oldestUnsoldLotOfItem : null;

        return view('batches.show', [
            'batch' => $batch,
            'timeline' => $timeline,
            'allocationCount' => $allocations->count(),
            'fullySoldInDays' => $fullySoldInDays,
            'fifoTip' => $fifoTip,
        ]);
    }

    /**
     * Branch-wide lot counts for the stat cards. "Stale" means a lot that
     * has had zero sales and was not received today — i.e. stock that has
     * sat completely untouched in the godown for at least a day, the
     * clearest "sell this one first" signal (distinct from the "Full"
     * filter pill, which also includes lots that just arrived today).
     */
    private function statsFor(int $branchId): array
    {
        $base = fn () => StockBatch::where('branch_id', $branchId);

        $totalCount = $base()->count();
        $fullCount = $base()->whereColumn('quantity_remaining', '=', 'quantity_in')->count();
        $soldOutCount = $base()->where('quantity_remaining', '<=', 0)->count();
        $sellingCount = max(0, $totalCount - $fullCount - $soldOutCount);

        $staleQuery = fn () => $base()
            ->whereColumn('quantity_remaining', '=', 'quantity_in')
            ->where('received_at', '<', now()->startOfDay());

        return [
            'total' => $totalCount,
            'full' => $fullCount,
            'selling' => $sellingCount,
            'sold_out' => $soldOutCount,
            'stale_count' => $staleQuery()->count(),
            'oldest_stale' => $staleQuery()->with('item')->orderBy('received_at')->first(),
        ];
    }
}
