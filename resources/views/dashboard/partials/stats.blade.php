@php
    $periodLabels = ['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')];
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
        <div class="text-sm text-gray-500">{{ __('Sales') }} ({{ $periodLabels[$period] }})</div>
        <div class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($salesTotal, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $salesCount }} {{ __('invoices') }}</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
        <div class="text-sm text-gray-500">{{ __('Profit') }} ({{ $periodLabels[$period] }})</div>
        <div class="text-2xl font-semibold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">₹{{ number_format($profit, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ __('sales minus cost of goods') }}</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
        <div class="text-sm text-gray-500">{{ __('Purchases') }} ({{ $periodLabels[$period] }})</div>
        <div class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($purchasesTotal, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $purchasesCount }} {{ __('purchases') }}</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
        <div class="text-sm text-gray-500">{{ __('Stock Value') }}</div>
        <div class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($totalStockValue, 2) }}</div>
        <div class="text-xs {{ $lowStockCount > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $lowStockCount }} {{ __('items low on stock') }}</div>
    </div>
</div>
