@php
    $periodLabels = ['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')];
    $palette = ['#6a00d9', '#f59e0b', '#16a34a', '#db2777', '#64748b'];
    $rows = collect(\App\Models\Expense::categories())
        ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'amount' => (float) ($expenseBreakdown[$key] ?? 0)])
        ->filter(fn ($row) => $row['amount'] > 0)
        ->values();
@endphp

@if ($expenseTotal > 0)
    <div class="p-5 flex flex-col sm:flex-row items-center gap-6">
        <div class="w-40 h-40 shrink-0" x-data="donutChart({
                labels: {{ \Illuminate\Support\Js::from($rows->pluck('label')) }},
                values: {{ \Illuminate\Support\Js::from($rows->pluck('amount')) }},
                colors: {{ \Illuminate\Support\Js::from(array_slice($palette, 0, $rows->count())) }},
            })" x-init="render($refs.canvas)">
            <canvas x-ref="canvas"></canvas>
        </div>

        <div class="flex-1 w-full divide-y divide-gray-50">
            @foreach ($rows as $i => $row)
                <div class="flex items-center justify-between py-2">
                    <span class="flex items-center gap-2 text-sm text-gray-700">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $palette[$i] }}"></span>
                        {{ $row['label'] }}
                    </span>
                    <span class="text-sm font-medium text-gray-800">&#8377;{{ number_format($row['amount'], 0) }}</span>
                </div>
            @endforeach
            <div class="flex items-center justify-between pt-2.5 font-semibold">
                <span class="text-sm text-gray-700">{{ __('Total') }} ({{ $periodLabels[$period] }})</span>
                <span class="text-sm text-gray-900">&#8377;{{ number_format($expenseTotal, 0) }}</span>
            </div>
        </div>
    </div>
@else
    <div class="px-5 py-8 text-sm text-gray-400 text-center">{{ __('No expenses in this period.') }}</div>
@endif
