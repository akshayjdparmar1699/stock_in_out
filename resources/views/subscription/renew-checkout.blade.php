<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Reactivate your plan') }} — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">

        <div class="bg-white border-b border-gray-100">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <span class="flex items-center gap-2.5 shrink-0">
                    <x-application-logo class="w-8 h-8" />
                    <span class="font-semibold text-gray-800 hidden sm:inline">{{ config('app.name') }}</span>
                </span>
                <span class="text-sm text-gray-400">{{ $admin->email }}</span>
            </div>
        </div>

        <div class="max-w-md mx-auto px-4 sm:px-6 py-10 lg:py-16">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </span>
                    <div>
                        <h1 class="font-bold text-gray-900">{{ __('Reactivate your plan') }}</h1>
                        <p class="text-sm text-gray-500">{{ $company->name }}</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                        <button type="button" id="term-1-button" class="term-button px-4 py-1.5 rounded-full text-sm font-semibold transition-colors">{{ __('1 year') }}</button>
                        <button type="button" id="term-3-button" class="term-button px-4 py-1.5 rounded-full text-sm font-semibold transition-colors flex items-center gap-1.5">
                            {{ __('3 years') }}
                            <span class="px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">{{ __('Save 30%') }}</span>
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-b border-gray-100 py-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                        <p class="text-xs text-gray-400" id="term-caption"></p>
                        <p class="text-xs text-green-600 font-medium mt-0.5 hidden" id="term-discount-note">{{ __('30% off the regular 3-year price') }}</p>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">
                        <span id="term-amount"></span>
                        <span class="text-sm font-normal text-gray-400" id="term-unit"></span>
                    </p>
                </div>

                <p class="text-xs text-gray-400 mt-4">
                    {{ __('You will pay via UPI, card or netbanking on Razorpay\'s secure checkout — PhonePe, Google Pay and Paytm all work.') }}
                </p>

                <button id="pay-button" type="button" class="mt-6 w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3 transition-colors">
                    <span id="pay-button-label"></span>
                </button>

                <p id="pay-error" class="hidden text-sm text-red-600 text-center mt-3"></p>
            </div>
        </div>

        <form id="verify-form" method="POST" action="{{ route('subscription.renew.verify') }}" class="hidden">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
            <input type="hidden" name="years" id="verify-years">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>

        <script>
            // One Razorpay order per term was already created server-side (an
            // order is fixed to the amount it was made with, so the term
            // toggle below can only switch between orders prepared upfront,
            // not change one after the fact).
            var orderIds = @json($orderIds);
            var prices = @json($prices);
            var term = {{ $initialTerm }};

            function formatRupees(n) {
                return '₹' + Number(n).toLocaleString('en-IN');
            }

            function renderTerm() {
                document.getElementById('term-1-button').className = 'term-button px-4 py-1.5 rounded-full text-sm font-semibold transition-colors' + (term === 1 ? ' bg-white shadow-sm text-gray-900' : ' text-gray-500');
                document.getElementById('term-3-button').className = 'term-button px-4 py-1.5 rounded-full text-sm font-semibold transition-colors flex items-center gap-1.5' + (term === 3 ? ' bg-white shadow-sm text-gray-900' : ' text-gray-500');

                document.getElementById('term-caption').textContent = @json($plan->hasUnlimitedBranches() ? __('Unlimited branches') : __('1 branch')) + ' · ' + (term === 3 ? @json(__('Billed once for 3 years')) : @json(__('Billed yearly')));
                document.getElementById('term-discount-note').classList.toggle('hidden', term !== 3);
                document.getElementById('term-amount').textContent = formatRupees(prices[term]);
                document.getElementById('term-unit').textContent = '/' + (term === 3 ? @json(__('3 yrs')) : @json(__('yr')));
                document.getElementById('pay-button-label').textContent = @json(__('Pay :amount now', ['amount' => ':amount'])).replace(':amount', formatRupees(prices[term]));
                document.getElementById('verify-years').value = term;
            }

            document.getElementById('term-1-button').addEventListener('click', function () { term = 1; renderTerm(); });
            document.getElementById('term-3-button').addEventListener('click', function () { term = 3; renderTerm(); });
            renderTerm();

            document.getElementById('pay-button').addEventListener('click', function () {
                var rzp = new Razorpay({
                    key: @json($razorpayKey),
                    order_id: orderIds[term],
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
