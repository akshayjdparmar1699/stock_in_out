<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('customers.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-5 hover:shadow-md transition-shadow">
        <div class="text-sm text-gray-500">{{ __('Customers Owe You') }}</div>
        <div class="text-2xl font-semibold {{ $customerDueTotal > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">₹{{ number_format(max($customerDueTotal, 0), 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ __('total outstanding, this branch') }}</div>
    </a>
    <a href="{{ route('purchases.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-5 hover:shadow-md transition-shadow">
        <div class="text-sm text-gray-500">{{ __('You Owe Suppliers') }}</div>
        <div class="text-2xl font-semibold {{ $supplierDueTotal > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">₹{{ number_format(max($supplierDueTotal, 0), 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ __('unpaid on purchases, this branch') }}</div>
    </a>
    <a href="{{ route('expenses.index', ['category' => 'upad']) }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-5 hover:shadow-md transition-shadow">
        <div class="text-sm text-gray-500">{{ __('Total Advance (Staff + Partners)') }}</div>
        <div class="text-2xl font-semibold {{ $totalUpad > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">₹{{ number_format($totalUpad, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ __('all time, this branch') }}</div>
    </a>
</div>
