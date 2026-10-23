<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.show', $invoice) }}" onclick="window.showCubeLoader()" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('invoices.index') }}" class="hover:text-gray-600">{{ __('Invoices') }}</a>
                    <span>/</span>
                    <a href="{{ route('invoices.show', $invoice) }}" class="hover:text-gray-600">{{ $invoice->invoice_number }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Edit') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit Bill') }}</h2>
            </div>
            <span class="text-sm text-gray-400 shrink-0">{{ $invoice->invoice_number }}</span>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="invoiceForm()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoice-form"
                    @submit="if (lines.length === 0) { alert('{{ __('Add at least one item.') }}'); $event.preventDefault(); return; } if (!selectedCustomer && !(guestMode && guestCustomerName.trim())) { alert('{{ __('Pick a customer, or bill by name.') }}'); $event.preventDefault(); }"
                    class="lg:col-span-2 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Customer --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">1</span>
                                <h3 class="font-semibold text-gray-900">{{ __('Customer') }}</h3>
                            </div>
                            <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm shrink-0">
                                <button type="button" @click="guestMode = false; guestCustomerName = ''"
                                    class="px-3 py-1.5 font-medium" :class="!guestMode ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'">
                                    {{ __('Saved customer') }}
                                </button>
                                <button type="button" @click="guestMode = true; selectedCustomer = null; customerQuery = ''"
                                    class="px-3 py-1.5 font-medium border-l border-gray-200" :class="guestMode ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'">
                                    {{ __('Walk-in') }}
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="customer_id" :value="selectedCustomer ? selectedCustomer.id : ''">
                        <input type="hidden" name="guest_customer_name" :value="guestMode ? guestCustomerName : ''">

                        <template x-if="!guestMode && !selectedCustomer && !showCustomerForm">
                            <div class="relative">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                                    <x-text-input type="text" class="w-full pl-9" placeholder="{{ __('Search customer by name or phone...') }}"
                                        x-model="customerQuery" @input.debounce.300ms="searchCustomers()" autocomplete="off" />
                                </div>

                                <div class="absolute z-10 bg-white border border-gray-200 rounded-lg shadow-md w-full mt-1" x-show="customerQuery.length > 1">
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
                                                        <a :href="`{{ url('/customers') }}/${customer.id}/edit`" target="_blank" class="text-xs text-brand-600 hover:underline shrink-0">
                                                            {{ __('Activate') }}
                                                        </a>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <p class="text-sm text-gray-500 px-4 py-3" x-show="customerResults.length === 0">
                                        {{ __('No customer found.') }}
                                        <button type="button" class="text-brand-600 hover:underline" @click="showCustomerForm = true; newCustomer.name = customerQuery">
                                            {{ __('+ Create new customer') }}
                                        </button>
                                    </p>

                                    <p class="text-xs text-gray-400 px-4 py-3 border-t border-gray-100">
                                        {{ __("Don't want to save their details?") }}
                                        <button type="button" class="text-brand-600 hover:underline" @click="guestMode = true; guestCustomerName = customerQuery; customerQuery = ''">
                                            {{ __('Just bill by name') }}
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template x-if="guestMode">
                            <div class="border border-amber-200 bg-amber-50 rounded-lg p-4 space-y-2">
                                <x-input-label for="guest_name_display" :value="__('Customer name')" />
                                <x-text-input id="guest_name_display" type="text" class="w-full" x-model="guestCustomerName" placeholder="{{ __('e.g. Ramesh (walk-in)') }}" />
                                <p class="text-xs text-amber-700">
                                    {{ __("No customer record will be saved — their name just appears on this bill.") }}
                                </p>
                            </div>
                        </template>

                        <template x-if="!guestMode && selectedCustomer">
                            <div class="flex items-center justify-between bg-brand-50 rounded-lg px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-semibold flex items-center justify-center shrink-0 text-sm" x-text="selectedCustomer.name.trim().substring(0, 2).toUpperCase()"></span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate" x-text="selectedCustomer.name"></div>
                                        <div class="text-xs text-gray-500">
                                            <span x-text="selectedCustomer.phone"></span>
                                            &middot;
                                            <span x-text="((selectedCustomer.due || 0) < 0
                                                ? 'Credit ₹' + Math.abs(selectedCustomer.due || 0).toFixed(0)
                                                : 'Current due ₹' + (selectedCustomer.due || 0).toFixed(0))"></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="text-sm font-medium text-brand-700 hover:underline shrink-0 bg-white border border-brand-200 rounded-lg px-3 py-1.5" @click="selectedCustomer = null; customerQuery = ''">
                                    {{ __('Change') }}
                                </button>
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
                                        <x-input-label :value="__('Already due (if any)')" />
                                        <x-text-input type="number" step="0.01" min="0" class="w-full" x-model.number="newCustomer.opening_balance" placeholder="0.00" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Credit limit (0 = no limit)')" />
                                        <x-text-input type="number" step="0.01" min="0" class="w-full" x-model.number="newCustomer.credit_limit" placeholder="0.00" />
                                    </div>
                                </div>
                                <p class="text-sm text-red-600" x-show="customerError" x-text="customerError"></p>
                                <div class="flex gap-2">
                                    <x-primary-button type="button" @click="createCustomer()">{{ __('Save & select customer') }}</x-primary-button>
                                    <x-secondary-button type="button" @click="showCustomerForm = false">{{ __('Cancel') }}</x-secondary-button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Items --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">2</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Items') }}</h3>
                        </div>

                        <div class="relative mb-4">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <x-text-input type="text" class="w-full pl-9" placeholder="{{ __('Type item name or SKU to add') }}"
                                x-model="itemQuery" @input.debounce.300ms="searchItems()" autocomplete="off" />

                            <div class="absolute z-10 bg-white border border-gray-200 rounded-lg shadow-md w-full mt-1 max-h-56 overflow-y-auto" x-show="itemResults.length > 0">
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

                        <div x-show="lines.length > 0" class="space-y-3">
                            <div class="hidden lg:grid lg:grid-cols-[1fr_8rem_8rem_7rem_5rem] lg:gap-3 px-1 pb-1 text-xs font-medium text-gray-400 uppercase border-b border-gray-100">
                                <div>{{ __('Item') }}</div>
                                <div class="text-right">{{ __('Qty') }}</div>
                                <div class="text-right">{{ __('Rate') }}</div>
                                <div class="text-right">{{ __('Amount') }}</div>
                                <div></div>
                            </div>

                            <template x-for="(line, index) in lines" :key="line.id">
                                <div class="rounded-xl border border-gray-100 p-3 lg:border-0 lg:rounded-none lg:border-b lg:border-gray-100 lg:p-0 lg:py-3 lg:grid lg:grid-cols-[1fr_8rem_8rem_7rem_5rem] lg:gap-3 lg:items-center">
                                    <div class="flex items-start justify-between gap-2 lg:block min-w-0">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 truncate" x-text="line.name"></div>
                                            <div class="text-xs text-gray-400 mt-0.5" x-text="'Stock ' + availableStock(line) + ' ' + line.baseUnit"></div>
                                            <div class="mt-1.5 inline-flex rounded-md border border-gray-300 overflow-hidden text-xs" x-show="line.altUnit">
                                                <button type="button" @click="setLineUnit(line, line.baseUnit)" class="px-2.5 py-1 font-medium"
                                                    :class="line.unit === line.baseUnit ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" x-text="line.baseUnit"></button>
                                                <button type="button" @click="setLineUnit(line, line.altUnit)" class="px-2.5 py-1 font-medium border-l border-gray-300"
                                                    :class="line.unit === line.altUnit ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" x-text="line.altUnit"></button>
                                            </div>
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
                                        <input type="hidden" :name="`items[${index}][unit]`" :value="line.unit">
                                    </div>

                                    <div class="flex items-center justify-between gap-3 mt-3 lg:mt-0 lg:contents">
                                        <div class="lg:ml-auto">
                                            <div class="text-xs text-gray-400 mb-1 lg:hidden">{{ __('Qty') }}</div>
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
                                            <div class="text-xs text-gray-400 mb-1 lg:hidden">{{ __('Rate') }}</div>
                                            <div class="relative">
                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                                <input type="number" step="0.01" min="0" x-model.number="line.unit_price"
                                                    :name="`items[${index}][unit_price]`" class="no-spinner w-24 pl-6 pr-2 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-400 mb-1 lg:hidden">{{ __('Amount') }}</div>
                                            <div class="text-sm font-semibold text-gray-900" x-text="'₹' + (line.quantity * (line.unit_price || 0)).toFixed(0)"></div>
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

                    {{-- Bill date + note --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="invoice_date" :value="__('Bill date')" />
                            <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required />
                        </div>
                        <div>
                            <x-input-label for="notes">{{ __('Note') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional, prints on bill') }})</span></x-input-label>
                            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full" value="{{ $invoice->notes }}" placeholder="{{ __('e.g. Delivered by tempo') }}" />
                        </div>
                        <p class="text-xs text-gray-400 sm:col-span-2">{{ __('Payments already recorded on this invoice are not changed here — use "Record a payment" on the invoice page for that.') }}</p>
                    </div>
                </form>

                {{-- Totals + Payment sidebar — both cards live in one sticky
                     wrapper so they scroll and stick together as a unit.
                     The Totals card's own fields live physically outside
                     #invoice-form (its sibling <form> for Record Payment
                     below can't nest inside another form), so they're
                     wired back to it via form="invoice-form" instead. --}}
                <div class="mt-4 lg:mt-0 lg:sticky lg:top-20 space-y-4">
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">3</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Totals') }}</h3>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500" x-text="'{{ __('Subtotal') }} (' + lines.length + ' {{ __('items') }})'"></span>
                            <span class="font-medium text-gray-900" x-text="'₹' + subtotal.toFixed(0)"></span>
                        </div>

                        <div class="flex flex-wrap justify-between items-center gap-y-1 text-sm"
                            x-effect="if (discountType === 'percentage') { discount = Math.round(subtotal * (discountPercent || 0) / 100 * 100) / 100 }">
                            <label for="discount" class="text-gray-500">{{ __('Discount') }}</label>
                            <div class="flex items-center gap-2">
                                <div class="inline-flex rounded-md border border-gray-300 overflow-hidden text-xs">
                                    <button type="button" @click="discountType = 'amount'"
                                        class="px-2.5 py-1.5 font-medium" :class="discountType === 'amount' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">₹</button>
                                    <button type="button" @click="discountType = 'percentage'"
                                        class="px-2.5 py-1.5 font-medium border-l border-gray-300" :class="discountType === 'percentage' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">%</button>
                                </div>
                                <template x-if="discountType === 'percentage'">
                                    <input type="number" step="0.01" min="0" max="100" x-model.number="discountPercent" placeholder="0"
                                        class="no-spinner w-14 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                </template>
                                <input id="discount" name="discount" form="invoice-form" type="number" step="0.01" min="0" x-model.number="discount"
                                    :readonly="discountType === 'percentage'"
                                    :class="discountType === 'percentage' ? 'bg-gray-100 text-gray-500' : ''"
                                    class="no-spinner w-20 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <label for="tax" class="text-gray-500">{{ __('Tax') }}</label>
                            <input id="tax" name="tax" form="invoice-form" type="number" step="0.01" min="0" x-model.number="tax" class="no-spinner w-24 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <label for="transportation" class="text-gray-500">{{ __('Transport') }}</label>
                            <input id="transportation" name="transportation" form="invoice-form" type="number" step="0.01" min="0" x-model.number="transportation" class="no-spinner w-24 text-right border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        </div>

                        <div class="flex justify-between items-center text-base font-bold text-gray-900 border-t border-gray-100 pt-3">
                            <span>{{ __('Total') }}</span>
                            <span x-text="'₹' + total.toFixed(0)"></span>
                        </div>

                        <div class="pt-1">
                            <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('Payment status') }}</p>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach (['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')] as $value => $label)
                                    <button type="button" @click="paymentStatus = '{{ $value }}'"
                                        class="px-2 py-2 text-sm font-medium rounded-lg border-2"
                                        :class="paymentStatus === '{{ $value }}' ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-2" x-show="paymentStatus === 'partial'">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                    <input type="number" step="0.01" min="0" :max="total" x-model.number="editedPaidAmount"
                                        class="no-spinner w-full pl-7 border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" placeholder="{{ __('Amount received') }}">
                                </div>
                            </div>

                            <p class="text-xs text-gray-400 mt-2">{{ __('Corrects the bill\'s own recorded status — it does not add a new entry to the payment history below.') }}</p>
                        </div>

                        <input type="hidden" name="payment_status" form="invoice-form" :value="paymentStatus">
                        <input type="hidden" name="paid_amount" form="invoice-form" :value="paidAmount">

                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>{{ __('Paid so far') }}</span>
                            <span x-text="'₹' + paidAmount.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold pt-1.5 border-t border-gray-100"
                            :class="(total - paidAmount) > 0 ? 'text-red-600' : 'text-green-600'">
                            <span x-text="(total - paidAmount) > 0 ? '{{ __('New balance due') }}' : '{{ __('Settled / In credit') }}'"></span>
                            <span x-text="(total - paidAmount) < 0
                                ? '₹' + Math.abs(total - paidAmount).toFixed(0) + ' CR'
                                : '₹' + (total - paidAmount).toFixed(0)"></span>
                        </div>

                        @if ($invoice->customer)
                            <div class="pt-2 border-t border-gray-100 space-y-1.5">
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>{{ __('Previous due') }}</span>
                                    <span x-text="previousDue < 0 ? '₹' + Math.abs(previousDue).toFixed(0) + ' CR' : '₹' + previousDue.toFixed(0)"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold pt-1.5 border-t border-gray-100"
                                    :class="dueAfterBill > 0.004 ? 'text-red-600' : 'text-green-600'">
                                    <span x-text="dueAfterBill > 0.004 ? '{{ __('Customer balance') }}' : '{{ __('Settled / In credit') }}'"></span>
                                    <span x-text="dueAfterBill < -0.004
                                        ? '₹' + Math.abs(dueAfterBill).toFixed(0) + ' CR'
                                        : '₹' + Math.max(0, dueAfterBill).toFixed(0)"></span>
                                </div>
                            </div>
                        @endif

                        <button type="submit" form="invoice-form" class="hidden lg:block w-full px-4 py-3 bg-brand-700 text-white text-sm font-semibold rounded-lg hover:bg-brand-800">
                            {{ __('Update bill') }}
                        </button>
                    </div>

                    {{--
                        A separate <form> (can't nest inside #invoice-form)
                        that records a real new payment against this bill,
                        exactly like the "Record payment" card on the
                        invoice's own view page. Unlike the Payment status
                        buttons above (which only correct this bill's own
                        recorded state), submitting this creates a real
                        InvoicePayment row, so it shows up in the history
                        below and reduces the customer's running due the
                        same way a payment recorded from Create or View
                        does. It's a sibling card in this same sticky
                        wrapper, not a separate block below it, so the two
                        scroll and stick together as one unit.
                    --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">{{ __('Payment') }}</h3>
                        <span @class([
                            'px-2.5 py-1 rounded-full text-xs font-semibold',
                            'bg-green-50 text-green-700' => $invoice->status === 'paid',
                            'bg-amber-50 text-amber-700' => $invoice->status === 'partial',
                            'bg-red-50 text-red-600' => $invoice->status === 'unpaid',
                        ])>
                            {{ $invoice->status === 'paid' ? __('Fully paid') : ($invoice->status === 'partial' ? __('Partially paid') : __('Unpaid')) }}
                        </span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-500">
                        <span>{{ __('Received') }}</span>
                        <span class="font-medium text-gray-800">&#8377;{{ number_format($invoice->paid_amount, 2) }} {{ __('of') }} &#8377;{{ number_format($invoice->total, 2) }}</span>
                    </div>

                    @if ($invoice->payments->isNotEmpty())
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('History') }}</p>
                            <div class="space-y-2.5 max-h-56 overflow-y-auto">
                                @foreach ($invoice->payments as $payment)
                                    <div class="flex justify-between text-sm">
                                        <div>
                                            <div class="text-gray-800">{{ $payment->note ?: __('Payment recorded') }}{{ $payment->mode ? ' · '.ucfirst($payment->mode) : '' }}</div>
                                            <div class="text-xs text-gray-400">{{ $payment->created_at->format('d M, h:i A') }} &middot; {{ $payment->user->name ?? '—' }}</div>
                                        </div>
                                        <span class="font-medium text-gray-800 shrink-0">&#8377;{{ number_format($payment->amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php $editCustomerDue = $invoice->customer?->dueAmount() ?? 0.0; @endphp
                    <div class="pt-3 border-t border-gray-100">
                        @if ($invoice->balanceDue() > 0 || ($invoice->customer && $editCustomerDue > 0))
                            <button type="button" @click="payOpen = !payOpen" class="w-full text-center px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg">
                                {{ __('Record payment') }}
                            </button>
                            <form method="POST"
                                action="{{ $invoice->balanceDue() > 0 ? route('invoices.payments.store', $invoice) : route('customers.payments.store', $invoice->customer) }}"
                                class="space-y-3 mt-3" x-show="payOpen" style="display: none" x-cloak>
                                @csrf
                                <input type="hidden" name="mode" :value="payMode">
                                <div>
                                    <x-input-label for="edit_payment_amount" :value="__('Amount received')" />
                                    <x-text-input id="edit_payment_amount" name="amount" type="number" step="0.01" min="0.01" @if ($invoice->balanceDue() > 0) :max="$invoice->balanceDue()" @endif class="mt-1 w-full" value="{{ old('amount') }}" required />
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                                        <button type="button" @click="payMode = '{{ $value }}'"
                                            class="px-2 py-1.5 text-sm font-medium rounded-md border-2"
                                            :class="payMode === '{{ $value }}' ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                                <x-text-input name="note" type="text" class="w-full" value="{{ old('note') }}" placeholder="{{ __('Note (optional)') }}" />
                                <x-primary-button class="w-full justify-center">{{ __('Record payment') }}</x-primary-button>
                                @if ($invoice->balanceDue() <= 0)
                                    <p class="text-xs text-gray-400 text-center">{{ __('This bill is already fully paid — this settles their other outstanding due instead.') }}</p>
                                @endif
                            </form>
                        @else
                            <button type="button" disabled class="w-full text-center px-4 py-2.5 bg-gray-50 text-gray-400 text-sm font-semibold rounded-lg cursor-not-allowed">
                                {{ __('Record payment (nothing due)') }}
                            </button>
                        @endif
                    </div>
                </div>

                {{--
                    Same standalone "settle their older due" section the New
                    Bill screen has — separate from the Payment card above,
                    which is about this one bill's own payments. Posts to
                    the customer's own FIFO-allocation endpoint, same as on
                    Create.
                --}}
                @if ($invoice->customer)
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4"
                        x-data="{ dueType: 'paid', dueMode: 'cash' }">
                        <h3 class="font-semibold text-gray-900">{{ __('Record payment') }}</h3>

                        <form method="POST" action="{{ route('customers.payments.store', $invoice->customer) }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="type" :value="dueType">
                            <input type="hidden" name="mode" :value="dueMode">

                            <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm w-full">
                                <button type="button" @click="dueType = 'paid'" class="flex-1 px-3 py-1.5 font-medium" :class="dueType === 'paid' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'">
                                    {{ __('We received from them') }}
                                </button>
                                <button type="button" @click="dueType = 'received'" class="flex-1 px-3 py-1.5 font-medium border-l border-gray-200" :class="dueType === 'received' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'">
                                    {{ __('Refund given to them') }}
                                </button>
                            </div>

                            <div>
                                <x-input-label x-text="dueType === 'received' ? '{{ __('Refund amount') }}' : '{{ __('Amount received') }}'" />
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                        class="no-spinner w-full pl-7 border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2" x-show="dueType === 'paid'">
                                @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                                    <button type="button" @click="dueMode = '{{ $value }}'"
                                        class="px-3 py-2 text-sm font-medium rounded-md border"
                                        :class="dueMode === '{{ $value }}' ? 'bg-brand-50 border-brand-500 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>

                            <div>
                                <x-input-label :value="__('Note (optional)')" />
                                <x-text-input name="note" type="text" class="mt-1 w-full" placeholder="{{ __('e.g. Paid by Rameshbhai') }}" />
                            </div>

                            <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-2.5">
                                {{ __('Save') }}
                            </button>

                            <p class="text-xs text-gray-400 text-center">{{ __('Payment is adjusted against their oldest unpaid bill first.') }}</p>
                        </form>
                    </div>
                @endif
            </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="invoice-form" class="w-full px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Update bill') }}
            </button>
        </div>
    </div>

    <script>
        function invoiceForm() {
            return {
                customerQuery: '',
                customerResults: [],
                selectedCustomer: @json($selectedCustomer),
                showCustomerForm: false,
                customerError: '',
                newCustomer: { name: '', phone: '', email: '', address: '', opening_balance: 0, credit_limit: 0 },
                guestMode: {{ $guestCustomerName ? 'true' : 'false' }},
                guestCustomerName: {{ \Illuminate\Support\Js::from($guestCustomerName ?? '') }},

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
                            baseUnit: item.unit || line.unit,
                            altUnit: item.alt_unit || null,
                            altUnitRatio: item.alt_unit_ratio || null,
                            baseStock: line.stock,
                            baseSellingPrice: item.selling_price ?? line.unit_price,
                        };
                    });
                },

                adjust(line, field, delta, min, max) {
                    let value = Math.round(((parseFloat(line[field]) || 0) + delta) * 100) / 100;
                    if (typeof min === 'number') value = Math.max(min, value);
                    if (typeof max === 'number') value = Math.min(max, value);
                    line[field] = value;
                },

                discount: {{ (float) $invoice->discount }},
                discountType: 'amount',
                discountPercent: 0,
                tax: {{ (float) $invoice->tax }},
                transportation: {{ (float) $invoice->transportation }},
                paymentStatus: {{ \Illuminate\Support\Js::from($invoice->status) }},
                editedPaidAmount: {{ (float) $invoice->paid_amount }},
                payOpen: false,
                payMode: 'cash',
                // The customer's due from every OTHER invoice, excluding
                // this one's own current (pre-edit) contribution — same
                // formula the invoice PDF and show page use for "Previous
                // due". Fixed at page load like selectedCustomer.due is;
                // dueAfterBill below reacts to live edits on top of it.
                previousDue: {{ $invoice->customer ? ((float) $invoice->customer->dueAmount() - ((float) $invoice->total - (float) $invoice->paid_amount)) : 0 }},

                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + (line.quantity * (line.unit_price || 0)), 0);
                },
                get total() {
                    return Math.max(0, this.subtotal - (this.discount || 0) + (this.tax || 0) + (this.transportation || 0));
                },
                get paidAmount() {
                    if (this.paymentStatus === 'paid') return this.total;
                    if (this.paymentStatus === 'partial') return Math.min(this.editedPaidAmount || 0, this.total);
                    return 0;
                },
                get dueAfterBill() {
                    return this.previousDue + (this.total - this.paidAmount);
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
                    const res = await fetch(`{{ route('items.search') }}?in_stock_only=1&q=` + encodeURIComponent(this.itemQuery));
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
                        baseSellingPrice: item.selling_price,
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
                        baseSellingPrice: line.baseSellingPrice,
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

                    if (unit === line.altUnit && line.altUnitRatio) {
                        line.unit_price = Math.round((line.baseSellingPrice / line.altUnitRatio) * 100) / 100;
                    } else {
                        line.unit_price = line.baseSellingPrice;
                    }

                    const max = this.availableStock(line);
                    if (line.quantity > max) line.quantity = max;
                },
            }
        }
    </script>
</x-app-layout>
