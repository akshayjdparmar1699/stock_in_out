<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Supplier') }}</h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6" x-data="{ open: false }">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Supplier / Wholesaler Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $supplier->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone Number (optional)')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $supplier->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="city" :value="__('Village / City (optional)')" />
                        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $supplier->city)" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="opening_balance" :value="__('Opening Due (before any purchases)')" />
                        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('opening_balance', $supplier->opening_balance)" />
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    @if (Auth::user()->isAdmin())
                        @php $currentBranchIds = old('branch_ids', $supplier->branches->pluck('id')->all()); @endphp
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
                            <p class="text-xs text-gray-400 mt-1">{{ __('Select more than one branch if you buy from this supplier at multiple branches.') }}</p>
                            <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                        </div>
                    @else
                        <div>
                            <x-input-label :value="__('Branch(es)')" />
                            <p class="text-sm text-gray-600 mt-1">{{ $supplier->branches->pluck('name')->join(', ') }}</p>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4">
                        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                            <span class="text-sm font-medium text-gray-700">{{ __('More details') }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open" x-transition style="display: none" class="space-y-4 mt-4">
                            <div>
                                <x-input-label for="address" :value="__('Full Address (optional)')" />
                                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $supplier->address)" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="gst_number" :value="__('GST Number (optional)')" />
                                <x-text-input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number', $supplier->gst_number)" />
                                <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button>{{ __('Update Supplier') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <p class="text-sm text-gray-500">{{ __('Current total we owe them') }}</p>
                @php $due = $supplier->dueAmount(); @endphp
                <p class="text-2xl font-semibold {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-900') }}">
                    ₹{{ number_format(abs($due), 2) }}
                    @if ($due < 0)
                        <span class="text-sm font-normal text-green-600">{{ __('(we overpaid / in credit)') }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
