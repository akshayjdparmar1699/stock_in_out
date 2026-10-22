<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }} — {{ __('Billing, stock and customer credit. All in one simple app.') }}</title>
        <meta name="description" content="{{ __('Make a bill in seconds and send it on WhatsApp. Your stock goes down by itself, and you always know who owes you money. Works on your phone and your computer.') }}">

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
                    <a href="#features" class="hover:text-gray-900">{{ __('Features') }}</a>
                    <a href="#how-it-works" class="hover:text-gray-900">{{ __('How it works') }}</a>
                    <a href="#phone-and-computer" class="hover:text-gray-900">{{ __('Phone and computer') }}</a>
                    <a href="#questions" class="hover:text-gray-900">{{ __('Questions') }}</a>
                    <a href="#contact" class="hover:text-gray-900">{{ __('Contact') }}</a>
                </nav>

                <div class="hidden lg:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" /></svg>
                        {{ __('Login') }}
                    </a>
                    <a href="#contact" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg">{{ __('Book free demo') }}</a>
                </div>

                <button type="button" @click="mobileNav = !mobileNav" class="lg:hidden w-9 h-9 flex items-center justify-center text-gray-600">
                    <svg x-show="!mobileNav" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    <svg x-show="mobileNav" style="display:none" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div x-show="mobileNav" style="display:none" @click="mobileNav = false" class="lg:hidden border-t border-gray-100 px-4 py-4 space-y-3 bg-white">
                <a href="#features" class="block text-sm font-medium text-gray-700">{{ __('Features') }}</a>
                <a href="#how-it-works" class="block text-sm font-medium text-gray-700">{{ __('How it works') }}</a>
                <a href="#phone-and-computer" class="block text-sm font-medium text-gray-700">{{ __('Phone and computer') }}</a>
                <a href="#questions" class="block text-sm font-medium text-gray-700">{{ __('Questions') }}</a>
                <a href="#contact" class="block text-sm font-medium text-gray-700">{{ __('Contact') }}</a>
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700">{{ __('Login') }}</a>
                    <a href="#contact" class="flex-1 text-center px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold">{{ __('Book free demo') }}</a>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="bg-gradient-to-b from-brand-50/70 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-brand-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        {{ __('Made for wholesalers and traders') }}
                    </span>

                    <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-gray-900">
                        {{ __('Bills, stock and customer credit.') }} <span class="text-brand-600">{{ __('All in one simple app.') }}</span>
                    </h1>

                    <p class="mt-5 text-gray-500 leading-relaxed max-w-lg">
                        {{ __('Make a bill in seconds and send it on WhatsApp. Your stock goes down by itself, and you always know who owes you money. Works on your phone and your computer.') }}
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="#contact" class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg">{{ __('Book a free demo') }}</a>
                        <a href="#how-it-works" class="inline-flex items-center gap-2 px-6 py-3 border border-gray-300 hover:bg-white text-gray-700 font-semibold text-sm rounded-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653z" /></svg>
                            {{ __('See how it works') }}
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-600">
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('No computer knowledge needed') }}</span>
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Bills on WhatsApp') }}</span>
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Many shops, one app') }}</span>
                    </div>

                    <p class="mt-5 text-sm text-gray-400">
                        {{ __('Already using it?') }} <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">{{ __('Login here') }} &rsaquo;</a>
                    </p>
                </div>

                <!-- Device mockup -->
                <div class="relative hidden sm:block">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-2xl p-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <x-application-logo class="w-6 h-6" />
                                <span class="text-sm font-semibold text-gray-800">{{ __('Good morning, Jaydip') }}</span>
                            </div>
                            <span class="px-3 py-1 bg-brand-600 text-white text-xs font-semibold rounded-md">+ {{ __('New Bill') }}</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mt-3 text-xs">
                            <div><p class="text-gray-400">{{ __('Sales') }}</p><p class="font-bold text-gray-900">&#8377;19.5L</p></div>
                            <div><p class="text-gray-400">{{ __('Profit') }}</p><p class="font-bold text-green-600">&#8377;1.35L</p></div>
                            <div><p class="text-gray-400">{{ __('Purchases') }}</p><p class="font-bold text-gray-900">&#8377;18L</p></div>
                            <div><p class="text-gray-400">{{ __('Stock value') }}</p><p class="font-bold text-gray-900">&#8377;4.6L</p></div>
                        </div>
                        <div class="mt-3 bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-1">{{ __('Sales and profit') }}</p>
                            <svg viewBox="0 0 240 60" class="w-full h-14" preserveAspectRatio="none">
                                <rect x="4" y="22" width="22" height="34" rx="3" fill="#DDBDFF" />
                                <rect x="34" y="14" width="22" height="42" rx="3" fill="#DDBDFF" />
                                <rect x="64" y="28" width="22" height="28" rx="3" fill="#DDBDFF" />
                                <rect x="94" y="5" width="22" height="51" rx="3" fill="#6A00D9" />
                                <rect x="124" y="20" width="22" height="36" rx="3" fill="#DDBDFF" />
                                <rect x="154" y="34" width="22" height="22" rx="3" fill="#DDBDFF" />
                            </svg>
                        </div>
                    </div>

                    <div class="absolute -top-5 -left-10 bg-white rounded-xl shadow-xl border border-gray-100 p-3 w-60 hidden lg:block">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-800">{{ __('Bill sent on WhatsApp') }}</p>
                                <p class="text-[11px] text-gray-400 truncate">INV-B01-00706 &middot; &#8377;3,200</p>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-10 -right-6 w-40 bg-white rounded-[1.5rem] border-[6px] border-gray-900 shadow-2xl p-2.5 hidden sm:block">
                        <div class="w-10 h-1 rounded-full bg-gray-900 mx-auto mb-2"></div>
                        <p class="text-[10px] font-semibold text-gray-800">INV-B01-00706</p>
                        <span class="inline-block mt-1 text-[9px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full">{{ __('PAID') }} &middot; &#8377;3,200</span>
                        <p class="text-[9px] text-gray-400 mt-1">{{ __('Patel Kirana Store') }}</p>
                        <div class="mt-2 space-y-1 text-[9px] text-gray-500">
                            <div class="flex justify-between"><span>Bateta 2 BAG</span><span>&#8377;1,300</span></div>
                            <div class="flex justify-between"><span>Dungali 1 BAG</span><span>&#8377;1,800</span></div>
                            <div class="flex justify-between"><span>{{ __('Transport') }}</span><span>&#8377;100</span></div>
                        </div>
                        <span class="block mt-2 text-center bg-green-600 text-white text-[9px] font-semibold rounded-md py-1.5">{{ __('Share on WhatsApp') }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Everything in one app -->
        <section id="features" class="py-16 lg:py-24" x-data="{ cat: 'billing' }">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Everything in one app') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Your whole shop, from buying to selling') }}</h2>
                    <p class="mt-3 text-gray-500">{{ __('Each part of your business has its own simple screen. Tap one to see what it does.') }}</p>
                </div>

                @php
                    $appCategories = [
                        'billing' => ['icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'color' => 'bg-brand-50 text-brand-600', 'title' => __('Billing'), 'blurb' => __('Bills in seconds, sent on WhatsApp')],
                        'customers' => ['icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', 'color' => 'bg-pink-50 text-pink-600', 'title' => __('Customers'), 'blurb' => __('Who owes you, and how much')],
                        'items' => ['icon' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25m0-9v9l-9 5.25m0-9l9 5.25', 'color' => 'bg-amber-50 text-amber-600', 'title' => __('Items'), 'blurb' => __('Your products with price and units')],
                        'stock' => ['icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6.25 3.75h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z', 'color' => 'bg-blue-50 text-blue-600', 'title' => __('Stock'), 'blurb' => __('Every bag in and out, with reason')],
                        'purchases' => ['icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M18.75 10.5h-10.5M8.25 18.75h-3.375c-.621 0-1.125-.504-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125H6.75m3-6.75h1.5c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125h-1.5', 'color' => 'bg-gray-100 text-gray-600', 'title' => __('Purchases'), 'blurb' => __('Record what you buy')],
                        'suppliers' => ['icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6a1.5 1.5 0 003 0m-6 0a1.5 1.5 0 013 0m-6 0h-6v-4.5m12 4.5h3V12a6 6 0 00-6-6h-3.75a.75.75 0 00-.75.75V4.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 004.5 4.5v2.25m0 0v4.5m0-4.5h-.75a1.5 1.5 0 00-1.5 1.5v3a1.5 1.5 0 001.5 1.5H4.5', 'color' => 'bg-teal-50 text-teal-600', 'title' => __('Suppliers'), 'blurb' => __('How much you owe each supplier')],
                        'expenses' => ['icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-rose-50 text-rose-600', 'title' => __('Expenses'), 'blurb' => __('Transport, labour, fuel and more')],
                        'staff' => ['icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', 'color' => 'bg-green-50 text-green-600', 'title' => __('Staff'), 'blurb' => __('Salary and advance for your team')],
                        'reports' => ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'color' => 'bg-yellow-50 text-yellow-600', 'title' => __('Reports'), 'blurb' => __('Sales and profit in simple words')],
                        'branches' => ['icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21', 'color' => 'bg-purple-50 text-purple-600', 'title' => __('Branches and users'), 'blurb' => __('Many shops, one login')],
                    ];
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-10">
                    @foreach ($appCategories as $key => $c)
                        <button type="button" @click="cat = '{{ $key }}'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border-2 text-left transition-colors"
                            :class="cat === '{{ $key }}' ? 'border-brand-500 bg-brand-50/60' : 'border-gray-200 hover:bg-gray-50'">
                            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 {{ $c['color'] }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}" /></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block font-semibold text-gray-900 text-sm">{{ $c['title'] }}</span>
                                <span class="block text-xs text-gray-500 truncate">{{ $c['blurb'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                @php
                    $appDetails = [
                        'billing' => [
                            'eyebrow' => __('Billing'), 'heading' => __('Make a bill'),
                            'desc' => __('Search item, set quantity, save. The bill goes to the customer on WhatsApp with your QR code.'),
                            'bullets' => [__('Sell in BAG or Kg in one bill'), __('Full payment, part payment or credit'), __('Print, PDF or WhatsApp')],
                            'panelTitle' => __('New Bill'), 'panelAction' => __('Save and share'),
                            'rows' => [['Bateta', '2 BAG x ₹650', '₹1,300'], ['Dungali', '1 BAG x ₹1,800', '₹1,800']],
                            'total' => ['Total', __('Paid by UPI'), '₹3,100'],
                        ],
                        'customers' => [
                            'eyebrow' => __('Customers'), 'heading' => __('Know who owes you'),
                            'desc' => __('Every bill and payment is written in the customer\'s account by itself, so the balance is always right.'),
                            'bullets' => [__('Set a credit limit for each customer'), __('Opening balance from your old notebook'), __('Record cash, UPI or bank payments')],
                            'panelTitle' => __('Babubhai Rangpur'), 'panelAction' => __('Receive payment'),
                            'rows' => [[__('Bill'), '16 Oct', '+ ₹3,250'], [__('Paid by UPI'), '14 Oct', '- ₹5,000']],
                            'total' => [__('Balance due'), '', '₹20,910'],
                        ],
                        'items' => [
                            'eyebrow' => __('Items'), 'heading' => __('Add your items once'),
                            'desc' => __('Set the unit, purchase price and selling price. Sell the same item in two units, like BAG and Kg.'),
                            'bullets' => [__('Two units per item, like BAG and Kg'), __('Purchase and selling price saved together'), __('Low stock alert below your minimum')],
                            'panelTitle' => __('Items'), 'panelAction' => __('+ Add Item'),
                            'rows' => [['Bateta', __('BAG') . ' · ' . __('Stock') . ' 427', '₹650'], ['Dungali', __('BAG') . ' · ' . __('Stock') . ' 12', '₹1,800']],
                            'total' => [__('Stock value'), '', '₹4,60,900'],
                        ],
                        'stock' => [
                            'eyebrow' => __('Stock'), 'heading' => __('Stock goes up when you buy, and down when you sell'),
                            'desc' => __('No counting at night. Every bag in and out is written with the reason and who did it.'),
                            'bullets' => [__('Stock added from purchases automatically'), __('Manual stock in/out with a reason'), __('Oldest batch sold first, for correct cost')],
                            'panelTitle' => __('Bateta'), 'panelAction' => __('+ Adjust stock'),
                            'rows' => [[__('From supplier'), __('Purchase'), '+70 BAG'], [__('Sold in bill'), __('Sale'), '-2 BAG']],
                            'total' => [__('Hansh sabu is low'), '', __('5 BOX left')],
                        ],
                        'purchases' => [
                            'eyebrow' => __('Purchases'), 'heading' => __('Record what you buy'),
                            'desc' => __('Add a purchase from a supplier, pay full, part or later, and stock goes up the moment you save.'),
                            'bullets' => [__('Pay supplier full, part or later'), __('Stock goes up the moment you save'), __('Attach the supplier\'s own bill number')],
                            'panelTitle' => __('New Purchase'), 'panelAction' => __('Save purchase'),
                            'rows' => [['Bateta', '70 BAG x ₹550', '₹38,500'], [__('Paid now'), __('Cash'), '- ₹10,000']],
                            'total' => [__('We will owe'), '', '₹28,500'],
                        ],
                        'suppliers' => [
                            'eyebrow' => __('Suppliers'), 'heading' => __('Always know what you owe'),
                            'desc' => __('Every supplier has their own running balance, built from purchases and payments.'),
                            'bullets' => [__('Running balance per supplier'), __('Pay anytime, partial or full'), __('Statement PDF on WhatsApp')],
                            'panelTitle' => __('Bharatbhai deesa'), 'panelAction' => __('Pay supplier'),
                            'rows' => [[__('Purchase'), '20 Oct', '+ ₹1,31,010'], [__('Paid'), '20 Oct', '- ₹25,510']],
                            'total' => [__('We still owe'), '', '₹1,05,500'],
                        ],
                        'expenses' => [
                            'eyebrow' => __('Expenses'), 'heading' => __('Every rupee tracked'),
                            'desc' => __('Transport, labour, rent, salary and more, sorted by category so you see where money goes.'),
                            'bullets' => [__('One tap categories like Transport and Labour'), __('See cost by category, day or person'), __('Salary and advance saved here automatically')],
                            'panelTitle' => __('Add Expense'), 'panelAction' => __('Save Expense'),
                            'rows' => [[__('Transport'), __('Cash'), '₹2,600'], [__('Labour'), __('UPI'), '₹4,450']],
                            'total' => [__('Today so far'), '', '₹7,050'],
                        ],
                        'staff' => [
                            'eyebrow' => __('Staff'), 'heading' => __('Pay your team, on time'),
                            'desc' => __('Track salary and advance separately for every staff member and partner, saved to expenses automatically.'),
                            'bullets' => [__('Salary and advance tracked separately'), __('Give each branch its own staff'), __('Payment history for every person')],
                            'panelTitle' => __('Mohitbhai'), 'panelAction' => __('Pay salary'),
                            'rows' => [[__('Salary for September'), __('UPI'), '₹12,000'], [__('Advance'), __('Cash'), '₹2,000']],
                            'total' => [__('Left to pay'), '', '₹10,000'],
                        ],
                        'reports' => [
                            'eyebrow' => __('Reports'), 'heading' => __('See your profit in simple words'),
                            'desc' => __('Sales, profit and expenses for today, this week or this month. No accounting words.'),
                            'bullets' => [__('Day-wise sales and profit chart'), __('Top items and top customers'), __('PDF and Excel export')],
                            'panelTitle' => __('Sales and profit'), 'panelAction' => __('Download PDF'),
                            'rows' => [[__('Sales'), __('332 bills'), '₹7,15,000'], [__('Gross profit'), '4.3%', '₹30,900']],
                            'total' => [__('For every ₹100 sold, you keep'), '', '₹3'],
                        ],
                        'branches' => [
                            'eyebrow' => __('Branches and users'), 'heading' => __('One login, every shop'),
                            'desc' => __('Run many shops or godowns from one account. Give each staff member a login for just their branch.'),
                            'bullets' => [__('Separate stock and bills per branch'), __('Staff see only their own branch'), __('Owner sees every branch at once')],
                            'panelTitle' => __('Branches'), 'panelAction' => __('+ Add Branch'),
                            'rows' => [[__('Om Sai Aalubhandar (Sugar Factory)'), __('Admin'), ''], [__('Om Sai Aalubhandar (Charajapa)'), __('1 staff user'), '']],
                            'total' => [__('Users with login'), '', '2'],
                        ],
                    ];
                @endphp

                <div class="mt-4 bg-[#140b26] rounded-2xl p-6 lg:p-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    @foreach ($appDetails as $key => $d)
                        <template x-if="cat === '{{ $key }}'">
                            <div class="contents">
                                <div>
                                    <p class="text-xs font-semibold text-brand-400 uppercase tracking-wide">{{ $d['eyebrow'] }}</p>
                                    <h3 class="text-xl lg:text-2xl font-bold text-white mt-2">{{ $d['heading'] }}</h3>
                                    <p class="text-white/60 mt-3 text-sm leading-relaxed">{{ $d['desc'] }}</p>
                                    <ul class="mt-4 space-y-2 text-sm text-white/80">
                                        @foreach ($d['bullets'] as $bullet)
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 mt-0.5 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                {{ $bullet }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="bg-[#1f1437] rounded-xl p-5">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="font-semibold text-white text-sm">{{ $d['panelTitle'] }}</p>
                                        <span class="px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-md">{{ $d['panelAction'] }}</span>
                                    </div>
                                    @foreach ($d['rows'] as $row)
                                        <div class="flex items-center justify-between text-sm py-2 border-b border-white/10">
                                            <span>
                                                <span class="block text-white">{{ $row[0] }}</span>
                                                @if ($row[1])<span class="block text-xs text-white/40">{{ $row[1] }}</span>@endif
                                            </span>
                                            @if ($row[2])<span class="text-white font-medium">{{ $row[2] }}</span>@endif
                                        </div>
                                    @endforeach
                                    <div class="flex items-center justify-between pt-3">
                                        <span>
                                            <span class="block text-white font-semibold">{{ $d['total'][0] }}</span>
                                            @if ($d['total'][1])<span class="block text-xs text-white/40">{{ $d['total'][1] }}</span>@endif
                                        </span>
                                        @if ($d['total'][2])<span class="text-brand-300 font-bold text-lg">{{ $d['total'][2] }}</span>@endif
                                    </div>
                                </div>
                            </div>
                        </template>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Make a bill and send it on WhatsApp -->
        <section class="py-16 lg:py-20 bg-gray-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Billing') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Make a bill and send it on WhatsApp in one go') }}</h2>
                    <p class="mt-3 text-gray-500 leading-relaxed">{{ __('Search the item, tap plus for quantity, choose how the customer paid. Tap "Save and share" and the bill reaches their WhatsApp with a scan and pay QR code.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
                        @foreach ([__('Sell in BAG or Kg in the same bill'), __('Full, part or on credit'), __('PDF, print or WhatsApp'), __('Low stock warning after billing')] as $pill)
                            <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-sm font-medium text-gray-700">{{ $pill }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-brand-50/60 rounded-[2rem] p-6 lg:p-10 flex items-center justify-center gap-4">
                    <div class="w-44 bg-white rounded-[1.5rem] border-[6px] border-gray-900 shadow-xl p-2.5">
                        <div class="w-10 h-1 rounded-full bg-gray-900 mx-auto mb-2"></div>
                        <p class="text-[10px] font-semibold text-gray-800 mb-1.5">{{ __('New Bill') }}</p>
                        <div class="flex items-center gap-1.5 bg-brand-50 rounded-md px-2 py-1 mb-1.5">
                            <span class="w-4 h-4 rounded-full bg-brand-200 text-brand-700 text-[7px] font-bold flex items-center justify-center shrink-0">PK</span>
                            <span class="text-[9px] text-gray-700 truncate">{{ __('Patel Kirana Store') }}</span>
                        </div>
                        <div class="border border-gray-200 rounded-md px-2 py-1 text-[8px] text-gray-400 mb-1.5">{{ __('Search item...') }}</div>
                        <div class="space-y-1 text-[9px]">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700">Bateta <span class="text-gray-400">BAG · ₹650</span></span>
                                <span class="flex items-center gap-1 text-gray-500">− 2 +</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700">Dungali <span class="text-gray-400">BAG · ₹1,800</span></span>
                                <span class="flex items-center gap-1 text-gray-500">− 1 +</span>
                            </div>
                        </div>
                        <div class="flex gap-1 mt-2">
                            <span class="flex-1 text-center bg-brand-600 text-white text-[8px] font-semibold rounded py-1">{{ __('Full') }}</span>
                            <span class="flex-1 text-center bg-gray-100 text-gray-500 text-[8px] font-semibold rounded py-1">{{ __('Part') }}</span>
                            <span class="flex-1 text-center bg-gray-100 text-gray-500 text-[8px] font-semibold rounded py-1">{{ __('Credit') }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100">
                            <span class="text-[10px] font-bold text-gray-900">₹3,100</span>
                            <span class="bg-green-600 text-white text-[8px] font-semibold rounded px-2 py-1">{{ __('Save and share') }}</span>
                        </div>
                    </div>

                    <div class="w-44 bg-[#e7f5ea] rounded-[1.5rem] border-[6px] border-gray-900 shadow-xl p-2.5">
                        <div class="w-10 h-1 rounded-full bg-gray-900 mx-auto mb-2"></div>
                        <p class="text-[10px] font-semibold text-gray-800 mb-2">{{ __('Patel Kirana Store') }}</p>
                        <div class="bg-white rounded-lg rounded-tl-none p-2 shadow-sm mb-1.5">
                            <div class="flex items-center gap-1 text-[8px] font-semibold text-red-500"><span>PDF</span><span class="text-gray-500 font-normal">INV-B01-00706.pdf</span></div>
                            <p class="text-[8px] text-gray-600 mt-1">{{ __('Namaste! Your bill for ₹3,100 is ready. Thank you.') }}</p>
                        </div>
                        <div class="bg-white rounded-lg rounded-tl-none p-2 shadow-sm inline-block">
                            <p class="text-[8px] text-gray-600">{{ __('Got it, thank you') }} 🙏</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Customer credit -->
        <section class="py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="bg-gray-50 rounded-2xl p-5 order-2 lg:order-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-full bg-amber-100 text-amber-700 text-xs font-bold flex items-center justify-center">BR</span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ __('Babubhai Rangpur') }}</p>
                                <p class="text-xs text-gray-400">{{ __('Rangpur') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">{{ __('Balance due') }}</p>
                            <p class="font-bold text-red-600">&#8377;20,910</p>
                        </div>
                    </div>
                    <div class="mt-4 bg-white rounded-xl border border-gray-100 divide-y divide-gray-50 text-sm">
                        <div class="flex items-center justify-between px-4 py-2.5"><span class="text-brand-700">+ ₹3,250 {{ __('bill') }}, 16 Oct</span><span class="text-gray-700">&#8377;20,910</span></div>
                        <div class="flex items-center justify-between px-4 py-2.5"><span class="text-green-600">- ₹5,000 {{ __('paid by UPI') }}, 14 Oct</span><span class="text-gray-700">&#8377;17,660</span></div>
                        <div class="flex items-center justify-between px-4 py-2.5"><span class="text-brand-700">+ ₹6,800 {{ __('bill') }}, 12 Oct</span><span class="text-gray-700">&#8377;22,660</span></div>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <span class="flex-1 text-center border border-brand-200 text-brand-700 font-semibold text-sm rounded-lg py-2.5">{{ __('Receive payment') }}</span>
                        <span class="flex-1 text-center bg-green-600 text-white font-semibold text-sm rounded-lg py-2.5">{{ __('Send statement') }}</span>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Customer credit') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Always know who owes you, and how much') }}</h2>
                    <p class="mt-3 text-gray-500 leading-relaxed">{{ __('Every bill and payment is written in the customer\'s account by itself. If a customer asks "how did it become ₹20,910?", show them the list or send the statement on WhatsApp.') }}</p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Set a credit limit for each customer') }}</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Opening balance from your old notebook') }}</li>
                        <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Record cash, UPI or bank payments') }}</li>
                    </ul>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-6 mt-14">
                <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-6 lg:p-7">
                    <p class="text-xs font-semibold tracking-wide text-blue-700 uppercase">{{ __('Stock') }}</p>
                    <h3 class="mt-2 text-xl font-bold text-gray-900">{{ __('Stock goes up when you buy, and down when you sell') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('No counting at night. Every bag in and out is written with the reason and who did it.') }}</p>
                    <div class="mt-4 bg-white rounded-xl border border-gray-100 divide-y divide-gray-50 text-sm">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="flex items-center gap-2 text-gray-700"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" /></svg>Bateta &middot; {{ __('from supplier') }}</span>
                            <span class="font-semibold text-green-600">+70 BAG</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="flex items-center gap-2 text-gray-700"><svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" /></svg>Bateta &middot; {{ __('sold in bill') }}</span>
                            <span class="font-semibold text-red-600">-2 BAG</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 bg-amber-50">
                            <span class="flex items-center gap-2 text-amber-800 font-medium"><svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>{{ __('Hansh sabu is low') }}</span>
                            <span class="font-semibold text-amber-700">5 BOX {{ __('left') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50/70 border border-green-100 rounded-2xl p-6 lg:p-7">
                    <p class="text-xs font-semibold tracking-wide text-green-700 uppercase">{{ __('Reports') }}</p>
                    <h3 class="mt-2 text-xl font-bold text-gray-900">{{ __('See your profit in simple words') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Sales, profit and expenses for today, this week or this month. No accounting words.') }}</p>
                    <div class="mt-4 bg-white rounded-xl border border-gray-100 p-4">
                        <svg viewBox="0 0 280 70" class="w-full h-16" preserveAspectRatio="none">
                            <rect x="5" y="25" width="28" height="40" rx="3" fill="#DDBDFF" />
                            <rect x="43" y="15" width="28" height="50" rx="3" fill="#DDBDFF" />
                            <rect x="81" y="35" width="28" height="30" rx="3" fill="#DDBDFF" />
                            <rect x="119" y="2" width="28" height="63" rx="3" fill="#6A00D9" />
                            <rect x="157" y="28" width="28" height="37" rx="3" fill="#DDBDFF" />
                            <rect x="195" y="42" width="28" height="23" rx="3" fill="#DDBDFF" />
                            <rect x="233" y="50" width="28" height="15" rx="3" fill="#DDBDFF" />
                        </svg>
                        <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden flex">
                            <div class="bg-amber-400 h-full" style="width:93%"></div>
                            <div class="bg-rose-400 h-full" style="width:4%"></div>
                            <div class="bg-green-500 h-full" style="width:3%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ __('For every ₹100 sold:') }} <span class="font-semibold text-amber-600">&#8377;93 {{ __('goods') }}</span>, <span class="font-semibold text-rose-500">&#8377;4 {{ __('expenses') }}</span>, <span class="font-semibold text-green-600">&#8377;3 {{ __('profit') }}</span></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how-it-works" class="bg-[#140b26] py-16 lg:py-20" x-data="{ step: 1 }">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-xs font-semibold tracking-wide text-brand-400 uppercase">{{ __('How it works') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-white">{{ __('Start in 5 easy steps') }}</h2>
                    <p class="mt-2 text-white/60">{{ __('Do this once on the first day. After that, you only make bills.') }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 mt-8">
                    @foreach ([
                        1 => __('Add your customers'),
                        2 => __('Add your items'),
                        3 => __('Add the stock you have'),
                        4 => __('Make your first bill'),
                        5 => __('Share on WhatsApp'),
                    ] as $n => $label)
                        <button type="button" @click="step = {{ $n }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full text-sm font-medium transition-colors"
                            :class="step === {{ $n }} ? 'bg-brand-600 text-white' : 'bg-white/5 text-white/70 border border-white/10 hover:bg-white/10'">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs shrink-0"
                                :class="step === {{ $n }} ? 'bg-white/20' : 'bg-white/10'">{{ $n }}</span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-8 bg-[#1f1437] rounded-2xl p-6 lg:p-10">
                    @php
                        $steps = [
                            1 => [
                                'title' => __('Add your customers'), 'desc' => __('Open Customers, tap Add Customer. Type the shop name and phone number. If they already owe you money, write it as opening balance.'), 'where' => __('Customers, then Add Customer'),
                                'cardTitle' => __('Add Customer'), 'button' => __('Save customer'),
                                'fields' => [[__('Name'), __('Patel Kirana Store')], [__('Phone'), '98765 43210']],
                                'badge' => [__('Already owes you'), '&#8377;5,000', __('Opening')],
                            ],
                            2 => [
                                'title' => __('Add your items'), 'desc' => __('Open Items, tap Add Item. Set the unit, purchase price and selling price for everything you sell.'), 'where' => __('Items, then Add Item'),
                                'cardTitle' => __('Add Item'), 'button' => __('Save item'),
                                'fields' => [[__('Name'), __('Sugar')], [__('Unit'), '50kg ' . __('bag')]],
                                'badge' => [__('Selling price'), '&#8377;1,900', null],
                            ],
                            3 => [
                                'title' => __('Add the stock you have'), 'desc' => __('Tell the app how much of each item you already have in the godown, once. After this, it updates on its own.'), 'where' => __('Stock, then Adjust stock'),
                                'cardTitle' => __('Adjust stock'), 'button' => __('Save stock'),
                                'fields' => [[__('Item'), __('Sugar') . ', 50kg ' . __('bag')], [__('Quantity'), '40']],
                                'badge' => [__('Now in stock'), '0 ' . __('bag'), __('Opening')],
                            ],
                            4 => [
                                'title' => __('Make your first bill'), 'desc' => __('Pick the customer, add items, and the total is ready in seconds, with discount and transport if needed.'), 'where' => __('Invoices, then New Bill'),
                                'cardTitle' => __('New Bill'), 'button' => __('Full'),
                                'fields' => [[__('Customer'), __('Patel Kirana Store')], ['Sugar, 50kg ' . __('bag') . ' x 4', '₹7,600']],
                                'badge' => [__('Total'), '&#8377;7,600', null],
                            ],
                            5 => [
                                'title' => __('Share on WhatsApp'), 'desc' => __('Tap Save and share. The bill with a scan and pay QR code lands straight on their WhatsApp.'), 'where' => __('New Bill, then Save and share'),
                                'cardTitle' => __('Invoice'), 'button' => __('Share on WhatsApp'),
                                'fields' => [[__('Bill no.'), 'INV-B01-00706'], [__('Customer'), __('Patel Kirana Store')]],
                                'badge' => [__('Total'), '&#8377;7,600', __('PAID')],
                            ],
                        ];
                    @endphp

                    @foreach ($steps as $n => $s)
                        <template x-if="step === {{ $n }}">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                                <div>
                                    <p class="text-xs font-semibold text-amber-400 uppercase tracking-wide">{{ __('Step') }} {{ $n }} {{ __('of') }} 5</p>
                                    <h3 class="text-xl font-bold text-white mt-1">{{ $s['title'] }}</h3>
                                    <p class="text-white/60 mt-3 text-sm leading-relaxed">{{ $s['desc'] }}</p>
                                    <div class="mt-4 bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white/70">
                                        <span class="font-semibold text-amber-400">{{ __('Where:') }}</span> {{ $s['where'] }}
                                    </div>
                                    <div class="flex items-center gap-3 mt-5">
                                        <button type="button" @click="step = Math.max(1, step - 1)" :disabled="step === 1" class="inline-flex items-center gap-1.5 px-4 py-2 border border-white/15 text-white/70 text-sm font-medium rounded-lg disabled:opacity-30">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                                            {{ __('Back') }}
                                        </button>
                                        <button type="button" @click="step = Math.min(5, step + 1)" :disabled="step === 5" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-gray-900 text-sm font-semibold rounded-lg disabled:opacity-30">
                                            {{ __('Next step') }}
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-center">
                                    <div class="w-56 bg-white rounded-[1.75rem] border-[8px] border-gray-900 shadow-2xl p-3">
                                        <div class="w-12 h-1 rounded-full bg-gray-900 mx-auto mb-2"></div>
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="font-semibold text-gray-800 text-xs">{{ $s['cardTitle'] }}</p>
                                            <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs">+</span>
                                        </div>
                                        <div class="space-y-1.5">
                                            @foreach ($s['fields'] as $field)
                                                <div class="border border-gray-200 rounded-md px-2 py-1.5">
                                                    <p class="text-[8px] text-gray-400">{{ $field[0] }}</p>
                                                    <p class="text-[10px] font-medium text-gray-800">{{ $field[1] }}</p>
                                                </div>
                                            @endforeach
                                            @if ($s['badge'])
                                                <div class="border border-gray-200 rounded-md px-2 py-1.5 flex items-center justify-between">
                                                    <span>
                                                        <span class="block text-[8px] text-gray-400">{{ $s['badge'][0] }}</span>
                                                        <span class="block text-[10px] font-medium text-gray-800">{!! $s['badge'][1] !!}</span>
                                                    </span>
                                                    @if ($s['badge'][2])
                                                        <span class="text-[8px] font-semibold text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded-full">{{ $s['badge'][2] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <span class="block text-center bg-brand-600 text-white text-[10px] font-semibold rounded-md py-2 mt-2.5">{{ $s['button'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Phone and computer -->
        <section id="phone-and-computer" class="py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Phone and computer') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Bill at the counter on the computer. Check sales on your phone at home.') }}</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-10">
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-2 mb-5">
                            <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 mb-1.5">
                                <span class="text-[9px] font-semibold text-gray-700">{{ __('Good morning, Jaydip') }}</span>
                                <span class="text-[8px] bg-brand-600 text-white px-1.5 py-0.5 rounded">+ {{ __('New Bill') }}</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1 text-[7px] text-gray-400">
                                <div>{{ __('Sales') }}<p class="font-bold text-gray-800 text-[8px]">&#8377;19.5L</p></div>
                                <div>{{ __('Profit') }}<p class="font-bold text-green-600 text-[8px]">&#8377;1.35L</p></div>
                                <div>{{ __('Purchases') }}<p class="font-bold text-gray-800 text-[8px]">&#8377;18L</p></div>
                                <div>{{ __('Stock value') }}<p class="font-bold text-gray-800 text-[8px]">&#8377;4.6L</p></div>
                            </div>
                        </div>
                        <h3 class="font-bold text-gray-900">{{ __('On the shop computer') }}</h3>
                        <p class="text-sm text-gray-500 mt-1.5">{{ __('Big screen for fast billing, purchases and reports. Print bills on any printer.') }}</p>
                    </div>

                    <div class="bg-brand-50/60 rounded-2xl p-6">
                        <div class="w-28 mx-auto bg-white rounded-[1.25rem] border-[5px] border-gray-900 shadow-lg p-2 mb-5">
                            <p class="text-[8px] text-gray-500">{{ __('Today') }}</p>
                            <div class="bg-brand-600 text-white rounded-md px-2 py-1.5 mt-1">
                                <p class="text-[7px] text-white/70">{{ __('Sales today') }}</p>
                                <p class="text-[10px] font-bold">&#8377;42,000</p>
                            </div>
                        </div>
                        <h3 class="font-bold text-gray-900 text-center">{{ __('On your mobile') }}</h3>
                        <p class="text-sm text-gray-500 mt-1.5 text-center">{{ __('Make a bill from the godown, see who owes you, and check today\'s sales from anywhere.') }}</p>
                    </div>

                    <div class="bg-green-50/70 rounded-2xl p-6">
                        <div class="space-y-2 mb-5">
                            <div class="bg-white rounded-lg px-3 py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-[9px] font-bold flex items-center justify-center">JB</span><span class="text-xs font-medium text-gray-800">{{ __('Owner') }}</span></span>
                                <span class="text-[9px] font-semibold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full">{{ __('ADMIN') }}</span>
                            </div>
                            <div class="bg-white rounded-lg px-3 py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-[9px] font-bold flex items-center justify-center">PB</span><span class="text-xs font-medium text-gray-800">{{ __('Counter staff') }}</span></span>
                                <span class="text-[9px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">{{ __('STAFF') }}</span>
                            </div>
                        </div>
                        <h3 class="font-bold text-gray-900">{{ __('For your whole team') }}</h3>
                        <p class="text-sm text-gray-500 mt-1.5">{{ __('Give staff their own login for one branch. You see everything from every branch.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- What changes in your shop -->
        <section class="bg-gray-50 py-16 lg:py-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 text-center">{{ __('What changes in your shop') }}</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-10">
                    <div class="bg-white border border-red-100 rounded-2xl p-6 lg:p-7">
                        <p class="font-semibold text-red-600">{{ __('Before, with notebooks') }}</p>
                        <ul class="mt-3 space-y-2.5 text-sm text-gray-600">
                            @foreach ([__('Bills written by hand, copies get lost'), __('Customer credit in many notebooks'), __('Stock counted at night, still wrong'), __('Profit known only at year end')] as $item)
                                <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="bg-white border-2 border-brand-500 rounded-2xl p-6 lg:p-7">
                        <p class="font-semibold text-brand-600">{{ __('After, with :app', ['app' => config('app.name')]) }}</p>
                        <ul class="mt-3 space-y-2.5 text-sm text-gray-700">
                            @foreach ([__('Bill made and sent on WhatsApp'), __('Every customer\'s balance in one tap'), __('Stock updates with every bill and purchase'), __('Today\'s sales and profit on your phone')] as $item)
                                <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-0.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="questions" class="py-16 lg:py-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Questions') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Things people ask us') }}</h2>
                </div>

                @php
                    $faqs = [
                        [__('Do I need to know computers to use this?'), __('No. If you can use WhatsApp on your phone, you can use this app. The screens use simple words and big buttons, and we help you set it up.')],
                        [__('Can I use it on my mobile phone?'), __('Yes, the whole app works on a phone browser, no separate install needed. You can bill, check stock and see reports from anywhere.')],
                        [__('Can my staff make bills without seeing everything?'), __('Yes. Give staff their own login for one branch. They can bill and manage stock there, while you see every branch from the owner login.')],
                        [__('I have more than one shop. Will it work?'), __('Yes. Add as many branches as you need. Each branch has its own stock, bills and bank details, all under one account.')],
                        [__('What about my old notebook with customer credit?'), __('When you add a customer, enter what they already owe as an opening balance. From then on, every bill and payment updates it automatically.')],
                        [__('How do I get started?'), __('Book a free demo below. We call you, understand your shop, and set it up with your own customers and items so you can try it before deciding.')],
                    ];
                @endphp

                <div class="mt-8 space-y-3" x-data="{ open: 0 }">
                    @foreach ($faqs as $i => $faq)
                        <div class="bg-white border border-gray-200 rounded-xl px-5 py-4">
                            <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between gap-4 text-left">
                                <span class="font-semibold text-gray-900">{{ $faq[0] }}</span>
                                <span class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-colors" :class="open === {{ $i }} ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'">
                                    <svg x-show="open !== {{ $i }}" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    <svg x-show="open === {{ $i }}" style="display:none" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </span>
                            </button>
                            <div x-show="open === {{ $i }}" x-transition style="display:none" class="mt-3 text-sm text-gray-500 leading-relaxed">
                                {{ $faq[1] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA banner -->
        <section class="px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto bg-gradient-to-r from-brand-600 to-brand-700 rounded-2xl px-6 py-8 lg:px-10 lg:py-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
                <div>
                    <h2 class="text-xl lg:text-2xl font-bold text-white">{{ __('See it working with your own items') }}</h2>
                    <p class="mt-1.5 text-white/80 text-sm">{{ __('We set up a free demo with your products and customers, so you can try billing before you decide.') }}</p>
                </div>
                <div class="flex gap-3 shrink-0">
                    <a href="#contact" class="inline-flex items-center px-5 py-2.5 bg-white text-brand-700 font-semibold text-sm rounded-lg">{{ __('Book free demo') }}</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 border border-white/50 text-white font-semibold text-sm rounded-lg">{{ __('Login') }}</a>
                </div>
            </div>
        </section>

        <!-- Contact / demo request -->
        <section id="contact" class="py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase">{{ __('Contact us') }}</p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-bold text-gray-900">{{ __('Book your free demo') }}</h2>
                    <p class="mt-3 text-gray-500 text-sm leading-relaxed">{{ __('Leave your number. We will call you, understand your shop, and show you the app with your own items.') }}</p>

                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg></span>
                            <span class="text-gray-500 italic">[{{ __('Your phone / WhatsApp number') }}]</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg></span>
                            <span class="text-gray-500 italic">[{{ __('Your email address') }}]</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg></span>
                            <span class="text-gray-500 italic">[{{ __('Your office address') }}]</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6" x-data="{ businessType: 'Grocery' }">
                    @if (session('status'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h3 class="font-semibold text-gray-900">{{ __('Free demo request') }}</h3>

                    <form method="POST" action="{{ route('leads.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Your name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        </div>
                        <div>
                            <x-input-label for="shop_name" :value="__('Shop name and city')" />
                            <x-text-input id="shop_name" name="shop_name" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Om Sai Traders, Kodinar') }}" :value="old('shop_name')" />
                        </div>
                        <div>
                            <x-input-label for="phone" :value="__('Mobile number (WhatsApp)')" />
                            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" required />
                        </div>
                        <div>
                            <x-input-label :value="__('What do you sell?')" />
                            <input type="hidden" name="business_type" :value="businessType">
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach ([__('Grocery'), __('Vegetables and potatoes'), __('Coconut and fruits'), __('Soap and household'), __('Other')] as $type)
                                    <button type="button" @click="businessType = '{{ $type }}'"
                                        class="px-3.5 py-1.5 rounded-full text-sm font-medium border"
                                        :class="businessType === '{{ $type }}' ? 'border-brand-500 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                        {{ $type }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-3">
                            {{ __('Request free demo') }}
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-black text-white/60 py-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <x-application-logo class="w-7 h-7" />
                        <span class="font-semibold text-white text-sm">{{ config('app.name') }}</span>
                    </a>
                    <p class="text-xs mt-2 max-w-xs">{{ __('Billing, stock and customer credit app for wholesalers and traders.') }}</p>
                </div>
                <nav class="flex flex-wrap items-center gap-5 text-sm">
                    <a href="#features" class="hover:text-white">{{ __('Features') }}</a>
                    <a href="#how-it-works" class="hover:text-white">{{ __('How it works') }}</a>
                    <a href="#questions" class="hover:text-white">{{ __('Questions') }}</a>
                    <a href="#contact" class="hover:text-white">{{ __('Contact') }}</a>
                    <a href="{{ route('login') }}" class="text-white font-semibold hover:underline">{{ __('Login') }}</a>
                </nav>
            </div>
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 pt-6 border-t border-white/10">
                <p class="text-xs">&copy; {{ now()->year }} {{ config('app.name') }}</p>
            </div>
        </footer>
    </body>
</html>
