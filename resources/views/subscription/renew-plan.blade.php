<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Choose a plan') }} — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">

        <div class="bg-white border-b border-gray-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <span class="flex items-center gap-2.5 shrink-0">
                    <x-application-logo class="w-8 h-8" />
                    <span class="font-semibold text-gray-800">{{ config('app.name') }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Log out') }}</button>
                </form>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
            <div class="text-center max-w-xl mx-auto">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900">{{ __('Your free trial has ended') }}</h1>
                <p class="mt-3 text-gray-500">{{ __('Choose a plan to keep billing, managing stock and seeing reports for :company.', ['company' => $company->name]) }}</p>
            </div>

            <div class="mt-10 max-w-5xl mx-auto" x-data="{ term: 1 }">
                <div class="flex justify-center mb-8">
                    <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                        <button type="button" @click="term = 1" class="px-4 py-1.5 rounded-full text-sm font-semibold transition-colors" :class="term === 1 ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'">
                            {{ __('1 year') }}
                        </button>
                        <button type="button" @click="term = 3" class="px-4 py-1.5 rounded-full text-sm font-semibold transition-colors flex items-center gap-1.5" :class="term === 3 ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'">
                            {{ __('3 years') }}
                            <span class="px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">{{ __('Save 30%') }}</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($plans as $plan)
                        @php
                            $isGold = (bool) $plan->is_popular;
                            $price1 = $plan->priceForYears(1);
                            $price3 = $plan->priceForYears(3);
                            $monthly1 = $plan->monthlyEquivalent(1);
                            $monthly3 = $plan->monthlyEquivalent(3);
                        @endphp
                        <div class="relative rounded-2xl p-6 flex flex-col {{ $isGold ? 'bg-gradient-to-br from-brand-600 to-brand-800 border-2 border-[#FFB020] shadow-xl' : 'bg-white border border-gray-200 shadow-sm' }}">
                            @if ($isGold)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#FFB020] text-brand-900 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide whitespace-nowrap">{{ __('Most popular') }}</span>
                            @endif

                            <h3 class="font-bold text-lg {{ $isGold ? 'text-white' : 'text-gray-900' }}">{{ $plan->name }}</h3>
                            <p class="text-sm mt-1.5 {{ $isGold ? 'text-white/80' : 'text-gray-500' }}">{{ $plan->tagline }}</p>

                            <div class="mt-5 flex items-end gap-1.5 flex-wrap">
                                <span class="text-3xl font-extrabold {{ $isGold ? 'text-white' : 'text-gray-900' }}">
                                    <span x-show="term === 1">&#8377;{{ number_format($price1) }}</span>
                                    <span x-show="term === 3" style="display: none;">&#8377;{{ number_format($price3) }}</span>
                                </span>
                                <span class="text-sm mb-1 {{ $isGold ? 'text-white/70' : 'text-gray-400' }}">
                                    <span x-show="term === 1">{{ __('/ year') }}</span>
                                    <span x-show="term === 3" style="display: none;">{{ __('/ 3 years') }}</span>
                                </span>
                            </div>
                            <p class="text-xs {{ $isGold ? 'text-white/60' : 'text-gray-400' }}">
                                <span x-show="term === 1">{{ __('≈ :amount / month', ['amount' => '₹'.number_format($monthly1)]) }}</span>
                                <span x-show="term === 3" style="display: none;">{{ __('≈ :amount / month', ['amount' => '₹'.number_format($monthly3)]) }}</span>
                            </p>

                            <span class="inline-block mt-3 self-start px-3 py-1 rounded-full text-xs font-medium {{ $isGold ? 'bg-white/15 text-white' : 'bg-brand-50 text-brand-700' }}">
                                {{ $plan->hasUnlimitedBranches() ? __('Unlimited branches') : trans_choice('{1} 1 branch|[2,*] :count branches', $plan->branch_limit, ['count' => $plan->branch_limit]) }}
                            </span>

                            <div class="border-t mt-5 pt-4 space-y-2.5 flex-1 {{ $isGold ? 'border-white/15' : 'border-gray-100' }}">
                                @foreach ($plan->features as $feature)
                                    <div class="flex items-start gap-2 text-sm {{ $isGold ? 'text-white/90' : 'text-gray-700' }}">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $isGold ? 'text-[#FFB020]' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        <span>{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <a :href="term === 3 ? {{ \Illuminate\Support\Js::from(route('subscription.renew', ['plan' => $plan->id, 'years' => 3])) }} : {{ \Illuminate\Support\Js::from(route('subscription.renew', ['plan' => $plan->id, 'years' => 1])) }}"
                                class="mt-6 w-full text-center py-2.5 rounded-lg font-semibold text-sm transition-colors {{ $isGold ? 'bg-[#FFB020] text-brand-900 hover:brightness-95' : 'bg-white border-2 border-brand-600 text-brand-700 hover:bg-brand-50' }}">
                                <span x-show="term === 1">{{ __('Pay :amount and continue', ['amount' => '₹'.number_format($price1)]) }}</span>
                                <span x-show="term === 3" style="display: none;">{{ __('Pay :amount and continue', ['amount' => '₹'.number_format($price3)]) }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </body>
</html>
