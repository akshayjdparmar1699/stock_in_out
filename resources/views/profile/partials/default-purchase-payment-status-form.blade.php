<section class="max-w-xl">
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Default purchase status') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Which payment status a New Purchase starts on. You can still change it per purchase.') }}</p>
    </header>

    <form method="POST" action="{{ route('profile.default-purchase-payment-status') }}" class="mt-5 space-y-3">
        @csrf
        @method('patch')

        <div class="grid grid-cols-3 gap-2 max-w-sm">
            @foreach (['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')] as $value => $label)
                <button type="submit" name="default_purchase_payment_status" value="{{ $value }}"
                    @class([
                        'px-3 py-2.5 text-sm font-medium rounded-lg border-2',
                        'border-brand-600 text-brand-700 bg-brand-50' => $defaultPurchasePaymentStatus === $value,
                        'border-gray-200 text-gray-600 hover:bg-gray-50' => $defaultPurchasePaymentStatus !== $value,
                    ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if (session('status') === 'default-purchase-payment-status-updated')
            <p class="text-sm text-green-600">{{ __('Saved.') }}</p>
        @endif
    </form>
</section>
