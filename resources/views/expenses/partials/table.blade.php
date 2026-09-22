<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Category') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Staff / Partner') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Note') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Recorded By') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($expenses as $expense)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-6 py-3 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $expense->category === 'upad' ? 'bg-amber-100 text-amber-700' : ($expense->category === 'salary' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $expense->categoryLabel() }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-700">{{ $expense->staffMember?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-right font-medium text-gray-800">₹{{ number_format($expense->amount, 2) }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $expense->note ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $expense->user->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-right text-sm">
                        <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No expenses recorded for this filter.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $expenses->links() }}
</div>
