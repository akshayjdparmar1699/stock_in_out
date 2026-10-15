@php
    $periodWords = ['today' => __('today'), 'week' => __('this week'), 'month' => __('this month'), 'all' => __('all time')];
    $periodLabels = ['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')];
    $margin = $salesTotal > 0 ? round(($profit / $salesTotal) * 100) : null;
@endphp
<div class="lg:hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-center justify-between gap-3">
        <span class="text-sm text-gray-500">{{ __('Sales') }} {{ $periodWords[$period] }}</span>
        <span class="shrink-0 text-xs font-medium text-green-700 bg-green-50 rounded-full px-2.5 py-1">{{ $salesCount }} {{ __('bills') }}</span>
    </div>
    <div class="text-2xl font-bold text-gray-900 mt-1.5">&#8377;{{ number_format($salesTotal, 2) }}</div>

    <div class="grid grid-cols-2 gap-3 mt-4">
        <div class="bg-green-50 rounded-xl p-3">
            <div class="text-xs text-gray-500">{{ __('Profit') }}</div>
            <div class="text-base font-bold {{ $profit >= 0 ? 'text-green-700' : 'text-red-600' }} mt-0.5">&#8377;{{ number_format($profit, 2) }}</div>
        </div>
        <div class="bg-amber-50 rounded-xl p-3">
            <div class="text-xs text-gray-500">{{ __('Purchases') }}</div>
            <div class="text-base font-bold text-amber-700 mt-0.5">&#8377;{{ number_format($purchasesTotal, 2) }}</div>
        </div>
    </div>
</div>

<div class="hidden lg:grid lg:grid-cols-4 gap-4">
    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5 border border-gray-100">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ __('Sales') }}</span>
            <span class="shrink-0 w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            </span>
        </div>
        <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($salesTotal, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $salesCount }} {{ __('invoices') }} {{ $periodWords[$period] }}</div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5 border border-gray-100">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ __('Profit') }}</span>
            <span class="shrink-0 w-9 h-9 rounded-lg {{ $profit >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941" /></svg>
            </span>
        </div>
        <div class="text-2xl font-bold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-3">&#8377;{{ number_format($profit, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">
            @if ($margin !== null)
                {{ $margin }}% {{ __('margin, sales minus cost of goods') }}
            @else
                {{ __('Sales minus cost of goods') }}
            @endif
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5 border border-gray-100">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ __('Purchases') }}</span>
            <span class="shrink-0 w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 0h-12" /></svg>
            </span>
        </div>
        <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($purchasesTotal, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $purchasesCount }} {{ __('purchases') }} {{ $periodWords[$period] }}</div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5 border border-gray-100">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ __('Stock Value') }}</span>
            <span class="shrink-0 w-9 h-9 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </span>
        </div>
        <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($totalStockValue, 2) }}</div>
        <div class="text-xs mt-1">
            @if ($lowStockCount > 0)
                <a href="{{ route('stock.index') }}" class="text-red-600 font-medium hover:underline">{{ $lowStockCount }} {{ __('items low on stock') }} &rarr;</a>
            @else
                <span class="text-gray-400">{{ __('All items well stocked') }}</span>
            @endif
        </div>
    </div>
</div>
