<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales-reports.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('sales-reports.index') }}" class="hover:text-gray-600">{{ __('Sales & Reports') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Profit and loss') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Profit and loss') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white">
                    @foreach (['month' => __('This month'), 'all' => __('All time')] as $value => $label)
                        <a href="{{ route('sales-reports.profit-loss', ['branch_id' => $selectedBranchId, 'period' => $value]) }}"
                            class="px-3 py-2 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('sales-reports.profit-loss.pdf', ['branch_id' => $selectedBranchId, 'period' => $period]) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    {{ __('Download PDF') }}
                </a>
            </div>
            <div class="lg:hidden shrink-0">
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Mobile period tabs + export --}}
            <div class="flex lg:hidden items-center gap-2">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white shrink-0">
                    @foreach (['month' => __('This month'), 'all' => __('All time')] as $value => $label)
                        <a href="{{ route('sales-reports.profit-loss', ['branch_id' => $selectedBranchId, 'period' => $value]) }}"
                            class="px-3 py-2 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('sales-reports.profit-loss.pdf', ['branch_id' => $selectedBranchId, 'period' => $period]) }}" target="_blank"
                    class="inline-flex items-center justify-center w-10 h-10 border border-gray-300 text-gray-700 rounded-lg shrink-0" aria-label="{{ __('Download PDF') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </a>
            </div>

            <div class="lg:grid lg:grid-cols-3 lg:gap-4 space-y-4 lg:space-y-0">
                <div class="lg:col-span-2 space-y-4">
                    {{-- Net profit hero --}}
                    <div class="bg-brand-600 text-white rounded-2xl p-5 lg:p-6">
                        <p class="text-sm text-white/70">{{ __('Net profit') }} &middot; {{ $period === 'month' ? __('this month') : __('all time') }}</p>
                        <div class="flex flex-wrap items-center justify-between gap-3 mt-1">
                            <div class="text-3xl lg:text-4xl font-bold">&#8377;{{ number_format($netProfit, 2) }}</div>
                            <div class="bg-white/15 rounded-lg px-3 py-1.5 text-sm">
                                {{ __('For every') }} &#8377;100 {{ __('sold, you keep') }} &#8377;{{ number_format($profitPer100, 2) }}
                            </div>
                        </div>
                        <div class="h-2.5 rounded-full bg-white/20 overflow-hidden flex mt-5">
                            <div class="bg-white/40 h-full" style="width: {{ min(100, $cogsPer100) }}%"></div>
                            <div class="bg-amber-300 h-full" style="width: {{ min(100, $expensesPer100) }}%"></div>
                            <div class="bg-green-300 h-full" style="width: {{ min(100, $profitPer100) }}%"></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-white/80">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-white/40"></span>{{ __('Cost of goods') }} &#8377;{{ number_format($cogsPer100, 2) }}</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-300"></span>{{ __('Expenses') }} &#8377;{{ number_format($expensesPer100, 2) }}</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-green-300"></span>{{ __('Profit') }} &#8377;{{ number_format($profitPer100, 2) }}</span>
                        </div>
                    </div>

                    {{-- Statement --}}
                    <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Statement') }}</h3>

                        <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                            <span class="text-sm text-gray-700">{{ __('Sales') }}</span>
                            <span class="text-sm font-semibold text-gray-900">&#8377;{{ number_format($salesTotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                            <span class="text-sm text-gray-500">{{ __('Less: cost of goods sold') }} <span class="text-xs text-gray-400">({{ __('purchase price of what you sold') }})</span></span>
                            <span class="text-sm font-medium text-red-500">- &#8377;{{ number_format($cogs, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                            <span class="text-sm font-semibold text-gray-900">{{ __('Gross profit') }} <span class="text-xs font-normal text-green-600">{{ $grossMargin }}% {{ __('margin') }}</span></span>
                            <span class="text-sm font-bold text-green-600">&#8377;{{ number_format($grossProfit, 2) }}</span>
                        </div>

                        <div class="py-2.5 border-b border-gray-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-700">{{ __('Less: expenses') }}</span>
                                <span class="text-sm font-medium text-red-500">- &#8377;{{ number_format($expensesTotal, 2) }}</span>
                            </div>
                            @foreach ($expensesByCategory as $row)
                                <div class="flex items-center justify-between gap-3 py-1 pl-2">
                                    <span class="text-xs text-gray-500 w-20 shrink-0">{{ $row['label'] }}</span>
                                    <div class="flex-1 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full bg-amber-400 rounded-full" style="width: {{ $row['percent'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 w-16 text-right shrink-0">&#8377;{{ number_format($row['total'], 0) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between py-3 px-3 bg-green-50 rounded-lg mt-3">
                            <span class="text-sm font-bold text-gray-900">{{ __('Net profit') }}</span>
                            <span class="text-sm font-bold text-green-700">&#8377;{{ number_format($netProfit, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ __('Money position today') }}</h3>
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ __('Customers owe you') }}</span>
                                <span class="font-semibold text-green-600">&#8377;{{ number_format($customerDueTotal, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ __('You owe suppliers') }}</span>
                                <span class="font-semibold text-red-600">- &#8377;{{ number_format($supplierDueTotal, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm pt-2.5 border-t border-gray-100">
                                <span class="text-gray-500">{{ __('Net to receive') }}</span>
                                <span class="font-bold text-brand-700">&#8377;{{ number_format($customerDueTotal - $supplierDueTotal, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ __('Stock in godown') }}</span>
                                <span class="font-semibold text-gray-900">&#8377;{{ number_format($stockValue, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 lg:p-5">
                        <h3 class="font-semibold text-amber-900 mb-1.5">{{ __('What this means') }}</h3>
                        <p class="text-sm text-amber-800">{{ $insight }}</p>
                        <a href="{{ route('customers.index', ['filter' => 'udhaar']) }}" class="inline-block mt-2 text-sm font-semibold text-amber-900 underline">{{ __('See who owes the most') }} &#8250;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
