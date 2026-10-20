<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('stock.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('stock.index') }}" class="hover:text-gray-600">{{ __('Stock') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Edit entry') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit Stock Entry') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6"
                x-data="{
                    quantity: {{ (float) $movement->quantity }},
                    altUnit: '{{ old('alt_unit', $movement->item->alt_unit) }}',
                    altUnitRatio: {{ old('alt_unit_ratio', $movement->item->alt_unit_ratio) ? (float) old('alt_unit_ratio', $movement->item->alt_unit_ratio) : 'null' }},
                    get altTotal() {
                        if (!this.altUnit || !this.altUnitRatio || !this.quantity) return null;
                        return Math.round(this.quantity * this.altUnitRatio * 100) / 100;
                    },
                }">
                <form method="POST" action="{{ route('stock.update', $movement) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label :value="__('Item')" />
                        <div class="mt-1 text-sm text-gray-800 font-medium">{{ $movement->item->name }} ({{ $movement->item->sku }})</div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Item cannot be changed here — delete this entry and add a fresh one instead.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.01" min="0.01" x-model.number="quantity" class="mt-1 block w-full" :value="old('quantity', $movement->quantity)" required />
                        <p class="text-xs text-gray-400 mt-1" x-show="altTotal !== null" style="display: none;">
                            = <span x-text="altTotal"></span> <span x-text="altUnit"></span>
                        </p>
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="unit_cost" :value="__('Purchase Price (per unit)')" />
                        <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('unit_cost', $movement->batch->unit_cost ?? $movement->item->purchase_price)" required />
                        <p class="text-xs text-gray-400 mt-1">{{ __('Whatever you paid for this — updates the item\'s cost.') }}</p>
                        <x-input-error :messages="$errors->get('unit_cost')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason', $movement->reason)" />
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>

                    <div class="border-t pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            {{ __('This updates the item, not just this stock entry. Leave blank to remove.') }}
                        </p>
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('stock.index') }}" class="text-sm text-gray-500 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Update Stock Entry') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('stock.destroy', $movement) }}" class="mt-4 text-center"
                onsubmit="return confirm('{{ __('Delete this stock entry? This cannot be undone.') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete this entry') }}</button>
            </form>
        </div>
    </div>
</x-app-layout>
