<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 flex items-center justify-between gap-3">
                <p class="text-sm text-indigo-900">
                    {{ __('Restocking from a supplier? Use') }}
                    <a href="{{ route('purchases.create') }}" class="font-medium underline">{{ __('New Purchase') }}</a>
                    {{ __('to record their bill and payment properly. Use the form below only for manual corrections (no supplier bill).') }}
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Manual Stock Adjustment') }}</h3>
                <form method="POST" action="{{ route('stock.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div class="sm:col-span-2">
                        <x-input-label for="item_id" :value="__('Item')" />
                        <select id="item_id" name="item_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select item') }}</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('item_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('quantity')" required />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" placeholder="{{ __('e.g. Purchase from supplier') }}" />
                    </div>
                    <div class="sm:col-span-4 flex justify-end">
                        <x-primary-button>{{ __('Add Stock') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('stock.partials.table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
