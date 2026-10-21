@forelse ($payments as $payment)
    @php
        $isSalary = $payment->category === 'salary';
        $label = $isSalary
            ? __('Salary for :month', ['month' => $payment->for_month?->format('F') ?? $payment->expense_date->format('F')])
            : ($staffMember->isPartner() ? __('Withdrawal') : __('Advance'));
    @endphp
    <div class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5">
        <div class="flex items-center gap-3 min-w-0">
            <span @class([
                'w-9 h-9 rounded-full flex items-center justify-center shrink-0 text-xs font-bold',
                'bg-brand-100 text-brand-700' => $isSalary,
                'bg-amber-100 text-amber-700' => ! $isSalary,
            ])>₹</span>
            <div class="min-w-0">
                <div class="text-sm font-medium text-gray-900 truncate">{{ $label }}</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    {{ $payment->expense_date->format('d M Y') }}
                    @if ($payment->mode)
                        &middot; {{ ucfirst($payment->mode) }}
                    @endif
                    &middot; {{ __('by') }} {{ $payment->user->name ?? __('Unknown') }}
                </div>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span @class([
                'inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase mb-1',
                'bg-brand-50 text-brand-700' => $isSalary,
                'bg-amber-50 text-amber-700' => ! $isSalary,
            ])>{{ $isSalary ? __('Salary') : ($staffMember->isPartner() ? __('Withdrawal') : __('Advance')) }}</span>
            <div class="text-sm font-bold text-gray-900">₹{{ number_format($payment->amount, 0) }}</div>
        </div>
    </div>
@empty
    <div class="px-6 py-10 text-sm text-gray-400 text-center">{{ __('No payments recorded yet.') }}</div>
@endforelse
