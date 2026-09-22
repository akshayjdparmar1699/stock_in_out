<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $item->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('items.edit', $item) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Edit Item') }}
                </a>
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    {{ __('+ New Purchase') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <div class="text-sm text-gray-500">{{ __('SKU') }}: {{ $item->sku }}</div>
                        @unless ($item->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('Inactive') }}</span>
                        @endunless
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Current Stock') }}</div>
                        <div class="text-xl font-semibold {{ $currentStock <= $item->low_stock_threshold ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $currentStock }} {{ $item->unit }}
                        </div>
                        @if ($currentStock <= $item->low_stock_threshold)
                            <div class="text-xs text-red-500">{{ __('Low stock') }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Purchase Price') }}</div>
                        <div class="text-xl font-semibold text-gray-900">₹{{ number_format($item->purchase_price, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Selling Price') }}</div>
                        <div class="text-xl font-semibold text-gray-900">₹{{ number_format($item->selling_price, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Stock Value') }}</div>
                        <div class="text-xl font-semibold text-gray-900">₹{{ number_format($currentStock * $item->selling_price, 2) }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t">
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Total Added (all time)') }}</div>
                        <div class="text-lg font-medium text-green-600">+{{ $totalIn }} {{ $item->unit }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Total Sold/Removed (all time)') }}</div>
                        <div class="text-lg font-medium text-red-600">-{{ $totalOut }} {{ $item->unit }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Low Stock Alert At') }}</div>
                        <div class="text-lg font-medium text-gray-700">{{ $item->low_stock_threshold }} {{ $item->unit }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Unit') }}</div>
                        <div class="text-lg font-medium text-gray-700">{{ $item->unit }}</div>
                    </div>
                </div>

                @if ($item->description)
                    <div class="mt-6 pt-6 border-t text-sm text-gray-500">
                        {{ $item->description }}
                    </div>
                @endif
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('items.partials.history-table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
