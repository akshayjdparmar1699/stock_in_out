<?php

namespace App\Http\Controllers;

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

        $batches = StockBatch::query()
            ->where('branch_id', $branchId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->whereHas('item', fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->with(['item', 'sourceMovement'])
            ->latest('received_at')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('batches.partials.table', ['batches' => $batches]);
        }

        return view('batches.index', ['batches' => $batches]);
    }

    public function show(Request $request, StockBatch $batch): View
    {
        abort_unless($batch->branch_id === BranchContext::id(), 404);

        $batch->load(['item', 'sourceMovement']);

        $allocations = $batch->allocations()
            ->with('movement.user')
            ->latest()
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('batches.partials.allocations-table', [
                'allocations' => $allocations,
                'batch' => $batch,
            ]);
        }

        return view('batches.show', [
            'batch' => $batch,
            'allocations' => $allocations,
        ]);
    }
}
