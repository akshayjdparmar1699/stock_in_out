@php
    $lastDate = null;
    $groupLabel = function ($date) {
        if ($date->isToday()) return __('Today').', '.$date->format('d M');
        if ($date->isYesterday()) return __('Yesterday').', '.$date->format('d M');
        return $date->format('D, d M');
    };
    $dayTotals = [];
    foreach ($expenses as $e) {
        $key = $e->expense_date->format('Y-m-d');
        $dayTotals[$key] = ($dayTotals[$key] ?? 0) + (float) $e->amount;
    }
@endphp

{{-- Desktop table --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Expense') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Category') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Recorded By') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($expenses as $expense)
                @php
                    $date = $expense->expense_date;
                    $isNewGroup = $lastDate === null || ! $date->isSameDay($lastDate);
                    $lastDate = $date;
                @endphp
                @if ($isNewGroup)
                    <tr>
                        <td colspan="5" class="px-6 py-2 text-xs font-semibold text-brand-700 bg-brand-50/60 flex items-center justify-between">
                            <span>{{ $groupLabel($date) }}</span>
                            <span class="text-gray-500 font-normal">&#8377;{{ number_format($dayTotals[$date->format('Y-m-d')], 0) }}</span>
                        </td>
                    </tr>
                @endif
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $expense->categoryColor() }}">
                                @include('expenses.partials.category-icon', ['icon' => $expense->categoryIcon()])
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">{{ $expense->note ?: $expense->categoryLabel() }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $expense->categoryLabel() }} &middot; {{ $expense->created_at->format('h:i A') }}
                                    @if ($expense->staffMember)
                                        &middot; {{ __('for') }} {{ $expense->staffMember->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $expense->categoryColor() }}">{{ $expense->categoryLabel() }}</span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $expense->user->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">&#8377;{{ number_format($expense->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <div class="flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                            <a href="{{ route('expenses.edit', $expense) }}" title="{{ __('Edit') }}" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                            </a>
                            <div class="relative">
                                <button type="button" @click="menuOpen = !menuOpen" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0zm6 2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" style="display: none"
                                    x-transition class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-10">
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($expense->categoryLabel().' ('.number_format($expense->amount, 2).')') }}; deleteOpen = true; menuOpen = false"
                                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No expenses recorded for this filter.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @php $lastDate = null; @endphp
    @forelse ($expenses as $expense)
        @php
            $date = $expense->expense_date;
            $isNewGroup = $lastDate === null || ! $date->isSameDay($lastDate);
            $lastDate = $date;
        @endphp
        @if ($isNewGroup)
            <div class="flex items-center justify-between pt-1 text-xs font-semibold text-brand-700">
                <span>{{ $groupLabel($date) }}</span>
                <span class="text-gray-400 font-normal">&#8377;{{ number_format($dayTotals[$date->format('Y-m-d')], 0) }}</span>
            </div>
        @endif
        <div onclick="window.location.href='{{ route('expenses.edit', $expense) }}'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 cursor-pointer">
            <span class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $expense->categoryColor() }}">
                @include('expenses.partials.category-icon', ['icon' => $expense->categoryIcon()])
            </span>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold text-gray-900 truncate">{{ $expense->note ?: $expense->categoryLabel() }}</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    {{ $expense->categoryLabel() }} &middot; {{ $expense->created_at->format('h:i A') }}
                    @if ($expense->staffMember)
                        &middot; {{ $expense->staffMember->name }}
                    @endif
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-sm font-bold text-gray-900">&#8377;{{ number_format($expense->amount, 2) }}</div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No expenses recorded for this filter.') }}</div>
    @endforelse
</div>

{{-- mb-20 on mobile clears the "+" FAB (fixed, bottom-20, 56px tall) so its Next link isn't covered. --}}
<div class="flex flex-wrap items-center justify-between gap-3 mt-4 mb-20 lg:mb-0" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $expenses->links() }}
</div>
