<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Suppliers') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Wholesalers you buy maal from') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden flex items-center">
                    @include('layouts.partials.settings-icon-mobile')
                    @include('layouts.partials.notification-bell')
                </div>
                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        {{ __('+ Add Supplier') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12" x-data="{ mobileView: 'cards' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Stat cards (desktop) --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border {{ $stats['we_owe_count'] > 0 ? 'border-red-200' : 'border-gray-100' }}">
                    <span class="text-sm text-gray-500">{{ __('We owe in total') }}</span>
                    <div class="text-2xl font-bold text-red-600 mt-3">&#8377;{{ number_format($stats['we_owe_total'], 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('to') }} {{ $stats['we_owe_count'] }} {{ __('suppliers') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Biggest due') }}</span>
                    <div class="text-xl font-bold text-gray-900 mt-3 truncate">{{ $stats['biggest_due_supplier']->name ?? __('None') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['biggest_due_supplier'] ? '₹'.number_format($stats['biggest_due'], 0) : '' }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Settled') }}</span>
                    <div class="text-2xl font-bold text-green-600 mt-3">{{ $stats['settled_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('nothing due') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Total suppliers') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $totalCount }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('saved') }}</div>
                </div>
            </div>

            {{-- Filters + search + sort --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="order-2 lg:order-1 flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1 lg:mx-0 lg:px-0">
                    @foreach ([
                        'all' => __('All').' '.$totalCount,
                        'we_owe' => __('We owe them').' '.$stats['we_owe_count'],
                        'settled' => __('Settled').' '.$stats['settled_count'],
                    ] as $value => $label)
                        <a href="{{ route('suppliers.index', array_filter(['filter' => $value, 'q' => request('q'), 'sort' => $sort])) }}"
                            class="shrink-0 px-3.5 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $filter === $value ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="order-1 lg:order-2 flex gap-2 shrink-0">
                    <form method="GET" class="flex gap-2 flex-1 lg:flex-none">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <x-text-input name="q" type="text" class="w-full lg:w-64" placeholder="{{ __('Search name, phone or city') }}" :value="request('q')" />
                        <button type="submit" aria-label="{{ __('Search') }}" class="lg:hidden shrink-0 w-11 h-11 flex items-center justify-center rounded-lg bg-brand-600 text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </button>
                        <x-secondary-button type="submit" class="hidden lg:inline-flex shrink-0">{{ __('Search') }}</x-secondary-button>
                    </form>
                    <form method="GET" class="hidden lg:block shrink-0">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <input type="hidden" name="q" value="{{ request('q') }}">
                        <select name="sort" onchange="this.form.submit()" class="border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                            <option value="name_asc" @selected($sort === 'name_asc')>{{ __('Sort: A to Z') }}</option>
                            <option value="name_desc" @selected($sort === 'name_desc')>{{ __('Sort: Z to A') }}</option>
                            <option value="due_desc" @selected($sort === 'due_desc')>{{ __('Sort: Highest due') }}</option>
                        </select>
                    </form>
                </div>
            </div>

            {{-- Mobile stat banner --}}
            <div class="lg:hidden bg-red-50 border border-red-100 rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('We owe in total') }}</p>
                    <p class="text-2xl font-bold text-red-600 mt-0.5">&#8377;{{ number_format($stats['we_owe_total'], 0) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-bold text-gray-900">{{ $stats['we_owe_count'] }}</p>
                    <p class="text-xs text-gray-500">{{ __('suppliers') }}</p>
                </div>
            </div>

            {{-- Mobile card/table view toggle --}}
            <div class="flex lg:hidden justify-end">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden shrink-0">
                    <button type="button" @click="mobileView = 'cards'" :class="mobileView === 'cards' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium" aria-label="{{ __('Card view') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        {{ __('Cards') }}
                    </button>
                    <button type="button" @click="mobileView = 'table'" :class="mobileView === 'table' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium border-l border-gray-200" aria-label="{{ __('Table view') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        {{ __('Table') }}
                    </button>
                </div>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('suppliers.partials.table')
                </div>
            </div>
        </div>

        <a href="{{ route('suppliers.create') }}" aria-label="{{ __('Add Supplier') }}"
            class="lg:hidden fixed right-4 bottom-20 z-30 w-14 h-14 rounded-full bg-brand-600 text-white shadow-lg flex items-center justify-center hover:bg-brand-700 transform-gpu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        </a>
    </div>
</x-app-layout>
