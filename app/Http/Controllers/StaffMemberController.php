<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffMemberRequest;
use App\Http\Requests\UpdateStaffMemberRequest;
use App\Models\Branch;
use App\Models\StaffMember;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffMemberController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();
        $status = $request->string('status', 'active')->toString();

        $staff = StaffMember::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->with('branches')
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where('name', 'ilike', $term);
            })
            ->orderByRaw("type = 'partner' desc")
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('staff.partials.table', ['staff' => $staff]);
        }

        return view('staff.index', ['staff' => $staff, 'status' => $status]);
    }

    public function create(): View
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('staff.create', ['branches' => $branches]);
    }

    public function store(StoreStaffMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $staff = StaffMember::create($data);

        if (! $branchIds) {
            $branchIds = $data['type'] === 'partner'
                ? Branch::query()->where('company_id', auth()->user()->company_id)->pluck('id')->all()
                : [BranchContext::id()];
        }

        $staff->branches()->sync($branchIds);

        return redirect()->route('staff.index')->with('status', "\"{$staff->name}\" added.");
    }

    /**
     * A staff member has no company_id of its own — it's only reachable
     * through the branches it's linked to — so ownership is checked that
     * way instead, same as a StaffMember with no branches at all (a state
     * that shouldn't normally happen) being treated as not this company's.
     */
    private function belongsToCurrentCompany(StaffMember $staffMember): bool
    {
        return $staffMember->branches()->where('branches.company_id', auth()->user()->company_id)->exists();
    }

    public function edit(StaffMember $staffMember): View
    {
        abort_unless($this->belongsToCurrentCompany($staffMember), 404);

        $staffMember->load('branches');

        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('staff.edit', ['staffMember' => $staffMember, 'branches' => $branches]);
    }

    public function update(UpdateStaffMemberRequest $request, StaffMember $staffMember): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($staffMember), 404);

        $data = $request->validated();
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $staffMember->update($data);

        if ($branchIds) {
            $staffMember->branches()->sync($branchIds);
        }

        return redirect()->route('staff.index')->with('status', "\"{$staffMember->name}\" updated.");
    }

    public function toggleActive(StaffMember $staffMember): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($staffMember), 404);

        $staffMember->update(['is_active' => ! $staffMember->is_active]);

        $status = $staffMember->is_active ? 'active' : 'inactive';

        return redirect()->back()
            ->with('status', "\"{$staffMember->name}\" marked {$status}.")
            ->with('status_type', $staffMember->is_active ? 'success' : 'danger');
    }
}
