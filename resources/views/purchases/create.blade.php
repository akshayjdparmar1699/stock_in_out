<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Purchase') }}</h2>
    </x-slot>

    <div class="py-12" x-data="purchaseForm()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('purchases.store') }}" @submit="if (lines.length === 0) { alert('{{ __('Add at least one item.') }}'); $event.preventDefault(); }">
                @csrf

                {{-- Supplier --}}
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="font-medium text-gray-700 mb-4">{{ __('1. Supplier') }}</h3>

                    <input type="hidden" name="supplier_id" :value="selectedSupplier ? selectedSupplier.id : ''">

                    <template x-if="!selectedSupplier && !showSupplierForm">
                        <div class="relative">
                            <x-text-input type="text" class="w-full" placeholder="{{ __('Search supplier by name or phone...') }}"
                                x-model="supplierQuery" @input.debounce.300ms="searchSuppliers()" autocomplete="off" />

                            <div class="absolute z-10 bg-white border border-gray-200 rounded-md shadow-md w-full mt-1" x-show="supplierQuery.length > 1">
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
                                    <button type="button" class="text-indigo-600 hover:underline" @click="showSupplierForm = true; newSupplier.name = supplierQuery">
                                        {{ __('+ Create new supplier') }}
                                    </button>
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedSupplier">
                        <div class="flex items-center justify-between bg-gray-50 rounded-md px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-800" x-text="selectedSupplier.name"></div>
                                <div class="text-xs text-gray-500">
                                    <span x-text="selectedSupplier.phone"></span>
                                    <span x-show="selectedSupplier.address" x-text="'— ' + selectedSupplier.address"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right" x-show="(selectedSupplier.due || 0) != 0">
                                    <div class="text-xs text-gray-500">{{ __('We currently owe') }}</div>
                                    <div class="text-sm font-semibold" :class="(selectedSupplier.due || 0) > 0 ? 'text-red-600' : 'text-green-600'"
                                        x-text="'₹' + Math.abs(selectedSupplier.due || 0).toFixed(2) + ((selectedSupplier.due || 0) < 0 ? ' CR' : '')"></div>
                                </div>
                                <button type="button" class="text-sm text-indigo-600 hover:underline" @click="selectedSupplier = null; supplierQuery = ''">
                                    {{ __('Change') }}
                                </button>
                            </div>
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
                                    <x-input-label :value="__('Already Due to Them (if any)')" />
                                    <x-text-input type="number" step="0.01" min="0" class="w-full" x-model.number="newSupplier.opening_balance" placeholder="0.00" />
                                </div>
                            </div>
                            <p class="text-sm text-red-600" x-show="supplierError" x-text="supplierError"></p>
                            <div class="flex gap-2">
                                <x-primary-button type="button" @click="createSupplier()">{{ __('Save & Select Supplier') }}</x-primary-button>
                                <x-secondary-button type="button" @click="showSupplierForm = false">{{ __('Cancel') }}</x-secondary-button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Items --}}
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="font-medium text-gray-700 mb-4">{{ __('2. Items Received') }}</h3>

                    <div class="mb-3">
                        <select class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @change="selectFromDropdown($event)">
                            <option value="">{{ __('-- Select an item --') }}</option>
                            @foreach ($items as $item)
                                <option value="{{ $item['id'] }}">{{ $item['name'] }} ({{ $item['unit'] }}) — {{ __('Current stock') }}: {{ rtrim(rtrim(number_format($item['stock'], 2), '0'), '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative mb-4">
                        <x-text-input type="text" class="w-full" placeholder="{{ __('...or search item by name or SKU') }}"
                            x-model="itemQuery" @input.debounce.300ms="searchItems()" autocomplete="off" />

                        <div class="absolute z-10 bg-white border border-gray-200 rounded-md shadow-md w-full mt-1 max-h-56 overflow-y-auto" x-show="itemResults.length > 0">
                            <template x-for="item in itemResults" :key="item.id">
                                <button type="button" @click="addItem(item)" class="flex justify-between w-full text-left px-4 py-2 hover:bg-gray-50">
                                    <span>
                                        <span class="text-sm text-gray-800" x-text="item.name"></span>
                                        <span class="text-xs text-gray-500" x-text="'(' + item.sku + ')'"></span>
                                    </span>
                                    <span class="text-xs text-gray-500" x-text="'Current stock: ' + item.stock"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="overflow-x-auto" x-show="lines.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">{{ __('Qty') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">{{ __('Cost/Unit') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">{{ __('Amount') }}</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(line, index) in lines" :key="line.item_id">
                                    <tr>
                                        <td class="py-2 text-sm text-gray-800">
                                            <span x-text="line.name"></span>
                                            <div class="text-xs text-gray-400" x-text="'Current stock: ' + line.stock + ' ' + line.unit"></div>
                                            <input type="hidden" :name="`items[${index}][item_id]`" :value="line.item_id">
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="inline-flex items-stretch rounded-md border border-gray-300 overflow-hidden">
                                                <button type="button" @click="adjust(line, 'quantity', -1, 0.01)"
                                                    class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-r border-gray-300 shrink-0">&minus;</button>
                                                <input type="number" step="0.01" min="0.01" x-model.number="line.quantity"
                                                    :name="`items[${index}][quantity]`" class="no-spinner w-14 text-center border-0 focus:ring-0">
                                                <button type="button" @click="adjust(line, 'quantity', 1, 0.01)"
                                                    class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-l border-gray-300 shrink-0">+</button>
                                            </div>
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="inline-flex items-stretch rounded-md border border-gray-300 overflow-hidden">
                                                <button type="button" @click="adjust(line, 'unit_cost', -1, 0)"
                                                    class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-r border-gray-300 shrink-0">&minus;</button>
                                                <input type="number" step="0.01" min="0" x-model.number="line.unit_cost"
                                                    :name="`items[${index}][unit_cost]`" class="no-spinner w-16 text-center border-0 focus:ring-0">
                                                <button type="button" @click="adjust(line, 'unit_cost', 1, 0)"
                                                    class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-l border-gray-300 shrink-0">+</button>
                                            </div>
                                        </td>
                                        <td class="py-2 text-right text-sm text-gray-800" x-text="'₹' + (line.quantity * line.unit_cost).toFixed(2)"></td>
                                        <td class="py-2 text-right">
                                            <button type="button" class="text-red-500 hover:text-red-700" @click="lines.splice(index, 1)">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-sm text-gray-400" x-show="lines.length === 0">{{ __('No items added yet.') }}</p>
                </div>

                {{-- Totals --}}
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="font-medium text-gray-700 mb-4">{{ __('3. Payment') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="purchase_date" :value="__('Purchase Date')" />
                                <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
                            </div>
                            <div>
                                <x-input-label for="notes" :value="__('Notes (optional)')" />
                                <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>{{ __('Subtotal') }}</span>
                                <span x-text="'₹' + subtotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label for="discount">{{ __('Discount') }}</label>
                                <input id="discount" name="discount" type="number" step="0.01" min="0" x-model.number="discount" class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label for="tax">{{ __('Tax') }}</label>
                                <input id="tax" name="tax" type="number" step="0.01" min="0" x-model.number="tax" class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex justify-between items-center text-base font-semibold text-gray-900 border-t pt-2">
                                <span>{{ __('Total') }}</span>
                                <span x-text="'₹' + total.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label for="paid_amount">{{ __('Paid Now') }}</label>
                                <input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" x-model.number="paidAmount" class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex justify-end">
                                <label class="inline-flex items-center gap-2 text-xs text-gray-500">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @change="paidAmount = $event.target.checked ? total : 0">
                                    {{ __('Paid in full now') }}
                                </label>
                            </div>

                            <div class="flex justify-between items-center text-sm pt-2 border-t" :class="dueAfterPurchase > 0 ? 'text-red-600' : 'text-green-600'">
                                <span class="font-medium">{{ __('We will owe supplier after this') }}</span>
                                <span class="font-semibold" x-text="(dueAfterPurchase < 0 ? '₹' + Math.abs(dueAfterPurchase).toFixed(2) + ' CR' : '₹' + dueAfterPurchase.toFixed(2))"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save Purchase') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function purchaseForm() {
            return {
                supplierQuery: '',
                supplierResults: [],
                selectedSupplier: null,
                showSupplierForm: false,
                supplierError: '',
                newSupplier: { name: '', phone: '', address: '', opening_balance: 0 },

                allItems: @json($items),
                itemQuery: '',
                itemResults: [],
                lines: [],

                selectFromDropdown(event) {
                    const id = parseInt(event.target.value, 10);
                    event.target.value = '';
                    if (!id) return;
                    const item = this.allItems.find(i => i.id === id);
                    if (item) this.addItem(item);
                },

                adjust(line, field, delta, min, max) {
                    let value = Math.round(((parseFloat(line[field]) || 0) + delta) * 100) / 100;
                    if (typeof min === 'number') value = Math.max(min, value);
                    if (typeof max === 'number') value = Math.min(max, value);
                    line[field] = value;
                },

                discount: 0,
                tax: 0,
                paidAmount: 0,

                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + (line.quantity * line.unit_cost), 0);
                },
                get total() {
                    return Math.max(0, this.subtotal - (this.discount || 0) + (this.tax || 0));
                },
                get dueAfterPurchase() {
                    const existing = (this.selectedSupplier && this.selectedSupplier.due) || 0;
                    return existing + (this.total - (this.paidAmount || 0));
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
                        item_id: item.id,
                        name: item.name,
                        unit: item.unit,
                        stock: item.stock,
                        quantity: 1,
                        unit_cost: item.purchase_price,
                    });
                    this.itemQuery = '';
                    this.itemResults = [];
                },
            }
        }
    </script>
</x-app-layout>
