<div x-data="{ type: 'in', quantityUnit: 'base', reason: '' }">
    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Adjust stock') }}</h3>

    <form method="POST" action="{{ route('stock.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">
        <input type="hidden" name="type" :value="type">
        <input type="hidden" name="reason" :value="reason">

        <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="type = 'in'"
                class="px-3 py-2 rounded-lg border text-sm font-medium"
                :class="type === 'in' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                {{ __('+ Add stock') }}
            </button>
            <button type="button" @click="type = 'out'"
                class="px-3 py-2 rounded-lg border text-sm font-medium"
                :class="type === 'out' ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                {{ __('- Remove stock') }}
            </button>
        </div>

        <div>
            <x-input-label for="adjust_quantity_{{ $item->id }}">{{ __('Quantity') }}</x-input-label>
            <div class="flex gap-2 mt-1">
                <x-text-input id="adjust_quantity_{{ $item->id }}" name="quantity" type="number" step="0.01" min="0.01" class="block w-full" required />
                @if ($item->hasAltUnit())
                    <select name="quantity_unit" x-model="quantityUnit" class="border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="base">{{ $item->unit }}</option>
                        <option value="alt">{{ $item->alt_unit }}</option>
                    </select>
                @else
                    <span class="shrink-0 inline-flex items-center px-3 rounded-md bg-gray-100 text-gray-600 text-sm font-medium">{{ $item->unit }}</span>
                @endif
            </div>
        </div>

        <div x-show="type === 'in'">
            <x-input-label for="adjust_unit_cost_{{ $item->id }}">{{ __('Unit cost') }} <span class="text-gray-400 text-xs font-normal">({{ __('per') }} <span x-text="quantityUnit === 'alt' ? '{{ $item->alt_unit }}' : '{{ $item->unit }}'"></span>)</span></x-input-label>
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                <x-text-input id="adjust_unit_cost_{{ $item->id }}" name="unit_cost" type="number" step="0.01" min="0" class="block w-full pl-7" :value="$item->purchase_price" />
            </div>
        </div>

        <div>
            <x-input-label>{{ __('Reason') }}</x-input-label>
            <div class="grid grid-cols-3 gap-2 mt-1" x-show="type === 'in'">
                @foreach (['Opening stock', 'Return from customer', 'Correction'] as $preset)
                    <button type="button" @click="reason = '{{ $preset }}'"
                        class="px-2 py-1.5 rounded-md border text-xs font-medium"
                        :class="reason === '{{ $preset }}' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                        {{ __($preset) }}
                    </button>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-2 mt-1" x-show="type === 'out'" style="display: none">
                @foreach (['Damaged / expired', 'Correction'] as $preset)
                    <button type="button" @click="reason = '{{ $preset }}'"
                        class="px-2 py-1.5 rounded-md border text-xs font-medium"
                        :class="reason === '{{ $preset }}' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                        {{ __($preset) }}
                    </button>
                @endforeach
            </div>
            <x-text-input type="text" class="mt-2 block w-full" x-model="reason" placeholder="{{ __('Or type a reason') }}" />
        </div>

        <button type="submit" class="w-full font-semibold text-sm rounded-lg py-2.5 text-white" :class="type === 'out' ? 'bg-red-600 hover:bg-red-700' : 'bg-brand-700 hover:bg-brand-800'">
            {{ __('Save adjustment') }}
        </button>

        <p class="text-xs text-gray-400 text-center">{{ __('For stock bought from a supplier, use New Purchase instead so the supplier khata also updates.') }}</p>
    </form>
</div>
