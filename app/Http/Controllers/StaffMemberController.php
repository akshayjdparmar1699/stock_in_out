<?php

namespace App\Http\Controllers;

use App\Support\Sql;

use App\Http\Requests\StoreStaffMemberRequest;
use App\Http\Requests\StoreStaffPaymentRequest;
use App\Http\Requests\UpdateStaffMemberRequest;
use App\Models\Branch;
use App\Models\Expense;
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
        $filter = $request->string('filter', 'active')->toString();

        $base = StaffMember::query()->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId));

        $stats = [
            'active_count' => (clone $base)->where('is_active', true)->count(),
            'inactive_count' => (clone $base)->where('is_active', false)->count(),
            'staff_count' => (clone $base)->where('is_active', true)->where('type', 'staff')->count(),
            'partner_count' => (clone $base)->where('is_active', true)->where('type', 'partner')->count(),
            'monthly_salary_total' => (clone $base)->where('is_active', true)->where('type', 'staff')->sum('monthly_salary'),
            'advance_total' => (float) Expense::query()
                ->where('branch_id', $branchId)
                ->where('category', 'upad')
                ->whereHas('staffMember', fn ($q) => $q->whereHas('branches', fn ($q2) => $q2->where('branches.id', $branchId)))
                ->sum('amount'),
        ];

        $staff = (clone $base)
            ->with('branches')
            ->when($filter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($filter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($filter === 'staff', fn ($q) => $q->where('type', 'staff'))
            ->when($filter === 'partner', fn ($q) => $q->where('type', 'partner'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where('name', Sql::caseInsensitiveLike(), $term);
            })
            ->orderByRaw("type = 'partner' desc")
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('staff.partials.table', ['staff' => $staff]);
        }

        $company = auth()->user()->company;
        $canAddStaffMember = $company->canAddStaffMember();
        $plan = $company->subscription?->plan;

        return view('staff.index', [
            'staff' => $staff,
            'filter' => $filter,
            'stats' => $stats,
            'canAddStaffMember' => $canAddStaffMember,
            'planLimitMessage' => $canAddStaffMember ? null : "Your {$plan?->name} plan allows up to {$plan?->staff_limit} staff and partners. Upgrade your plan to add more.",
        ]);
    }

    public function create(): View
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('staff.create', ['branches' => $branches]);
    }

    public function store(StoreStaffMemberRequest $request): RedirectResponse
    {
        $company = auth()->user()->company;

        if (! $company->canAddStaffMember()) {
            $plan = $company->subscription?->plan;

            return back()
                ->withInput()
                ->with('status', "Your {$plan?->name} plan allows up to {$plan?->staff_limit} staff and partners. Upgrade your plan to add more.")
                ->with('status_type', 'danger');
        }

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

    public function show(StaffMember $staffMember): View
    {
        abort_unless($this->belongsToCurrentCompany($staffMember), 404);

        $staffMember->load('branches');

        $month = now()->startOfMonth();
        $lastPayment = $staffMember->expenses()->latest('expense_date')->latest('id')->first();

        $monthOptions = collect(range(0, 5))->map(function ($i) use ($month, $staffMember) {
            $m = $month->copy()->subMonths($i);

            return [
                'key' => $m->format('Y-m-01'),
                'label' => $m->format('F Y'),
                'advance' => $staffMember->advanceForMonth($m),
                'suggested' => max(0, (float) $staffMember->monthly_salary - $staffMember->advanceForMonth($m)),
            ];
        })->values();

        $payments = $staffMember->expenses()
            ->whereIn('category', ['salary', 'upad'])
            ->with('user')
            ->latest('expense_date')
            ->latest('id')
            ->get();

        return view('staff.show', [
            'staffMember' => $staffMember,
            'month' => $month,
            'monthOptions' => $monthOptions,
            'payments' => $payments,
            'lastPayment' => $lastPayment,
        ]);
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

    /**
     * Paying salary or giving an advance/withdrawal IS recording an
     * Expense — there's no separate ledger table, so "also saved in
     * Expenses automatically" is just always true rather than a synced
     * side effect.
     */
    public function storePayment(StoreStaffPaymentRequest $request, StaffMember $staffMember): RedirectResponse
    {
        abort_unless($this->belongsToCurrentCompany($staffMember), 404);

        $data = $request->validated();

        Expense::create([
            'branch_id' => BranchContext::id(),
            'staff_member_id' => $staffMember->id,
            'user_id' => auth()->id(),
            'category' => $data['category'],
            'amount' => $data['amount'],
            'mode' => $data['mode'] ?? null,
            'for_month' => $data['category'] === 'salary' ? ($data['for_month'] ?? null) : null,
            'expense_date' => now()->toDateString(),
            'note' => $data['note'] ?? null,
        ]);

        $label = $data['category'] === 'salary' ? 'Salary' : ($staffMember->isPartner() ? 'Withdrawal' : 'Advance');

        return redirect()->route('staff.show', $staffMember)
            ->with('status', "{$label} of ₹".number_format($data['amount'], 2)." recorded for \"{$staffMember->name}\".");
    }
}
