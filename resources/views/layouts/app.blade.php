<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('layouts.partials.pwa-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100">

            <!-- Desktop sidebar -->
            @include('layouts.navigation')

            <!-- Mobile sidebar drawer -->
            <div x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" style="display: none;">
                <div x-show="sidebarOpen"
                        x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        @click="sidebarOpen = false" class="fixed inset-0 bg-gray-900/50"></div>

                <div x-show="sidebarOpen"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                        class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col shadow-xl">
                    <div class="h-16 shrink-0 flex items-center justify-between px-4 border-b border-gray-100">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                            <x-application-logo class="w-8 h-8 text-indigo-600" />
                            <span class="font-semibold text-gray-800">{{ config('app.name') }}</span>
                        </a>
                        <button @click="sidebarOpen = false" class="p-2 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                        @include('layouts.partials.sidebar-links')
                    </nav>

                    <div class="px-3 py-4 border-t border-gray-100">
                        @if (Auth::user()->isAdmin())
                            <form method="POST" action="{{ route('branches.switch') }}" class="mb-3">
                                @csrf
                                <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1 px-1">{{ __('Branch') }}</label>
                                <select name="branch_id" onchange="window.showCubeLoader(); this.form.submit()" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (\App\Models\Branch::orderBy('name')->get() as $branch)
                                        <option value="{{ $branch->id }}" @selected(\App\Services\BranchContext::id() === $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <div class="px-1 mb-3">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ __('Branch') }}</p>
                                <p class="text-sm text-gray-700">{{ \App\Services\BranchContext::current()?->name }}</p>
                            </div>
                        @endif

                        <div x-data="{ installable: false }"
                            x-init="window.addEventListener('pwa-installable', () => installable = true); window.addEventListener('pwa-installed', () => installable = false); installable = !!window.deferredInstallPrompt"
                            x-show="installable" style="display: none;" class="mb-3">
                            <button type="button" @click="window.promptPwaInstall()"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                {{ __('Install App') }}
                            </button>
                        </div>

                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open" class="w-full flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-100 text-left">
                                <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</span>
                                    <span class="block text-xs text-gray-400 truncate">{{ Auth::user()->email }}</span>
                                </span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 9l3.75 6" transform="rotate(180 12 12)" />
                                </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false"
                                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute bottom-full left-0 right-0 mb-2 bg-white border border-gray-100 rounded-lg shadow-lg overflow-hidden"
                                    style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    {{ __('Profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:pl-64 flex flex-col min-h-screen">
                <!-- Top bar -->
                <div class="sticky top-0 z-30 bg-white border-b border-gray-100">
                    <div class="flex items-center gap-4 px-4 sm:px-6 lg:px-8 py-4">
                        <button @click="sidebarOpen = true" class="lg:hidden -ml-1 p-2 text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>

                        @isset($header)
                            <div class="flex-1 min-w-0">{{ $header }}</div>
                        @endisset
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
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
