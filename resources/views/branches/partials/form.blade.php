@php $branch ??= null; @endphp

<div>
    <x-input-label for="name" :value="__('Branch Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $branch?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="code" :value="__('Branch Code (short, unique)')" />
    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $branch?->code)" required />
    <x-input-error :messages="$errors->get('code')" class="mt-2" />
</div>

<div>
    <x-input-label for="address" :value="__('Address')" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $branch?->address)" />
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $branch?->phone)" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="border-t pt-4 space-y-4">
    <div class="text-sm font-medium text-gray-700">{{ __('Payment Details (optional)') }}</div>
    <p class="text-xs text-gray-400 -mt-2">{{ __("Shown on this branch's invoices. Leave blank to keep showing the default QR and bank details.") }}</p>

    <div>
        <x-input-label for="qr_code_photo" :value="__('Payment QR Code')" />
        @if ($branch?->qr_code_path)
            <div class="flex items-center gap-3 mt-1 mb-2">
                <img src="{{ $branch->paymentQrUrl() }}" alt="{{ __('Current QR code') }}" class="w-16 h-16 border rounded">
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remove_qr_code" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                    {{ __('Remove current QR code') }}
                </label>
            </div>
        @endif
        <input id="qr_code_photo" name="qr_code_photo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
        <x-input-error :messages="$errors->get('qr_code_photo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="bank_name" :value="__('Bank Name')" />
        <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $branch?->bank_name)" />
        <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="account_number" :value="__('Account Number')" />
        <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" :value="old('account_number', $branch?->account_number)" />
        <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="account_holder_name" :value="__('Account Holder Name')" />
        <x-text-input id="account_holder_name" name="account_holder_name" type="text" class="mt-1 block w-full" :value="old('account_holder_name', $branch?->account_holder_name)" />
        <x-input-error :messages="$errors->get('account_holder_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ifsc_code" :value="__('IFSC Code')" />
        <x-text-input id="ifsc_code" name="ifsc_code" type="text" class="mt-1 block w-full" :value="old('ifsc_code', $branch?->ifsc_code)" />
        <x-input-error :messages="$errors->get('ifsc_code')" class="mt-2" />
    </div>
</div>
