<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Bill') }}</h2>
    </x-slot>

    <div class="py-12" x-data="invoiceForm()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('invoices.store') }}" @submit="if (lines.length === 0) { alert('{{ __('Add at least one item.') }}'); $event.preventDefault(); }">
                @csrf

                {{-- Customer --}}
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="font-medium text-gray-700 mb-4">{{ __('1. Customer') }}</h3>

                    <input type="hidden" name="customer_id" :value="selectedCustomer ? selectedCustomer.id : ''">

                    <template x-if="!selectedCustomer && !showCustomerForm">
                        <div class="relative">
                            <x-text-input type="text" class="w-full" placeholder="{{ __('Search customer by name or phone...') }}"
                                x-model="customerQuery" @input.debounce.300ms="searchCustomers()" autocomplete="off" />

                            <div class="absolute z-10 bg-white border border-gray-200 rounded-md shadow-md w-full mt-1" x-show="customerQuery.length > 1">
                                <div class="max-h-56 overflow-y-auto" x-show="customerResults.length > 0">
                                    <template x-for="customer in customerResults" :key="customer.id">
                                        <div>
                                            <template x-if="customer.is_active">
                                                <button type="button" @click="selectCustomer(customer)" class="block w-full text-left px-4 py-2 hover:bg-gray-50">
                                                    <div class="text-sm text-gray-800" x-text="customer.name"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="customer.phone"></span>
                                                        <span x-show="customer.address" x-text="'— ' + customer.address"></span>
                                                    </div>
                                                </button>
                                            </template>
                                            <template x-if="!customer.is_active">
                                                <div class="px-4 py-2 bg-gray-50 flex items-center justify-between gap-2">
                                                    <div>
                                                        <div class="text-sm text-gray-500" x-text="customer.name"></div>
                                                        <div class="text-xs text-gray-400">
                                                            <span x-text="customer.phone"></span>
                                                            <span x-show="customer.address" x-text="'— ' + customer.address"></span>
                                                        </div>
                                                        <div class="text-xs text-red-600">{{ __('Inactive customer — activate them first.') }}</div>
                                                    </div>
                                                    <a :href="`{{ url('/customers') }}/${customer.id}/edit`" target="_blank" class="text-xs text-indigo-600 hover:underline shrink-0">
                                                        {{ __('Activate') }}
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <p class="text-sm text-gray-500 px-4 py-3" x-show="customerResults.length === 0">
                                    {{ __('No customer found.') }}
                                    <button type="button" class="text-indigo-600 hover:underline" @click="showCustomerForm = true; newCustomer.name = customerQuery">
                                        {{ __('+ Create new customer') }}
                                    </button>
                                </p>

                                <p class="text-sm text-amber-600 px-4 py-3 border-t border-gray-100" x-show="exactNameMatch">
                                    {{ __('A customer with this exact name already exists above (check the phone/address to be sure). If this is a different person,') }}
                                    <button type="button" class="text-indigo-600 hover:underline font-medium" @click="showCustomerForm = true; newCustomer.name = customerQuery">
                                        {{ __('+ create a new customer with the same name') }}
                                    </button>
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedCustomer">
                        <div class="flex items-center justify-between bg-gray-50 rounded-md px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-800" x-text="selectedCustomer.name"></div>
                                <div class="text-xs text-gray-500">
                                    <span x-text="selectedCustomer.phone"></span>
                                    <span x-show="selectedCustomer.address" x-text="'— ' + selectedCustomer.address"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right" x-show="(selectedCustomer.due || 0) != 0">
                                    <div class="text-xs text-gray-500">{{ __('Existing balance') }}</div>
                                    <div class="text-sm font-semibold" :class="(selectedCustomer.due || 0) > 0 ? 'text-red-600' : 'text-green-600'"
                                        x-text="'₹' + Math.abs(selectedCustomer.due || 0).toFixed(2) + ((selectedCustomer.due || 0) < 0 ? ' CR' : ' due')"></div>
                                </div>
                                <button type="button" class="text-sm text-indigo-600 hover:underline" @click="selectedCustomer = null; customerQuery = ''">
                                    {{ __('Change') }}
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="showCustomerForm">
                        <div class="mt-4 border-t pt-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <x-input-label :value="__('Name')" />
                                    <x-text-input type="text" class="w-full" x-model="newCustomer.name" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Phone')" />
                                    <x-text-input type="text" class="w-full" x-model="newCustomer.phone" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Email (optional)')" />
                                    <x-text-input type="email" class="w-full" x-model="newCustomer.email" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Address (optional)')" />
                                    <x-text-input type="text" class="w-full" x-model="newCustomer.address" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Already Due (if any)')" />
                                    <x-text-input type="number" step="0.01" min="0" class="w-full" x-model.number="newCustomer.opening_balance" placeholder="0.00" />
                                </div>
                                <div>
                                    <x-input-label :value="__('Credit Limit (0 = no limit)')" />
                                    <x-text-input type="number" step="0.01" min="0" class="w-full" x-model.number="newCustomer.credit_limit" placeholder="0.00" />
                                </div>
                            </div>
                            <p class="text-sm text-red-600" x-show="customerError" x-text="customerError"></p>
                            <div class="flex gap-2">
                                <x-primary-button type="button" @click="createCustomer()">{{ __('Save & Select Customer') }}</x-primary-button>
                                <x-secondary-button type="button" @click="showCustomerForm = false">{{ __('Cancel') }}</x-secondary-button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Items --}}
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="font-medium text-gray-700 mb-4">{{ __('2. Items') }}</h3>

                    <div class="mb-3">
                        <select class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @change="selectFromDropdown($event)">
                            <option value="">{{ __('-- Select an item --') }}</option>
                            @foreach ($items as $item)
                                <option value="{{ $item['id'] }}">{{ $item['name'] }} ({{ $item['unit'] }}) — {{ __('Stock') }}: {{ rtrim(rtrim(number_format($item['stock'], 2), '0'), '.') }}</option>
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
                                    <span class="text-xs" :class="item.stock > 0 ? 'text-gray-500' : 'text-red-500'" x-text="'Stock: ' + item.stock"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="lines.length > 0" class="space-y-2">
                        <div class="hidden sm:grid sm:grid-cols-[1fr_7.5rem_8rem_7rem_5.5rem] sm:gap-3 px-1 pb-1 text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                            <div>{{ __('Item') }}</div>
                            <div class="text-right">{{ __('Qty') }}</div>
                            <div class="text-right">{{ __('Rate') }}</div>
                            <div class="text-right">{{ __('Amount') }}</div>
                            <div></div>
                        </div>

                        <template x-for="(line, index) in lines" :key="line.id">
                            <div class="rounded-lg border border-gray-200 p-3 sm:p-0 sm:py-3 sm:border-0 sm:border-b sm:border-gray-100 sm:rounded-none sm:grid sm:grid-cols-[1fr_7.5rem_8rem_7rem_5.5rem] sm:gap-3 sm:items-center">
                                <div class="min-w-0 mb-3 sm:mb-0">
                                    <div class="text-sm font-medium text-gray-800 truncate" x-text="line.name"></div>
                                    <div class="text-xs text-gray-400" x-text="'In stock: ' + availableStock(line) + ' ' + line.unit"></div>
                                    <div class="mt-1 inline-flex rounded-md border border-gray-300 overflow-hidden text-xs" x-show="line.altUnit">
                                        <button type="button" @click="setLineUnit(line, line.baseUnit)" class="px-2 py-0.5"
                                            :class="line.unit === line.baseUnit ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" x-text="line.baseUnit"></button>
                                        <button type="button" @click="setLineUnit(line, line.altUnit)" class="px-2 py-0.5 border-l border-gray-300"
                                            :class="line.unit === line.altUnit ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" x-text="line.altUnit"></button>
                                    </div>
                                    <input type="hidden" :name="`items[${index}][item_id]`" :value="line.item_id">
                                    <input type="hidden" :name="`items[${index}][unit]`" :value="line.unit">
                                </div>

                                <div class="flex flex-wrap items-end gap-3 sm:contents">
                                    <div>
                                        <div class="text-xs text-gray-400 mb-1 sm:hidden">{{ __('Qty') }}</div>
                                        <div class="inline-flex items-stretch rounded-md border border-gray-300 overflow-hidden">
                                            <button type="button" @click="adjust(line, 'quantity', -1, 0.01, availableStock(line))"
                                                class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-r border-gray-300 shrink-0">&minus;</button>
                                            <input type="number" step="0.01" min="0.01" :max="availableStock(line)" x-model.number="line.quantity"
                                                :name="`items[${index}][quantity]`" class="no-spinner w-14 text-center border-0 focus:ring-0">
                                            <button type="button" @click="adjust(line, 'quantity', 1, 0.01, availableStock(line))"
                                                class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-l border-gray-300 shrink-0">+</button>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-400 mb-1 sm:hidden">{{ __('Rate') }}</div>
                                        <div class="inline-flex items-stretch rounded-md border border-gray-300 overflow-hidden">
                                            <button type="button" @click="adjust(line, 'unit_price', -1, 0)"
                                                class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-r border-gray-300 shrink-0">&minus;</button>
                                            <input type="number" step="0.01" min="0" x-model.number="line.unit_price" placeholder="{{ __('Rate') }}"
                                                :name="`items[${index}][unit_price]`" class="no-spinner w-16 text-center border-0 focus:ring-0">
                                            <button type="button" @click="adjust(line, 'unit_price', 1, 0)"
                                                class="w-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 text-base border-l border-gray-300 shrink-0">+</button>
                                        </div>
                                    </div>
                                    <div class="ml-auto sm:ml-0 sm:text-right">
                                        <div class="text-xs text-gray-400 mb-1 sm:hidden">{{ __('Amount') }}</div>
                                        <div class="text-sm font-semibold text-gray-800 sm:font-normal" x-text="'₹' + (line.quantity * (line.unit_price || 0)).toFixed(2)"></div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-gray-100 sm:mt-0 sm:pt-0 sm:border-t-0">
                                    <button type="button" @click="duplicateLine(line)" title="{{ __('Add this item again at a different price') }}"
                                        class="w-9 h-9 flex items-center justify-center rounded-full bg-green-50 text-green-600 hover:bg-green-100 shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    </button>
                                    <button type="button" @click="lines.splice(index, 1)" title="{{ __('Remove') }}"
                                        class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <p class="text-sm text-gray-400" x-show="lines.length === 0">{{ __('No items added yet.') }}</p>
                </div>

                {{-- Totals --}}
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="font-medium text-gray-700 mb-4">{{ __('3. Payment') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="invoice_date" :value="__('Invoice Date')" />
                                <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
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
                            <div class="flex flex-wrap justify-between items-center gap-y-1 text-sm text-gray-600"
                                x-effect="if (discountType === 'percentage') { discount = Math.round(subtotal * (discountPercent || 0) / 100 * 100) / 100 }">
                                <label for="discount">{{ __('Discount') }}</label>
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex rounded-md border border-gray-300 overflow-hidden text-xs">
                                        <button type="button" @click="discountType = 'amount'"
                                            class="px-2 py-1" :class="discountType === 'amount' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">₹</button>
                                        <button type="button" @click="discountType = 'percentage'"
                                            class="px-2 py-1 border-l border-gray-300" :class="discountType === 'percentage' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">%</button>
                                    </div>
                                    <template x-if="discountType === 'percentage'">
                                        <input type="number" step="0.01" min="0" max="100" x-model.number="discountPercent" placeholder="0"
                                            class="w-14 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </template>
                                    <input id="discount" name="discount" type="number" step="0.01" min="0" x-model.number="discount"
                                        :readonly="discountType === 'percentage'"
                                        :class="discountType === 'percentage' ? 'bg-gray-100 text-gray-500' : ''"
                                        class="w-24 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label for="tax">{{ __('Tax') }}</label>
                                <input id="tax" name="tax" type="number" step="0.01" min="0" x-model.number="tax" class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label for="transportation">{{ __('Transportation') }}</label>
                                <input id="transportation" name="transportation" type="number" step="0.01" min="0" x-model.number="transportation" class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex justify-between items-center text-base font-semibold text-gray-900 border-t pt-2">
                                <span>{{ __('Total') }}</span>
                                <span x-text="'₹' + total.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label for="paid_amount">{{ __('Paid Amount') }}</label>
                                <input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" x-model.number="paidAmount" class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex justify-end">
                                <label class="inline-flex items-center gap-2 text-xs text-gray-500">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @change="paidAmount = $event.target.checked ? total : 0">
                                    {{ __('Received full payment now') }}
                                </label>
                            </div>

                            <div x-show="dueAfterBill < -0.004" style="display: none;"
                                class="bg-amber-50 border border-amber-200 rounded-md px-3 py-2 text-xs text-amber-800">
                                ⚠️ {{ __('Paid Amount is') }} <span class="font-semibold" x-text="'₹' + Math.abs(dueAfterBill).toFixed(2)"></span>
                                {{ __('more than what this customer actually owes — they will end up in credit. Double-check the amount if this was a mistake.') }}
                            </div>

                            <div class="pt-2 border-t space-y-1" x-show="selectedCustomer">
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>{{ __('Previous Due') }}</span>
                                    <span x-text="((selectedCustomer && selectedCustomer.due) || 0) < 0
                                        ? '₹' + Math.abs((selectedCustomer && selectedCustomer.due) || 0).toFixed(2) + ' CR'
                                        : '₹' + ((selectedCustomer && selectedCustomer.due) || 0).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>{{ __('This Bill') }}</span>
                                    <span x-text="'₹' + total.toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>{{ __('Payment Now') }}</span>
                                    <span x-text="'₹' + (paidAmount || 0).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-semibold pt-1 border-t" :class="dueAfterBill > 0 ? 'text-red-600' : 'text-green-600'">
                                    <span x-text="dueAfterBill > 0 ? '{{ __('Balance Due') }}' : '{{ __('Settled / In Credit') }}'"></span>
                                    <span x-text="(dueAfterBill < 0 ? '₹' + Math.abs(dueAfterBill).toFixed(2) + ' CR' : '₹' + dueAfterBill.toFixed(2))"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save Bill') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    @endpush

    <script>
        function invoiceForm() {
            return {
                customerQuery: '',
                customerResults: [],
                selectedCustomer: null,
                showCustomerForm: false,
                customerError: '',
                newCustomer: { name: '', phone: '', email: '', address: '', opening_balance: 0, credit_limit: 0 },

                allItems: @json($items),
                itemQuery: '',
                itemResults: [],
                lines: [],
                nextLineId: 1,

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
                discountType: 'amount',
                discountPercent: 0,
                tax: 0,
                transportation: 0,
                paidAmount: 0,

                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + (line.quantity * (line.unit_price || 0)), 0);
                },
                get total() {
                    return Math.max(0, this.subtotal - (this.discount || 0) + (this.tax || 0) + (this.transportation || 0));
                },
                get dueAfterBill() {
                    const existing = (this.selectedCustomer && this.selectedCustomer.due) || 0;
                    return existing + (this.total - (this.paidAmount || 0));
                },
                get exactNameMatch() {
                    const q = this.customerQuery.trim().toLowerCase();
                    if (! q) return false;
                    return this.customerResults.some(c => c.name.trim().toLowerCase() === q);
                },

                async searchCustomers() {
                    if (this.customerQuery.length < 2) { this.customerResults = []; return; }
                    const res = await fetch(`{{ route('customers.search') }}?q=` + encodeURIComponent(this.customerQuery));
                    this.customerResults = await res.json();
                },
                selectCustomer(customer) {
                    this.selectedCustomer = customer;
                    this.customerResults = [];
                    this.showCustomerForm = false;
                },
                async createCustomer() {
                    this.customerError = '';
                    const res = await fetch('{{ route('customers.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.newCustomer),
                    });
                    if (res.ok) {
                        const customer = await res.json();
                        this.selectCustomer(customer);
                    } else {
                        const data = await res.json();
                        this.customerError = Object.values(data.errors || {}).flat().join(' ') || '{{ __('Could not save customer.') }}';
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
                        baseUnit: item.unit,
                        altUnit: item.alt_unit || null,
                        altUnitRatio: item.alt_unit_ratio || null,
                        baseStock: item.stock,
                        unit: item.unit,
                        quantity: 1,
                        unit_price: item.selling_price,
                    });
                    this.itemQuery = '';
                    this.itemResults = [];
                },
                duplicateLine(line) {
                    this.lines.push({
                        id: this.nextLineId++,
                        item_id: line.item_id,
                        name: line.name,
                        baseUnit: line.baseUnit,
                        altUnit: line.altUnit,
                        altUnitRatio: line.altUnitRatio,
                        baseStock: line.baseStock,
                        unit: line.unit,
                        quantity: 1,
                        unit_price: line.unit_price,
                    });
                },
                availableStock(line) {
                    if (line.altUnit && line.altUnitRatio && line.unit === line.altUnit) {
                        return Math.round(line.baseStock * line.altUnitRatio * 100) / 100;
                    }
                    return line.baseStock;
                },
                setLineUnit(line, unit) {
                    if (line.unit === unit) return;
                    line.unit = unit;
                    line.unit_price = '';
                    const max = this.availableStock(line);
                    if (line.quantity > max) line.quantity = max;
                },
            }
        }
    </script>
</x-app-layout>
