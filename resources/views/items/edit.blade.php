<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Item') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('items.update', $item) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Item Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $item->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="sku" :value="__('SKU / Item Code')" />
                        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $item->sku)" required />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="unit" :value="__('Unit (pcs, kg, box...)')" />
                            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $item->unit)" required />
                            <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="low_stock_threshold" :value="__('Low Stock Alert At')" />
                            <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" step="1" min="0" class="mt-1 block w-full" :value="old('low_stock_threshold', $item->low_stock_threshold)" required />
                            <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="purchase_price" :value="__('Purchase Price')" />
                            <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('purchase_price', $item->purchase_price)" required />
                            <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="selling_price" :value="__('Selling Price')" />
                            <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('selling_price', $item->selling_price)" required />
                            <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="alt_unit" :value="__('Alternate Selling Unit (optional)')" />
                            <x-text-input id="alt_unit" name="alt_unit" type="text" placeholder="e.g. kg" class="mt-1 block w-full" :value="old('alt_unit', $item->alt_unit)" />
                            <x-input-error :messages="$errors->get('alt_unit')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="alt_unit_ratio" :value="__('Conversion (1 unit = ? alt unit)')" />
                            <x-text-input id="alt_unit_ratio" name="alt_unit_ratio" type="number" step="0.0001" min="0" placeholder="e.g. 50" class="mt-1 block w-full" :value="old('alt_unit_ratio', $item->alt_unit_ratio)" />
                            <x-input-error :messages="$errors->get('alt_unit_ratio')" class="mt-2" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 -mt-2">{{ __('Fill this only if this item is also sold in a different unit, e.g. a bag also sold loose in kg. Leave blank if not needed.') }}</p>

                    <div>
                        <x-input-label for="description" :value="__('Description (optional)')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $item->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">{{ __('Active (visible for billing)') }}</span>
                    </label>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Update Item') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
