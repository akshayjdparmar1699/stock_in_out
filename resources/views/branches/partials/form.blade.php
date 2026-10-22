@php
    $branch ??= null;
    $hasCustomBank = $branch && ($branch->bank_name || $branch->account_number || $branch->account_holder_name || $branch->ifsc_code || $branch->qr_code_path);
@endphp

<input type="hidden" name="bank_mode" :value="bankMode">
<input type="hidden" name="remove_qr_code" :value="bankMode === 'default' ? '1' : '0'">

{{-- Section 1: Branch details --}}
<div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
    <div class="flex items-center gap-2.5 mb-4">
        <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">1</span>
        <h3 class="font-semibold text-gray-900">{{ __('Branch details') }}</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="name">{{ __('Branch name') }} <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Om Sai Aalubhandar (Kodinar)') }}" :value="old('name', $branch?->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="code">{{ __('Short code') }} <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" placeholder="{{ __('E.G. KDR') }}" :value="old('code', $branch?->code)" required />
            <p class="text-xs text-gray-400 mt-1">{{ __('Shown on this branch\'s bills and in the branch switcher.') }}</p>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="address">{{ __('Address') }}</x-input-label>
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" placeholder="{{ __('Shop no., area, city') }}" :value="old('address', $branch?->address)" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="phone">{{ __('Phone') }}</x-input-label>
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" placeholder="{{ __('98765 43210') }}" :value="old('phone', $branch?->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
    </div>
    <p class="text-xs text-gray-400 mt-3">{{ __("Address and phone print on this branch's bills.") }}</p>
</div>

{{-- Section 2: Payment details on bills --}}
<div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 mt-4">
    <div class="flex items-center gap-2.5 mb-4">
        <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">2</span>
        <h3 class="font-semibold text-gray-900">{{ __('Payment details on bills') }}</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button type="button" @click="bankMode = 'default'"
            class="text-left p-4 rounded-xl border-2"
            :class="bankMode === 'default' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
            <div class="font-semibold text-gray-900">{{ __('Same as main bank') }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ __('Shows the default QR and bank details') }}</div>
        </button>
        <button type="button" @click="bankMode = 'own'"
            class="text-left p-4 rounded-xl border-2"
            :class="bankMode === 'own' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
            <div class="font-semibold text-gray-900">{{ __("Use this branch's own") }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ __('Different bank account or QR for this shop') }}</div>
        </button>
    </div>

    <div class="mt-4 space-y-4" x-show="bankMode === 'own'" style="display: none;">
        <div>
            <x-input-label for="qr_code_photo" :value="__('Payment QR code')" />
            @if ($branch?->qr_code_path)
                <div class="flex items-center gap-3 mt-1 mb-2">
                    <img src="{{ $branch->paymentQrUrl() }}" alt="{{ __('Current QR code') }}" class="w-16 h-16 border rounded">
                    <span class="text-xs text-gray-400">{{ __('Current QR — upload a new one to replace it') }}</span>
                </div>
            @endif
            <input id="qr_code_photo" name="qr_code_photo" type="file" accept="image/*" x-bind:disabled="bankMode !== 'own'" class="mt-1 block w-full text-sm text-gray-600">
            <x-input-error :messages="$errors->get('qr_code_photo')" class="mt-2" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="bank_name" :value="__('Bank name')" />
                <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" x-bind:disabled="bankMode !== 'own'" :value="old('bank_name', $branch?->bank_name)" />
                <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="account_number" :value="__('Account number')" />
                <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" x-bind:disabled="bankMode !== 'own'" :value="old('account_number', $branch?->account_number)" />
                <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="account_holder_name" :value="__('Account holder name')" />
                <x-text-input id="account_holder_name" name="account_holder_name" type="text" class="mt-1 block w-full" x-bind:disabled="bankMode !== 'own'" :value="old('account_holder_name', $branch?->account_holder_name)" />
                <x-input-error :messages="$errors->get('account_holder_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="ifsc_code" :value="__('IFSC code')" />
                <x-text-input id="ifsc_code" name="ifsc_code" type="text" class="mt-1 block w-full" x-bind:disabled="bankMode !== 'own'" :value="old('ifsc_code', $branch?->ifsc_code)" />
                <x-input-error :messages="$errors->get('ifsc_code')" class="mt-2" />
            </div>
        </div>
    </div>
</div>
