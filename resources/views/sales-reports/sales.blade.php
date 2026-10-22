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
                    <span class="text-gray-700 font-medium">{{ __('Sales report') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Sales report') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white">
                    @foreach (['7days' => __('Last 7 days'), 'month' => __('This month')] as $value => $label)
                        <a href="{{ route('sales-reports.sales', ['branch_id' => $selectedBranchId, 'period' => $value]) }}"
                            class="px-3 py-2 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('sales-reports.sales.pdf', ['branch_id' => $selectedBranchId, 'period' => $period]) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    <span>{{ __('PDF') }}</span>
                </a>
                <a href="{{ route('sales-reports.sales.excel', ['branch_id' => $selectedBranchId, 'period' => $period]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    <span>{{ __('Excel') }}</span>
                </a>
            </div>
            <div class="lg:hidden shrink-0">
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Mobile period tabs + export (desktop has these in the header instead) --}}
            <div class="flex lg:hidden items-center gap-2">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white shrink-0">
                    @foreach (['7days' => __('7 days'), 'month' => __('Month')] as $value => $label)
                        <a href="{{ route('sales-reports.sales', ['branch_id' => $selectedBranchId, 'period' => $value]) }}"
                            class="px-3 py-2 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('sales-reports.sales.pdf', ['branch_id' => $selectedBranchId, 'period' => $period]) }}" target="_blank"
                    class="inline-flex items-center justify-center w-10 h-10 border border-gray-300 text-gray-700 rounded-lg shrink-0" aria-label="{{ __('PDF') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </a>
                <a href="{{ route('sales-reports.sales.excel', ['branch_id' => $selectedBranchId, 'period' => $period]) }}"
                    class="inline-flex items-center justify-center w-10 h-10 border border-gray-300 text-gray-700 rounded-lg shrink-0" aria-label="{{ __('Excel') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </a>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
                <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                    <span class="text-xs lg:text-sm text-gray-500">{{ __('Sales') }}</span>
                    <div class="text-xl lg:text-2xl font-bold text-gray-900 mt-2 lg:mt-3">&#8377;{{ number_format($salesTotal, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $billCount }} {{ __('bills') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                    <span class="text-xs lg:text-sm text-gray-500">{{ __('Gross profit') }}</span>
                    <div class="text-xl lg:text-2xl font-bold text-green-600 mt-2 lg:mt-3">&#8377;{{ number_format($grossProfit, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $margin }}% {{ __('margin') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                    <span class="text-xs lg:text-sm text-gray-500">{{ __('Average bill') }}</span>
                    <div class="text-xl lg:text-2xl font-bold text-gray-900 mt-2 lg:mt-3">&#8377;{{ number_format($averageBill, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('per bill') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                    <span class="text-xs lg:text-sm text-gray-500">{{ __('Received now') }}</span>
                    <div class="text-xl lg:text-2xl font-bold text-gray-900 mt-2 lg:mt-3">&#8377;{{ number_format($received, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('cash and UPI') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                    <span class="text-xs lg:text-sm text-gray-500">{{ __('Given on credit') }}</span>
                    <div class="text-xl lg:text-2xl font-bold text-red-600 mt-2 lg:mt-3">&#8377;{{ number_format($onCredit, 0) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('added to khata') }}</div>
                </div>
            </div>

            {{-- Chart --}}
            @php
                $days = $dailyTrend;
                $lastIndex = max(0, count($days) - 1);
            @endphp
            <div class="lg:grid lg:grid-cols-3 lg:gap-4" x-data="{ days: {{ \Illuminate\Support\Js::from($days) }}, selectedIndex: {{ $lastIndex }} }" @chart-bar-click="selectedIndex = $event.detail.index">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Sales and profit') }}</h3>
                        <div class="hidden lg:flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-brand-600"></span>{{ __('Sales') }}</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>{{ __('Profit') }}</span>
                        </div>
                        <span class="lg:hidden text-xs text-gray-400">{{ __('Tap a bar') }}</span>
                    </div>
                    <div x-data="barLineClickChart({
                            labels: days.map(d => d.short),
                            sales: days.map(d => d.sales),
                            profit: days.map(d => d.profit),
                        })" x-init="render($refs.canvas)">
                        <canvas x-ref="canvas" height="100"></canvas>
                    </div>
                </div>

                <div class="mt-4 lg:mt-0 bg-brand-50 rounded-xl p-5 flex flex-col">
                    <template x-if="days[selectedIndex]">
                        <div>
                            <div class="text-sm font-semibold text-brand-700" x-text="days[selectedIndex].label"></div>
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">{{ __('Sales') }}</span>
                                    <span class="text-sm font-bold text-gray-900" x-text="'₹' + Math.round(days[selectedIndex].sales).toLocaleString('en-IN')"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">{{ __('Profit') }}</span>
                                    <span class="text-sm font-bold text-green-600" x-text="'₹' + Math.round(days[selectedIndex].profit).toLocaleString('en-IN')"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">{{ __('Bills') }}</span>
                                    <span class="text-sm font-bold text-gray-900" x-text="days[selectedIndex].bills"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p class="text-xs text-gray-400 mt-auto pt-6 hidden lg:block">{{ __('Click a bar to see that day') }}</p>
                </div>
            </div>

            {{-- Top items + top customers --}}
            <div class="lg:grid lg:grid-cols-2 lg:gap-4 space-y-4 lg:space-y-0">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900">{{ __('Top items') }}</h3>
                        <a href="{{ route('sales-reports.item-wise-profit', ['branch_id' => $selectedBranchId, 'period' => $period]) }}" class="text-sm font-medium text-brand-600">{{ __('All items') }} &#8250;</a>
                    </div>
                    <div class="hidden sm:grid sm:grid-cols-[1fr_6rem_6rem_6rem] text-xs font-medium text-gray-400 uppercase pb-2 border-b border-gray-100">
                        <div>{{ __('Item') }}</div>
                        <div class="text-right">{{ __('Sold') }}</div>
                        <div class="text-right">{{ __('Sales') }}</div>
                        <div class="text-right">{{ __('Profit') }}</div>
                    </div>
                    @php $maxSales = (float) ($topItems->max('sales_total') ?: 1); @endphp
                    @forelse ($topItems as $row)
                        <div class="py-2.5 border-b border-gray-50 last:border-0">
                            <div class="sm:grid sm:grid-cols-[1fr_6rem_6rem_6rem] items-center gap-2">
                                <div class="font-medium text-gray-800 text-sm">{{ $row->name }}</div>
                                <div class="text-right text-sm text-gray-500">{{ rtrim(rtrim(number_format((float) $row->sold_qty, 2), '0'), '.') }} {{ $row->unit }}</div>
                                <div class="text-right text-sm font-semibold text-gray-900">&#8377;{{ number_format($row->sales_total, 0) }}</div>
                                <div class="text-right text-sm font-semibold text-green-600">&#8377;{{ number_format($row->profit, 0) }}</div>
                            </div>
                            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden mt-1.5">
                                <div class="h-full bg-brand-600 rounded-full" style="width: {{ round((float) $row->sales_total / $maxSales * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">{{ __('No sales in this period.') }}</p>
                    @endforelse
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900">{{ __('Top customers') }}</h3>
                        <a href="{{ route('customers.index') }}" class="text-sm font-medium text-brand-600">{{ __('All') }} &#8250;</a>
                    </div>
                    @forelse ($topCustomers as $i => $row)
                        <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                            <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-800 truncate">{{ $row['customer']->name }}</div>
                                @if ($row['due'] > 0)
                                    <div class="text-xs text-red-600">{{ __('Owes') }} &#8377;{{ number_format($row['due'], 0) }}</div>
                                @else
                                    <div class="text-xs text-green-600">{{ __('Nothing due') }}</div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-sm font-bold text-gray-900">&#8377;{{ number_format($row['sales_total'], 0) }}</div>
                                <div class="text-xs text-gray-400">{{ $row['bill_count'] }} {{ __('bills') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">{{ __('No sales in this period.') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Breakdown bars --}}
            <div class="lg:grid lg:grid-cols-3 lg:gap-4 space-y-4 lg:space-y-0">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ __('How customers paid') }}</h3>
                    @if (count($paymentBreakdown))
                        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden flex mb-3">
                            @foreach ($paymentBreakdown as $row)
                                <div class="{{ $row['color'] }} h-full" style="width: {{ $row['percent'] }}%"></div>
                            @endforeach
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($paymentBreakdown as $row)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-full {{ $row['color'] }}"></span>{{ $row['label'] }}</span>
                                    <span class="font-medium text-gray-800">&#8377;{{ number_format($row['amount'], 0) }} &middot; {{ $row['percent'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400">{{ __('No payments in this period.') }}</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ __('Sales by branch') }}</h3>
                    @if ($salesByBranch->isNotEmpty())
                        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden flex mb-3">
                            @foreach ($salesByBranch as $row)
                                <div class="{{ $row['color'] }} h-full" style="width: {{ $row['percent'] }}%"></div>
                            @endforeach
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($salesByBranch as $row)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-full {{ $row['color'] }}"></span>{{ $row['name'] }}</span>
                                    <span class="font-medium text-gray-800">&#8377;{{ number_format($row['total'], 0) }} &middot; {{ $row['percent'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400">{{ __('No sales in this period.') }}</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 lg:p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ __('Bills made by') }}</h3>
                    @if ($billsMadeBy->isNotEmpty())
                        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden flex mb-3">
                            @foreach ($billsMadeBy as $row)
                                <div class="{{ $row['color'] }} h-full" style="width: {{ $row['percent'] }}%"></div>
                            @endforeach
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($billsMadeBy as $row)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-full {{ $row['color'] }}"></span>{{ $row['name'] }}</span>
                                    <span class="font-medium text-gray-800">&#8377;{{ number_format($row['total'], 0) }} &middot; {{ $row['percent'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400">{{ __('No bills in this period.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
