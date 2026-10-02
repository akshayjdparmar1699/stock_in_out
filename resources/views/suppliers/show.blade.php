<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $supplier->name }}</h2>
            <p class="text-sm text-gray-500">{{ $supplier->phone }}</p>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '' }">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="text-sm text-gray-500">{{ $due > 0 ? __('We Owe Them') : __('Settled / In Credit') }}</div>
                    <div class="text-2xl font-bold {{ $due > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                    </div>
                </div>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit Supplier') }}</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6" x-data="{ type: '{{ old('type', 'paid') }}' }">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Record a Payment') }}</h3>

                <form method="POST" action="{{ route('suppliers.payments.store', $supplier) }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="w-full flex gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="type" value="paid" x-model="type" class="text-indigo-600 focus:ring-indigo-500">
                            {{ __('We Paid Them') }}
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="type" value="received" x-model="type" class="text-indigo-600 focus:ring-indigo-500">
                            {{ __('Refund Received From Them') }}
                        </label>
                    </div>
                    <div>
                        <x-input-label for="amount" :value="old('type') === 'received' ? __('Refund Amount') : __('Amount Paid')" x-text="type === 'received' ? '{{ __('Refund Amount') }}' : '{{ __('Amount Paid') }}'" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-40" value="{{ old('amount') }}" required />
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <x-input-label for="note" :value="__('Note (optional)')" />
                        <x-text-input id="note" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('e.g. Cash, UPI, part payment') }}" />
                    </div>
                    <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
                </form>
                <p class="text-xs text-gray-400 mt-3">
                    {{ __('"We Paid Them" is applied to their oldest outstanding purchases first, then their opening balance; paying more than we owe adds the extra as credit. "Refund Received" is for money they handed back to us (e.g. short delivery) — it reduces that credit instead.') }}
                </p>
                @if ($supplier->credit_balance > 0)
                    <p class="text-xs text-green-600 mt-1">
                        {{ __('Credit available for our next purchase:') }} ₹{{ number_format($supplier->credit_balance, 2) }}
                    </p>
                @endif
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 sm:px-6 py-3 bg-gray-50 border-b border-gray-100">
                    <h3 class="text-sm font-medium text-gray-600">{{ __('Statement') }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($entries as $entry)
                        <div class="flex items-center justify-between gap-2 px-4 sm:px-6 py-3 hover:bg-gray-50">
                            <a href="{{ $entry['url'] }}" class="flex-1 min-w-0 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm text-gray-800">{{ $entry['date']->format('d M Y, h:i A') }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $entry['label'] }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ __('Bal.') }} ₹{{ number_format(abs($entry['balance_after']), 2) }}{{ $entry['balance_after'] < 0 ? ' CR' : '' }}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs text-gray-400 uppercase">{{ $entry['type'] === 'billed' ? __('Billed') : ($entry['type'] === 'refund' ? __('Refund') : __('Paid')) }}</div>
                                    <div class="text-base font-semibold {{ in_array($entry['type'], ['billed', 'refund'], true) ? 'text-red-600' : 'text-green-600' }}">
                                        ₹{{ number_format($entry['amount'], 2) }}
                                    </div>
                                </div>
                            </a>
                            @if ($entry['payment_key'])
                                <form method="POST" action="{{ route('suppliers.payments.destroy', [$supplier, $entry['payment_key']]) }}" class="shrink-0">
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
                    @empty
                        <div class="px-6 py-10 text-sm text-gray-400 text-center">{{ __('No purchase history yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
