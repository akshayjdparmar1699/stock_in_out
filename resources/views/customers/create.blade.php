<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Customer') }}</h2>
    </x-slot>

    <div class="py-12">
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
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
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
</x-app-layout>
