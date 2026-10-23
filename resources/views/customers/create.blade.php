<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Customer') }}</h2>
        </div>
    </x-slot>

    {{-- Desktop form (unchanged) --}}
    <div class="hidden lg:block py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Customer Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone Number')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email (optional)')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="address" :value="__('Address (optional)')" />
                        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="gst_number" :value="__('GST Number (optional)')" />
                        <x-text-input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number')" />
                        <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                    </div>

                    @if ($gstEnabled ?? false)
                        <div>
                            <x-input-label for="state" :value="__('State')" />
                            <select id="state" name="state" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm">
                                <option value="">{{ __('Select state') }}</option>
                                @foreach (\App\Support\GstStateCodes::all() as $name => $code)
                                    <option value="{{ $name }}" @selected(old('state') === $name)>{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Needed to decide CGST+SGST vs IGST on GST invoices.') }}</p>
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="opening_balance" :value="__('Already Due (if any)')" />
                        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('opening_balance', 0)" placeholder="0.00" />
                        <p class="text-xs text-gray-400 mt-1">{{ __('If this customer already owed money before joining, enter it here.') }}</p>
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="credit_limit" :value="__('Credit Limit (optional)')" />
                        <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('credit_limit', 0)" placeholder="0.00" />
                        <p class="text-xs text-gray-400 mt-1">{{ __("Get a reminder once this customer's due crosses this amount. Leave 0 for no limit.") }}</p>
                        <x-input-error :messages="$errors->get('credit_limit')" class="mt-2" />
                    </div>

                    @if (Auth::user()->isAdmin())
                        <div>
                            <x-input-label :value="__('Belongs to Branch(es)')" />
                            <div class="mt-2 space-y-2">
                                @foreach ($branches as $branch)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                            class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                            @checked(collect(old('branch_ids', [\App\Services\BranchContext::id()]))->contains($branch->id))>
                                        <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Select more than one branch if this customer buys from multiple branches.') }}</p>
                            <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Save Customer') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Mobile form --}}
    <div class="lg:hidden pb-28" x-data="{ pickContact() { window.pickCustomerContact(); } }">
        <form method="POST" action="{{ route('customers.store') }}" id="mobile-customer-form">
            @csrf

            <div class="px-4 pt-4 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-4">
                    <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('Basic Details') }}</p>

                    <div>
                        <x-input-label for="m_name">{{ __('Customer / shop name') }} <span class="text-red-500">*</span></x-input-label>
                        <x-text-input id="m_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="{{ __('e.g. Patel Kirana Store') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>{{ __('Phone number') }} <span class="text-red-500">*</span></x-input-label>
                        <div class="mt-1 flex gap-2">
                            <span class="shrink-0 inline-flex items-center px-3 rounded-md bg-gray-100 text-gray-600 text-sm font-medium">+91</span>
                            <x-text-input id="m_phone" name="phone" type="tel" class="block w-full" :value="old('phone')" placeholder="98765 43210" required />
                        </div>
                        <button type="button" onclick="window.pickCustomerContact()" id="pick-contact-btn" style="display:none" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-brand-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            {{ __('Pick from phone contacts') }}
                        </button>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="m_address">{{ __('Village / city') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <x-text-input id="m_address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" placeholder="{{ __('e.g. Kodinar') }}" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    @if ($gstEnabled ?? false)
                        <div>
                            <x-input-label for="m_gst_number">{{ __('GST Number') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <x-text-input id="m_gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number')" />
                            <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="m_state">{{ __('State') }}</x-input-label>
                            <select id="m_state" name="state" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm">
                                <option value="">{{ __('Select state') }}</option>
                                @foreach (\App\Support\GstStateCodes::all() as $name => $code)
                                    <option value="{{ $name }}" @selected(old('state') === $name)>{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-4">
                    <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('Hisaab') }}</p>

                    <div>
                        <x-input-label for="m_opening_balance" :value="__('Already due (pehle ka baaki)')" />
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                            <x-text-input id="m_opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('opening_balance', 0)" />
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Customer owed money before joining? Enter it here, it becomes the opening balance in the khata.') }}</p>
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="m_credit_limit">{{ __('Credit limit') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                            <x-text-input id="m_credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('credit_limit')" placeholder="{{ __('No limit') }}" />
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Leave empty for no limit.') }}</p>
                        <x-input-error :messages="$errors->get('credit_limit')" class="mt-2" />
                    </div>
                </div>

                @if (Auth::user()->isAdmin())
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                        <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('Branch') }}</p>
                        <div class="space-y-2">
                            @foreach ($branches as $branch)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                        class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                        @checked(collect(old('branch_ids', [\App\Services\BranchContext::id()]))->contains($branch->id))>
                                    <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400">{{ __('Select more than one branch if this customer buys from multiple branches.') }}</p>
                        <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                    </div>
                @endif
            </div>
        </form>
    </div>

    <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
        <button type="submit" form="mobile-customer-form" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3">
            {{ __('Save Customer') }}
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if ('contacts' in navigator && 'ContactsManager' in window) {
                const btn = document.getElementById('pick-contact-btn');
                if (btn) btn.style.display = 'inline-flex';
            }
        });

        window.pickCustomerContact = async function () {
            if (!('contacts' in navigator && 'ContactsManager' in window)) return;
            try {
                const contacts = await navigator.contacts.select(['name', 'tel'], { multiple: false });
                if (!contacts.length) return;
                const contact = contacts[0];
                if (contact.name && contact.name[0]) {
                    document.getElementById('m_name').value = contact.name[0];
                }
                if (contact.tel && contact.tel[0]) {
                    document.getElementById('m_phone').value = contact.tel[0].replace(/\D/g, '').slice(-10);
                }
            } catch (e) {
                // User cancelled the picker or it isn't available — nothing to do.
            }
        };
    </script>
</x-app-layout>
