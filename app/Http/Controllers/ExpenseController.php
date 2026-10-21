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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->when($request->filled('q'), fn ($q) => $q->where('note', 'like', '%'.$request->string('q').'%'))
            ->when($from && $to, fn ($q) => $q->whereBetween('expense_date', [$from, $to]));

        $totalForFilter = (clone $query)->sum('amount');
        $daysWithExpenses = (clone $query)->distinct('expense_date')->count('expense_date');
        $averagePerDay = $daysWithExpenses > 0 ? $totalForFilter / $daysWithExpenses : 0;

        $byCategory = (clone $query)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'label' => Expense::categories()[$row->category] ?? 'Other',
                'total' => (float) $row->total,
                'percent' => $totalForFilter > 0 ? round($row->total / $totalForFilter * 100) : 0,
            ]);

        $biggestCost = $byCategory->first();

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
            'averagePerDay' => $averagePerDay,
            'daysWithExpenses' => $daysWithExpenses,
            'byCategory' => $byCategory,
            'biggestCost' => $biggestCost,
            'staffOptions' => $staffOptions,
            'categories' => Expense::categories(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $branchId = BranchContext::id();

        $query = Expense::query()
            ->where('branch_id', $branchId)
            ->with(['staffMember', 'user'])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('staff_member_id'), fn ($q) => $q->where('staff_member_id', $request->integer('staff_member_id')))
            ->latest('expense_date')->latest('id');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Category', 'Staff/Partner', 'Amount', 'Mode', 'Note', 'Recorded By']);

            $query->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $expense) {
                    fputcsv($out, [
                        $expense->expense_date->format('Y-m-d'),
                        $expense->categoryLabel(),
                        $expense->staffMember->name ?? '',
                        number_format($expense->amount, 2),
                        $expense->mode ?? '',
                        $expense->note,
                        $expense->user->name ?? '',
                    ]);
                }
            });

            fclose($out);
        }, 'expenses.csv');
    }

    public function create(): View
    {
        $branchId = BranchContext::id();

        $staffOptions = StaffMember::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $todaysExpenses = Expense::query()
            ->where('branch_id', $branchId)
            ->whereDate('expense_date', now()->toDateString())
            ->latest('id')
            ->get();

        $recentNotes = Expense::query()
            ->where('branch_id', $branchId)
            ->whereNotNull('note')
            ->where('note', '!=', '')
            ->latest('id')
            ->limit(30)
            ->pluck('note')
            ->unique()
            ->take(5)
            ->values();

        return view('expenses.create', [
            'staffOptions' => $staffOptions,
            'categories' => Expense::primaryCategories(),
            'todaysExpenses' => $todaysExpenses,
            'recentNotes' => $recentNotes,
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = BranchContext::id();
        $data['user_id'] = auth()->id();

        Expense::create($data);

        if ($request->boolean('add_another')) {
            return redirect()->route('expenses.create')->with('status', 'Expense recorded.');
        }

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

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_unless($expense->branch_id === BranchContext::id(), 404);

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense deleted.');
    }
}
