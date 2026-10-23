<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.show', $customer) }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Customer') }}</h2>
        </div>
    </x-slot>

    {{-- Desktop form (unchanged) --}}
    <div class="hidden lg:block py-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false }">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Customer Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone Number')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $customer->phone)" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email (optional)')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="address" :value="__('Address (optional)')" />
                        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $customer->address)" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="gst_number" :value="__('GST Number (optional)')" />
                        <x-text-input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number', $customer->gst_number)" />
                        <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                    </div>

                    @if ($gstEnabled ?? false)
                        <div>
                            <x-input-label for="state" :value="__('State')" />
                            <select id="state" name="state" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm">
                                <option value="">{{ __('Select state') }}</option>
                                @foreach (\App\Support\GstStateCodes::all() as $name => $code)
                                    <option value="{{ $name }}" @selected(old('state', $customer->state) === $name)>{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Needed to decide CGST+SGST vs IGST on GST invoices.') }}</p>
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="opening_balance" :value="__('Opening Due (before any invoices)')" />
                        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('opening_balance', $customer->opening_balance)" />
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="credit_limit" :value="__('Credit Limit (optional)')" />
                        <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('credit_limit', $customer->credit_limit)" />
                        <p class="text-xs text-gray-400 mt-1">{{ __("Get a reminder once this customer's due crosses this amount. Leave 0 for no limit.") }}</p>
                        <x-input-error :messages="$errors->get('credit_limit')" class="mt-2" />
                    </div>

                    @if (Auth::user()->isAdmin())
                        @php $currentBranchIds = old('branch_ids', $customer->branches->pluck('id')->all()); @endphp
                        <div>
                            <x-input-label :value="__('Belongs to Branch(es)')" />
                            <div class="mt-2 space-y-2">
                                @foreach ($branches as $branch)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                            class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                            @checked(collect($currentBranchIds)->contains($branch->id))>
                                        <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Select more than one branch if this customer buys from multiple branches.') }}</p>
                            <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                        </div>
                    @else
                        <div>
                            <x-input-label :value="__('Branch(es)')" />
                            <p class="text-sm text-gray-600 mt-1">{{ $customer->branches->pluck('name')->join(', ') }}</p>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Update Customer') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <p class="text-sm text-gray-500">{{ __('Current total balance due') }}</p>
                @php $due = $customer->dueAmount(); @endphp
                <p class="text-2xl font-semibold {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-900') }}">
                    ₹{{ number_format(abs($due), 2) }}
                    @if ($due < 0)
                        <span class="text-sm font-normal text-green-600">{{ __('(in credit)') }}</span>
                    @endif
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Customer Status') }}</p>
                    <p class="text-sm font-medium {{ $customer->is_active ? 'text-green-600' : 'text-gray-500' }}">
                        {{ $customer->is_active ? __('Active') : __('Inactive') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('Mark inactive if this customer no longer buys from you. They stay in your records but won\'t show up while billing.') }}</p>
                </div>
                <form method="POST" action="{{ route('customers.toggle-active', $customer) }}">
                    @csrf
                    @method('PATCH')
                    <button type="button"
                        data-name="{{ $customer->name }}"
                        data-active="{{ $customer->is_active ? '1' : '0' }}"
                        @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $customer->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $customer->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        @include('customers.partials.status-confirm-modal')
    </div>

    {{-- Mobile form --}}
    <div class="lg:hidden pb-28" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false }">
        <form method="POST" action="{{ route('customers.update', $customer) }}" id="mobile-customer-form">
            @csrf
            @method('PUT')

            <div class="px-4 pt-4 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-4">
                    <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('Basic Details') }}</p>

                    <div>
                        <x-input-label for="m_name">{{ __('Customer / shop name') }} <span class="text-red-500">*</span></x-input-label>
                        <x-text-input id="m_name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer->name)" placeholder="{{ __('e.g. Patel Kirana Store') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>{{ __('Phone number') }} <span class="text-red-500">*</span></x-input-label>
                        <div class="mt-1 flex gap-2">
                            <span class="shrink-0 inline-flex items-center px-3 rounded-md bg-gray-100 text-gray-600 text-sm font-medium">+91</span>
                            <x-text-input id="m_phone" name="phone" type="tel" class="block w-full" :value="old('phone', $customer->phone)" placeholder="98765 43210" required />
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="m_address">{{ __('Village / city') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <x-text-input id="m_address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $customer->address)" placeholder="{{ __('e.g. Kodinar') }}" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    @if ($gstEnabled ?? false)
                        <div>
                            <x-input-label for="m_gst_number">{{ __('GST Number') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <x-text-input id="m_gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number', $customer->gst_number)" />
                            <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="m_state">{{ __('State') }}</x-input-label>
                            <select id="m_state" name="state" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm">
                                <option value="">{{ __('Select state') }}</option>
                                @foreach (\App\Support\GstStateCodes::all() as $name => $code)
                                    <option value="{{ $name }}" @selected(old('state', $customer->state) === $name)>{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-4">
                    <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('Hisaab') }}</p>

                    <div>
                        <x-input-label for="m_opening_balance" :value="__('Opening due (pehle ka baaki)')" />
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                            <x-text-input id="m_opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('opening_balance', $customer->opening_balance)" />
                        </div>
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="m_credit_limit">{{ __('Credit limit') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                            <x-text-input id="m_credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('credit_limit', $customer->credit_limit)" placeholder="{{ __('No limit') }}" />
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Leave empty for no limit.') }}</p>
                        <x-input-error :messages="$errors->get('credit_limit')" class="mt-2" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                    <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('Branch') }}</p>
                    @if (Auth::user()->isAdmin())
                        @php $currentBranchIdsMobile = old('branch_ids', $customer->branches->pluck('id')->all()); @endphp
                        <div class="space-y-2">
                            @foreach ($branches as $branch)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                        class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                        @checked(collect($currentBranchIdsMobile)->contains($branch->id))>
                                    <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400">{{ __('Select more than one branch if this customer buys from multiple branches.') }}</p>
                    @else
                        <p class="text-sm text-gray-600">{{ $customer->branches->pluck('name')->join(', ') }}</p>
                    @endif
                    <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                </div>
            </div>
        </form>

        <div class="px-4 mt-4 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-sm text-gray-500">{{ __('Current total balance due') }}</p>
                @php $mobileDue = $customer->dueAmount(); @endphp
                <p class="text-xl font-semibold {{ $mobileDue > 0 ? 'text-red-600' : ($mobileDue < 0 ? 'text-green-600' : 'text-gray-900') }} mt-0.5">
                    ₹{{ number_format(abs($mobileDue), 2) }}
                    @if ($mobileDue < 0)
                        <span class="text-sm font-normal text-green-600">{{ __('(in credit)') }}</span>
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500">{{ __('Customer Status') }}</p>
                    <p class="text-sm font-medium {{ $customer->is_active ? 'text-green-600' : 'text-gray-500' }}">
                        {{ $customer->is_active ? __('Active') : __('Inactive') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('Mark inactive if this customer no longer buys from you.') }}</p>
                </div>
                <form method="POST" action="{{ route('customers.toggle-active', $customer) }}" class="shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="button"
                        data-name="{{ $customer->name }}"
                        data-active="{{ $customer->is_active ? '1' : '0' }}"
                        @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $customer->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $customer->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        @include('customers.partials.status-confirm-modal')
    </div>

    <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
        <button type="submit" form="mobile-customer-form" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3">
            {{ __('Update Customer') }}
        </button>
    </div>
</x-app-layout>
