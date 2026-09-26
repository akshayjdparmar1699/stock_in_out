<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $customer->name }}</h2>
                <p class="text-sm text-gray-500">{{ $customer->phone }}</p>
            </div>
            <a href="{{ route('customers.statement-pdf', $customer) }}" target="_blank"
                onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($customer->name.' - Statement.pdf') }})"
                class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 self-start">
                {{ __('Download Statement PDF') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($customer->isOverCreditLimit())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-red-800">
                            {{ __('Over credit limit') }} — {{ __('due is') }} ₹{{ number_format($due, 2) }}, {{ __('limit is') }} ₹{{ number_format($customer->credit_limit, 2) }}
                        </p>
                        <p class="text-sm text-red-700 mt-0.5">{{ __('Time to call them or visit their shop to collect payment.') }}</p>
                    </div>
                    <a href="{{ $creditLimitAdminUrl }}" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 shrink-0">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.001 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.373 5.07L2 22l5.06-1.348A9.94 9.94 0 0012.001 22C17.523 22 22 17.52 22 12S17.523 2 12.001 2zm0 18.062a8.02 8.02 0 01-4.253-1.213l-.305-.181-3.005.8.803-2.93-.198-.303A8.024 8.024 0 013.938 12c0-4.444 3.618-8.062 8.063-8.062S20.062 7.556 20.062 12 16.446 20.062 12.001 20.062z"/></svg>
                        {{ __('Notify Admin') }}
                    </a>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="text-sm text-gray-500">{{ $due > 0 ? __('Balance Due') : __('Settled / In Credit') }}</div>
                    <div class="text-2xl font-bold {{ $due > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                    </div>
                </div>
                <a href="{{ route('customers.edit', $customer) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit Customer') }}</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Record a Payment') }}</h3>

                <form method="POST" action="{{ route('customers.payments.store', $customer) }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div>
                        <x-input-label for="amount" :value="__('Amount Received')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-40" value="{{ old('amount') }}" required />
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <x-input-label for="note" :value="__('Note (optional)')" />
                        <x-text-input id="note" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('e.g. Cash, UPI, part payment') }}" />
                    </div>
                    <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
                </form>
                <p class="text-xs text-gray-400 mt-3">
                    {{ __('Applied to their oldest outstanding invoices first, then their opening balance. Paying more than they owe adds the extra as credit for their next bill.') }}
                </p>
                @if ($customer->credit_balance > 0)
                    <p class="text-xs text-green-600 mt-1">
                        {{ __('Credit available for their next invoice:') }} ₹{{ number_format($customer->credit_balance, 2) }}
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
                                <div class="text-xs text-gray-400 uppercase">{{ $entry['type'] === 'billed' ? __('Billed') : __('Received') }}</div>
                                <div class="text-base font-semibold {{ $entry['type'] === 'billed' ? 'text-red-600' : 'text-green-600' }}">
                                    ₹{{ number_format($entry['amount'], 2) }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-10 text-sm text-gray-400 text-center">{{ __('No billing history yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
