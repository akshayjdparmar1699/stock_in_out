<div x-data="{ type: 'paid', mode: 'cash' }">
    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Record payment') }}</h3>

    <form method="POST" action="{{ route('suppliers.payments.store', $supplier) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="mode" :value="mode">

        <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm w-full">
            <button type="button" @click="type = 'paid'" class="flex-1 px-3 py-1.5 font-medium" :class="type === 'paid' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'">
                {{ __('We paid them') }}
            </button>
            <button type="button" @click="type = 'received'" class="flex-1 px-3 py-1.5 font-medium border-l border-gray-200" :class="type === 'received' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'">
                {{ __('They paid us back') }}
            </button>
        </div>
        <input type="hidden" name="type" :value="type">

        <div>
            <x-input-label for="amount_{{ $supplier->id }}" x-text="type === 'received' ? '{{ __('Refund amount') }}' : '{{ __('Amount we paid') }}'" />
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                <x-text-input id="amount_{{ $supplier->id }}" name="amount" type="number" step="0.01" min="0.01" class="w-full pl-7" value="{{ old('amount') }}" required />
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2" x-show="type === 'paid'">
            @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                <button type="button" @click="mode = '{{ $value }}'"
                    class="px-3 py-2 text-sm font-medium rounded-md border"
                    :class="mode === '{{ $value }}' ? 'bg-brand-50 border-brand-500 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div>
            <x-input-label for="note_{{ $supplier->id }}" :value="__('Note (optional)')" />
            <x-text-input id="note_{{ $supplier->id }}" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('e.g. Paid by Rameshbhai') }}" />
        </div>

        <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-2.5">
            {{ __('Save') }}
        </button>

        <p class="text-xs text-gray-400 text-center">{{ __('Payment is adjusted against their oldest unpaid purchase first.') }}</p>

        @if ($supplier->credit_balance > 0)
            <p class="text-xs text-green-600 text-center">
                {{ __('Credit available for our next purchase:') }} ₹{{ number_format($supplier->credit_balance, 2) }}
            </p>
        @endif
    </form>
</div>
