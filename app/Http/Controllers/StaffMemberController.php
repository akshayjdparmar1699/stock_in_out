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
        return view('staff.create', ['branches' => Branch::orderBy('name')->get()]);
    }

    public function store(StoreStaffMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $staff = StaffMember::create($data);

        if (! $branchIds) {
            $branchIds = $data['type'] === 'partner'
                ? Branch::query()->pluck('id')->all()
                : [BranchContext::id()];
        }

        $staff->branches()->sync($branchIds);

        return redirect()->route('staff.index')->with('status', "\"{$staff->name}\" added.");
    }

    public function edit(StaffMember $staffMember): View
    {
        $staffMember->load('branches');

        return view('staff.edit', ['staffMember' => $staffMember, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(UpdateStaffMemberRequest $request, StaffMember $staffMember): RedirectResponse
    {
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
        $staffMember->update(['is_active' => ! $staffMember->is_active]);

        $status = $staffMember->is_active ? 'active' : 'inactive';

        return redirect()->back()
            ->with('status', "\"{$staffMember->name}\" marked {$status}.")
            ->with('status_type', $staffMember->is_active ? 'success' : 'danger');
    }
}
