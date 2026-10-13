@php
    $moreLinkClass = fn (bool $active) => $active
        ? 'flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-indigo-50 text-indigo-700'
        : 'flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-gray-50 text-gray-700 hover:bg-gray-100';
    $moreIconClass = 'w-7 h-7 shrink-0';
@endphp

<div x-show="moreOpen" style="display: none;" class="lg:hidden print:hidden fixed inset-0 z-50">
    <div x-show="moreOpen"
        x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="moreOpen = false" class="fixed inset-0 bg-gray-900/50"></div>

    <div x-show="moreOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
        class="fixed inset-x-0 bottom-0 top-12 bg-gray-50 rounded-t-2xl shadow-xl flex flex-col">

        <div class="shrink-0 flex items-center justify-between px-4 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 class="font-semibold text-gray-800">{{ __('More') }}</h2>
            <button @click="moreOpen = false" class="p-1.5 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4" style="padding-bottom: calc(env(safe-area-inset-bottom) + 1rem);">
            <div class="grid grid-cols-3 gap-3">
                <a href="{{ route('items.index') }}" class="{{ $moreLinkClass(request()->routeIs('items.*')) }}">
                    <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                    <span class="text-xs font-medium text-center">{{ __('Items') }}</span>
                </a>

                <a href="{{ route('batches.index') }}" class="{{ $moreLinkClass(request()->routeIs('batches.*')) }}">
                    <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                    </svg>
                    <span class="text-xs font-medium text-center">{{ __('Batches') }}</span>
                </a>

                <a href="{{ route('purchases.index') }}" class="{{ $moreLinkClass(request()->routeIs('purchases.*')) }}">
                    <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <span class="text-xs font-medium text-center">{{ __('Purchases') }}</span>
                </a>

                <a href="{{ route('suppliers.index') }}" class="{{ $moreLinkClass(request()->routeIs('suppliers.*')) }}">
                    <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 0h-12" />
                    </svg>
                    <span class="text-xs font-medium text-center">{{ __('Suppliers') }}</span>
                </a>

                <a href="{{ route('staff.index') }}" class="{{ $moreLinkClass(request()->routeIs('staff.*')) }}">
                    <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    <span class="text-xs font-medium text-center">{{ __('Staff & Partners') }}</span>
                </a>

                <a href="{{ route('expenses.index') }}" class="{{ $moreLinkClass(request()->routeIs('expenses.*')) }}">
                    <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-medium text-center">{{ __('Expenses') }}</span>
                </a>

                @if (Auth::user()->isAdmin())
                    <a href="{{ route('branches.index') }}" class="{{ $moreLinkClass(request()->routeIs('branches.*')) }}">
                        <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64M6 3.75h12M6.75 21V3.75m10.5 17.25V3.75M4.5 21V9.75A2.25 2.25 0 016.75 7.5h10.5a2.25 2.25 0 012.25 2.25V21" />
                        </svg>
                        <span class="text-xs font-medium text-center">{{ __('Branches') }}</span>
                    </a>

                    <a href="{{ route('users.index') }}" class="{{ $moreLinkClass(request()->routeIs('users.*') || request()->routeIs('register')) }}">
                        <svg class="{{ $moreIconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        <span class="text-xs font-medium text-center">{{ __('Users') }}</span>
                    </a>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 space-y-3">
                @if (Auth::user()->isAdmin())
                    <form method="POST" action="{{ route('branches.switch') }}">
                        @csrf
                        <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1 px-1">{{ __('Branch') }}</label>
                        <select name="branch_id" onchange="window.showCubeLoader(); this.form.submit()" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Models\Branch::where('company_id', Auth::user()->company_id)->orderBy('name')->get() as $branch)
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

                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-100">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</span>
                        <span class="block text-xs text-gray-400 truncate">{{ Auth::user()->email }}</span>
                    </span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
