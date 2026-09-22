<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Suppliers') }}</h2>
            <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ __('+ Add Supplier') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <form method="GET" class="flex gap-2">
                <x-text-input name="q" type="text" class="w-full sm:w-80" placeholder="{{ __('Search by name or phone') }}" :value="request('q')" />
                <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
            </form>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('suppliers.partials.table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
