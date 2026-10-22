<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Maal In Out') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Every lot of stock that came in, and how much of it is sold') }}</p>
                </div>
            </div>
            <div class="lg:hidden">
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12" x-data="{ mobileView: 'cards' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Stat cards (desktop only) --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Lots with stock') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['stale_count'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('still in godown') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Partly sold') }}</span>
                    <div class="text-2xl font-bold text-amber-600 mt-3">{{ $stats['selling'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('selling now') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Sold out') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $stats['sold_out'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('fully finished') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border {{ $stats['oldest_stale'] ? 'border-amber-200' : 'border-gray-100' }}">
                    <span class="text-sm text-gray-500">{{ __('Oldest unsold lot') }}</span>
                    <div class="text-2xl font-bold text-amber-600 mt-3">{{ $stats['oldest_stale']?->received_at->format('d M') ?? __('None') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('sell this first') }}</div>
                </div>
            </div>

            {{-- Filter pills + search --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1 lg:mx-0 lg:px-0">
                    @foreach ([
                        'all' => __('All lots').' '.$stats['total'],
                        'full' => __('Full (not sold yet)').' '.$stats['full'],
                        'selling' => __('Selling').' '.$stats['selling'],
                        'sold_out' => __('Sold out').' '.$stats['sold_out'],
                    ] as $value => $label)
                        <a href="{{ route('batches.index', array_filter(['filter' => $value, 'q' => request('q')])) }}"
                            class="shrink-0 px-3.5 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $filter === $value ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="flex gap-2 shrink-0">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <x-text-input name="q" type="text" class="w-full lg:w-72" placeholder="{{ __('Search item or purchase number') }}" :value="request('q')" />
                    <button type="submit" aria-label="{{ __('Search') }}" class="lg:hidden shrink-0 w-11 h-11 flex items-center justify-center rounded-lg bg-brand-600 text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </button>
                    <x-secondary-button type="submit" class="hidden lg:inline-flex shrink-0">{{ __('Search') }}</x-secondary-button>
                </form>
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
                    @include('batches.partials.table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
