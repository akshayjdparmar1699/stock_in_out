<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Purchases') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Maal bought from suppliers') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('purchases.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('Export') }}
                    </a>
                    <a href="{{ route('purchases.create') }}" onclick="window.showCubeLoader()" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        {{ __('+ New Purchase') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '', customOpen: false, mobileView: 'cards' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Date-range tabs --}}
            <div class="flex items-center justify-between gap-3">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-x-auto scrollbar-hide text-sm bg-white max-w-full">
                    @foreach (['all' => __('All time'), 'today' => __('Today'), 'week' => __('This week'), 'month' => __('This month')] as $value => $label)
                        <a href="{{ route('purchases.index', array_filter(['period' => $value, 'q' => request('q'), 'status' => request('status')])) }}"
                            class="shrink-0 px-3.5 py-2 {{ $activeQuickFilter === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="relative shrink-0">
                    <button type="button" @click="customOpen = !customOpen"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border text-sm font-medium"
                        :class="customOpen || {{ $activeQuickFilter === null ? 'true' : 'false' }} ? 'border-brand-500 text-brand-700 bg-brand-50' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        <span class="hidden sm:inline">{{ __('Custom dates') }}</span>
                    </button>

                    <div x-show="customOpen" @click.outside="customOpen = false" x-transition style="display: none"
                        class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-100 p-4 z-20">
                        <form method="GET" class="space-y-3">
                            <input type="hidden" name="q" value="{{ request('q') }}">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <div>
                                <x-input-label for="from" :value="__('From date')" />
                                <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="request('from')" />
                            </div>
                            <div>
                                <x-input-label for="to" :value="__('To date')" />
                                <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="request('to')" />
                            </div>
                            <x-primary-button type="submit" class="w-full justify-center">{{ __('Apply') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Stat cards (desktop) --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Total purchased') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($stats['total_purchased'], 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['total_count'] }} {{ __('purchases') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Paid to suppliers') }}</span>
                    <div class="text-2xl font-bold text-green-600 mt-3">&#8377;{{ number_format($stats['total_paid'], 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['paid_count'] }} {{ __('paid in full') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border {{ $stats['total_pending'] > 0 ? 'border-red-200' : 'border-gray-100' }}">
                    <span class="text-sm text-gray-500">{{ __('We still owe') }}</span>
                    <div class="text-2xl font-bold text-red-600 mt-3">&#8377;{{ number_format($stats['total_pending'], 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['unpaid_count'] + $stats['partial_count'] }} {{ __('not fully paid') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Suppliers') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['supplier_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('bought from') }}</div>
                </div>
            </div>

            {{-- Status pills + search --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" class="order-1 lg:order-2 flex gap-2 shrink-0">
                    <input type="hidden" name="period" value="{{ request('period') }}">
                    <input type="hidden" name="from" value="{{ request('from') }}">
                    <input type="hidden" name="to" value="{{ request('to') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <x-text-input name="q" type="text" class="w-full lg:w-72" placeholder="{{ __('Supplier, phone or purchase number') }}" :value="request('q')" />
                    <x-secondary-button type="submit" class="hidden lg:inline-flex shrink-0">{{ __('Search') }}</x-secondary-button>
                </form>

                <div class="order-2 lg:order-1 flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1 lg:mx-0 lg:px-0">
                    @foreach ([
                        'all' => __('All').' '.$stats['total_count'],
                        'paid' => __('Paid').' '.$stats['paid_count'],
                        'unpaid' => __('Unpaid').' '.$stats['unpaid_count'],
                        'partial' => __('Partial').' '.$stats['partial_count'],
                    ] as $value => $label)
                        <a href="{{ route('purchases.index', array_filter(['status' => $value, 'period' => request('period'), 'from' => request('from'), 'to' => request('to'), 'q' => request('q')])) }}"
                            class="shrink-0 px-3.5 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $status === $value ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Mobile stat card --}}
            <div class="lg:hidden bg-gradient-to-br from-brand-600 to-brand-700 text-white rounded-2xl p-4 grid grid-cols-2 divide-x divide-white/20">
                <div>
                    <p class="text-xs text-white/70">{{ $activeQuickFilter === 'today' ? __('Bought today') : __('Total purchased') }}</p>
                    <p class="text-xl font-bold mt-0.5">&#8377;{{ number_format($stats['total_purchased'], 0) }}</p>
                    <p class="text-xs text-white/70 mt-0.5">{{ $stats['total_count'] }} {{ __('purchases') }}</p>
                </div>
                <div class="pl-4">
                    <p class="text-xs text-white/70">{{ __('Pending') }}</p>
                    <p class="text-xl font-bold mt-0.5">&#8377;{{ number_format($stats['total_pending'], 0) }}</p>
                    <p class="text-xs text-white/70 mt-0.5">{{ $stats['unpaid_count'] + $stats['partial_count'] }} {{ __('purchases') }}</p>
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
                    @include('purchases.partials.table')
                </div>
            </div>
        </div>

        <a href="{{ route('purchases.create') }}" onclick="window.showCubeLoader()" aria-label="{{ __('New Purchase') }}"
            class="lg:hidden fixed right-4 bottom-20 z-30 w-14 h-14 rounded-full bg-brand-600 text-white shadow-lg flex items-center justify-center hover:bg-brand-700 transform-gpu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        </a>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
