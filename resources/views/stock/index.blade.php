<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Every stock in and out, with reason') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6a1.5 1.5 0 003 0m-6 0a1.5 1.5 0 013 0m-6 0h-6v-4.5m12 4.5h3V12a6 6 0 00-6-6h-3.75a.75.75 0 00-.75.75V4.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 004.5 4.5v2.25m0 0v4.5m0-4.5h-.75a1.5 1.5 0 00-1.5 1.5v3a1.5 1.5 0 001.5 1.5H4.5" /></svg>
                        {{ __('New Purchase') }}
                    </a>
                    <a href="{{ route('stock.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        {{ __('+ Adjust stock') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Stat cards (desktop only) --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Stock in entries') }}</span>
                    <div class="text-2xl font-bold text-green-600 mt-3">{{ $stats['in'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('purchases and additions') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Stock out entries') }}</span>
                    <div class="text-2xl font-bold text-red-600 mt-3">{{ $stats['out'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('sales and removals') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('From purchases') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['from_purchases'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('linked to supplier bill') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border {{ $stats['manual'] > 0 ? 'border-amber-200' : 'border-gray-100' }}">
                    <span class="text-sm text-gray-500">{{ __('Manual changes') }}</span>
                    <div class="text-2xl font-bold text-amber-600 mt-3">{{ $stats['manual'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('no bill, check these') }}</div>
                </div>
            </div>

            {{-- Filter pills + item dropdown --}}
            <div class="bg-white lg:bg-transparent rounded-xl lg:rounded-none p-3 lg:p-0 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" class="lg:hidden">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <select name="item_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All items') }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected($filterItemId === $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                        @endforeach
                    </select>
                </form>

                <div class="flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1 lg:mx-0 lg:px-0">
                    @foreach ([
                        'all' => __('All').' '.$stats['total'],
                        'in' => __('Stock in').' '.$stats['in'],
                        'out' => __('Stock out').' '.$stats['out'],
                        'manual' => __('Manual only').' '.$stats['manual'],
                    ] as $value => $label)
                        <a href="{{ route('stock.index', array_filter(['filter' => $value, 'item_id' => $filterItemId])) }}"
                            class="shrink-0 px-3.5 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $filter === $value ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="hidden lg:flex items-center gap-2 shrink-0">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <label for="item_id_desktop" class="text-sm text-gray-500 shrink-0">{{ __('Item') }}</label>
                    <select id="item_id_desktop" name="item_id" class="w-56 border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All items') }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected($filterItemId === $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('stock.partials.table')
                </div>
            </div>
        </div>

        <a href="{{ route('stock.create') }}" aria-label="{{ __('Adjust stock') }}"
            class="lg:hidden fixed right-4 bottom-20 z-30 inline-flex items-center gap-2 px-4 h-12 rounded-full bg-brand-600 text-white shadow-lg hover:bg-brand-700 transform-gpu font-semibold text-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            {{ __('Adjust stock') }}
        </a>
    </div>
</x-app-layout>
