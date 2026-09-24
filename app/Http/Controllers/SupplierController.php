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

    public function show(Supplier $supplier): View
    {
        $purchases = $supplier->purchases()->with('payments')->orderBy('created_at')->get();

        $entries = collect();

        foreach ($purchases as $purchase) {
            $entries->push([
                'date' => $purchase->created_at,
                'type' => 'billed',
                'label' => "Purchase {$purchase->purchase_number}",
                'amount' => (float) $purchase->total,
                'url' => route('purchases.show', $purchase),
            ]);

            foreach ($purchase->payments as $payment) {
                $entries->push([
                    'date' => $payment->created_at,
                    'type' => 'paid',
                    'label' => $payment->note ? "Payment ({$payment->note})" : "Payment for {$purchase->purchase_number}",
                    'amount' => (float) $payment->amount,
                    'url' => route('purchases.show', $purchase),
                ]);
            }
        }

        $balance = (float) $supplier->opening_balance;

        $entries = $entries->sortBy('date')->values()
            ->map(function (array $entry) use (&$balance) {
                $balance += $entry['type'] === 'billed' ? $entry['amount'] : -$entry['amount'];
                $entry['balance_after'] = round($balance, 2);

                return $entry;
            })
            ->reverse()
            ->values();

        return view('suppliers.show', [
            'supplier' => $supplier,
            'entries' => $entries,
            'due' => $supplier->dueAmount(),
        ]);
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
