<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock Batch') }} &mdash; {{ $batch->item->name }}</h2>
            <a href="{{ route('items.show', $batch->item) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                {{ __('View Item') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 mb-4">
                    {{ __('Received on') }} {{ $batch->received_at->format('d M Y, h:i A') }}
                    @if ($batch->sourceMovement?->referenceUrl())
                        &middot; <a href="{{ $batch->sourceMovement->referenceUrl() }}" class="text-indigo-600 hover:underline">{{ __($batch->sourceMovement->referenceLabel()) }}</a>
                    @elseif ($batch->sourceMovement?->reason)
                        &middot; {{ $batch->sourceMovement->reason }}
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Quantity Received') }}</div>
                        <div class="text-xl font-semibold text-gray-900">{{ $batch->quantity_in }} {{ $batch->item->unit }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Sold / Used') }}</div>
                        <div class="text-xl font-semibold text-red-600">{{ number_format($batch->quantitySold(), 2) }} {{ $batch->item->unit }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Remaining') }}</div>
                        <div class="text-xl font-semibold {{ $batch->isDepleted() ? 'text-gray-400' : 'text-green-600' }}">
                            {{ $batch->quantity_remaining }} {{ $batch->item->unit }}
                        </div>
                        @if ($batch->isDepleted())
                            <div class="text-xs text-gray-400">{{ __('Fully sold out') }}</div>
                        @endif
                    </div>
                    @if ($batch->unit_cost)
                        <div>
                            <div class="text-sm text-gray-500">{{ __('Unit Cost') }}</div>
                            <div class="text-xl font-semibold text-gray-900">₹{{ number_format($batch->unit_cost, 2) }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('batches.partials.allocations-table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
