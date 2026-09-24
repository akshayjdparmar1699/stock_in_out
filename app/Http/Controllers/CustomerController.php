<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Services\BranchContext;
use App\Services\PerPagePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = BranchContext::id();
        $status = $request->string('status', 'active')->toString();

        $customers = Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->with('branches')
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('customers.partials.table', ['customers' => $customers]);
        }

        return view('customers.index', ['customers' => $customers, 'status' => $status]);
    }

    public function create(): View
    {
        return view('customers.create', ['branches' => Branch::orderBy('name')->get()]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $customer = Customer::create($data);

        $customer->branches()->sync(
            auth()->user()->isAdmin() && $branchIds ? $branchIds : [BranchContext::id()]
        );

        if ($request->wantsJson()) {
            $customer->due = $customer->dueAmount();

            return response()->json($customer, 201);
        }

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" added.");
    }

    public function edit(Customer $customer): View
    {
        $customer->load('branches');

        return view('customers.edit', ['customer' => $customer, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $branchIds = $data['branch_ids'] ?? null;
        unset($data['branch_ids']);

        $customer->update($data);

        if (auth()->user()->isAdmin() && $branchIds) {
            $customer->branches()->sync($branchIds);
        }

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" updated.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->invoices()->exists()) {
            return back()
                ->with('status', "Cannot delete \"{$customer->name}\": they have billing history. Mark them inactive instead.")
                ->with('status_type', 'danger');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" deleted.");
    }

    public function toggleActive(Customer $customer): RedirectResponse
    {
        $customer->update(['is_active' => ! $customer->is_active]);

        $status = $customer->is_active ? 'active' : 'inactive';

        return redirect()->back()
            ->with('status', "Customer \"{$customer->name}\" marked {$status}.")
            ->with('status_type', $customer->is_active ? 'success' : 'danger');
    }

    public function search(Request $request): JsonResponse
    {
        $branchId = BranchContext::id();
        $term = '%'.$request->string('q').'%';

        $customers = Customer::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'address', 'opening_balance', 'is_active'])
            ->map(function (Customer $customer) {
                $customer->due = $customer->dueAmount();

                return $customer;
            });

        return response()->json($customers);
    }
}
