@php
    $unitPresets = ['BAG', 'BOX', 'Kg', 'Pcs', 'Ltr', 'Dozen'];
    $altUnitPresets = ['Kg', 'Pcs', 'Gram', 'Ltr'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('items.show', $item) }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('items.index') }}" class="hover:text-gray-600">{{ __('Items') }}</a>
                    <span>/</span>
                    <a href="{{ route('items.show', $item) }}" class="hover:text-gray-600">{{ $item->name }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Edit') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit Item') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('items.show', $item) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" form="item-form" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('Update Item') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12"
        x-data="{
            name: {{ \Illuminate\Support\Js::from(old('name', $item->name)) }},
            unit: {{ \Illuminate\Support\Js::from(old('unit', $item->unit)) }},
            sellingPrice: {{ \Illuminate\Support\Js::from(old('selling_price', $item->selling_price)) }},
            threshold: {{ \Illuminate\Support\Js::from(old('low_stock_threshold', $item->low_stock_threshold)) }},
            sellLoose: {{ old('alt_unit', $item->alt_unit) ? 'true' : 'false' }},
            altUnit: {{ \Illuminate\Support\Js::from(old('alt_unit', $item->alt_unit ?: 'Kg')) }},
            confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false,
        }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                <form method="POST" action="{{ route('items.update', $item) }}" id="item-form" class="lg:col-span-2 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Section 1: Item details --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="hidden lg:flex items-center gap-2.5 mb-5">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">1</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Item details') }}</h3>
                        </div>
                        <p class="lg:hidden text-xs font-semibold tracking-wide text-gray-400 uppercase mb-3">{{ __('Item Details') }}</p>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name">{{ __('Item name') }} <span class="text-red-500">*</span></x-input-label>
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" x-model="name" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="sku">{{ __('Short code (SKU)') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                                <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full uppercase" :value="old('sku', $item->sku)" placeholder="{{ __('e.g. BTA') }}" />
                                <p class="text-xs text-gray-400 mt-1">{{ __('Helps search fast while billing') }}</p>
                                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                            </div>
                            @if ($gstEnabled ?? false)
                                <div>
                                    <x-input-label for="hsn_code">{{ __('HSN code') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                                    <x-text-input id="hsn_code" name="hsn_code" type="text" class="mt-1 block w-full" :value="old('hsn_code', $item->hsn_code)" placeholder="{{ __('e.g. 0701') }}" />
                                    <x-input-error :messages="$errors->get('hsn_code')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="gst_rate">{{ __('GST rate %') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                                    <x-text-input id="gst_rate" name="gst_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('gst_rate', $item->gst_rate)" placeholder="{{ __('e.g. 5') }}" />
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Used to calculate CGST+SGST or IGST on GST invoices.') }}</p>
                                    <x-input-error :messages="$errors->get('gst_rate')" class="mt-2" />
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Section 2: How do you sell it --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="hidden lg:flex items-center gap-2.5 mb-5">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">2</span>
                            <h3 class="font-semibold text-gray-900">{{ __('How do you sell it?') }}</h3>
                        </div>
                        <p class="lg:hidden text-xs font-semibold tracking-wide text-gray-400 uppercase mb-3">{{ __('Unit and Price') }}</p>

                        <x-input-label>{{ __('Main unit') }}</x-input-label>
                        <input type="hidden" name="unit" x-model="unit">
                        <div class="grid grid-cols-3 lg:flex lg:flex-wrap gap-2 mt-1">
                            @foreach ($unitPresets as $preset)
                                <button type="button" @click="unit = '{{ $preset }}'"
                                    class="px-3 py-2 rounded-lg border text-sm font-medium"
                                    :class="unit === '{{ $preset }}' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                    {{ $preset }}
                                </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5" x-show="!{{ \Illuminate\Support\Js::from(in_array($item->unit, $unitPresets)) }}">
                            {{ __('Current unit') }}: <span x-text="unit"></span>
                        </p>
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-5">
                            <div>
                                <x-input-label for="purchase_price" x-text="'{{ __('Purchase price') }} (' + '{{ __('per') }} ' + unit + ')'"></x-input-label>
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                    <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('purchase_price', $item->purchase_price)" />
                                </div>
                                <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="selling_price" x-text="'{{ __('Selling price') }} (' + '{{ __('per') }} ' + unit + ')'"></x-input-label>
                                <span class="text-red-500 text-sm align-top">*</span>
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                    <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="block w-full pl-7" x-model="sellingPrice" required />
                                </div>
                                <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-5 pt-5 border-t border-gray-100">
                            <div>
                                <x-input-label>{{ __('Also sell loose?') }}</x-input-label>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="'{{ __('e.g. a') }} ' + unit + ' {{ __('also sold in a different unit') }}'"></p>
                            </div>
                            <button type="button" role="switch" :aria-checked="sellLoose.toString()" @click="sellLoose = !sellLoose"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 ml-4"
                                :class="sellLoose ? 'bg-brand-600' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="sellLoose ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>

                        <div x-show="sellLoose" style="display: none" class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="alt_unit">{{ __('Loose unit') }}</x-input-label>
                                <input type="hidden" name="alt_unit" :value="sellLoose ? altUnit : ''">
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach ($altUnitPresets as $preset)
                                        <button type="button" @click="altUnit = '{{ $preset }}'"
                                            class="px-3 py-1.5 rounded-lg border text-sm font-medium"
                                            :class="altUnit === '{{ $preset }}' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                            {{ $preset }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input-label for="alt_unit_ratio" x-text="'1 ' + unit + ' = ? ' + altUnit"></x-input-label>
                                <x-text-input id="alt_unit_ratio" name="alt_unit_ratio" type="number" step="0.0001" min="0" class="mt-1 block w-full" :value="old('alt_unit_ratio', $item->alt_unit_ratio)" placeholder="{{ __('e.g. 50') }}" />
                                <x-input-error :messages="$errors->get('alt_unit_ratio')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Stock settings --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="hidden lg:flex items-center gap-2.5 mb-5">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">3</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Stock settings') }}</h3>
                        </div>
                        <p class="lg:hidden text-xs font-semibold tracking-wide text-gray-400 uppercase mb-3">{{ __('Stock Settings') }}</p>

                        <div>
                            <x-input-label for="low_stock_threshold">{{ __('Warn me when stock falls to') }}</x-input-label>
                            <div class="relative mt-1 max-w-xs">
                                <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" step="1" min="0" class="block w-full pr-16" x-model="threshold" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm uppercase" x-text="unit"></span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ __('You get a low stock alert at this level') }}</p>
                            <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description">{{ __('Note') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description', $item->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>
                </form>

                {{-- Status sidebar (desktop) --}}
                <div class="hidden lg:block space-y-4 mt-0">
                    <div class="bg-white shadow-sm rounded-lg p-5 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Item Status') }}</p>
                            <p class="text-sm font-medium {{ $item->is_active ? 'text-green-600' : 'text-gray-500' }} mt-0.5">
                                {{ $item->is_active ? __('Active') : __('Inactive') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Inactive items are hidden from billing.') }}</p>
                        </div>
                        <form method="POST" action="{{ route('items.update', $item) }}" id="toggle-active-form" class="shrink-0">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $item->name }}">
                            <input type="hidden" name="sku" value="{{ $item->sku }}">
                            <input type="hidden" name="unit" value="{{ $item->unit }}">
                            <input type="hidden" name="purchase_price" value="{{ $item->purchase_price }}">
                            <input type="hidden" name="selling_price" value="{{ $item->selling_price }}">
                            <input type="hidden" name="low_stock_threshold" value="{{ $item->low_stock_threshold }}">
                            <input type="hidden" name="alt_unit" value="{{ $item->alt_unit }}">
                            <input type="hidden" name="alt_unit_ratio" value="{{ $item->alt_unit_ratio }}">
                            <input type="hidden" name="description" value="{{ $item->description }}">
                            <input type="hidden" name="is_active" value="{{ $item->is_active ? '0' : '1' }}">
                            <button type="button"
                                @click="confirmForm = $el.closest('form'); confirmName = {{ \Illuminate\Support\Js::from($item->name) }}; confirmActive = {{ $item->is_active ? 'true' : 'false' }}; confirmOpen = true"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $item->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $item->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="item-form" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3">
                {{ __('Update Item') }}
            </button>
        </div>

        @include('customers.partials.status-confirm-modal')
    </div>
</x-app-layout>
