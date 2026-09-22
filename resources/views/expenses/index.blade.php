<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expenses') }}</h2>
            <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ __('+ Add Expense') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white">
                    @foreach (['all' => __('All'), 'today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month')] as $value => $label)
                        <a href="{{ route('expenses.index', array_filter(['period' => $value, 'category' => request('category'), 'staff_member_id' => request('staff_member_id')])) }}"
                            class="px-3 py-1.5 {{ $activeQuickFilter === $value ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="bg-white shadow-sm rounded-lg px-4 py-2.5">
                    <span class="text-sm text-gray-500">{{ __('Total for this filter') }}</span>
                    <span class="text-lg font-semibold text-gray-900 ml-2">₹{{ number_format($totalForFilter, 2) }}</span>
                </div>
            </div>

            <form method="GET" class="bg-white shadow-sm rounded-lg p-4 sm:p-5 grid grid-cols-1 sm:flex sm:flex-wrap sm:items-end gap-4 sm:gap-3">
                <div class="w-full sm:w-auto">
                    <x-input-label for="category" :value="__('Category')" />
                    <select id="category" name="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <x-input-label for="staff_member_id" :value="__('Staff / Partner')" />
                    <select id="staff_member_id" name="staff_member_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Anyone') }}</option>
                        @foreach ($staffOptions as $member)
                            <option value="{{ $member->id }}" @selected((string) request('staff_member_id') === (string) $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <x-input-label for="from" :value="__('From date')" />
                    <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="request('from')" />
                </div>
                <div class="w-full sm:w-auto">
                    <x-input-label for="to" :value="__('To date')" />
                    <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="request('to')" />
                </div>
                <x-primary-button class="w-full sm:w-auto justify-center">{{ __('Filter') }}</x-primary-button>
            </form>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('expenses.partials.table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
