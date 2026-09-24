<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Purchases') }}</h2>
            <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ __('+ New Purchase') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white">
                    @foreach (['all' => __('All'), 'today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month')] as $value => $label)
                        <a href="{{ route('purchases.index', array_filter(['period' => $value, 'q' => request('q')])) }}"
                            class="px-3 py-1.5 {{ $activeQuickFilter === $value ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <form method="GET" class="bg-white shadow-sm rounded-lg p-4 sm:p-5 grid grid-cols-1 sm:flex sm:flex-wrap sm:items-end gap-4 sm:gap-3">
                <div class="w-full sm:flex-1 sm:min-w-[200px]">
                    <x-input-label for="q" :value="__('Supplier name or phone')" />
                    <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" :value="request('q')" placeholder="{{ __('e.g. Rajkot Wholesale Traders') }}" />
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
                    @include('purchases.partials.table')
                </div>
            </div>
        </div>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
