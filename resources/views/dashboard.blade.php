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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
                    <div class="text-sm text-gray-500">{{ __('Sales') }} ({{ $periodLabels[$period] }})</div>
                    <div class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($salesTotal, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $salesCount }} {{ __('invoices') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
                    <div class="text-sm text-gray-500">{{ __('Profit') }} ({{ $periodLabels[$period] }})</div>
                    <div class="text-2xl font-semibold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">₹{{ number_format($profit, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('sales minus cost of goods') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
                    <div class="text-sm text-gray-500">{{ __('Purchases') }} ({{ $periodLabels[$period] }})</div>
                    <div class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($purchasesTotal, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $purchasesCount }} {{ __('purchases') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-5">
                    <div class="text-sm text-gray-500">{{ __('Stock Value') }}</div>
                    <div class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($totalStockValue, 2) }}</div>
                    <div class="text-xs {{ $lowStockCount > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $lowStockCount }} {{ __('items low on stock') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('customers.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-5 hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500">{{ __('Customers Owe You') }}</div>
                    <div class="text-2xl font-semibold {{ $customerDueTotal > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">₹{{ number_format(max($customerDueTotal, 0), 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('total outstanding, this branch') }}</div>
                </a>
                <a href="{{ route('purchases.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-5 hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500">{{ __('You Owe Suppliers') }}</div>
                    <div class="text-2xl font-semibold {{ $supplierDueTotal > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">₹{{ number_format(max($supplierDueTotal, 0), 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('unpaid on purchases, this branch') }}</div>
                </a>
                <a href="{{ route('expenses.index', ['category' => 'upad']) }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-5 hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500">{{ __('Total Advance (Staff + Partners)') }}</div>
                    <div class="text-2xl font-semibold {{ $totalUpad > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">₹{{ number_format($totalUpad, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('all time, this branch') }}</div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('invoices')">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Recent Invoices') }}</span>
                        <a href="{{ route('invoices.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.invoices')
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('purchases')">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Recent Purchases') }}</span>
                        <a href="{{ route('purchases.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.purchases')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('low-stock')">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Low Stock') }}</span>
                        @if ($lowStockAdminUrl)
                            <a href="{{ $lowStockAdminUrl }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 hover:text-green-800">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.001 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.373 5.07L2 22l5.06-1.348A9.94 9.94 0 0012.001 22C17.523 22 22 17.52 22 12S17.523 2 12.001 2zm0 18.062a8.02 8.02 0 01-4.253-1.213l-.305-.181-3.005.8.803-2.93-.198-.303A8.024 8.024 0 013.938 12c0-4.444 3.618-8.062 8.063-8.062S20.062 7.556 20.062 12 16.446 20.062 12.001 20.062z"/></svg>
                                {{ __('Notify Admin') }}
                            </a>
                        @endif
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.low-stock')
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('followup')">
                    <div class="px-5 py-4 border-b font-medium text-gray-700">
                        {{ __('Customers to Follow Up') }}
                        <span class="text-xs font-normal text-gray-400">{{ __('(no purchase in the last 7 days)') }}</span>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.followup')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Expenses by Category') }} ({{ $periodLabels[$period] }})</span>
                        <a href="{{ route('expenses.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                    </div>
                    <div class="divide-y">
                        @forelse (\App\Models\Expense::categories() as $key => $label)
                            @php $amount = (float) ($expenseBreakdown[$key] ?? 0); @endphp
                            @if ($amount > 0)
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                    <span class="text-sm font-medium text-gray-800">₹{{ number_format($amount, 2) }}</span>
                                </div>
                            @endif
                        @empty
                        @endforelse
                        @if ($expenseTotal <= 0)
                            <div class="px-5 py-6 text-sm text-gray-400">{{ __('No expenses in this period.') }}</div>
                        @endif
                    </div>
                    @if ($expenseTotal > 0)
                        <div class="px-5 py-3 border-t flex justify-between font-medium">
                            <span class="text-sm text-gray-700">{{ __('Total') }}</span>
                            <span class="text-sm text-gray-900">₹{{ number_format($expenseTotal, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg" x-data="dashboardWidget('expenses')">
                    <div class="px-5 py-4 border-b font-medium text-gray-700 flex justify-between items-center">
                        <span>{{ __('Recent Expenses') }}</span>
                        <a href="{{ route('expenses.index') }}" class="text-xs font-normal text-indigo-600 hover:underline">{{ __('View all') }}</a>
                    </div>
                    <div x-ref="content" :class="loading && 'opacity-50'">
                        @include('dashboard.partials.expenses')
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function dashboardWidget(name) {
            return {
                loading: false,
                onClick(e) {
                    const link = e.target.closest('a[href]');
                    if (!link) return;
                    e.preventDefault();
                    const historyUrl = link.href;
                    const fetchUrl = new URL(link.href);
                    fetchUrl.searchParams.set('widget', name);
                    this.load(fetchUrl.toString(), historyUrl);
                },
                async load(fetchUrl, historyUrl) {
                    this.loading = true;
                    window.showAjaxSpinner(this.$refs.content);
                    try {
                        const res = await fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        this.$refs.content.innerHTML = await res.text();
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
