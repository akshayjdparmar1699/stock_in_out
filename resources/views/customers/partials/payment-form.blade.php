<div x-data="{ type: 'paid', mode: 'cash' }">
    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Receive payment') }}</h3>

    <form method="POST" action="{{ route('customers.payments.store', $customer) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="mode" :value="mode">

        <div class="flex flex-wrap gap-x-4 gap-y-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="type" value="paid" x-model="type" class="text-brand-600 focus:ring-brand-500">
                {{ __('We Received From Them') }}
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="type" value="received" x-model="type" class="text-brand-600 focus:ring-brand-500">
                {{ __('Refund Given To Them') }}
            </label>
        </div>

        <div>
            <x-input-label for="amount_{{ $customer->id }}" :value="__('Amount')" />
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                <x-text-input id="amount_{{ $customer->id }}" name="amount" type="number" step="0.01" min="0.01" class="w-full pl-7" value="{{ old('amount') }}" required />
            </div>
        </div>

        <div>
            <x-input-label :value="__('Mode')" />
            <div class="grid grid-cols-3 gap-2 mt-1">
                @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                    <button type="button" @click="mode = '{{ $value }}'"
                        class="px-3 py-2 text-sm font-medium rounded-md border"
                        :class="mode === '{{ $value }}' ? 'bg-brand-50 border-brand-500 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div>
            <x-input-label for="note_{{ $customer->id }}" :value="__('Note (optional)')" />
            <x-text-input id="note_{{ $customer->id }}" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('Optional') }}" />
        </div>

        <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-2.5">
            {{ __('Save payment') }}
        </button>

        <p class="text-xs text-gray-400 text-center">{{ __('Customer gets a WhatsApp receipt after saving.') }}</p>

        @if ($customer->credit_balance > 0)
            <p class="text-xs text-green-600 text-center">
                {{ __('Credit available for their next invoice:') }} ₹{{ number_format($customer->credit_balance, 2) }}
            </p>
        @endif
    </form>
</div>
