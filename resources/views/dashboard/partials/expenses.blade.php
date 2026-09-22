<div class="divide-y">
    @forelse ($recentExpenses as $expense)
        <a href="{{ route('expenses.edit', $expense) }}" class="flex justify-between px-5 py-3 hover:bg-gray-50">
            <div>
                <div class="text-sm font-medium text-gray-800">{{ $expense->categoryLabel() }}</div>
                <div class="text-xs text-gray-500">
                    {{ $expense->staffMember?->name ?? __('General') }} &middot; {{ $expense->expense_date->format('d M Y') }}
                </div>
            </div>
            <div class="text-sm text-gray-700">₹{{ number_format($expense->amount, 2) }}</div>
        </a>
    @empty
        <div class="px-5 py-6 text-sm text-gray-400">{{ __('No expenses recorded yet.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t" @click="onClick($event)">{{ $recentExpenses->onEachSide(0)->links() }}</div>
