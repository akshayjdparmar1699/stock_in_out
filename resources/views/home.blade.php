<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }} — {{ __('Billing, stock and udhaar in one app') }}</title>
        <meta name="description" content="{{ __('Add your customers and items once, then make a bill in seconds and send it on WhatsApp. Stock, profit and udhaar update on their own, for every branch.') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white" x-data="{ mobileNav: false }">

        <!-- Nav -->
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    <x-application-logo class="w-8 h-8" />
                    <span class="font-semibold text-gray-800">{{ config('app.name') }}</span>
                </a>

                <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-gray-600">
                    <a href="#home" class="hover:text-gray-900">{{ __('Home') }}</a>
                    <a href="#how-it-works" class="hover:text-gray-900">{{ __('How it works') }}</a>
                    <a href="#features" class="hover:text-gray-900">{{ __('Features') }}</a>
                    <a href="#about" class="hover:text-gray-900">{{ __('About') }}</a>
                    <a href="#contact" class="hover:text-gray-900">{{ __('Contact Us') }}</a>
                </nav>

                <div class="hidden lg:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Login') }}</a>
                    <a href="{{ route('get-started') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg">{{ __('Start free') }}</a>
                </div>

                <button type="button" @click="mobileNav = !mobileNav" class="lg:hidden w-9 h-9 flex items-center justify-center text-gray-600">
                    <svg x-show="!mobileNav" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    <svg x-show="mobileNav" style="display:none" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div x-show="mobileNav" style="display:none" @click="mobileNav = false" class="lg:hidden border-t border-gray-100 px-4 py-4 space-y-3 bg-white">
                <a href="#home" class="block text-sm font-medium text-gray-700">{{ __('Home') }}</a>
                <a href="#how-it-works" class="block text-sm font-medium text-gray-700">{{ __('How it works') }}</a>
                <a href="#features" class="block text-sm font-medium text-gray-700">{{ __('Features') }}</a>
                <a href="#about" class="block text-sm font-medium text-gray-700">{{ __('About') }}</a>
                <a href="#contact" class="block text-sm font-medium text-gray-700">{{ __('Contact Us') }}</a>
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700">{{ __('Login') }}</a>
                    <a href="{{ route('get-started') }}" class="flex-1 text-center px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold">{{ __('Start free') }}</a>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section id="home" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-semibold bg-brand-50 text-brand-700 rounded-full px-3 py-1.5">
                    {{ __('Billing, stock and udhaar in one app') }}
                </span>

                <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-gray-900">
                    {{ __('Your wholesale shop, fully on your phone and computer.') }}
                </h1>

                <p class="mt-4 text-gray-500 leading-relaxed">
                    {{ __('Add your customers and items once, then make a bill in seconds and send it on WhatsApp. Stock, profit and udhaar update on their own, for every branch.') }}
                </p>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('get-started') }}" class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg">{{ __('Start using now') }}</a>
                    <a href="#how-it-works" class="inline-flex items-center px-6 py-3 border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-sm rounded-lg">{{ __('See how it works') }}</a>
                </div>

                <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-500">
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('WhatsApp bill sharing') }}</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Multi-branch') }}</span>
                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Low stock alerts') }}</span>
                </div>
            </div>

            <!-- Dashboard preview mockup -->
            <div class="relative">
                <div class="absolute -inset-6 bg-brand-100/40 rounded-[2rem] -z-10 hidden lg:block"></div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-xl p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ __('Good morning, Rameshbhai') }}</p>
                            <p class="text-xs text-gray-400">{{ __('Main branch · Today') }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-lg">+ {{ __('New Bill') }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">{{ __('Sales') }}</p>
                            <p class="font-bold text-gray-900">&#8377;1,24,500</p>
                            <p class="text-[11px] text-gray-400">48 {{ __('invoices') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">{{ __('Profit') }}</p>
                            <p class="font-bold text-green-600">&#8377;9,860</p>
                            <p class="text-[11px] text-gray-400">{{ __('sales minus cost') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">{{ __('Customers owe you') }}</p>
                            <p class="font-bold text-green-600">&#8377;3,42,100</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">{{ __('Stock value') }}</p>
                            <p class="font-bold text-gray-900">&#8377;4,60,900</p>
                            <p class="text-[11px] text-red-500">5 {{ __('items low on stock') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1">{{ __('Sales and profit') }} &middot; {{ __('Last 7 days') }}</p>
                        <svg viewBox="0 0 240 60" class="w-full h-14">
                            <polyline points="0,40 30,25 60,38 90,15 120,30 150,10 180,28 210,18 240,30" fill="none" stroke="#6a00d9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            <polyline points="0,52 30,50 60,53 90,47 120,50 150,44 180,49 210,45 240,48" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how-it-works" class="bg-gray-50 py-16 lg:py-20" x-data="{ step: 1 }">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('How it works') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('From your first customer to a bill on WhatsApp in 5 steps.') }}</h2>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 mt-8">
                    @foreach ([
                        1 => __('Add customer'),
                        2 => __('Add items'),
                        3 => __('Add stock'),
                        4 => __('Create invoice'),
                        5 => __('Share on WhatsApp'),
                    ] as $n => $label)
                        <button type="button" @click="step = {{ $n }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full text-sm font-medium transition-colors"
                            :class="step === {{ $n }} ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200'">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs shrink-0"
                                :class="step === {{ $n }} ? 'bg-white/20' : 'bg-gray-100'">{{ $n }}</span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

                    <!-- Step 1 -->
                    <template x-if="step === 1">
                        <div class="contents">
                            <div>
                                <p class="text-xs font-semibold text-brand-600 uppercase">{{ __('Step 1') }}</p>
                                <h3 class="text-xl font-bold text-gray-900 mt-1">{{ __('Add your customers') }}</h3>
                                <p class="text-gray-500 mt-2 text-sm">{{ __('Open Customers and tap Add Customer. Enter the shop name, phone and village or city. Set a credit limit so you know when udhaar gets too high.') }}</p>
                                <ul class="mt-4 space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Search any customer by name or phone') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Balance due shows on every customer') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Over credit limit? Send a WhatsApp reminder') }}</li>
                                </ul>
                            </div>
                            <div>
                                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                    <p class="font-semibold text-gray-800 text-sm mb-3">{{ __('Add Customer') }}</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><p class="text-gray-400 mb-1">{{ __('Name') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">{{ __('Shree Ram Provision') }}</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Phone') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">98XXX XXX10</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Address') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">Kodinar</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Credit limit') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">&#8377;50,000</div></div>
                                    </div>
                                    <span class="inline-block mt-3 px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-md">{{ __('Save customer') }}</span>
                                </div>
                                <div class="mt-3 bg-white border border-gray-100 rounded-xl divide-y divide-gray-50">
                                    <div class="flex items-center justify-between px-4 py-2.5 text-sm"><span class="text-brand-600">{{ __('Patel Kirana Store') }}</span><span class="text-gray-700">&#8377;7,510.00</span></div>
                                    <div class="flex items-center justify-between px-4 py-2.5 text-sm"><span class="text-brand-600">{{ __('Mahadev Traders') }}</span><span class="text-gray-700">&#8377;0.00</span></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 2 -->
                    <template x-if="step === 2">
                        <div class="contents">
                            <div>
                                <p class="text-xs font-semibold text-brand-600 uppercase">{{ __('Step 2') }}</p>
                                <h3 class="text-xl font-bold text-gray-900 mt-1">{{ __('Add your items') }}</h3>
                                <p class="text-gray-500 mt-2 text-sm">{{ __('Open Items and tap Add Item. Set the unit, purchase price and selling price. Add a low stock minimum so you never run out without warning.') }}</p>
                                <ul class="mt-4 space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Two units per item, like bag and kg') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Purchase and selling price saved together') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Low stock alert below your minimum') }}</li>
                                </ul>
                            </div>
                            <div>
                                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                    <p class="font-semibold text-gray-800 text-sm mb-3">{{ __('Add Item') }}</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><p class="text-gray-400 mb-1">{{ __('Name') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">{{ __('Sugar') }}</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Unit') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">50kg {{ __('bag') }}</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Purchase price') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">&#8377;1,750</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Selling price') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">&#8377;1,900</div></div>
                                    </div>
                                    <span class="inline-block mt-3 px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-md">{{ __('Save item') }}</span>
                                </div>
                                <div class="mt-3 bg-white border border-gray-100 rounded-xl divide-y divide-gray-50">
                                    <div class="flex items-center justify-between px-4 py-2.5 text-sm"><span class="text-gray-700">{{ __('Sugar') }}, 50kg {{ __('bag') }}</span><span class="text-gray-500">&#8377;1,900</span></div>
                                    <div class="flex items-center justify-between px-4 py-2.5 text-sm"><span class="text-gray-700">{{ __('Rice') }}, 25kg {{ __('bag') }}</span><span class="text-gray-500">&#8377;1,400</span></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 3 -->
                    <template x-if="step === 3">
                        <div class="contents">
                            <div>
                                <p class="text-xs font-semibold text-brand-600 uppercase">{{ __('Step 3') }}</p>
                                <h3 class="text-xl font-bold text-gray-900 mt-1">{{ __('Add your opening stock') }}</h3>
                                <p class="text-gray-500 mt-2 text-sm">{{ __('Tell the app how much of each item you already have. After this, every bill and purchase updates it on its own — no manual counting.') }}</p>
                                <ul class="mt-4 space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Opening stock recorded per branch') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Stock updates automatically after every bill') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('See total stock value any time') }}</li>
                                </ul>
                            </div>
                            <div>
                                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                    <p class="font-semibold text-gray-800 text-sm mb-3">{{ __('Add Opening Stock') }}</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><p class="text-gray-400 mb-1">{{ __('Item') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">{{ __('Sugar') }}, 50kg {{ __('bag') }}</div></div>
                                        <div><p class="text-gray-400 mb-1">{{ __('Quantity') }}</p><div class="bg-white border border-gray-200 rounded-md px-2 py-1.5 text-gray-700">40</div></div>
                                    </div>
                                    <span class="inline-block mt-3 px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-md">{{ __('Save stock') }}</span>
                                </div>
                                <div class="mt-3 bg-white border border-gray-100 rounded-xl divide-y divide-gray-50">
                                    <div class="flex items-center justify-between px-4 py-2.5 text-sm"><span class="text-gray-700">{{ __('Sugar') }}, 50kg {{ __('bag') }}</span><span class="text-gray-500">40 {{ __('bags') }}</span></div>
                                    <div class="flex items-center justify-between px-4 py-2.5 text-sm"><span class="text-gray-700">{{ __('Rice') }}, 25kg {{ __('bag') }}</span><span class="text-gray-500">25 {{ __('bags') }}</span></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 4 -->
                    <template x-if="step === 4">
                        <div class="contents">
                            <div>
                                <p class="text-xs font-semibold text-brand-600 uppercase">{{ __('Step 4') }}</p>
                                <h3 class="text-xl font-bold text-gray-900 mt-1">{{ __('Create your first bill') }}</h3>
                                <p class="text-gray-500 mt-2 text-sm">{{ __('Pick the customer, add items, and the total — with discount, tax and transport if needed — is ready in seconds.') }}</p>
                                <ul class="mt-4 space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Pick customer, add items, done in seconds') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Discount, tax and transport included') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Udhaar and stock update automatically') }}</li>
                                </ul>
                            </div>
                            <div>
                                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-gray-800 text-sm">{{ __('New bill') }}</p>
                                        <span class="text-xs bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full">{{ __('Patel Kirana Store') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100"><span class="text-gray-600">{{ __('Sugar') }}, 50kg {{ __('bag') }} &times; 4</span><span class="text-gray-800">&#8377;7,600</span></div>
                                    <div class="flex items-center justify-between text-sm py-1.5"><span class="text-gray-600">{{ __('Rice') }}, 25kg {{ __('bag') }} &times; 2</span><span class="text-gray-800">&#8377;2,800</span></div>
                                    <div class="flex items-center justify-between text-sm font-semibold mt-2 pt-2 border-t border-gray-200"><span>{{ __('Total') }}</span><span>&#8377;10,400</span></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 5 -->
                    <template x-if="step === 5">
                        <div class="contents">
                            <div>
                                <p class="text-xs font-semibold text-brand-600 uppercase">{{ __('Step 5') }}</p>
                                <h3 class="text-xl font-bold text-gray-900 mt-1">{{ __('Share the bill instantly') }}</h3>
                                <p class="text-gray-500 mt-2 text-sm">{{ __('One tap sends a clean, professional bill straight to your customer\'s WhatsApp — no printer needed.') }}</p>
                                <ul class="mt-4 space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('One tap WhatsApp share') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Customer gets a clean PDF bill') }}</li>
                                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('You look professional, instantly') }}</li>
                                </ul>
                            </div>
                            <div>
                                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="font-semibold text-gray-800 text-sm">{{ __('Invoice') }} INV-B01-00705</p>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                                            {{ __('Share') }}
                                        </span>
                                    </div>
                                    <div class="bg-white rounded-lg border border-gray-100 p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ __('Om Sai Aalubhandar') }}</p>
                                                <p class="text-xs text-gray-400">{{ __('Kodinar') }}</p>
                                            </div>
                                            <p class="text-[11px] font-semibold text-brand-600 uppercase">{{ __('Invoice') }}</p>
                                        </div>
                                        <div class="flex items-center justify-between text-xs mt-3 pt-2 border-t border-gray-100">
                                            <span class="text-gray-600">{{ __('Sugar') }}, 50kg {{ __('bag') }} &times; 4</span>
                                            <span class="text-gray-800">&#8377;7,600</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm font-semibold mt-2 pt-2 border-t border-gray-200">
                                            <span>{{ __('Total') }}</span>
                                            <span>&#8377;7,600</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 bg-[#e7f5ea] rounded-xl p-3">
                                    <div class="bg-white rounded-xl rounded-tl-none p-3 shadow-sm">
                                        <p class="text-sm font-semibold text-gray-800">{{ __('Invoice from') }} {{ config('app.name') }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ __('Bill no.') }} INV-B01-00705 &middot; &#8377;7,600</p>
                                        <span class="inline-flex items-center gap-1 mt-2 text-xs text-green-700 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                            {{ __('PDF attached') }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2 text-right">{{ __('Sent via WhatsApp') }} &middot; {{ now()->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button type="button" @click="step = Math.max(1, step - 1)" :disabled="step === 1"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 disabled:opacity-40">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        {{ __('Previous') }}
                    </button>
                    <p class="text-sm text-gray-400">{{ __('Step') }} <span x-text="step"></span> {{ __('of') }} 5</p>
                    <button type="button" @click="step = Math.min(5, step + 1)" :disabled="step === 5"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 disabled:opacity-40">
                        {{ __('Next') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </button>
                </div>

                <div class="text-center mt-6">
                    <a href="{{ route('get-started') }}" class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg">{{ __('Try it now, start free') }}</a>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Features') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Everything you see in the app.') }}</h2>
                </div>

                @php
                    $features = [
                        ['icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25', 'title' => __('Dashboard'), 'desc' => __('Sales, profit, purchases and stock value for today, this week, this month or all time.')],
                        ['icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', 'title' => __('Customers and udhaar'), 'desc' => __('Balance due, credit limits and one-tap WhatsApp reminders for customers over their limit.')],
                        ['icon' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9', 'title' => __('Items, stock and batches'), 'desc' => __('Two units per item, a full stock movement ledger and low stock alerts below your minimum.')],
                        ['icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'title' => __('Invoices'), 'desc' => __('Bills with stamp and pay QR, discount, tax and transport. Share, download PDF or print.')],
                        ['icon' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z', 'title' => __('Purchases and suppliers'), 'desc' => __('Record purchases as paid, partial or unpaid and always know how much you owe each supplier.')],
                        ['icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => __('Expenses, staff and partners'), 'desc' => __('Track majuri, salary, petrol and other kharcha by category, staff member and date.')],
                        ['icon' => 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64M6 3.75h12M6.75 21V3.75m10.5 17.25V3.75M4.5 21V9.75A2.25 2.25 0 016.75 7.5h10.5a2.25 2.25 0 012.25 2.25V21', 'title' => __('Branches and users'), 'desc' => __('Run many shops or godowns from one account and give each user access to their branch.')],
                        ['icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.625c.621 0 1.125.504 1.125 1.125V6h-.75a.75.75 0 01-.75-.75v-.75m0 0H3.75', 'title' => __('Money position'), 'desc' => __('See what customers owe you, what you owe suppliers and the net amount you will receive.')],
                        ['icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z', 'title' => __('WhatsApp everywhere'), 'desc' => __('Share bills, remind customers about payment and notify the admin about low stock.')],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-10">
                    @foreach ($features as $feature)
                        <div class="bg-gray-50 rounded-xl p-5">
                            <span class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" /></svg>
                            </span>
                            <p class="font-semibold text-gray-900 mt-3">{{ $feature['title'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- About -->
        <section id="about" class="bg-gray-50 py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('About us') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Made by people who know the wholesale counter.') }}</h2>
                    <p class="mt-4 text-gray-500 leading-relaxed">
                        {{ __("Wholesale Na Vepari is built for Gujarat's wholesalers, aalubhandars, kirana suppliers and traders who sell in bags and boxes and give udhaar every day. No accounting knowledge needed: if you can use WhatsApp, you can use this app.") }}
                    </p>
                    <p class="mt-3 text-sm text-gray-400 italic">[{{ __('Add your story here: who built it, since when, and from which city.') }}]</p>
                </div>

                <div class="bg-gradient-to-br from-brand-600 to-brand-800 text-white rounded-2xl p-7 lg:p-8">
                    <h3 class="text-xl font-bold">{{ __('Ready to stop writing bills by hand?') }}</h3>
                    <p class="mt-2 text-white/80 text-sm">{{ __('Create your account, add your first customer and send your first WhatsApp bill today.') }}</p>
                    <div class="flex flex-wrap gap-3 mt-5">
                        <a href="{{ route('get-started') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-brand-700 font-semibold text-sm rounded-lg">{{ __('Start free') }}</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 border border-white/40 text-white font-semibold text-sm rounded-lg">{{ __('I already have an account') }}</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact" class="bg-[#1b1030] text-white py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-[#FFB020] uppercase">{{ __('Contact Us') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold">{{ __('Need help setting up?') }}</h2>
                    <p class="mt-3 text-white/70 text-sm">{{ __("Call or WhatsApp us. We'll help you add your customers, items and opening stock.") }}</p>

                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg></span>
                            <span>[{{ __('Your phone / WhatsApp number') }}]</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg></span>
                            <span>[{{ __('Your email address') }}]</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg></span>
                            <span>[{{ __('Your office address') }}]</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white text-gray-900 rounded-2xl p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h3 class="font-semibold text-gray-900">{{ __('Send us a message') }}</h3>

                    <form method="POST" action="{{ route('leads.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Your name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            </div>
                            <div>
                                <x-input-label for="shop_name" :value="__('Shop / business name')" />
                                <x-text-input id="shop_name" name="shop_name" type="text" class="mt-1 block w-full" :value="old('shop_name')" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="phone" :value="__('Mobile number')" />
                            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" required />
                        </div>
                        <div>
                            <x-input-label for="message" :value="__('Message')" />
                            <textarea id="message" name="message" rows="3" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3">
                            {{ __('Send message') }}
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-black text-white/60 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="w-7 h-7" />
                    <span class="font-semibold text-white text-sm">{{ config('app.name') }}</span>
                </a>
                <nav class="flex items-center gap-5 text-sm">
                    <a href="#how-it-works" class="hover:text-white">{{ __('How it works') }}</a>
                    <a href="#features" class="hover:text-white">{{ __('Features') }}</a>
                    <a href="#contact" class="hover:text-white">{{ __('Contact Us') }}</a>
                    <a href="{{ route('login') }}" class="hover:text-white">{{ __('Login') }}</a>
                </nav>
                <p class="text-xs">&copy; {{ now()->year }} {{ config('app.name') }}</p>
            </div>
        </footer>
    </body>
</html>
