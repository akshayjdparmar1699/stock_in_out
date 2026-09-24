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

            <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="text-sm text-gray-500">{{ $due > 0 ? __('Balance Due') : __('Settled / In Credit') }}</div>
                    <div class="text-2xl font-bold {{ $due > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                    </div>
                </div>
                <a href="{{ route('customers.edit', $customer) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit Customer') }}</a>
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
