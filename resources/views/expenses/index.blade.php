<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expenses') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Every business cost, sorted by category') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        {{ __('+ Add Expense') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '', mobileView: 'cards', customOpen: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-4">
            @php
                $barPalette = ['bg-amber-400', 'bg-blue-400', 'bg-teal-400', 'bg-pink-400', 'bg-purple-400', 'bg-green-400', 'bg-red-400', 'bg-indigo-400', 'bg-gray-400'];
            @endphp

            {{-- Date tabs --}}
            <div class="order-1 flex items-center justify-between gap-3">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-x-auto scrollbar-hide text-sm bg-white max-w-full">
                    @foreach (['all' => __('All'), 'today' => __('Today'), 'week' => __('This week'), 'month' => __('This month')] as $value => $label)
                        <a href="{{ route('expenses.index', array_filter(['period' => $value, 'category' => request('category'), 'staff_member_id' => request('staff_member_id'), 'q' => request('q')])) }}"
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
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            <input type="hidden" name="staff_member_id" value="{{ request('staff_member_id') }}">
                            <input type="hidden" name="q" value="{{ request('q') }}">
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

            {{-- Stat cards (desktop only) --}}
            <div class="hidden lg:grid lg:order-2 lg:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Total spent') }}</span>
                    <div class="text-2xl font-bold text-red-600 mt-3">&#8377;{{ number_format($totalForFilter, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $expenses->total() }} {{ __('expenses') }} &middot; {{ $activeQuickFilter === 'today' ? __('today') : __('all time') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Average per day') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($averagePerDay, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('over') }} {{ $daysWithExpenses }} {{ __('days with expenses') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Biggest cost') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">{{ $biggestCost['label'] ?? '—' }}</div>
                    <div class="text-xs text-gray-400 mt-1">
                        @if ($biggestCost)
                            &#8377;{{ number_format($biggestCost['total'], 0) }} &middot; {{ $biggestCost['percent'] }}% {{ __('of total') }}
                        @else
                            {{ __('no expenses yet') }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Mobile combined stat card --}}
            <div class="lg:hidden order-3 bg-white rounded-2xl border border-red-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-500">{{ __('Total spent') }} &middot; {{ $activeQuickFilter === 'today' ? __('today') : __('all time') }}</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">&#8377;{{ number_format($totalForFilter, 0) }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-500">{{ __('Per day') }}</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">&#8377;{{ number_format($averagePerDay, 0) }}</p>
                    </div>
                </div>
                @if ($byCategory->isNotEmpty())
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden flex mt-3">
                        @foreach ($byCategory as $i => $row)
                            <div class="{{ $barPalette[$i % count($barPalette)] }} h-full" style="width: {{ $row['percent'] }}%"></div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- By category (desktop only) --}}
            @if ($byCategory->isNotEmpty())
                <div class="hidden lg:block lg:order-3 bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900">{{ __('By category') }}</h3>
                        <span class="hidden sm:inline text-xs text-gray-400">{{ __('Tap a category to see only those') }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden flex mb-4">
                        @foreach ($byCategory as $i => $row)
                            <div class="{{ $barPalette[$i % count($barPalette)] }} h-full" style="width: {{ $row['percent'] }}%"></div>
                        @endforeach
                    </div>
                    <div class="flex gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1">
                        <a href="{{ route('expenses.index', array_filter(['period' => request('period'), 'q' => request('q')])) }}"
                            class="shrink-0 px-3.5 py-2.5 rounded-lg border text-sm {{ ! request('category') ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50' }}">
                            <span class="font-medium text-gray-700">{{ __('All categories') }}</span>
                            <span class="block text-xs text-gray-400">&#8377;{{ number_format($totalForFilter, 0) }}</span>
                        </a>
                        @foreach ($byCategory as $row)
                            <a href="{{ route('expenses.index', array_filter(['period' => request('period'), 'category' => $row['category'], 'q' => request('q')])) }}"
                                class="shrink-0 px-3.5 py-2.5 rounded-lg border text-sm {{ request('category') === $row['category'] ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                <span class="block text-xs text-gray-400">&#8377;{{ number_format($row['total'], 0) }} &middot; {{ $row['percent'] }}%</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Filters + search --}}
            <div class="order-2 lg:order-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" class="flex flex-wrap gap-2 w-full lg:w-auto">
                    <input type="hidden" name="period" value="{{ request('period') }}">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <select name="staff_member_id" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="">{{ __('Anyone') }}</option>
                        @foreach ($staffOptions as $member)
                            <option value="{{ $member->id }}" @selected((string) request('staff_member_id') === (string) $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <div class="relative flex-1 lg:flex-none">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <x-text-input name="q" type="text" class="w-full lg:w-64 pl-9" placeholder="{{ __('Search note') }}" :value="request('q')" />
                    </div>
                    <button type="submit" aria-label="{{ __('Search') }}" class="lg:hidden shrink-0 w-11 h-11 flex items-center justify-center rounded-lg bg-brand-600 text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </button>
                    <x-secondary-button type="submit" class="hidden lg:inline-flex shrink-0">{{ __('Search') }}</x-secondary-button>
                </form>
            </div>

            {{-- Mobile card/table view toggle --}}
            <div class="order-4 flex lg:hidden justify-end">
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

            <div class="order-5" x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('expenses.partials.table')
                </div>
            </div>
        </div>

        <a href="{{ route('expenses.create') }}" aria-label="{{ __('Add Expense') }}"
            class="lg:hidden fixed right-4 bottom-20 z-30 w-14 h-14 rounded-full bg-brand-600 text-white shadow-lg flex items-center justify-center hover:bg-brand-700 transform-gpu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        </a>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
