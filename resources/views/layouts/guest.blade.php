<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        @include('layouts.partials.pwa-head')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">

            <!-- Branding panel -->
            <div class="relative lg:w-[42%] bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-900 text-white px-8 py-10 lg:py-16 flex flex-col justify-between overflow-hidden">
                <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute bottom-0 -left-16 w-64 h-64 rounded-full bg-white/5"></div>

                <div class="relative flex items-center gap-3">
                    <x-application-logo class="w-9 h-9 text-white" />
                    <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
                </div>

                <div class="relative mt-10 lg:mt-0 max-w-sm">
                    <h1 class="text-2xl lg:text-3xl font-semibold leading-snug">
                        {{ __('Manage stock and billing across every branch, from one place.') }}
                    </h1>
                    <ul class="mt-8 space-y-4 text-sm text-indigo-100">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full bg-white/15 flex items-center justify-center">
                                <svg class="w-3 h-3" viewBox="0 0 12 12" fill="none"><path d="M2 6.5L4.5 9L10 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            {{ __('Separate, real-time stock for every branch') }}
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full bg-white/15 flex items-center justify-center">
                                <svg class="w-3 h-3" viewBox="0 0 12 12" fill="none"><path d="M2 6.5L4.5 9L10 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            {{ __('Search or create a customer in seconds while billing') }}
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full bg-white/15 flex items-center justify-center">
                                <svg class="w-3 h-3" viewBox="0 0 12 12" fill="none"><path d="M2 6.5L4.5 9L10 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            {{ __('Stock auto-updates the moment a bill is made') }}
                        </li>
                    </ul>
                </div>

                <p class="relative hidden lg:block text-xs text-indigo-200/70">
                    &copy; {{ now()->year }} {{ config('app.name') }}
                </p>
            </div>

            <!-- Form panel -->
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 bg-gray-50">
                <div class="w-full sm:max-w-sm">
                    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
