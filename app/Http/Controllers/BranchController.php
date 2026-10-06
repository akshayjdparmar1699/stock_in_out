<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
use App\Models\ItemStock;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::withCount('users')->latest()->get();

        return view('branches.index', ['branches' => $branches]);
    }

    public function create(): View
    {
        return view('branches.create', ['branches' => Branch::orderBy('name')->get()]);
    }

    /**
     * Off by default — a new branch starts with no items at all (the Items
     * page only shows items that have a stock row for the current branch),
     * and items only appear there once they're individually purchased or
     * manually stocked for it. Checking "copy items" requires picking which
     * existing branch's item set to mirror, since branches can now carry
     * different items from each other.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $copyFromBranchId = $request->boolean('copy_items') ? $data['copy_from_branch_id'] : null;
        unset($data['copy_items'], $data['copy_from_branch_id']);

        $branch = Branch::create($data);

        if ($copyFromBranchId) {
            $itemIds = ItemStock::query()->where('branch_id', $copyFromBranchId)->pluck('item_id');

            $stockRows = $itemIds->map(fn ($itemId) => [
                'branch_id' => $branch->id,
                'item_id' => $itemId,
                'quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($stockRows->isNotEmpty()) {
                ItemStock::insert($stockRows->all());
            }
        }

        return redirect()->route('branches.index')->with('status', "Branch \"{$branch->name}\" created.");
    }

    public function edit(Branch $branch): View
    {
        return view('branches.edit', ['branch' => $branch]);
    }

    public function update(StoreBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')->with('status', "Branch \"{$branch->name}\" updated.");
    }

    public function switch(Request $request): RedirectResponse
    {
        $request->validate(['branch_id' => ['required', 'exists:branches,id']]);

        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        BranchContext::set((int) $request->input('branch_id'));

        return redirect()->back()->with('status', 'Branch switched.');
    }
}
