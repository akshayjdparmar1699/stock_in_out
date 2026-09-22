<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Customer') }}</h2>
    </x-slot>

    <div class="py-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false }">
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

                    <div>
                        <x-input-label for="opening_balance" :value="__('Opening Due (before any invoices)')" />
                        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('opening_balance', $customer->opening_balance)" />
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    @if (Auth::user()->isAdmin())
                        @php $currentBranchIds = old('branch_ids', $customer->branches->pluck('id')->all()); @endphp
                        <div>
                            <x-input-label :value="__('Belongs to Branch(es)')" />
                            <div class="mt-2 space-y-2">
                                @foreach ($branches as $branch)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
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
</x-app-layout>
