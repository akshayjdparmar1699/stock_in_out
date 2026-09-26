<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $supplier->name }}</h2>
            <p class="text-sm text-gray-500">{{ $supplier->phone }}</p>
        </div>
    </x-slot>

    <div class="py-12">
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

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Record a Payment') }}</h3>

                <form method="POST" action="{{ route('suppliers.payments.store', $supplier) }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div>
                        <x-input-label for="amount" :value="__('Amount Paid')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-40" value="{{ old('amount') }}" required />
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <x-input-label for="note" :value="__('Note (optional)')" />
                        <x-text-input id="note" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('e.g. Cash, UPI, part payment') }}" />
                    </div>
                    <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
                </form>
                <p class="text-xs text-gray-400 mt-3">
                    {{ __('Applied to their oldest outstanding purchases first, then their opening balance. Paying more than we owe adds the extra as credit for our next purchase from them.') }}
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
                        <a href="{{ $entry['url'] }}" class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 hover:bg-gray-50">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-800">{{ $entry['date']->format('d M Y, h:i A') }}</div>
                                <div class="text-xs text-gray-400 truncate">{{ $entry['label'] }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ __('Bal.') }} ₹{{ number_format(abs($entry['balance_after']), 2) }}{{ $entry['balance_after'] < 0 ? ' CR' : '' }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-gray-400 uppercase">{{ $entry['type'] === 'billed' ? __('Billed') : __('Paid') }}</div>
                                <div class="text-base font-semibold {{ $entry['type'] === 'billed' ? 'text-red-600' : 'text-green-600' }}">
                                    ₹{{ number_format($entry['amount'], 2) }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-10 text-sm text-gray-400 text-center">{{ __('No purchase history yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
