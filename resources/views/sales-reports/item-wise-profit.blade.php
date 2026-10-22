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
                    <span class="text-gray-700 font-medium">{{ __('Item-wise profit') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Item-wise profit') }}</h2>
            </div>
            <div class="lg:hidden shrink-0">
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-x-auto scrollbar-hide text-sm bg-white max-w-full">
                    @foreach (['today' => __('Today'), '7days' => __('7 days'), 'month' => __('This month'), 'all' => __('All time')] as $value => $label)
                        <a href="{{ route('sales-reports.item-wise-profit', ['branch_id' => $selectedBranchId, 'period' => $value, 'sort' => $sort]) }}"
                            class="shrink-0 px-3.5 py-2 {{ $period === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm bg-white">
                    @foreach (['profit' => __('Profit'), 'sales_total' => __('Sales'), 'sold_qty' => __('Sold')] as $value => $label)
                        <a href="{{ route('sales-reports.item-wise-profit', ['branch_id' => $selectedBranchId, 'period' => $period, 'sort' => $value]) }}"
                            class="px-3 py-2 {{ $sort === $value ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Sold') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Sales') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Profit') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $row)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $row->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ rtrim(rtrim(number_format((float) $row->sold_qty, 2), '0'), '.') }} {{ $row->unit }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">&#8377;{{ number_format($row->sales_total, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right {{ $row->profit >= 0 ? 'text-green-600' : 'text-red-600' }}">&#8377;{{ number_format($row->profit, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ $row->sales_total > 0 ? round($row->profit / $row->sales_total * 100, 1) : 0 }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No sales in this period.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
