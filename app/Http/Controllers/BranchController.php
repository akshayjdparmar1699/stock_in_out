<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
use App\Models\Item;
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
        return view('branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = Branch::create($request->validated());

        $stockRows = Item::query()->pluck('id')->map(fn ($itemId) => [
            'branch_id' => $branch->id,
            'item_id' => $itemId,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($stockRows->isNotEmpty()) {
            ItemStock::insert($stockRows->all());
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
