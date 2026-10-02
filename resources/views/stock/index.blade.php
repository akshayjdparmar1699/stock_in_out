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
                    'purchase_price' => (float) $item->purchase_price,
                ]);
                $hasOldStockInput = $errors->any() || old('item_id');
            @endphp
            <div class="bg-white shadow-sm rounded-lg p-6"
                x-data="{
                    showForm: {{ $hasOldStockInput ? 'true' : 'false' }},
                    itemId: '{{ old('item_id') }}',
                    quantity: {{ old('quantity') ? (float) old('quantity') : 'null' }},
                    quantityUnit: '{{ old('quantity_unit', 'base') }}',
                    unitCost: {{ old('unit_cost') ? (float) old('unit_cost') : 'null' }},
                    unitLabel: '',
                    altUnit: '{{ old('alt_unit') }}',
                    altUnitRatio: {{ old('alt_unit_ratio') ? (float) old('alt_unit_ratio') : 'null' }},
                    itemsMeta: {{ \Illuminate\Support\Js::from($stockItemsMeta) }},
                    onItemChange() {
                        const meta = this.itemsMeta[this.itemId] ?? null;
                        this.altUnit = meta ? (meta.alt_unit || '') : '';
                        this.altUnitRatio = meta ? meta.ratio : null;
                        this.unitLabel = meta ? meta.unit : '';
                        this.unitCost = meta ? meta.purchase_price : null;
                        this.quantityUnit = 'base';
                    },
                    onUnitToggle() {
                        // The price typed in one unit is meaningless in the
                        // other (₹/bag vs ₹/kg), so it's cleared rather than
                        // silently carried over or auto-converted.
                        this.unitCost = null;
                    },
                    get altTotal() {
                        if (!this.altUnit || !this.altUnitRatio || !this.quantity || this.quantityUnit !== 'base') return null;
                        return Math.round(this.quantity * this.altUnitRatio * 100) / 100;
                    },
                }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-700">{{ __('Manual Stock Adjustment') }}</h3>
                    <x-secondary-button type="button" @click="showForm = !showForm" x-text="showForm ? '{{ __('Cancel') }}' : '{{ __('+ Add Stock') }}'"></x-secondary-button>
                </div>
                <form method="POST" action="{{ route('stock.store') }}" class="flex flex-wrap items-end gap-4" x-show="showForm" style="display: none;">
                    @csrf
                    <div class="w-full sm:w-auto sm:flex-1 sm:min-w-[220px]">
                        <x-input-label for="item_id" :value="__('Item')" />
                        <select id="item_id" name="item_id" required x-model="itemId" @change="onItemChange()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select item') }}</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('item_id')" class="mt-2" />
                    </div>

                    <input type="hidden" name="quantity_unit" :value="quantityUnit">
                    <div class="w-full flex gap-4" x-show="altUnit && altUnitRatio" style="display: none;">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" value="base" x-model="quantityUnit" @change="onUnitToggle()" class="text-indigo-600 focus:ring-indigo-500">
                            <span x-text="unitLabel"></span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" value="alt" x-model="quantityUnit" @change="onUnitToggle()" class="text-indigo-600 focus:ring-indigo-500">
                            <span x-text="altUnit"></span>
                        </label>
                    </div>

                    <div class="w-full sm:w-28">
                        <x-input-label for="quantity">
                            {{ __('Quantity') }} <span class="text-gray-400 font-normal" x-show="quantityUnit === 'alt' && altUnit" x-text="'(' + altUnit + ')'"></span>
                        </x-input-label>
                        <x-text-input id="quantity" name="quantity" type="number" step="0.01" min="0.01" x-model.number="quantity" class="mt-1 block w-full" :value="old('quantity')" required />
                        <p class="text-xs text-gray-400 mt-1" x-show="altTotal !== null" style="display: none;">
                            = <span x-text="altTotal"></span> <span x-text="altUnit"></span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1" x-show="quantityUnit === 'alt' && altUnitRatio" style="display: none;">
                            = <span x-text="quantity ? Math.round((quantity / altUnitRatio) * 10000) / 10000 : 0"></span> <span x-text="unitLabel"></span>
                        </p>
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>
                    <div class="w-full sm:w-36">
                        <x-input-label for="unit_cost">
                            {{ __('Purchase Price') }} <span class="text-gray-400 font-normal" x-text="'(per ' + (quantityUnit === 'alt' ? altUnit : (unitLabel || 'unit')) + ')'"></span>
                        </x-input-label>
                        <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" x-model.number="unitCost" class="mt-1 block w-full" :value="old('unit_cost')" required />
                        <x-input-error :messages="$errors->get('unit_cost')" class="mt-2" />
                    </div>
                    <div class="w-full sm:w-auto sm:flex-1 sm:min-w-[180px]">
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" placeholder="{{ __('e.g. Purchase from supplier') }}" />
                    </div>
                    <div class="w-full sm:w-auto">
                        <x-primary-button class="w-full sm:w-auto justify-center">{{ __('Add Stock') }}</x-primary-button>
                    </div>

                    <p class="text-xs text-gray-400 w-full -mt-2">{{ __("Quantity and purchase price are both in whichever unit you pick above, it updates the item's cost.") }}</p>

                    <div class="w-full border-t pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="itemId" style="display: none;">
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
                            {{ __('Only if this item is also sold in a different unit, this updates the item, not just this stock entry. Leave blank if not needed.') }}
                        </p>
                    </div>
                </form>
            </div>

            <form method="GET" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="item_id" :value="__('Filter by item')" />
                    <select id="item_id" name="item_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">{{ __('All items') }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected($filterItemId === $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                        @endforeach
                    </select>
                </div>
                @if ($filterItemId)
                    <a href="{{ route('stock.index') }}" class="text-sm text-gray-500 hover:underline">{{ __('Clear filter') }}</a>
                @endif
            </form>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('stock.partials.table')
                </div>
            </div>
        </div>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
