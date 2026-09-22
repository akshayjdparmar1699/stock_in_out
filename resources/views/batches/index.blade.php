<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock Batches') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <p class="text-sm text-gray-500">
                {{ __('Every time stock comes in (from a supplier purchase or a manual stock add), it is recorded here as its own batch/lot so you can trace exactly how much of it was sold and when.') }}
            </p>

            <form method="GET" class="flex gap-2">
                <x-text-input name="q" type="text" class="w-full sm:w-80" placeholder="{{ __('Search by item name or SKU') }}" :value="request('q')" />
                <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
            </form>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('batches.partials.table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
