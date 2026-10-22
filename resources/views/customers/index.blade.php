<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Customers') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">
                        {{ $stats['udhaar_count'] + $stats['settled_count'] }} {{ __('active customers') }}
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
                    <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        {{ __('+ Add Customer') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12"
        x-data="{
            confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false,
            deleteOpen: false, deleteForm: null, deleteLabel: '',
            mobileView: 'cards',
        }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Stat cards (desktop) --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">{{ __('To collect (udhaar)') }}</span>
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75m-17.25-9h18M3.75 9.75V7.5A2.25 2.25 0 016 5.25h12a2.25 2.25 0 012.25 2.25v2.25" /></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-green-600 mt-3">&#8377;{{ number_format($stats['udhaar_total'], 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['udhaar_count'] }} {{ __('customers have a balance') }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">{{ __('Over credit limit') }}</span>
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-red-600 mt-3">{{ $stats['over_limit_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('Time to collect payment') }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">{{ __('Settled') }}</span>
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['settled_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('No balance due') }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">{{ __('Inactive') }}</span>
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['inactive_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('Hidden from billing') }}</div>
                </div>
            </div>

            {{-- Filters + search --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" class="order-1 lg:order-2 flex gap-2 shrink-0">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <x-text-input name="q" type="text" class="w-full lg:w-72" placeholder="{{ __('Search name, phone or village') }}" :value="request('q')" />
                    <button type="submit" aria-label="{{ __('Search') }}" class="lg:hidden shrink-0 w-11 h-11 flex items-center justify-center rounded-lg bg-brand-600 text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </button>
                    <x-secondary-button type="submit" class="hidden lg:inline-flex shrink-0">{{ __('Search') }}</x-secondary-button>
                </form>

                <div class="order-2 lg:order-1 flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1 lg:mx-0 lg:px-0">
                    @foreach ([
                        'all' => __('All').' '.$totalCount,
                        'udhaar' => __('Udhaar due').' '.$stats['udhaar_count'],
                        'over_limit' => __('Over limit').' '.$stats['over_limit_count'],
                        'settled' => __('Settled').' '.$stats['settled_count'],
                        'inactive' => __('Inactive').' '.$stats['inactive_count'],
                    ] as $value => $label)
                        <a href="{{ route('customers.index', array_filter(['filter' => $value, 'q' => request('q')])) }}"
                            class="shrink-0 px-3.5 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $filter === $value ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- "To collect" banner (mobile) --}}
            <div class="lg:hidden bg-gradient-to-br from-brand-600 to-brand-700 text-white rounded-2xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-white/70">{{ __('To collect (udhaar)') }}</p>
                        <p class="text-2xl font-bold mt-0.5">&#8377;{{ number_format($stats['udhaar_total'], 0) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold">{{ $stats['over_limit_count'] }}</p>
                        <p class="text-xs text-white/70">{{ __('over limit') }}</p>
                    </div>
                </div>
            </div>

            {{-- Mobile card/table view toggle --}}
            <div class="flex lg:hidden justify-end">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden shrink-0">
                    <button type="button" @click="mobileView = 'cards'" :class="mobileView === 'cards' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium" aria-label="{{ __('Card view') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        {{ __('Cards') }}
                    </button>
                    <button type="button" @click="mobileView = 'table'" :class="mobileView === 'table' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium border-l border-gray-200" aria-label="{{ __('Table view') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        {{ __('Table') }}
                    </button>
                </div>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('customers.partials.table')
                </div>
            </div>
        </div>

        <a href="{{ route('customers.create') }}" aria-label="{{ __('Add Customer') }}"
            class="lg:hidden fixed right-4 bottom-20 z-30 w-14 h-14 rounded-full bg-brand-600 text-white shadow-lg flex items-center justify-center hover:bg-brand-700 transform-gpu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        </a>

        @include('customers.partials.status-confirm-modal')
        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
