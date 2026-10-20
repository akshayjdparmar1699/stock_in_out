@php
    $itemsMeta = $items->keyBy('id')->map(fn ($item) => [
        'unit' => $item->unit,
        'altUnit' => $item->alt_unit,
        'altUnitRatio' => $item->alt_unit_ratio !== null ? (float) $item->alt_unit_ratio : null,
        'purchasePrice' => (float) $item->purchase_price,
        'stock' => (float) ($stockByItem[$item->id] ?? 0),
    ]);
    $inReasons = ['Opening stock', 'Return from customer', 'Found extra in count', 'Other'];
    $outReasons = ['Damaged / expired', 'Correction', 'Other'];
@endphp
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
                    <span class="text-gray-700 font-medium">{{ __('Adjust stock') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Adjust stock') }}</h2>
            </div>
            <div class="hidden lg:block">
                <a href="{{ route('stock.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12"
        x-data="{
            type: {{ \Illuminate\Support\Js::from(old('type', 'in')) }},
            itemId: {{ \Illuminate\Support\Js::from(old('item_id', '')) }},
            quantity: {{ \Illuminate\Support\Js::from(old('quantity', '')) }},
            quantityUnit: {{ \Illuminate\Support\Js::from(old('quantity_unit', 'base')) }},
            unitCost: {{ \Illuminate\Support\Js::from(old('unit_cost', '')) }},
            reasonPreset: {{ \Illuminate\Support\Js::from(old('reason', 'Opening stock')) }},
            customReason: '',
            note: '',
            date: {{ \Illuminate\Support\Js::from(old('movement_date', now()->format('Y-m-d'))) }},
            itemsMeta: {{ \Illuminate\Support\Js::from($itemsMeta) }},
            inReasons: {{ \Illuminate\Support\Js::from($inReasons) }},
            outReasons: {{ \Illuminate\Support\Js::from($outReasons) }},

            get meta() { return this.itemsMeta[this.itemId] || null; },
            get unitLabel() { return this.meta ? this.meta.unit : ''; },
            get hasAltUnit() { return !!(this.meta && this.meta.altUnit && this.meta.altUnitRatio); },
            get stockBase() { return this.meta ? this.meta.stock : 0; },
            get stockAlt() { return this.hasAltUnit ? Math.round(this.stockBase * this.meta.altUnitRatio * 100) / 100 : null; },
            get quantityBase() {
                const q = parseFloat(this.quantity) || 0;
                if (this.quantityUnit === 'alt' && this.hasAltUnit) return q / this.meta.altUnitRatio;
                return q;
            },
            get afterBase() {
                const delta = this.quantityBase;
                return this.type === 'out' ? this.stockBase - delta : this.stockBase + delta;
            },
            get afterAlt() { return this.hasAltUnit ? Math.round(this.afterBase * this.meta.altUnitRatio * 100) / 100 : null; },
            get reason() { return this.reasonPreset === 'Other' ? this.customReason : this.reasonPreset; },
            get combinedReason() {
                const note = this.note.trim();
                const reason = (this.reason || '').trim();
                if (!note) return reason;
                return reason ? (reason + ' — ' + note) : note;
            },
            get canSave() { return !!this.itemId && parseFloat(this.quantity) > 0; },

            onItemChange() {
                this.quantityUnit = 'base';
                this.unitCost = this.meta ? (this.meta.purchasePrice || '') : '';
            },
            onTypeChange(newType) {
                this.type = newType;
                this.reasonPreset = newType === 'in' ? this.inReasons[0] : this.outReasons[0];
            },
            fmt(n) {
                n = Math.round((n + Number.EPSILON) * 100) / 100;
                return (Math.round(n) === n ? n.toString() : n.toFixed(2)).replace(/\.00$/, '');
            },
        }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('stock.store') }}" id="stock-form" class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                @csrf
                <input type="hidden" name="redirect_to_index" value="1">
                <input type="hidden" name="type" :value="type">
                <input type="hidden" name="quantity_unit" :value="quantityUnit">
                <input type="hidden" name="reason" :value="combinedReason">
                <input type="hidden" name="movement_date" :value="date">

                <div class="lg:col-span-2 space-y-4">
                    {{-- Info banner --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center justify-between gap-3 flex-wrap">
                        <p class="text-sm text-blue-900 flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            <span>
                                <span class="font-semibold">{{ __('Bought from a supplier?') }}</span>
                                {{ __('Use') }} <a href="{{ route('purchases.create') }}" class="underline font-medium lg:hidden">{{ __('New Purchase') }}</a><span class="hidden lg:inline">New Purchase</span> {{ __('instead, so the supplier bill and payment are saved too. This page is only for corrections without a bill.') }}
                            </span>
                        </p>
                        <a href="{{ route('purchases.create') }}" class="hidden lg:inline-flex shrink-0 items-center px-4 py-2 bg-white text-brand-700 text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-50">
                            {{ __('Go to New Purchase') }}
                        </a>
                    </div>

                    {{-- Type toggle --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('What do you want to do?') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="onTypeChange('in')"
                                class="flex items-start gap-3 p-4 rounded-xl border-2 text-left"
                                :class="type === 'in' ? 'border-brand-500 bg-brand-50/40' : 'border-gray-200 hover:bg-gray-50'">
                                <span class="w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center shrink-0 font-bold text-lg">+</span>
                                <span>
                                    <span class="block font-semibold text-gray-900">{{ __('Add stock') }}</span>
                                    <span class="block text-xs text-gray-400 mt-0.5">{{ __('Opening stock, return, found extra') }}</span>
                                </span>
                            </button>
                            <button type="button" @click="onTypeChange('out')"
                                class="flex items-start gap-3 p-4 rounded-xl border-2 text-left"
                                :class="type === 'out' ? 'border-red-400 bg-red-50/40' : 'border-gray-200 hover:bg-gray-50'">
                                <span class="w-9 h-9 rounded-full bg-red-100 text-red-700 flex items-center justify-center shrink-0 font-bold text-lg">&minus;</span>
                                <span>
                                    <span class="block font-semibold text-gray-900">{{ __('Remove stock') }}</span>
                                    <span class="block text-xs text-gray-400 mt-0.5">{{ __('Damaged, expired, lost, used') }}</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Form fields --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                        <div>
                            <x-input-label for="item_id">{{ __('Item') }} <span class="text-red-500">*</span></x-input-label>
                            <select id="item_id" name="item_id" required x-model="itemId" @change="onItemChange()"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">{{ __('Select item') }}</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->sku }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('item_id')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1.5" x-show="itemId" style="display: none;">
                                {{ __('Now in stock:') }} <span class="font-semibold text-gray-700" x-text="fmt(stockBase) + ' ' + unitLabel"></span>
                                <template x-if="hasAltUnit"><span> = <span x-text="fmt(stockAlt) + ' ' + meta.altUnit"></span></span></template>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="quantity">{{ __('Quantity') }} <span class="text-red-500">*</span></x-input-label>
                                <div class="flex gap-2 mt-1">
                                    <x-text-input id="quantity" name="quantity" type="number" step="0.01" min="0.01" x-model.number="quantity" class="block w-full" placeholder="0" required />
                                    <template x-if="hasAltUnit">
                                        <div class="inline-flex rounded-md border border-gray-300 overflow-hidden text-sm shrink-0">
                                            <button type="button" @click="quantityUnit = 'base'; unitCost = meta ? (meta.purchasePrice || '') : ''" class="px-3 font-medium" :class="quantityUnit === 'base' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'" x-text="unitLabel"></button>
                                            <button type="button" @click="quantityUnit = 'alt'; unitCost = ''" class="px-3 font-medium border-l border-gray-300" :class="quantityUnit === 'alt' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'" x-text="meta ? meta.altUnit : ''"></button>
                                        </div>
                                    </template>
                                    <template x-if="itemId && !hasAltUnit">
                                        <span class="shrink-0 inline-flex items-center px-3 rounded-md bg-gray-100 text-gray-600 text-sm font-medium" x-text="unitLabel"></span>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-400 mt-1" x-show="hasAltUnit" style="display: none;">
                                    1 <span x-text="unitLabel"></span> = <span x-text="meta ? meta.altUnitRatio : ''"></span> <span x-text="meta ? meta.altUnit : ''"></span>
                                </p>
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>
                            <div x-show="type === 'in'">
                                <x-input-label for="unit_cost">
                                    {{ __('Cost price per') }} <span x-text="quantityUnit === 'alt' ? (meta ? meta.altUnit : '') : unitLabel"></span>
                                    <span class="text-gray-400 font-normal">({{ __('optional') }})</span>
                                </x-input-label>
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                    <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" x-model.number="unitCost" class="block w-full pl-7" placeholder="0" />
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Leave empty to keep the current cost') }}</p>
                                <x-input-error :messages="$errors->get('unit_cost')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label>{{ __('Reason') }} <span class="text-red-500">*</span></x-input-label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <template x-for="preset in (type === 'in' ? inReasons : outReasons)" :key="preset">
                                    <button type="button" @click="reasonPreset = preset"
                                        class="px-3 py-1.5 rounded-lg border text-sm font-medium"
                                        :class="reasonPreset === preset ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                                        x-text="preset"></button>
                                </template>
                            </div>
                            <x-text-input type="text" class="mt-2 block w-full" x-model="customReason" x-show="reasonPreset === 'Other'" placeholder="{{ __('Type the reason') }}" style="display: none;" />
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="note">{{ __('Note') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></x-input-label>
                                <x-text-input id="note" type="text" class="mt-1 block w-full" x-model="note" placeholder="{{ __('e.g. 2 bags got wet in godown') }}" maxlength="180" />
                            </div>
                            <div>
                                <x-input-label for="date">{{ __('Date') }}</x-input-label>
                                <x-text-input id="date" type="date" class="mt-1 block w-full" x-model="date" :max="now()->format('Y-m-d')" />
                                <x-input-error :messages="$errors->get('movement_date')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- After-saving preview sidebar --}}
                <div class="mt-4 lg:mt-0 lg:sticky lg:top-20">
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                        <p class="text-xs font-semibold text-gray-400 tracking-wide uppercase">{{ __('After saving') }}</p>

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400">{{ __('Now') }}</p>
                                <p class="text-lg font-bold text-gray-900" x-text="itemId ? (fmt(stockBase) + ' ' + unitLabel) : '—'"></p>
                            </div>
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </span>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">{{ __('After') }}</p>
                                <p class="text-lg font-bold" :class="type === 'out' ? 'text-red-600' : 'text-green-600'" x-text="itemId ? (fmt(afterBase) + ' ' + unitLabel) : '—'"></p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 text-right -mt-2" x-show="hasAltUnit" style="display: none;">
                            <span x-text="fmt(stockAlt) + ' ' + (meta ? meta.altUnit : '')"></span> &rarr; <span x-text="fmt(afterAlt) + ' ' + (meta ? meta.altUnit : '')"></span>
                        </p>

                        <div class="flex items-center justify-between text-sm border-t border-gray-100 pt-3">
                            <span class="text-gray-500">{{ __('Reason') }}</span>
                            <span class="font-medium text-gray-900 text-right" x-text="combinedReason || '—'"></span>
                        </div>

                        <button type="submit" :disabled="!canSave"
                            class="hidden lg:flex w-full items-center justify-center px-4 py-3 text-sm font-semibold rounded-lg text-white disabled:cursor-not-allowed"
                            :class="!canSave ? 'bg-gray-300' : (type === 'out' ? 'bg-red-600 hover:bg-red-700' : 'bg-brand-700 hover:bg-brand-800')"
                            x-text="type === 'out' ? '{{ __('Save, remove from stock') }}' : '{{ __('Save, add to stock') }}'"></button>

                        <p class="text-xs text-gray-400 text-center">
                            {{ __('Every manual change is saved in the stock ledger with your name, so owners can check it later.') }}
                        </p>
                    </div>
                </div>
            </form>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-500">{{ __('Now') }} <span class="font-semibold text-gray-900" x-text="itemId ? (fmt(stockBase) + ' ' + unitLabel) : '—'"></span></span>
                <span class="text-gray-400">&rarr;</span>
                <span class="text-right text-gray-500">{{ __('After') }} <span class="font-semibold" :class="type === 'out' ? 'text-red-600' : 'text-green-600'" x-text="itemId ? (fmt(afterBase) + ' ' + unitLabel) : '—'"></span></span>
            </div>
            <button type="submit" form="stock-form" :disabled="!canSave"
                class="w-full px-4 py-2.5 text-sm font-semibold rounded-lg text-white disabled:cursor-not-allowed"
                :class="!canSave ? 'bg-gray-300' : (type === 'out' ? 'bg-red-600' : 'bg-brand-700')"
                x-text="type === 'out' ? '{{ __('Save, remove from stock') }}' : '{{ __('Save, add to stock') }}'"></button>
        </div>
    </div>
</x-app-layout>
