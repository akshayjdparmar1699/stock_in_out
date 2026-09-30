<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }} &mdash; {{ \App\Services\BranchContext::current()?->name }}
        </h2>
    </x-slot>

    @php
        $periodLabels = ['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-wrap items-center justify-center sm:justify-between gap-4">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white">
                    @foreach ($periodLabels as $value => $label)
                        <a href="{{ route('dashboard', ['period' => $value]) }}"
                            class="px-3 py-1.5 {{ $period === $value ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        {{ __('+ New Bill') }}
                    </a>
                    <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        {{ __('+ New Purchase') }}
                    </a>
                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        {{ __('+ Add Expense') }}
                    </a>
                </div>
            </div>

            <div x-data="dashboardWidget('stats')" x-init="init()">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-500">{{ __('Overview') }}</span>
                    @include('dashboard.partials.refresh-button')
                </div>
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('dashboard.partials.placeholder')
                </div>
            </div>

            <div x-data="dashboardWidget('dues')" x-init="init()">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-500">{{ __('Balances') }}</span>
                    @include('dashboard.partials.refresh-button')
                </div>
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('dashboard.partials.placeholder')
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('invoices')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Recent Invoices') }}</span>
                        <div class="flex items-center gap-3">
                            @include('dashboard.partials.refresh-button')
                            <a href="{{ route('invoices.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                        </div>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('purchases')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Recent Purchases') }}</span>
                        <div class="flex items-center gap-3">
                            @include('dashboard.partials.refresh-button')
                            <a href="{{ route('purchases.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                        </div>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('low-stock')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Low Stock') }}</span>
                        @include('dashboard.partials.refresh-button')
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('followup')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <div>
                            {{ __('Customers to Follow Up') }}
                            <span class="text-xs font-normal text-gray-400">{{ __('(no purchase in the last 3 days)') }}</span>
                        </div>
                        @include('dashboard.partials.refresh-button')
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('over-limit')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <div>
                            {{ __('Customers Over Credit Limit') }}
                            <span class="text-xs font-normal text-gray-400">{{ __('(time to collect payment)') }}</span>
                        </div>
                        @include('dashboard.partials.refresh-button')
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('expense-breakdown')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Expenses by Category') }}</span>
                        <div class="flex items-center gap-3">
                            @include('dashboard.partials.refresh-button')
                            <a href="{{ route('expenses.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                        </div>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.placeholder')
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('expenses')" x-init="init()">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Recent Expenses') }}</span>
                        <div class="flex items-center gap-3">
                            @include('dashboard.partials.refresh-button')
                            <a href="{{ route('expenses.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                        </div>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
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
                    // Nothing is fetched automatically — a section only ever
                    // queries the database once its own refresh icon (or a
                    // pagination link inside it) is clicked, so a page visit
                    // never pays for sections nobody looks at.
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
                    window.showAjaxSpinner(this.$refs.content);
                    try {
                        const res = await fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        this.$refs.content.innerHTML = await res.text();
                        this.loaded = true;
                        if (historyUrl) window.history.replaceState({}, '', historyUrl);
                    } finally {
                        this.loading = false;
                        window.hideAjaxSpinner(this.$refs.content);
                    }
                },
            };
        }
    </script>
</x-app-layout>
