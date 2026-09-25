<aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-white border-r border-gray-200">
    <a href="{{ route('dashboard') }}" class="h-16 shrink-0 flex items-center gap-3 px-6 border-b border-gray-100">
        <x-application-logo class="w-8 h-8 text-indigo-600" />
        <span class="font-semibold text-gray-800">{{ config('app.name') }}</span>
    </a>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @include('layouts.partials.sidebar-links')
    </nav>

    <div class="px-3 py-4 border-t border-gray-100 space-y-3">
        <div x-data="{ installable: false, showIosHint: false }"
            x-init="window.addEventListener('pwa-installable', () => installable = true); window.addEventListener('pwa-installed', () => installable = false); installable = !!window.deferredInstallPrompt; showIosHint = window.isIosInstallable && !localStorage.getItem('pwaIosHintDismissed')">
            <button type="button" x-show="installable" style="display: none;" @click="window.promptPwaInstall()"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                {{ __('Install App') }}
            </button>

            <div x-show="showIosHint" style="display: none;" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs text-indigo-700 flex items-start gap-2">
                <span class="flex-1">{{ __('To install: tap the Share icon, then "Add to Home Screen".') }}</span>
                <button type="button" @click="showIosHint = false; localStorage.setItem('pwaIosHintDismissed', '1')" class="shrink-0 text-indigo-400 hover:text-indigo-600">&times;</button>
            </div>
        </div>

        @if (Auth::user()->isAdmin())
            <form method="POST" action="{{ route('branches.switch') }}">
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
            <div class="px-1">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ __('Branch') }}</p>
                <p class="text-sm text-gray-700">{{ \App\Services\BranchContext::current()?->name }}</p>
            </div>
        @endif

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="w-full flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-100 text-left">
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
</aside>
