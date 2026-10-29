<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Business details') }} — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50" x-data="{ showPassword: false, showConfirm: false }">

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
                    <span class="flex items-center gap-2 font-semibold text-brand-700">
                        <span class="w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs">2</span>
                        {{ __('Business details') }}
                    </span>
                    <span class="w-10 h-px bg-gray-200"></span>
                    <span class="flex items-center gap-2 text-gray-400">
                        <span class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-xs">3</span>
                        {{ __('Start billing') }}
                    </span>
                </div>

                <div class="lg:hidden flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    <span class="w-5 h-1.5 rounded-full bg-[#FFB020]"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-200"></span>
                </div>

                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600">
                    <span class="text-brand-600 hover:text-brand-700 underline">{{ __('Log in') }}</span>
                </a>
            </div>
        </div>

        <div class="max-w-xl mx-auto px-4 sm:px-6 py-10 lg:py-14">
            <div class="text-center mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Tell us about your business') }}</h1>
                <p class="text-gray-500 text-sm mt-2">
                    {{ __('You picked :name', ['name' => $plan->name]) }}
                    &middot; <span class="font-medium text-brand-700">{{ $plan->isFreeTrial() ? ($plan->price > 0 ? __(':price for :days days', ['price' => '₹'.$plan->price, 'days' => $plan->trial_days]) : __('Free for :days days', ['days' => $plan->trial_days])) : '₹'.number_format($plan->priceForYears($years)).' / '.($plan->billing_period === 'yearly' ? ($years === 3 ? __('3 years') : __('year')) : __('month')) }}</span>
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-100 text-red-700 text-sm px-4 py-3">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('get-started.details.store') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-8 space-y-5">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <input type="hidden" name="years" value="{{ $years }}">

                <div>
                    <x-input-label for="company_name" :value="__('Business name')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name')" required autofocus placeholder="{{ __('e.g. Wholesale Na Vepari') }}" />
                </div>

                <div>
                    <x-input-label for="branch_name" :value="__('Shop / godown name')" />
                    <x-text-input id="branch_name" name="branch_name" type="text" class="mt-1 block w-full" :value="old('branch_name')" required placeholder="{{ __('e.g. Main Branch') }}" />
                    <p class="text-xs text-gray-400 mt-1">{{ __('You can add more branches later from Gold.') }}</p>
                </div>

                <div class="border-t border-gray-100 pt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="admin_name" :value="__('Your name')" />
                        <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full" :value="old('admin_name')" required placeholder="{{ __('Full name') }}" />
                    </div>
                    <div>
                        <x-input-label for="admin_phone" :value="__('Phone (optional)')" />
                        <x-text-input id="admin_phone" name="admin_phone" type="tel" class="mt-1 block w-full" :value="old('admin_phone')" placeholder="{{ __('10-digit mobile number') }}" />
                    </div>
                </div>

                <div>
                    <x-input-label for="admin_email" :value="__('Email')" />
                    <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email')" required autocomplete="username" placeholder="you@business.com" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="admin_password" :value="__('Password')" />
                        <div class="relative mt-1">
                            <input id="admin_password" name="admin_password" :type="showPassword ? 'text' : 'password'"
                                class="block w-full pr-10 border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm"
                                required autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg x-show="showPassword" style="display:none" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <x-input-label for="admin_password_confirmation" :value="__('Confirm password')" />
                        <div class="relative mt-1">
                            <input id="admin_password_confirmation" name="admin_password_confirmation" :type="showConfirm ? 'text' : 'password'"
                                class="block w-full pr-10 border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm"
                                required autocomplete="new-password" placeholder="{{ __('Type it again') }}">
                            <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg x-show="showConfirm" style="display:none" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3 transition-colors">
                    {{ $plan->isFreeTrial() && $plan->price == 0 ? __('Start my free trial') : __('Continue to payment') }}
                </button>

                <p class="text-xs text-gray-400 text-center">
                    {{ __('By continuing you agree this account is for business use.') }}
                </p>
            </form>
        </div>
    </body>
</html>
