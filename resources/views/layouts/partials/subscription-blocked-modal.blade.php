@php
    $statusLabel = match ($subscription->status) {
        \App\Models\Subscription::STATUS_TRIALING => __('Trial expired on :date', ['date' => optional($subscription->expiredAt())->format('d M Y') ?? '—']),
        \App\Models\Subscription::STATUS_ACTIVE => __('Expired on :date', ['date' => optional($subscription->expiredAt())->format('d M Y') ?? '—']),
        \App\Models\Subscription::STATUS_PAST_DUE => __('Payment failed'),
        \App\Models\Subscription::STATUS_CANCELED => $subscription->cancelled_at
            ? __('Cancelled on :date', ['date' => $subscription->cancelled_at->format('d M Y')])
            : __('Cancelled'),
        default => __('Not active'),
    };
@endphp

<div
    x-data="{
        seconds: 5,
        timer: null,
        init() {
            this.timer = setInterval(() => {
                this.seconds--;
                if (this.seconds <= 0) {
                    clearInterval(this.timer);
                    window.location.href = '{{ route('subscription.renew') }}';
                }
            }, 1000);
        },
    }"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    role="dialog"
    aria-modal="true"
>
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-8 text-center">
        <span class="mx-auto w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
        </span>

        <h2 class="mt-4 text-xl font-bold text-gray-900">{{ __('Please pay to continue') }}</h2>
        <p class="mt-2 text-sm text-gray-500">{{ __('Your subscription is not paid. Pay now to keep making bills, managing stock and seeing reports.') }}</p>

        <div class="mt-5 bg-gray-50 rounded-xl p-4 text-sm space-y-2">
            <div class="flex justify-between gap-3"><span class="text-gray-500">{{ __('Shop') }}</span><span class="font-semibold text-gray-900 text-right">{{ $company->name }}</span></div>
            <div class="flex justify-between gap-3"><span class="text-gray-500">{{ __('Plan') }}</span><span class="font-semibold text-gray-900 text-right">{{ $plan->name }}</span></div>
            <div class="flex justify-between gap-3"><span class="text-gray-500">{{ __('Status') }}</span><span class="font-semibold text-red-600 text-right">{{ $statusLabel }}</span></div>
        </div>

        <div class="mt-4 flex items-start gap-2 bg-green-50 text-green-700 rounded-lg p-3 text-sm text-left">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>{{ __('Your bills, customers and stock are saved. Nothing is deleted.') }}</span>
        </div>

        <a href="{{ route('subscription.renew') }}" @click="clearInterval(timer)"
            class="mt-5 block w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg py-3">
            {{ __('Pay now and continue') }} &rarr;
        </a>

        <p class="mt-3 text-xs text-gray-400" x-text="`{{ __('Taking you to the payment page in') }} ${seconds} {{ __('seconds...') }}`"></p>

        <div class="mt-4 flex items-center justify-center gap-5 text-sm">
            <a href="tel:+910000000000" class="text-brand-600 font-medium hover:underline">{{ __('Need help? Call us') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-500 hover:underline">{{ __('Log out') }}</button>
            </form>
        </div>
    </div>
</div>
