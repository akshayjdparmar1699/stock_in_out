<x-app-layout>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? __('Good morning') : ($hour < 17 ? __('Good afternoon') : __('Good evening'));
        $firstName = explode(' ', trim(Auth::user()->name))[0];
        $periodLabels = ['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')];
    @endphp

    <x-slot name="header">
        <div class="hidden lg:flex lg:items-center lg:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $greeting }}, {{ $firstName }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ \App\Services\BranchContext::current()?->name }} &middot; {{ now()->format('D, j M Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add Expense') }}
                </a>
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('New Purchase') }}
                </a>
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('New Bill') }}
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Mobile hero header (purple) — desktop keeps the plain header above instead -->
    <div class="lg:hidden bg-gradient-to-br from-brand-600 to-brand-800 text-white px-5 pt-5 pb-20 rounded-b-[2rem]">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shrink-0 shadow-sm p-1.5">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                        <circle cx="15" cy="13" r="2.6" fill="#6a00d9" />
                        <path d="M15 19L21 31" stroke="#6a00d9" stroke-width="4" stroke-linecap="round" />
                        <path d="M21 31L33 16" stroke="#FFB020" stroke-width="4" stroke-linecap="round" />
                        <path d="M24 15L34 15L34 25" stroke="#FFB020" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-white/70">{{ $greeting }}</p>
                    <p class="text-base font-semibold truncate">{{ Auth::user()->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if (Auth::user()->isAdmin())
                    <div class="w-12 h-12 rounded-full bg-white/15 flex items-center justify-center shrink-0 [&>a]:!text-white [&>a]:!p-0 [&>a>svg]:w-7 [&>a>svg]:h-7">
                        @include('layouts.partials.settings-icon-mobile')
                    </div>
                @endif
                <div class="relative w-12 h-12 rounded-full bg-white/15 flex items-center justify-center shrink-0 [&>div>button]:!text-white [&>div>button]:!p-0 [&>div>button>svg]:w-7 [&>div>button>svg]:h-7">
                    @include('layouts.partials.notification-bell')
                </div>
                <a href="{{ route('profile.edit') }}" aria-label="{{ __('Profile') }}" class="w-12 h-12 rounded-full bg-white/15 flex items-center justify-center shrink-0 text-base font-semibold overflow-hidden">
                    @if (Auth::user()->avatarUrl())
                        <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </a>
            </div>
        </div>

        <div class="mt-5 inline-flex items-center gap-2 max-w-full bg-white/15 rounded-full px-4 py-2.5 text-sm font-medium">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            <span class="truncate">{{ \App\Services\BranchContext::current()?->name }}</span>
        </div>

        <div class="mt-5 flex gap-1.5">
            @foreach ($periodLabels as $value => $label)
                <a href="{{ route('dashboard', ['period' => $value]) }}"
                    class="flex-1 text-center px-2 py-2 rounded-full text-xs font-medium {{ $period === $value ? 'bg-white text-brand-700' : 'bg-white/15 text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="lg:py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6 space-y-6 -mt-16 lg:mt-0">

            <div x-data="{
                    canUsePush: window.canUsePushNotifications,
                    permission: window.pushPermissionState,
                    dismissed: localStorage.getItem('dashboardPushBannerDismissed') === '1',
                    async enable() {
                        await window.subscribeToPushNotifications();
                        this.permission = window.pushPermissionState;
                    },
                    dismiss() {
                        this.dismissed = true;
                        localStorage.setItem('dashboardPushBannerDismissed', '1');
                    },
                }"
                x-show="canUsePush && permission === 'default' && !dismissed" style="display: none;"
                class="relative bg-brand-50 border border-brand-100 rounded-xl px-5 py-4 flex items-start gap-4">
                <span class="shrink-0 w-10 h-10 rounded-full bg-brand-600 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-800">{{ __('Turn on alerts for stock & payment updates') }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ __('Get notified the instant an item runs low or a customer goes over their credit limit — even when the app is closed.') }}</p>
                    <button type="button" @click="enable()" class="mt-3 inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg">
                        {{ __('Enable Notifications') }}
                    </button>
                </div>
                <button type="button" @click="dismiss()" class="shrink-0 text-gray-400 hover:text-gray-600" aria-label="{{ __('Dismiss') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="hidden lg:inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white w-full sm:w-auto">
                @foreach ($periodLabels as $value => $label)
                    <a href="{{ route('dashboard', ['period' => $value]) }}"
                        class="flex-1 sm:flex-none text-center px-3 py-1.5 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div x-data="dashboardWidget('stats')" x-init="init()">
                <div x-ref="content">
                    @include('dashboard.partials.placeholder')
                </div>
            </div>

            <div class="lg:hidden grid grid-cols-4 gap-3">
                <a href="{{ route('invoices.create') }}" class="flex flex-col items-center gap-1.5">
                    <span class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </span>
                    <span class="text-[11px] font-medium text-gray-600">{{ __('New Bill') }}</span>
                </a>
                <a href="{{ route('purchases.create') }}" class="flex flex-col items-center gap-1.5">
                    <span class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 0h-12" /></svg>
                    </span>
                    <span class="text-[11px] font-medium text-gray-600">{{ __('Purchase') }}</span>
                </a>
                <a href="{{ route('expenses.create') }}" class="flex flex-col items-center gap-1.5">
                    <span class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    <span class="text-[11px] font-medium text-gray-600">{{ __('Expense') }}</span>
                </a>
                <a href="{{ route('customers.create') }}" class="flex flex-col items-center gap-1.5">
                    <span class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                    </span>
                    <span class="text-[11px] font-medium text-gray-600">{{ __('Customer') }}</span>
                </a>
            </div>

            <div class="flex flex-col lg:grid lg:grid-cols-5 gap-6">
                <div class="order-2 lg:order-1 lg:col-span-3 space-y-4" x-data="dashboardWidget('trend')" x-init="init()">
                    <div x-ref="content">
                        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                            @include('dashboard.partials.placeholder')
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2 lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100" x-data="dashboardWidget('dues')" x-init="init()">
                    <div x-ref="content">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100" x-data="dashboardWidget('over-limit')" x-init="init()">
                    <div x-ref="content">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100" x-data="dashboardWidget('low-stock')" x-init="init()">
                    <div x-ref="content">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:grid lg:grid-cols-3 gap-6">
                <div class="order-2 lg:order-1 bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100" x-data="dashboardWidget('followup')" x-init="init()">
                    <div x-ref="content">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>

                <div class="order-1 lg:order-2 bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100" x-data="dashboardWidget('invoices')" x-init="init()">
                    <div x-ref="content">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>

                <div class="order-3 lg:order-3 bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100" x-data="dashboardWidget('expenses')" x-init="init()">
                    <div x-ref="content">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function dashboardWidget(name) {
            return {
                loading: false,
                loaded: false,
                init() {
                    // Every section fetches itself the moment the page loads
                    // (each is its own independent request, so they all run
                    // in parallel rather than one after another).
                    this.refresh();
                },
                onClick(e) {
                    const link = e.target.closest('a[href]');
                    if (!link) return;
                    e.preventDefault();
                    const historyUrl = link.href;
                    const fetchUrl = new URL(link.href);
                    fetchUrl.searchParams.set('widget', name);
                    this.load(fetchUrl.toString(), historyUrl);
                },
                refresh() {
                    const fetchUrl = new URL(window.location.href);
                    fetchUrl.searchParams.set('widget', name);
                    this.load(fetchUrl.toString());
                },
                async load(fetchUrl, historyUrl) {
                    this.loading = true;
                    try {
                        const res = await fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        this.$refs.content.innerHTML = await res.text();
                        this.loaded = true;
                        if (historyUrl) window.history.replaceState({}, '', historyUrl);
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
