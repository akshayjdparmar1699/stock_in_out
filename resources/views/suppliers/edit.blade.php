<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Supplier') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6">
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
                        <x-input-label for="address" :value="__('Address (optional)')" />
                        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $supplier->address)" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="gst_number" :value="__('GST Number (optional)')" />
                        <x-text-input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number', $supplier->gst_number)" />
                        <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="opening_balance" :value="__('Opening Due (before any purchases)')" />
                        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('opening_balance', $supplier->opening_balance)" />
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
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
