<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.show', $purchase) }}" onclick="window.showCubeLoader()" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('purchases.index') }}" class="hover:text-gray-600">{{ __('Purchases') }}</a>
                    <span>/</span>
                    <a href="{{ route('purchases.show', $purchase) }}" class="hover:text-gray-600">{{ $purchase->purchase_number }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Edit') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit Purchase') }}</h2>
            </div>
            <span class="lg:hidden shrink-0 text-xs text-gray-400">{{ $purchase->purchase_number }}</span>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="purchaseForm()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('purchases.update', $purchase) }}" id="purchase-form"
                @submit="if (lines.length === 0) { alert('{{ __('Add at least one item.') }}'); $event.preventDefault(); }"
                class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                @csrf
                @method('PUT')

                <div class="lg:col-span-2 space-y-4">
                    <x-input-error :messages="$errors->get('items')" />

                    {{-- Supplier --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">1</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Supplier') }}</h3>
                        </div>

                        <input type="hidden" name="supplier_id" :value="selectedSupplier ? selectedSupplier.id : ''">

                        <template x-if="!selectedSupplier && !showSupplierForm">
                            <div class="relative">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                                    <x-text-input type="text" class="w-full pl-9" placeholder="{{ __('Search supplier by name or phone...') }}"
                                        x-model="supplierQuery" @input.debounce.300ms="searchSuppliers()" autocomplete="off" />
                                </div>

                                <div class="absolute z-10 bg-white border border-gray-200 rounded-lg shadow-md w-full mt-1" x-show="supplierQuery.length > 1">
                                    <div class="max-h-56 overflow-y-auto" x-show="supplierResults.length > 0">
                                        <template x-for="supplier in supplierResults" :key="supplier.id">
                                            <button type="button" @click="selectSupplier(supplier)" class="block w-full text-left px-4 py-2 hover:bg-gray-50">
                                                <div class="text-sm text-gray-800" x-text="supplier.name"></div>
                                                <div class="text-xs text-gray-500">
                                                    <span x-text="supplier.phone"></span>
                                                    <span x-show="supplier.address" x-text="'— ' + supplier.address"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>

                                    <p class="text-sm text-gray-500 px-4 py-3" x-show="supplierResults.length === 0">
                                        {{ __('No supplier found.') }}
                                        <button type="button" class="text-brand-600 hover:underline" @click="showSupplierForm = true; newSupplier.name = supplierQuery">
                                            {{ __('+ Create new supplier') }}
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedSupplier">
                            <div class="flex items-center justify-between bg-brand-50 rounded-lg px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-semibold flex items-center justify-center shrink-0 text-sm" x-text="selectedSupplier.name.trim().substring(0, 2).toUpperCase()"></span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate" x-text="selectedSupplier.name"></div>
                                        <div class="text-xs text-gray-500">
                                            <span x-text="selectedSupplier.phone"></span>
                                            &middot;
                                            <span x-text="((selectedSupplier.due || 0) < 0
                                                ? 'Credit ₹' + Math.abs(selectedSupplier.due || 0).toFixed(0)
                                                : 'We owe ₹' + (selectedSupplier.due || 0).toFixed(0))"></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="text-sm font-medium text-brand-700 hover:underline shrink-0 bg-white border border-brand-200 rounded-lg px-3 py-1.5" @click="selectedSupplier = null; supplierQuery = ''">
                                    {{ __('Change') }}
                                </button>
                            </div>
                        </template>

                        <template x-if="showSupplierForm">
                            <div class="mt-4 border-t pt-4 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label :value="__('Name')" />
                                        <x-text-input type="text" class="w-full" x-model="newSupplier.name" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Phone (optional)')" />
                                        <x-text-input type="text" class="w-full" x-model="newSupplier.phone" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Address (optional)')" />
                                        <x-text-input type="text" class="w-full" x-model="newSupplier.address" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Already due to them (if any)')" />
                                        <x-text-input type="number" step="0.01" min="0" class="w-full" x-model.number="newSupplier.opening_balance" placeholder="0.00" />
                                    </div>
                                </div>
                                <p class="text-sm text-red-600" x-show="supplierError" x-text="supplierError"></p>
                                <div class="flex gap-2">
                                    <x-primary-button type="button" @click="createSupplier()">{{ __('Save & select supplier') }}</x-primary-button>
                                    <x-secondary-button type="button" @click="showSupplierForm = false">{{ __('Cancel') }}</x-secondary-button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Items --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">2</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Items received') }}</h3>
                        </div>

                        <div class="relative mb-4">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <x-text-input type="text" class="w-full pl-9" placeholder="{{ __('Type item name or SKU to add (e.g. bat, BTA)') }}"
                                x-model="itemQuery" @input.debounce.300ms="searchItems()" autocomplete="off" />

                            <div class="absolute z-10 bg-white border border-gray-200 rounded-lg shadow-md w-full mt-1 max-h-56 overflow-y-auto" x-show="itemResults.length > 0">
                                <template x-for="item in itemResults" :key="item.id">
                                    <button type="button" @click="addItem(item)" class="flex justify-between w-full text-left px-4 py-2 hover:bg-gray-50">
                                        <span>
                                            <span class="text-sm text-gray-800" x-text="item.name"></span>
                                            <span class="text-xs text-gray-500" x-text="'(' + item.sku + ')'"></span>
                                        </span>
                                        <span class="text-xs text-gray-500" x-text="'Now in stock: ' + item.stock"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="lines.length > 0" class="space-y-3">
                            <div class="hidden lg:grid lg:grid-cols-[1fr_8rem_8rem_7rem_5rem] lg:gap-3 px-1 pb-1 text-xs font-medium text-gray-400 uppercase border-b border-gray-100">
                                <div>{{ __('Item') }}</div>
                                <div class="text-right">{{ __('Qty') }}</div>
                                <div class="text-right">{{ __('Cost/Unit') }}</div>
                                <div class="text-right">{{ __('Amount') }}</div>
                                <div></div>
                            </div>

                            <template x-for="(line, index) in lines" :key="line.id">
                                <div class="rounded-xl border border-gray-100 p-3 lg:border-0 lg:rounded-none lg:border-b lg:border-gray-100 lg:p-0 lg:py-3 lg:grid lg:grid-cols-[1fr_8rem_8rem_7rem_5rem] lg:gap-3 lg:items-center">
                                    <div class="flex items-start justify-between gap-2 lg:block min-w-0">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 truncate" x-text="line.name"></div>
                                            <div class="text-xs text-gray-400 mt-0.5" x-text="'Now in stock ' + line.stock + ' ' + line.unit"></div>

                                            <template x-if="line.altUnitRatio">
                                                <div class="mt-1.5 inline-flex rounded-md border border-gray-300 overflow-hidden text-xs">
                                                    <button type="button" @click="line.quantityUnit = 'base'; line.unit_cost = null" class="px-2.5 py-1 font-medium"
                                                        :class="line.quantityUnit === 'base' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" x-text="line.unit"></button>
                                                    <button type="button" @click="line.quantityUnit = 'alt'; line.unit_cost = null" class="px-2.5 py-1 font-medium border-l border-gray-300"
                                                        :class="line.quantityUnit === 'alt' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" x-text="line.altUnit"></button>
                                                </div>
                                            </template>
                                            <button type="button" class="text-xs text-brand-600 hover:underline block mt-1.5" @click="line.showAltConfig = !line.showAltConfig"
                                                x-text="line.altUnit ? ('Alt unit: ' + line.altUnit) : '+ Set alt unit'"></button>
                                        </div>
                                        <div class="lg:hidden flex items-center gap-2 shrink-0">
                                            <button type="button" @click="duplicateLine(line)" title="{{ __('Add this item again at a different price') }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-full border border-green-200 text-green-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            </button>
                                            <button type="button" @click="lines.splice(index, 1)" title="{{ __('Remove') }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-full border border-red-200 text-red-500">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                        <input type="hidden" :name="`items[${index}][item_id]`" :value="line.item_id">
                                        <input type="hidden" :name="`items[${index}][quantity_unit]`" :value="line.quantityUnit">
                                    </div>

                                    <div class="lg:col-span-5 grid grid-cols-2 gap-3 mt-3 lg:mt-0" x-show="line.showAltConfig" style="display: none;">
                                        <div>
                                            <label class="text-xs text-gray-500">{{ __('Alternate selling unit (e.g. Kg)') }}</label>
                                            <input type="text" x-model="line.altUnit" :name="`items[${index}][alt_unit]`" placeholder="{{ __('e.g. Kg') }}"
                                                class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">{{ __('1 unit = ? alt unit') }}</label>
                                            <input type="number" step="0.0001" min="0" x-model.number="line.altUnitRatio" :name="`items[${index}][alt_unit_ratio]`" placeholder="{{ __('e.g. 50') }}"
                                                class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 mt-3 lg:mt-0 lg:contents">
                                        <div class="lg:ml-auto">
                                            <div class="text-xs text-gray-400 mb-1 lg:hidden">{{ __('Qty') }}</div>
                                            <div class="inline-flex items-stretch rounded-md border border-gray-300 overflow-hidden">
                                                <button type="button" @click="adjust(line, 'quantity', -1, 0.01)"
                                                    class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-r border-gray-300 shrink-0">&minus;</button>
                                                <input type="number" step="0.01" min="0.01" x-model.number="line.quantity"
                                                    :name="`items[${index}][quantity]`" class="no-spinner w-14 text-center border-0 focus:ring-0">
                                                <button type="button" @click="adjust(line, 'quantity', 1, 0.01)"
                                                    class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-l border-gray-300 shrink-0">+</button>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-1 lg:hidden">{{ __('Cost/Unit') }}</div>
                                            <div class="relative">
                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                                <input type="number" step="0.01" min="0" x-model.number="line.unit_cost"
                                                    :name="`items[${index}][unit_cost]`" class="no-spinner w-24 pl-6 pr-2 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-400 mb-1 lg:hidden">{{ __('Amount') }}</div>
                                            <div class="text-sm font-semibold text-gray-900" x-text="'₹' + (line.quantity * (line.unit_cost || 0)).toFixed(0)"></div>
                                        </div>
                                    </div>

                                    <div class="hidden lg:flex items-center gap-1 justify-self-end">
                                        <button type="button" @click="duplicateLine(line)" title="{{ __('Add this item again at a different price') }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-green-600 border border-green-200 hover:bg-green-50">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </button>
                                        <button type="button" @click="lines.splice(index, 1)" title="{{ __('Remove') }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-red-500 border border-red-200 hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <p class="text-sm text-gray-400 text-center py-4" x-show="lines.length === 0">{{ __('No items added yet.') }}</p>
                    </div>

                    {{-- Purchase date + note --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="purchase_date" :value="__('Purchase date')" />
                            <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full" value="{{ $purchase->purchase_date->format('Y-m-d') }}" required />
                        </div>
                        <div>
                            <x-input-label for="notes">{{ __('Supplier bill no. or note') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full" value="{{ $purchase->notes }}" />
                        </div>
                    </div>
                </div>

                {{-- Totals sidebar --}}
                <div class="mt-4 lg:mt-0 lg:sticky lg:top-20">
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Totals') }}</h3>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500" x-text="'{{ __('Subtotal') }} (' + lines.length + ' {{ __('items') }})'"></span>
                            <span class="font-medium text-gray-900" x-text="'₹' + subtotal.toFixed(0)"></span>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <label for="discount" class="text-gray-500">{{ __('Discount') }}</label>
                            <input id="discount" name="discount" type="number" step="0.01" min="0" x-model.number="discount" class="no-spinner w-24 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <label for="tax" class="text-gray-500">{{ __('Tax') }}</label>
                            <input id="tax" name="tax" type="number" step="0.01" min="0" x-model.number="tax" class="no-spinner w-24 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <label for="transportation" class="text-gray-500">{{ __('Transport') }}</label>
                            <input id="transportation" name="transportation" type="number" step="0.01" min="0" x-model.number="transportation" class="no-spinner w-24 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        </div>

                        <div class="flex justify-between items-center text-base font-bold text-gray-900 border-t border-gray-100 pt-3">
                            <span>{{ __('Total') }}</span>
                            <span x-text="'₹' + total.toFixed(0)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>{{ __('Paid so far') }}</span>
                            <span>&#8377;{{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold pt-1.5 border-t border-gray-100"
                            :class="(total - {{ (float) $purchase->paid_amount }}) > 0 ? 'text-red-600' : 'text-green-600'">
                            <span x-text="(total - {{ (float) $purchase->paid_amount }}) > 0 ? '{{ __('New balance due') }}' : '{{ __('Settled') }}'"></span>
                            <span x-text="(total - {{ (float) $purchase->paid_amount }}) < 0
                                ? '₹' + Math.abs(total - {{ (float) $purchase->paid_amount }}).toFixed(0) + ' CR'
                                : '₹' + (total - {{ (float) $purchase->paid_amount }}).toFixed(0)"></span>
                        </div>

                        <p class="text-xs text-gray-400">{{ __('Payments already recorded on this purchase are not changed here — use the payment form on the purchase page for that.') }}</p>

                        <div class="hidden lg:block pt-2">
                            <button type="submit" class="w-full px-4 py-3 bg-brand-700 text-white text-sm font-semibold rounded-lg hover:bg-brand-800">
                                {{ __('Update purchase') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="purchase-form" class="w-full px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Update purchase') }}
            </button>
        </div>
    </div>

    <script>
        function purchaseForm() {
            return {
                supplierQuery: '',
                supplierResults: [],
                selectedSupplier: @json($selectedSupplier),
                showSupplierForm: false,
                supplierError: '',
                newSupplier: { name: '', phone: '', address: '', opening_balance: 0 },

                allItems: @json($items),
                itemQuery: '',
                itemResults: [],
                lines: @json($initialLines),
                nextLineId: {{ count($initialLines) + 1 }},

                init() {
                    this.lines = this.lines.map(line => {
                        const item = this.allItems.find(i => i.id === line.item_id) || {};
                        return {
                            ...line,
                            altUnit: item.alt_unit || null,
                            altUnitRatio: item.alt_unit_ratio || null,
                            quantityUnit: 'base',
                            showAltConfig: false,
                        };
                    });
                },

                adjust(line, field, delta, min, max) {
                    let value = Math.round(((parseFloat(line[field]) || 0) + delta) * 100) / 100;
                    if (typeof min === 'number') value = Math.max(min, value);
                    if (typeof max === 'number') value = Math.min(max, value);
                    line[field] = value;
                },

                discount: {{ (float) $purchase->discount }},
                tax: {{ (float) $purchase->tax }},
                transportation: {{ (float) $purchase->transportation }},

                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + (line.quantity * (line.unit_cost || 0)), 0);
                },
                get total() {
                    return Math.max(0, this.subtotal - (this.discount || 0) + (this.tax || 0) + (this.transportation || 0));
                },

                async searchSuppliers() {
                    if (this.supplierQuery.length < 2) { this.supplierResults = []; return; }
                    const res = await fetch(`{{ route('suppliers.search') }}?q=` + encodeURIComponent(this.supplierQuery));
                    this.supplierResults = await res.json();
                },
                selectSupplier(supplier) {
                    this.selectedSupplier = supplier;
                    this.supplierResults = [];
                    this.showSupplierForm = false;
                },
                async createSupplier() {
                    this.supplierError = '';
                    const res = await fetch('{{ route('suppliers.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.newSupplier),
                    });
                    if (res.ok) {
                        const supplier = await res.json();
                        this.selectSupplier(supplier);
                    } else {
                        const data = await res.json();
                        this.supplierError = Object.values(data.errors || {}).flat().join(' ') || '{{ __('Could not save supplier.') }}';
                    }
                },

                async searchItems() {
                    if (this.itemQuery.length < 1) { this.itemResults = []; return; }
                    const res = await fetch(`{{ route('items.search') }}?q=` + encodeURIComponent(this.itemQuery));
                    this.itemResults = await res.json();
                },
                addItem(item) {
                    if (this.lines.find(l => l.item_id === item.id)) {
                        this.itemQuery = '';
                        this.itemResults = [];
                        return;
                    }
                    this.lines.push({
                        id: this.nextLineId++,
                        item_id: item.id,
                        name: item.name,
                        unit: item.unit,
                        altUnit: item.alt_unit || null,
                        altUnitRatio: item.alt_unit_ratio || null,
                        quantityUnit: 'base',
                        showAltConfig: false,
                        stock: item.stock,
                        quantity: 1,
                        unit_cost: item.purchase_price,
                    });
                    this.itemQuery = '';
                    this.itemResults = [];
                },
                duplicateLine(line) {
                    this.lines.push({
                        id: this.nextLineId++,
                        item_id: line.item_id,
                        name: line.name,
                        unit: line.unit,
                        altUnit: line.altUnit,
                        altUnitRatio: line.altUnitRatio,
                        quantityUnit: line.quantityUnit,
                        showAltConfig: false,
                        stock: line.stock,
                        quantity: 1,
                        unit_cost: line.unit_cost,
                    });
                },
            }
        }
    </script>
</x-app-layout>
