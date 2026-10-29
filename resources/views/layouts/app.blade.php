<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="vapid-public-key" content="{{ config('services.webpush.public_key') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('layouts.partials.pwa-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- min-h-dvh (not min-h-screen/100vh) tracks the mobile browser's actual visible
             viewport as its address bar collapses/expands on scroll, instead of staying
             pinned to the largest possible one - that mismatch was what made the fixed
             bottom tab bar visibly jump on scroll. --}}
        <div x-data="{ moreOpen: false, planLimitOpen: false, planLimitMessage: '' }" class="min-h-dvh bg-gray-100 {{ isset($subscriptionBlocked) ? 'blur-sm pointer-events-none select-none' : '' }}">

            <!-- Desktop sidebar -->
            @include('layouts.navigation')

            @if (Auth::user()->isSuperAdmin())
                <!-- Mobile bottom tab bar (Companies + Leads only — nothing else exists for a super admin) -->
                @include('layouts.partials.mobile-bottom-nav-super-admin')
            @else
                <!-- Mobile bottom tab bar + "More" sheet -->
                @include('layouts.partials.mobile-bottom-nav')
                @include('layouts.partials.mobile-more-sheet')
            @endif

            <div class="lg:pl-64 flex flex-col min-h-dvh">
                <!-- Top bar -->
                <div class="sticky top-0 z-30 bg-white border-b border-gray-100">
                    @unless (Auth::user()->isSuperAdmin() || request()->routeIs('dashboard', 'profile.edit', 'customers.index', 'customers.show', 'customers.create', 'customers.edit', 'items.index', 'items.show', 'items.create', 'items.edit', 'invoices.index', 'invoices.show', 'invoices.create', 'invoices.edit', 'stock.index', 'stock.create', 'stock.edit', 'batches.index', 'batches.show', 'purchases.index', 'purchases.show', 'purchases.create', 'purchases.edit', 'suppliers.index', 'suppliers.show', 'suppliers.create', 'suppliers.edit', 'staff.index', 'staff.show', 'staff.create', 'staff.edit', 'expenses.index', 'expenses.create', 'expenses.edit', 'branches.index', 'branches.create', 'branches.edit', 'users.index', 'users.create', 'users.edit', 'settings.edit', 'sales-reports.index', 'sales-reports.sales', 'sales-reports.profit-loss', 'sales-reports.item-wise-profit'))
                        <!-- Mobile top strip: branch switcher, notifications, profile -->
                        @include('layouts.partials.mobile-top-strip')
                    @endunless

                    <div class="{{ request()->routeIs('dashboard', 'profile.edit', 'settings.edit') ? 'hidden lg:flex' : 'flex' }} items-center gap-4 px-4 sm:px-6 lg:px-6 py-4">
                        @if (Auth::user()->isSuperAdmin())
                            <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                                @csrf
                                <button type="submit" class="-ml-1 p-2 text-gray-500 hover:text-gray-700" title="{{ __('Log Out') }}">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                </button>
                            </form>
                        @endif

                        @isset($header)
                            <div class="flex-1 min-w-0">{{ $header }}</div>
                        @endisset

                        @unless (Auth::user()->isSuperAdmin())
                            <div class="hidden lg:block shrink-0">
                                @include('layouts.partials.notification-bell')
                            </div>
                        @endunless
                    </div>
                </div>

                @if (session('status'))
                    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        @if (session('status_type') === 'danger')
                            <div class="rounded-md bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
                                {{ session('status') }}
                            </div>
                        @else
                            <div class="rounded-md bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
                                {{ session('status') }}
                            </div>
                        @endif
                    </div>
                @endif

                @if ($errors->any())
                    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <div class="rounded-md bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <main class="flex-1 pb-20 lg:pb-0">
                    {{ $slot }}
                </main>
            </div>

            @include('layouts.partials.plan-limit-modal')
        </div>

        @isset($subscriptionBlocked)
            @include('layouts.partials.subscription-blocked-modal', $subscriptionBlocked)
        @endisset

        @include('layouts.partials.pwa-install-banner')

        @unless (Auth::user()->isSuperAdmin())
            @if (session('just_logged_in'))
                @include('layouts.partials.login-push-prompt')
            @endif
        @endunless
    </body>
</html>
