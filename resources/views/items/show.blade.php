@php
    $isOut = $currentStock <= 0;
    $isLow = ! $isOut && $currentStock <= $item->low_stock_threshold;
    $stockLabel = $isOut ? __('Out of stock') : ($isLow ? __('Low stock') : __('In stock'));
    $stockBadgeClass = $isOut ? 'bg-red-50 text-red-600' : ($isLow ? 'bg-amber-50 text-amber-600' : 'bg-green-50 text-green-700');
    $referenceMax = max((float) $currentStock, (float) $item->low_stock_threshold * 2, 1);
    $stockPct = min(round(((float) $currentStock / $referenceMax) * 100), 100);
    $thresholdPct = min(round(((float) $item->low_stock_threshold / $referenceMax) * 100), 100);
    $stockValue = (float) $currentStock * (float) $item->selling_price;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('items.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('items.index') }}" class="hover:text-gray-600">{{ __('Items') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium truncate">{{ $item->name }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Item') }}</h2>
            </div>
            <a href="{{ route('items.edit', $item) }}" class="lg:hidden shrink-0 text-gray-500 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            </a>
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('items.edit', $item) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    {{ __('Edit Item') }}
                </a>
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700">
                    {{ __('+ New Purchase') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="{ activeTab: 'all', adjustOpen: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 lg:space-y-6">

            {{-- Desktop identity card --}}
            <div class="hidden lg:block bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="font-semibold text-xl text-gray-900">{{ $item->name }}</h1>
                            @unless ($item->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">{{ __('Inactive') }}</span>
                            @endunless
                        </div>
                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ __('SKU') }} {{ $item->sku ?: '—' }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ __('Unit') }} {{ $item->unit }}</span>
                            @if ($item->hasAltUnit())
                                <span class="px-2 py-0.5 rounded-full text-xs bg-purple-50 text-purple-700">1 {{ $item->unit }} = {{ rtrim(rtrim(number_format($item->alt_unit_ratio, 2), '0'), '.') }} {{ $item->alt_unit }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $stockBadgeClass }}">{{ strtoupper($stockLabel) }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">{{ __('Current stock') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-0.5">
                            {{ rtrim(rtrim(number_format($currentStock, 2), '0'), '.') }} {{ $item->unit }}
                            @if ($item->hasAltUnit())
                                <span class="text-sm font-normal text-gray-400">= {{ number_format($item->altUnitQuantity($currentStock), 2) }} {{ $item->alt_unit }}</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Alert at') }} {{ $item->low_stock_threshold }} {{ $item->unit }}</p>
                    </div>
                </div>
                <div class="relative h-2 rounded-full bg-gray-100 overflow-hidden mt-4">
                    <div class="absolute inset-y-0 left-0 {{ $isOut ? 'bg-red-500' : ($isLow ? 'bg-amber-400' : 'bg-green-500') }}" style="width: {{ $stockPct }}%"></div>
                    <div class="absolute inset-y-0 w-0.5 bg-orange-500" style="left: {{ $thresholdPct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Orange line is the low stock alert level') }}</p>
            </div>

            {{-- Mobile identity + stock card --}}
            <div class="lg:hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="font-semibold text-gray-900">{{ $item->name }}</h1>
                    @unless ($item->is_active)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">{{ __('Inactive') }}</span>
                    @endunless
                </div>
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ __('SKU') }} {{ $item->sku ?: '—' }}</span>
                    @if ($item->hasAltUnit())
                        <span class="px-2 py-0.5 rounded-full text-xs bg-purple-50 text-purple-700">1 {{ $item->unit }} = {{ rtrim(rtrim(number_format($item->alt_unit_ratio, 2), '0'), '.') }} {{ $item->alt_unit }}</span>
                    @endif
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $stockBadgeClass }}">{{ strtoupper($stockLabel) }}</span>
                </div>
            </div>

            <div class="lg:hidden {{ $isOut ? 'bg-red-50 border-red-100' : ($isLow ? 'bg-amber-50 border-amber-100' : 'bg-green-50 border-green-100') }} border rounded-2xl p-4">
                <p class="text-sm text-gray-500">{{ __('Current stock') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">
                    {{ rtrim(rtrim(number_format($currentStock, 2), '0'), '.') }} {{ $item->unit }}
                    @if ($item->hasAltUnit())
                        <span class="text-sm font-normal text-gray-500">= {{ number_format($item->altUnitQuantity($currentStock), 2) }} {{ $item->alt_unit }}</span>
                    @endif
                </p>
                <div class="relative h-2 rounded-full bg-white/70 overflow-hidden mt-3">
                    <div class="absolute inset-y-0 left-0 {{ $isOut ? 'bg-red-500' : ($isLow ? 'bg-amber-400' : 'bg-green-500') }}" style="width: {{ $stockPct }}%"></div>
                    <div class="absolute inset-y-0 w-0.5 bg-orange-500" style="left: {{ $thresholdPct }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1.5">{{ __('Low stock alert at') }} {{ $item->low_stock_threshold }} {{ $item->unit }} ({{ __('orange line') }})</p>
            </div>

            {{-- Stat cards --}}
            <div class="hidden lg:grid lg:grid-cols-5 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Purchase price') }}</p>
                    <p class="text-xl font-bold text-gray-900 mt-1">&#8377;{{ number_format($item->purchase_price, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('per') }} {{ $item->unit }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Selling price') }}</p>
                    <p class="text-xl font-bold text-gray-900 mt-1">&#8377;{{ number_format($item->selling_price, 2) }}</p>
                    @if ($item->hasAltUnit())
                        <p class="text-xs text-gray-400 mt-1">&#8377;{{ number_format($item->selling_price / $item->alt_unit_ratio, 2) }} {{ __('per') }} {{ $item->alt_unit }} {{ __('loose') }}</p>
                    @endif
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Profit per') }} {{ $item->unit }}</p>
                    <p class="text-xl font-bold {{ $profitPerUnit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">&#8377;{{ number_format($profitPerUnit, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $item->selling_price > 0 ? round(($profitPerUnit / $item->selling_price) * 100) : 0 }}% {{ __('margin') }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Stock value') }}</p>
                    <p class="text-xl font-bold text-gray-900 mt-1">&#8377;{{ number_format($stockValue, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('at selling price') }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Added / Sold') }}</p>
                    <p class="text-lg font-bold mt-1"><span class="text-green-600">+{{ rtrim(rtrim(number_format($totalIn, 2), '0'), '.') }}</span> <span class="text-red-600">-{{ rtrim(rtrim(number_format($totalOut, 2), '0'), '.') }}</span></p>
                    <p class="text-xs text-gray-400 mt-1">{{ $item->unit }}, {{ __('all time') }}</p>
                </div>
            </div>

            {{-- Mobile mini stats --}}
            <div class="lg:hidden grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-xs text-gray-400">{{ __('Selling price') }}</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">&#8377;{{ number_format($item->selling_price, 2) }}</p>
                    <p class="text-xs {{ $profitPerUnit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-0.5">&#8377;{{ number_format($profitPerUnit, 2) }} {{ __('profit') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-xs text-gray-400">{{ __('Purchase price') }}</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">&#8377;{{ number_format($item->purchase_price, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-xs text-gray-400">{{ __('Stock value') }}</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">&#8377;{{ number_format($stockValue, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-xs text-gray-400">{{ __('Added / Sold') }}</p>
                    <p class="text-base font-bold mt-0.5"><span class="text-green-600">+{{ rtrim(rtrim(number_format($totalIn, 2), '0'), '.') }}</span> <span class="text-red-600">-{{ rtrim(rtrim(number_format($totalOut, 2), '0'), '.') }}</span></p>
                </div>
            </div>

            @if ($item->description)
                <div class="bg-white shadow-sm rounded-lg p-4 lg:p-5 text-sm text-gray-600">{{ $item->description }}</div>
            @endif

            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="flex items-center gap-1 px-2 pt-2 border-b border-gray-100">
                        @foreach (['all' => __('All'), 'in' => __('Stock in'), 'out' => __('Stock out')] as $tab => $label)
                            <a href="{{ route('items.show', [$item, 'type' => $tab]) }}"
                                class="px-3.5 py-2.5 text-sm font-medium border-b-2 -mb-px"
                                @class(['border-brand-600 text-brand-700' => $movementType === $tab, 'border-transparent text-gray-500 hover:text-gray-700' => $movementType !== $tab])>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div x-data="listTable()">
                        <div x-ref="content" :class="loading && 'opacity-50'">
                            @include('items.partials.history-table')
                        </div>
                    </div>
                </div>

                {{-- Adjust stock sidebar (desktop) --}}
                <div id="adjust-stock" class="hidden lg:block mt-0 scroll-mt-6">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        @include('items.partials.adjust-stock-form', ['item' => $item])
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 flex gap-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="button" @click="adjustOpen = true" class="flex-1 px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg text-center">
                {{ __('Adjust stock') }}
            </button>
            <a href="{{ route('purchases.create') }}" class="flex-1 px-4 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg text-center">
                {{ __('+ New Purchase') }}
            </a>
        </div>

        {{-- Mobile adjust-stock modal --}}
        <div x-show="adjustOpen" style="display: none" class="lg:hidden fixed inset-0 z-50 flex items-end">
            <div x-show="adjustOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-gray-900/50" @click="adjustOpen = false"></div>
            <div x-show="adjustOpen"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-6"
                class="relative bg-white rounded-t-2xl w-full p-6 max-h-[85vh] overflow-y-auto">
                @include('items.partials.adjust-stock-form', ['item' => $item])
            </div>
        </div>
    </div>
</x-app-layout>
