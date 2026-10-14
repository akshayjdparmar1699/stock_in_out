<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        @include('layouts.partials.pwa-head')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">

            <!-- Branding panel -->
            <div class="relative lg:w-[46%] bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-950 text-white px-6 sm:px-10 py-10 lg:py-16 flex flex-col overflow-hidden">
                <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute bottom-0 -left-16 w-64 h-64 rounded-full bg-white/5"></div>
                <div class="absolute bottom-24 right-10 w-40 h-40 rounded-full bg-white/5 hidden lg:block"></div>

                <div class="relative flex items-center gap-3">
                    <x-application-logo class="w-9 h-9 shrink-0" />
                    <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
                </div>

                <div class="relative mt-8 lg:mt-12">
                    <span class="inline-flex items-center gap-2 text-xs font-medium bg-white/10 rounded-full px-3 py-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        {{ __('Made for wholesalers with one or many branches') }}
                    </span>

                    <h1 class="mt-5 text-3xl lg:text-4xl font-extrabold leading-tight tracking-tight">
                        {{ __('Bill faster. Track stock live.') }}
                        <span class="text-amber-400">{{ __('Grow every branch.') }}</span>
                    </h1>

                    <p class="mt-4 max-w-sm text-sm text-indigo-100">
                        {{ __('Billing, stock and customers for all your branches in one place, so you always know what sold, what is left and what to reorder.') }}
                    </p>

                    <!-- Compact pills: mobile only -->
                    <div class="flex flex-wrap gap-2 mt-5 lg:hidden">
                        <span class="text-xs font-medium bg-white/10 rounded-full px-3 py-1.5">{{ __('Live branch stock') }}</span>
                        <span class="text-xs font-medium bg-white/10 rounded-full px-3 py-1.5">{{ __('Fast billing') }}</span>
                        <span class="text-xs font-medium bg-white/10 rounded-full px-3 py-1.5">{{ __('Auto stock update') }}</span>
                    </div>
                </div>

                <!-- Preview card + full feature list: desktop only -->
                <div class="relative mt-10 hidden lg:block max-w-md">
                    <div class="bg-white text-gray-800 rounded-2xl shadow-xl p-5">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm">{{ __('New bill') }}</span>
                            <span class="text-xs bg-indigo-50 text-indigo-700 rounded-full px-2.5 py-1">{{ __('Rajkot branch') }}</span>
                        </div>
                        <div class="mt-4 space-y-2.5 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">{{ __('Sugar, 50 kg bag') }} &times; 4</span>
                                <span class="font-medium">&#8377;[{{ __('AMOUNT') }}]</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">{{ __('Tea, 1 kg') }} &times; 10</span>
                                <span class="font-medium">&#8377;[{{ __('AMOUNT') }}]</span>
                            </div>
                            <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 font-semibold">
                                <span>{{ __('Total') }}</span>
                                <span>&#8377;[{{ __('TOTAL') }}]</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2 bg-green-50 text-green-700 text-xs font-medium rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('Stock updated automatically') }}
                        </div>
                    </div>

                    <ul class="mt-8 space-y-5 text-sm">
                        <li class="flex items-start gap-3">
                            <span class="shrink-0 w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                            </span>
                            <span>
                                <span class="block font-semibold">{{ __('Live stock for every branch') }}</span>
                                <span class="block text-indigo-200 mt-0.5">{{ __('Each branch keeps its own real-time stock.') }}</span>
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="shrink-0 w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            </span>
                            <span>
                                <span class="block font-semibold">{{ __('Customers in seconds') }}</span>
                                <span class="block text-indigo-200 mt-0.5">{{ __('Search or add a customer right while billing.') }}</span>
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="shrink-0 w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941" /></svg>
                            </span>
                            <span>
                                <span class="block font-semibold">{{ __('Zero manual updates') }}</span>
                                <span class="block text-indigo-200 mt-0.5">{{ __('Stock changes the moment a bill is made.') }}</span>
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="relative mt-auto pt-10 hidden lg:flex items-center justify-between text-xs text-indigo-200/70">
                    <span>{{ __('Trusted by wholesalers running multiple branches') }}</span>
                    <span>&copy; {{ now()->year }} {{ config('app.name') }}</span>
                </div>
            </div>

            <!-- Form panel -->
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-10 lg:py-12 bg-gray-50">
                <div class="w-full sm:max-w-sm">
                    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-8">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Feature list: mobile only, shown below the card -->
                <div class="w-full sm:max-w-sm mt-8 space-y-5 text-sm lg:hidden">
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-800">{{ __('Live stock for every branch') }}</span>
                            <span class="block text-gray-500 mt-0.5">{{ __('Each branch keeps its own real-time stock.') }}</span>
                        </span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-800">{{ __('Customers in seconds') }}</span>
                            <span class="block text-gray-500 mt-0.5">{{ __('Search or add a customer while billing.') }}</span>
                        </span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941" /></svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-800">{{ __('Zero manual updates') }}</span>
                            <span class="block text-gray-500 mt-0.5">{{ __('Stock changes the moment a bill is made.') }}</span>
                        </span>
                    </div>
                    <p class="text-center text-xs text-gray-400 pt-4">
                        {{ __('Trusted by wholesalers running multiple branches') }} &middot; &copy; {{ now()->year }}
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
