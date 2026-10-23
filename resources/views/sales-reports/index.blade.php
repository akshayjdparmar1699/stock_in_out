<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales & Reports') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('How your business is doing, in one place') }}</p>
                </div>
            </div>
            <div class="lg:hidden">
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    @php
        $reports = [
            ['icon' => 'chart-bar', 'color' => 'bg-purple-50 text-brand-600', 'title' => __('Sales report'), 'desc' => __('Day-wise sales, top items, top customers and payment mode'), 'label' => __('Sales'), 'value' => '&#8377;'.number_format($salesTotal, 0), 'href' => route('sales-reports.sales', ['branch_id' => $selectedBranchId, 'period' => $period === 'all' ? '7days' : $period])],
            ['icon' => 'trending-up', 'color' => 'bg-green-50 text-green-600', 'title' => __('Profit and loss'), 'desc' => __('Sales minus cost of goods and expenses'), 'label' => __('Net profit'), 'value' => '&#8377;'.number_format($netProfit, 0), 'href' => route('sales-reports.profit-loss', ['branch_id' => $selectedBranchId, 'period' => $period === '7days' || $period === 'today' ? 'month' : $period])],
            ['icon' => 'cube', 'color' => 'bg-amber-50 text-amber-600', 'title' => __('Item-wise profit'), 'desc' => __('Which items earn the most and which earn nothing'), 'label' => __('Best item'), 'value' => e($bestItem ?? '—'), 'href' => route('sales-reports.item-wise-profit', ['branch_id' => $selectedBranchId, 'period' => $period])],
            ['icon' => 'users', 'color' => 'bg-red-50 text-red-500', 'title' => __('Customer dues'), 'desc' => __('Who owes you, how much and since when'), 'label' => __('To collect'), 'value' => '&#8377;'.number_format($customerDueTotal, 0), 'href' => route('customers.index', ['filter' => 'udhaar'])],
            ['icon' => 'truck', 'color' => 'bg-blue-50 text-blue-600', 'title' => __('Supplier dues'), 'desc' => __('How much you owe each supplier'), 'label' => __('We owe'), 'value' => '&#8377;'.number_format($supplierDueTotal, 0), 'href' => route('suppliers.index', ['filter' => 'we_owe'])],
            ['icon' => 'archive-box', 'color' => 'bg-teal-50 text-teal-600', 'title' => __('Stock report'), 'desc' => __('Stock value, low stock and slow-moving items'), 'label' => __('Stock value'), 'value' => '&#8377;'.number_format($stockValue, 0), 'href' => route('stock.index')],
            ['icon' => 'clipboard', 'color' => 'bg-gray-100 text-gray-600', 'title' => __('Purchases report'), 'desc' => __('What you bought, from whom and at what cost'), 'label' => __('Purchases'), 'value' => '&#8377;'.number_format($purchasesTotal, 0), 'href' => route('purchases.index')],
            ['icon' => 'currency', 'color' => 'bg-pink-50 text-pink-600', 'title' => __('Expenses report'), 'desc' => __('Costs by category, day and person'), 'label' => __('Expenses'), 'value' => '&#8377;'.number_format($expensesTotal, 0), 'href' => route('expenses.index')],
        ];
    @endphp

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Branch + period filters --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex self-start rounded-lg border border-gray-200 overflow-x-auto scrollbar-hide text-sm bg-white max-w-full order-2 lg:order-1">
                    @foreach (['today' => __('Today'), '7days' => __('7 days'), 'month' => __('This month'), 'all' => __('All time')] as $value => $label)
                        <a href="{{ route('sales-reports.index', ['branch_id' => $selectedBranchId, 'period' => $value]) }}"
                            class="shrink-0 px-3.5 py-2 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="order-1 lg:order-2 shrink-0">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        <select name="branch_id" onchange="this.form.submit()"
                            class="w-full lg:w-56 appearance-none border-gray-200 bg-gray-50 rounded-lg pl-9 pr-8 py-2.5 text-sm font-medium text-gray-700 focus:border-brand-500 focus:ring-brand-500">
                            <option value="all" @selected($selectedBranchId === 'all')>{{ __('All branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </div>
                </form>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <div class="bg-brand-600 text-white shadow-sm rounded-xl p-5">
                    <span class="text-sm text-white/70">{{ __('Sales') }}</span>
                    <div class="text-2xl font-bold mt-3">&#8377;{{ number_format($salesTotal, 0) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Gross profit') }}</span>
                    <div class="text-2xl font-bold text-green-600 mt-3">&#8377;{{ number_format($grossProfit, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $salesTotal > 0 ? round($grossProfit / $salesTotal * 100, 1) : 0 }}% {{ __('margin') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <span class="text-sm text-gray-500">{{ __('Net profit') }}</span>
                    <div class="text-2xl font-bold text-gray-900 mt-3">&#8377;{{ number_format($netProfit, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('after') }} &#8377;{{ number_format($expensesTotal, 0) }} {{ __('expenses') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border {{ $customerDueTotal > 0 ? 'border-red-200' : 'border-gray-100' }}">
                    <span class="text-sm text-gray-500">{{ __('Money to collect') }}</span>
                    <div class="text-2xl font-bold text-red-600 mt-3">&#8377;{{ number_format($customerDueTotal, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('from customers') }}</div>
                </div>
            </div>

            <h3 class="font-semibold text-gray-900 pt-2">{{ __('All reports') }}</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($reports as $report)
                    <a href="{{ $report['href'] }}" class="bg-white shadow-sm rounded-xl border border-gray-100 p-5 hover:border-brand-200 hover:shadow transition">
                        <div class="flex items-start justify-between mb-3">
                            <span class="w-10 h-10 rounded-lg flex items-center justify-center {{ $report['color'] }}">
                                @include('sales-reports.partials.report-icon', ['icon' => $report['icon']])
                            </span>
                        </div>
                        <div class="font-semibold text-gray-900">{{ $report['title'] }}</div>
                        <p class="text-xs text-gray-400 mt-1 mb-3">{{ $report['desc'] }}</p>
                        <div class="flex items-end justify-between pt-2 border-t border-gray-50">
                            <div>
                                <div class="text-xs text-gray-400">{{ $report['label'] }}</div>
                                <div class="font-bold text-gray-900">{!! $report['value'] !!}</div>
                            </div>
                            <span class="text-sm font-medium text-brand-600">{{ __('Open') }} &#8250;</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
