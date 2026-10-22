@php
    $plan = $subscription?->plan;
    $expiresAt = $subscription?->expiredAt();
@endphp

<section class="max-w-xl">
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Plan & billing') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Your current subscription plan and when it needs renewing.') }}</p>
    </header>

    @if ($plan)
        <div class="mt-5 bg-gray-50 rounded-xl p-4 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-900">{{ $plan->name }}</span>
                    @if ($subscription->is_lifetime)
                        <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-semibold">{{ __('Lifetime') }}</span>
                    @elseif ($subscription->isActive())
                        <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-semibold">{{ __('Active') }}</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-xs font-semibold">{{ __('Not active') }}</span>
                    @endif
                </div>

                @if ($subscription->is_lifetime)
                    <p class="text-sm text-gray-500 mt-1">{{ __('Lifetime access — this plan never expires.') }}</p>
                @elseif ($expiresAt)
                    <p class="text-sm mt-1 {{ $subscription->isActive() ? 'text-gray-500' : 'text-red-600 font-medium' }}">
                        @if ($subscription->isActive())
                            {{ $subscription->isOnTrial() ? __('Trial ends on :date', ['date' => $expiresAt->format('d M Y')]) : __('Renews on :date', ['date' => $expiresAt->format('d M Y')]) }}
                        @else
                            {{ __('Expired on :date', ['date' => $expiresAt->format('d M Y')]) }}
                        @endif
                    </p>
                @else
                    <p class="text-sm text-gray-500 mt-1">&#8377;{{ number_format($plan->price) }} {{ $plan->billing_period === 'yearly' ? __('/ year') : __('/ month') }}</p>
                @endif
            </div>

            @unless ($subscription->is_lifetime && $subscription->isActive())
                <a href="{{ route('subscription.renew') }}" class="shrink-0 inline-flex items-center px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg whitespace-nowrap">
                    {{ $subscription->isActive() ? __('Manage plan') : __('Renew plan') }}
                </a>
            @endunless
        </div>
    @else
        <p class="mt-5 text-sm text-gray-400">{{ __('No subscription found for your company.') }}</p>
    @endif
</section>
