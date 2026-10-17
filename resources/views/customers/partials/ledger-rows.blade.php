@forelse ($entries as $entry)
    @php
        $isBill = $entry['type'] === 'billed';
        $isRefund = $entry['type'] === 'refund';
        $sign = in_array($entry['type'], ['billed', 'refund'], true) ? '+' : '-';
    @endphp

    {{-- Desktop row --}}
    <div class="hidden lg:flex items-center justify-between gap-2 px-4 sm:px-6 py-3 hover:bg-gray-50">
        <a href="{{ $entry['url'] }}" class="flex-1 min-w-0 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm text-gray-800">{{ $entry['date']->format('d M Y, h:i A') }}</div>
                <div class="text-xs text-gray-400 truncate">{{ $entry['label'] }}</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    {{ __('Bal.') }} ₹{{ number_format(abs($entry['balance_after']), 2) }}{{ $entry['balance_after'] < 0 ? ' CR' : '' }}
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-xs text-gray-400 uppercase">{{ $isBill ? __('Billed') : ($isRefund ? __('Refund') : __('Received')) }}</div>
                <div class="text-base font-semibold {{ $sign === '+' ? 'text-red-600' : 'text-green-600' }}">
                    ₹{{ number_format($entry['amount'], 2) }}
                </div>
            </div>
        </a>
        @if ($entry['payment_key'])
            <form method="POST" action="{{ route('customers.payments.destroy', [$customer, $entry['payment_key']]) }}" class="shrink-0">
                @csrf
                @method('DELETE')
                <button type="button"
                    @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from('This statement entry') }}; deleteOpen = true"
                    class="text-red-400 hover:text-red-600 p-1" title="{{ __('Delete') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </form>
        @endif
    </div>

    {{-- Mobile row --}}
    <div class="lg:hidden flex items-center gap-3 px-4 py-3">
        <a href="{{ $entry['url'] }}" class="flex-1 min-w-0 flex items-center gap-3">
            <span @class([
                'w-9 h-9 rounded-lg flex items-center justify-center shrink-0',
                'bg-purple-50 text-purple-600' => $isBill,
                'bg-green-50 text-green-600' => ! $isBill && ! $isRefund,
                'bg-red-50 text-red-600' => $isRefund,
            ])>
                @if ($isBill)
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                @else
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                @endif
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-medium text-gray-800 truncate">{{ $entry['label'] }}</span>
                <span class="block text-xs text-gray-400">{{ $entry['date']->format('d M') }} &middot; {{ __('Bal') }} ₹{{ number_format(abs($entry['balance_after']), 0) }}</span>
            </span>
        </a>
        <span @class(['text-sm font-semibold shrink-0', 'text-gray-900' => $sign === '+', 'text-green-600' => $sign === '-'])>
            {{ $sign }}₹{{ number_format($entry['amount'], 0) }}
        </span>
        @if ($entry['payment_key'])
            <form method="POST" action="{{ route('customers.payments.destroy', [$customer, $entry['payment_key']]) }}" class="shrink-0">
                @csrf
                @method('DELETE')
                <button type="button"
                    @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from('This statement entry') }}; deleteOpen = true"
                    class="text-red-400 p-1" title="{{ __('Delete') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </form>
        @endif
    </div>
@empty
    <div class="px-6 py-10 text-sm text-gray-400 text-center">{{ __('No entries here yet.') }}</div>
@endforelse
