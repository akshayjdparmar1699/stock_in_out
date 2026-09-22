<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\StaffMember;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();

        $period = $request->string('period')->toString();
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;

        if ($period && ! $from && ! $to) {
            [$from, $to] = match ($period) {
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'week' => [now()->startOfWeek(), now()->endOfWeek()],
                'month' => [now()->startOfMonth(), now()->endOfMonth()],
                default => [null, null],
            };
        }

        $activeQuickFilter = match (true) {
            $request->filled('from') || $request->filled('to') => null,
            $period !== '' => $period,
            default => 'all',
        };

        $query = Expense::query()
            ->where('branch_id', $branchId)
            ->with(['staffMember', 'user'])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('staff_member_id'), fn ($q) => $q->where('staff_member_id', $request->integer('staff_member_id')))
            ->when($from && $to, fn ($q) => $q->whereBetween('expense_date', [$from, $to]));

        $totalForFilter = (clone $query)->sum('amount');

        $expenses = $query->latest('expense_date')->latest('id')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('expenses.partials.table', ['expenses' => $expenses]);
        }

        $staffOptions = StaffMember::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->orderBy('name')
            ->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'activeQuickFilter' => $activeQuickFilter,
            'totalForFilter' => $totalForFilter,
            'staffOptions' => $staffOptions,
            'categories' => Expense::categories(),
        ]);
    }

    public function create(): View
    {
        $branchId = BranchContext::id();

        $staffOptions = StaffMember::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expenses.create', ['staffOptions' => $staffOptions, 'categories' => Expense::categories()]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = BranchContext::id();
        $data['user_id'] = auth()->id();

        Expense::create($data);

        return redirect()->route('expenses.index')->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense): View
    {
        $branchId = BranchContext::id();
        abort_unless($expense->branch_id === $branchId, 404);

        $staffOptions = StaffMember::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->orderBy('name')
            ->get();

        return view('expenses.edit', ['expense' => $expense, 'staffOptions' => $staffOptions, 'categories' => Expense::categories()]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        abort_unless($expense->branch_id === BranchContext::id(), 404);

        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('status', 'Expense updated.');
    }
}
