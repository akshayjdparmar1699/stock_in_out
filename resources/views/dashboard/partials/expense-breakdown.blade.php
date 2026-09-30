@php
    $periodLabels = ['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')];
@endphp
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
        <span class="text-sm text-gray-700">{{ __('Total') }} ({{ $periodLabels[$period] }})</span>
        <span class="text-sm text-gray-900">₹{{ number_format($expenseTotal, 2) }}</span>
    </div>
@endif
