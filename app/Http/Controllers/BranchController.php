<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
use App\Models\ItemStock;
use App\Models\User;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->withCount('users')->latest()->get();

        return view('branches.index', ['branches' => $branches]);
    }

    public function create(): View
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('branches.create', ['branches' => $branches]);
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
        $companyId = auth()->user()->company_id;
        $copyFromBranchId = $request->boolean('copy_items') ? $data['copy_from_branch_id'] : null;
        unset($data['copy_items'], $data['copy_from_branch_id']);

        $branch = Branch::create([...$data, 'company_id' => $companyId]);

        // Re-checked here (not just in validation) so a tampered request
        // can't copy another company's items in via a branch id that
        // doesn't actually belong to this admin's own company.
        if ($copyFromBranchId && Branch::whereKey($copyFromBranchId)->where('company_id', $companyId)->exists()) {
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
        abort_unless($branch->company_id === auth()->user()->company_id, 404);

        return view('branches.edit', ['branch' => $branch]);
    }

    public function update(StoreBranchRequest $request, Branch $branch): RedirectResponse
    {
        abort_unless($branch->company_id === auth()->user()->company_id, 404);

        $data = $request->validated();
        unset($data['copy_items'], $data['copy_from_branch_id']);

        $branch->update($data);

        return redirect()->route('branches.index')->with('status', "Branch \"{$branch->name}\" updated.");
    }

    /**
     * Invoices/purchases already can't be deleted out from under a branch
     * at the database level (restrictOnDelete), but item stock, stock
     * movements, and expenses would otherwise cascade-delete silently —
     * so every kind of real activity is checked up front and reported
     * together, rather than letting a raw constraint error through for
     * the first one or quietly wiping out the rest.
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        abort_unless($branch->company_id === auth()->user()->company_id, 404);

        if (Branch::where('company_id', $branch->company_id)->count() <= 1) {
            return back()
                ->with('status', 'Cannot delete the only branch — the company needs at least one.')
                ->with('status_type', 'danger');
        }

        if (User::where('branch_id', $branch->id)->exists()) {
            return back()
                ->with('status', "Cannot delete \"{$branch->name}\": it still has staff users assigned to it. Reassign or remove them first.")
                ->with('status_type', 'danger');
        }

        $blockers = [
            'invoices' => $branch->invoices()->exists(),
            'purchases' => $branch->purchases()->exists(),
            'expenses' => $branch->expenses()->exists(),
            'stock movements' => $branch->stockMovements()->exists(),
        ];

        $reasons = array_keys(array_filter($blockers));

        if (! empty($reasons)) {
            return back()
                ->with('status', "Cannot delete \"{$branch->name}\": it already has ".implode(', ', $reasons).'.')
                ->with('status_type', 'danger');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('status', "Branch \"{$branch->name}\" deleted.");
    }

    public function switch(Request $request): RedirectResponse
    {
        $request->validate(['branch_id' => ['required', 'exists:branches,id']]);

        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $branchId = (int) $request->input('branch_id');

        abort_unless(
            Branch::whereKey($branchId)->where('company_id', $request->user()->company_id)->exists(),
            403
        );

        BranchContext::set($branchId);

        return redirect()->back()->with('status', 'Branch switched.');
    }
}
