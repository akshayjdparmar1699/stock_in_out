<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Start billing') }} — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">

        <!-- Top bar -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <a href="{{ route('get-started') }}" class="flex items-center gap-2.5 shrink-0">
                    <x-application-logo class="w-8 h-8" />
                    <span class="font-semibold text-gray-800 hidden sm:inline">{{ config('app.name') }}</span>
                </a>

                <div class="hidden lg:flex items-center gap-3 text-sm">
                    <span class="flex items-center gap-2 text-gray-400">
                        <span class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-xs">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        {{ __('Choose plan') }}
                    </span>
                    <span class="w-10 h-px bg-gray-200"></span>
                    <span class="flex items-center gap-2 text-gray-400">
                        <span class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-xs">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        {{ __('Business details') }}
                    </span>
                    <span class="w-10 h-px bg-gray-200"></span>
                    <span class="flex items-center gap-2 font-semibold text-brand-700">
                        <span class="w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs">3</span>
                        {{ __('Start billing') }}
                    </span>
                </div>

                <div class="lg:hidden flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    <span class="w-5 h-1.5 rounded-full bg-[#FFB020]"></span>
                </div>

                <span class="text-sm text-gray-400">{{ $admin->email }}</span>
            </div>
        </div>

        <div class="max-w-md mx-auto px-4 sm:px-6 py-10 lg:py-16">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-7.5h18A2.25 2.25 0 0121 11.25v6.75A2.25 2.25 0 0118.75 20.25H3.75A2.25 2.25 0 011.5 18V11.25A2.25 2.25 0 013.75 9z" /></svg>
                    </span>
                    <div>
                        <h1 class="font-bold text-gray-900">{{ __('Start billing') }}</h1>
                        <p class="text-sm text-gray-500">{{ $company->name }}</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-b border-gray-100 py-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $plan->hasUnlimitedBranches() ? __('Unlimited branches') : __('1 branch') }} &middot;
                            {{ $plan->isFreeTrial() ? __(':days-day trial', ['days' => $plan->trial_days]) : ($plan->billing_period === 'yearly' ? __('Billed yearly') : __('Billed monthly')) }}
                        </p>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">&#8377;{{ number_format($plan->price) }}
                        @unless ($plan->isFreeTrial())
                            <span class="text-sm font-normal text-gray-400">/{{ $plan->billing_period === 'yearly' ? __('yr') : __('mo') }}</span>
                        @endunless
                    </p>
                </div>

                <p class="text-xs text-gray-400 mt-4">
                    {{ __('You will pay via UPI, card or netbanking on Razorpay\'s secure checkout — PhonePe, Google Pay and Paytm all work.') }}
                </p>

                <button id="pay-button" type="button" class="mt-6 w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3 transition-colors">
                    {{ __('Pay :amount now', ['amount' => '₹'.number_format($plan->price)]) }}
                </button>

                <p id="pay-error" class="hidden text-sm text-red-600 text-center mt-3"></p>
            </div>
        </div>

        <form id="verify-form" method="POST" action="{{ route('get-started.billing.verify') }}" class="hidden">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>

        <script>
            document.getElementById('pay-button').addEventListener('click', function () {
                var rzp = new Razorpay({
                    key: @json($razorpayKey),
                    order_id: @json($orderId),
                    name: @json(config('app.name')),
                    description: @json($plan->name . ' ' . __('plan')),
                    prefill: {
                        name: @json($admin->name),
                        email: @json($admin->email),
                    },
                    theme: { color: '#6a00d9' },
                    handler: function (response) {
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                        document.getElementById('razorpay_signature').value = response.razorpay_signature;
                        document.getElementById('verify-form').submit();
                    },
                    modal: {
                        ondismiss: function () {
                            document.getElementById('pay-error').textContent = @json(__('Payment was not completed. You can try again.'));
                            document.getElementById('pay-error').classList.remove('hidden');
                        },
                    },
                });
                rzp.on('payment.failed', function (response) {
                    document.getElementById('pay-error').textContent = response.error.description || @json(__('Payment failed. Please try again.'));
                    document.getElementById('pay-error').classList.remove('hidden');
                });
                rzp.open();
            });
        </script>
    </body>
</html>
