<div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <div>
        <h3 class="font-semibold text-gray-700">{{ __('Recent expenses') }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('Total shown') }}: &#8377;{{ number_format($totalShown, 2) }}</p>
    </div>
    <a href="{{ route('expenses.index') }}" class="text-xs font-medium text-brand-600 hover:underline">{{ __('View all') }}</a>
</div>
<div class="divide-y divide-gray-50">
    @forelse ($recentExpenses as $expense)
        <a href="{{ route('expenses.edit', $expense) }}" class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50">
            <div class="min-w-0">
                <div class="text-sm font-medium text-gray-800">{{ $expense->categoryLabel() }}</div>
                <div class="text-xs text-gray-400 truncate">
                    {{ $expense->staffMember?->name ?? __('General') }} &middot; {{ $expense->expense_date->format('d M') }}, {{ $expense->created_at->format('h:i A') }}
                </div>
            </div>
            <div class="text-sm font-semibold text-gray-800 shrink-0 ml-3">&#8377;{{ number_format($expense->amount, 2) }}</div>
        </a>
    @empty
        <div class="px-5 py-8 text-sm text-gray-400 text-center">{{ __('No expenses recorded yet.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t border-gray-100" @click="onClick($event)">{{ $recentExpenses->onEachSide(0)->links() }}</div>
