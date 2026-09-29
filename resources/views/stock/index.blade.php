<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock') }}</h2>
    </x-slot>

    <div class="py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 flex items-center justify-between gap-3">
                <p class="text-sm text-indigo-900">
                    {{ __('Restocking from a supplier? Use') }}
                    <a href="{{ route('purchases.create') }}" class="font-medium underline">{{ __('New Purchase') }}</a>
                    {{ __('to record their bill and payment properly. Use the form below only for manual corrections (no supplier bill).') }}
                </p>
            </div>

            @php
                $stockItemsMeta = $items->keyBy('id')->map(fn ($item) => [
                    'alt_unit' => $item->alt_unit,
                    'ratio' => $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null,
                    'unit' => $item->unit,
                ]);
            @endphp
            <div class="bg-white shadow-sm rounded-lg p-6"
                x-data="{
                    itemId: '{{ old('item_id') }}',
                    quantity: {{ old('quantity') ? (float) old('quantity') : 'null' }},
                    altUnit: '{{ old('alt_unit') }}',
                    altUnitRatio: {{ old('alt_unit_ratio') ? (float) old('alt_unit_ratio') : 'null' }},
                    itemsMeta: @json($stockItemsMeta),
                    onItemChange() {
                        const meta = this.itemsMeta[this.itemId] ?? null;
                        this.altUnit = meta ? (meta.alt_unit || '') : '';
                        this.altUnitRatio = meta ? meta.ratio : null;
                    },
                    get altTotal() {
                        if (!this.altUnit || !this.altUnitRatio || !this.quantity) return null;
                        return Math.round(this.quantity * this.altUnitRatio * 100) / 100;
                    },
                }">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Manual Stock Adjustment') }}</h3>
                <form method="POST" action="{{ route('stock.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div class="sm:col-span-2">
                        <x-input-label for="item_id" :value="__('Item')" />
                        <select id="item_id" name="item_id" required x-model="itemId" @change="onItemChange()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select item') }}</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('item_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.01" min="0.01" x-model.number="quantity" class="mt-1 block w-full" :value="old('quantity')" required />
                        <p class="text-xs text-gray-400 mt-1" x-show="altTotal !== null" style="display: none;">
                            = <span x-text="altTotal"></span> <span x-text="altUnit"></span>
                        </p>
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" placeholder="{{ __('e.g. Purchase from supplier') }}" />
                    </div>

                    <div class="sm:col-span-4 border-t pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="itemId" style="display: none;">
                        <div>
                            <x-input-label for="alt_unit" :value="__('Alternate Selling Unit (e.g. kg)')" />
                            <x-text-input id="alt_unit" name="alt_unit" type="text" x-model="altUnit" class="mt-1 block w-full" placeholder="{{ __('e.g. kg') }}" />
                            <x-input-error :messages="$errors->get('alt_unit')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="alt_unit_ratio" :value="__('1 unit = ? alt unit')" />
                            <x-text-input id="alt_unit_ratio" name="alt_unit_ratio" type="number" step="0.0001" min="0" x-model.number="altUnitRatio" class="mt-1 block w-full" placeholder="{{ __('e.g. 50') }}" />
                            <x-input-error :messages="$errors->get('alt_unit_ratio')" class="mt-2" />
                        </div>
                        <p class="text-xs text-gray-400 sm:col-span-2 -mt-2">
                            {{ __('Only if this item is also sold in a different unit — this updates the item, not just this stock entry. Leave blank if not needed.') }}
                        </p>
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

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
