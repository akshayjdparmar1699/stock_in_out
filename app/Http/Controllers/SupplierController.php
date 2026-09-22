<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\PerPagePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('suppliers.partials.table', ['suppliers' => $suppliers]);
        }

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier = Supplier::create($data);

        if ($request->wantsJson()) {
            $supplier->due = $supplier->dueAmount();

            return response()->json($supplier, 201);
        }

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" added.");
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" updated.");
    }

    public function search(Request $request): JsonResponse
    {
        $term = '%'.$request->string('q').'%';

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'address'])
            ->map(function (Supplier $supplier) {
                $supplier->due = $supplier->dueAmount();

                return $supplier;
            });

        return response()->json($suppliers);
    }
}
