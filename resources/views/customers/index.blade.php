<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Customers') }}</h2>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ __('+ Add Customer') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <x-text-input name="q" type="text" class="w-full sm:w-80" placeholder="{{ __('Search by name or phone') }}" :value="request('q')" />
                    <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
                </form>

                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                    @foreach (['active' => __('Active'), 'inactive' => __('Inactive'), 'all' => __('All')] as $value => $label)
                        <a href="{{ route('customers.index', array_filter(['status' => $value, 'q' => request('q')])) }}"
                            class="px-3 py-1.5 {{ $status === $value ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('customers.partials.table')
                </div>
            </div>
        </div>

        @include('customers.partials.status-confirm-modal')
    </div>
</x-app-layout>
