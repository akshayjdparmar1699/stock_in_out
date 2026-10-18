<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Items') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">
                        {{ $totalCount }} {{ __('items') }}
                        @if ($branch)
                            &middot; {{ $branch->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('items.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        {{ __('+ Add Item') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '', mobileView: 'cards' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Stat cards (desktop) --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Total items') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['total_items'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('in this branch') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Stock value') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($stats['stock_value'], 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('at selling price') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border {{ $stats['low_stock_count'] > 0 ? 'border-amber-200' : 'border-gray-100' }}">
                    <span class="text-sm text-gray-500">{{ __('Low stock') }}</span>
                    <div class="text-2xl font-bold text-amber-600 mt-3">{{ $stats['low_stock_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('at or below alert level') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Out of stock') }}</span>
                    <div class="text-2xl font-bold text-red-600 mt-3">{{ $stats['out_of_stock_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('cannot be billed') }}</div>
                </div>
            </div>

            {{-- Filters + search --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" class="order-1 lg:order-2 flex gap-2 shrink-0">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <x-text-input name="q" type="text" class="w-full lg:w-72" placeholder="{{ __('Search item or SKU') }}" :value="request('q')" />
                    <x-secondary-button type="submit" class="hidden lg:inline-flex shrink-0">{{ __('Search') }}</x-secondary-button>
                </form>

                <div class="order-2 lg:order-1 flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1 lg:mx-0 lg:px-0">
                    @foreach ([
                        'all' => __('All').' '.$totalCount,
                        'low_stock' => __('Low stock').' '.$stats['low_stock_count'],
                        'in_stock' => __('In stock').' '.$stats['in_stock_count'],
                        'out_of_stock' => __('Out of stock').' '.$stats['out_of_stock_count'],
                    ] as $value => $label)
                        <a href="{{ route('items.index', array_filter(['filter' => $value, 'q' => request('q')])) }}"
                            class="shrink-0 px-3.5 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $filter === $value ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Mobile stat cards --}}
            <div class="lg:hidden grid grid-cols-2 gap-3">
                <div class="bg-gradient-to-br from-brand-600 to-brand-700 text-white rounded-2xl p-4">
                    <p class="text-xs text-white/70">{{ __('Stock value') }}</p>
                    <p class="text-xl font-bold mt-0.5">&#8377;{{ number_format($stats['stock_value'], 0) }}</p>
                </div>
                <div class="bg-white border border-amber-200 rounded-2xl p-4">
                    <p class="text-xs text-gray-500">{{ __('Low stock') }}</p>
                    <p class="text-xl font-bold text-amber-600 mt-0.5">{{ $stats['low_stock_count'] }} {{ __('items') }}</p>
                </div>
            </div>

            {{-- Mobile card/table view toggle --}}
            <div class="flex lg:hidden justify-end">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden shrink-0">
                    <button type="button" @click="mobileView = 'cards'" :class="mobileView === 'cards' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium" aria-label="{{ __('Card view') }}">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        {{ __('Cards') }}
                    </button>
                    <button type="button" @click="mobileView = 'table'" :class="mobileView === 'table' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium border-l border-gray-200" aria-label="{{ __('Table view') }}">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        {{ __('Table') }}
                    </button>
                </div>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('items.partials.table')
                </div>
            </div>
        </div>

        <a href="{{ route('items.create') }}" aria-label="{{ __('Add Item') }}"
            class="lg:hidden fixed right-4 bottom-20 z-30 w-14 h-14 rounded-full bg-brand-600 text-white shadow-lg flex items-center justify-center hover:bg-brand-700 transform-gpu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        </a>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
