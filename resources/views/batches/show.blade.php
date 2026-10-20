@php
    $isPurchase = $batch->sourceMovement?->referenceLabel() === 'View purchase';
    $statusBadge = [
        'full' => ['bg-green-100 text-green-700', __('Full')],
        'selling' => ['bg-amber-100 text-amber-700', __('Selling')],
        'sold_out' => ['bg-gray-100 text-gray-500', __('Sold out')],
    ];
    [$badgeClass, $badgeLabel] = $statusBadge[$batch->statusKey()];
    $sold = $batch->quantitySold();
    $pctIn = 100;
    $pctSold = (float) $batch->quantity_in > 0 ? min(100, round($sold / (float) $batch->quantity_in * 100)) : 0;
    $pctLeft = (float) $batch->quantity_in > 0 ? min(100, round((float) $batch->quantity_remaining / (float) $batch->quantity_in * 100)) : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('batches.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('batches.index') }}" class="hover:text-gray-600">{{ __('Maal In Out') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __(':item lot, :date', ['item' => $batch->item->name, 'date' => $batch->received_at->format('d M')]) }}</span>
                </nav>
                <h2 class="font-semibold text-lg text-gray-800 leading-tight lg:hidden truncate">{{ __(':item lot, :date', ['item' => $batch->item->name, 'date' => $batch->received_at->format('d M')]) }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                @if ($isPurchase)
                    <a href="{{ $batch->sourceMovement->referenceUrl() }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        {{ __('View purchase bill') }}
                    </a>
                @endif
                <a href="{{ route('items.show', $batch->item) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('View item') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Identity card --}}
            <div class="bg-white shadow-sm rounded-lg p-4 lg:p-6">
                <div class="lg:flex lg:items-start lg:justify-between gap-6">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl font-bold text-gray-900">{{ $batch->item->name }}</h1>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ strtoupper($badgeLabel) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ __('Came in on') }} <span class="font-medium text-gray-700">{{ $batch->received_at->format('d M Y, h:i A') }}</span>
                            {{ $isPurchase ? __('from a supplier purchase') : __('via manual stock entry') }}
                        </p>
                        @if ($fullySoldInDays !== null)
                            <span class="inline-block mt-2 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                {{ $fullySoldInDays === 0 ? __('Fully sold the same day') : __('Fully sold in :days day(s)', ['days' => $fullySoldInDays]) }}
                            </span>
                        @endif
                    </div>

                    <div class="hidden lg:grid lg:grid-cols-3 gap-6 shrink-0 text-right">
                        <div>
                            <p class="text-xs text-gray-400">{{ __('Came in') }}</p>
                            <p class="text-lg font-bold text-gray-900">{{ rtrim(rtrim(number_format($batch->quantity_in, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                            @if ($batch->item->hasAltUnit())
                                <p class="text-xs text-gray-400">= {{ number_format($batch->item->altUnitQuantity($batch->quantity_in), 2) }} {{ $batch->item->alt_unit }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">{{ __('Sold') }}</p>
                            <p class="text-lg font-bold text-brand-700">{{ rtrim(rtrim(number_format($sold, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                            <p class="text-xs text-gray-400">{{ __('in :count bills', ['count' => $allocationCount]) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">{{ __('Left') }}</p>
                            <p class="text-lg font-bold {{ $batch->statusKey() === 'sold_out' ? 'text-gray-400' : 'text-green-600' }}">{{ rtrim(rtrim(number_format($batch->quantity_remaining, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                        </div>
                    </div>
                </div>

                {{-- Mobile stat row --}}
                <div class="lg:hidden grid grid-cols-3 gap-3 mt-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">{{ __('Came in') }}</p>
                        <p class="text-base font-bold text-gray-900">{{ rtrim(rtrim(number_format($batch->quantity_in, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                        @if ($batch->item->hasAltUnit())
                            <p class="text-xs text-gray-400">= {{ number_format($batch->item->altUnitQuantity($batch->quantity_in), 2) }} {{ $batch->item->alt_unit }}</p>
                        @endif
                    </div>
                    <div class="bg-brand-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">{{ __('Sold') }}</p>
                        <p class="text-base font-bold text-brand-700">{{ rtrim(rtrim(number_format($sold, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                        <p class="text-xs text-gray-400">{{ __(':count bills', ['count' => $allocationCount]) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">{{ __('Left') }}</p>
                        <p class="text-base font-bold {{ $batch->statusKey() === 'sold_out' ? 'text-gray-400' : 'text-green-600' }}">{{ rtrim(rtrim(number_format($batch->quantity_remaining, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                    </div>
                </div>

                <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden mt-4">
                    <div class="h-full rounded-full {{ $batch->statusKey() === 'sold_out' ? 'bg-gray-300' : 'bg-brand-600' }}" style="width: {{ $pctSold }}%"></div>
                </div>
            </div>

            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start space-y-4 lg:space-y-0">
                {{-- Timeline --}}
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg p-4 lg:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Where this lot went') }}</h3>

                    <div class="space-y-0">
                        <div class="flex gap-3 relative pb-5">
                            <div class="flex flex-col items-center shrink-0">
                                <span class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" /></svg>
                                </span>
                                @if ($timeline->isNotEmpty())
                                    <span class="w-px flex-1 bg-gray-200 mt-1"></span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $isPurchase ? __('Came in from supplier') : __('Added manually') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $batch->received_at->format('d M, h:i A') }} &middot; {{ $batch->sourceMovement?->user->name ?? '—' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-green-600">+{{ rtrim(rtrim(number_format($batch->quantity_in, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                                    <p class="text-xs text-gray-400">{{ __('Left') }} {{ rtrim(rtrim(number_format($batch->quantity_in, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                                </div>
                            </div>
                        </div>

                        @foreach ($timeline as $i => $entry)
                            @php $allocation = $entry['allocation']; @endphp
                            <div class="flex gap-3 relative pb-5">
                                <div class="flex flex-col items-center shrink-0">
                                    <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center z-10">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l6.75 6.75M12 4.5l-6.75 6.75" /></svg>
                                    </span>
                                    @if (! $loop->last)
                                        <span class="w-px flex-1 bg-gray-200 mt-1"></span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ __('Sold :qty :unit', ['qty' => rtrim(rtrim(number_format($allocation->quantity, 2), '0'), '.'), 'unit' => $batch->item->unit]) }}{{ $entry['lot_finished'] ? ', '.__('lot finished') : '' }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $allocation->created_at->format('d M, h:i A') }} &middot; {{ $allocation->movement->user->name ?? '—' }}</p>
                                        @if ($allocation->movement->referenceUrl())
                                            <a href="{{ $allocation->movement->referenceUrl() }}" class="text-xs text-brand-600 hover:underline font-medium">{{ __('View bill') }} &rsaquo;</a>
                                        @elseif ($allocation->movement->reference_type === null)
                                            <a href="{{ route('stock.edit', $allocation->movement) }}" class="text-xs text-brand-600 hover:underline font-medium">{{ __('View entry') }} &rsaquo;</a>
                                        @endif
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-brand-700">-{{ rtrim(rtrim(number_format($allocation->quantity, 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                                        <p class="text-xs text-gray-400">{{ __('Left') }} {{ rtrim(rtrim(number_format($entry['remaining_after'], 2), '0'), '.') }} {{ $batch->item->unit }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($timeline->isEmpty())
                            <p class="text-sm text-gray-400 text-center py-6">{{ __('Nothing has been sold from this lot yet.') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-4">
                    <div class="bg-white shadow-sm rounded-lg p-4 lg:p-6">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ __('Cost of this lot') }}</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('Cost per :unit', ['unit' => $batch->item->unit]) }}</span>
                                <span class="font-semibold text-gray-900">&#8377;{{ number_format($batch->unit_cost, 2) }}</span>
                            </div>
                            @if ($batch->item->hasAltUnit())
                                <div class="flex justify-between">
                                    <span class="text-gray-500">{{ __('Cost per :unit', ['unit' => $batch->item->alt_unit]) }}</span>
                                    <span class="font-semibold text-gray-900">&#8377;{{ number_format((float) $batch->unit_cost / (float) $batch->item->alt_unit_ratio, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-gray-100">
                                <span class="text-gray-500">{{ __('Total cost') }}</span>
                                <span class="font-bold text-gray-900">&#8377;{{ number_format((float) $batch->unit_cost * (float) $batch->quantity_in, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($fifoTip)
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
                            {{ __('Next :item lot: :date, :qty :unit still left. Try to sell older lots first so maal does not stay too long in the godown.', [
                                'item' => $batch->item->name,
                                'date' => $fifoTip->received_at->format('d M'),
                                'qty' => rtrim(rtrim(number_format($fifoTip->quantity_remaining, 2), '0'), '.'),
                                'unit' => $batch->item->unit,
                            ]) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 flex gap-2 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            @if ($isPurchase)
                <a href="{{ $batch->sourceMovement->referenceUrl() }}" class="flex-1 flex items-center justify-center px-3 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg">
                    {{ __('Purchase bill') }}
                </a>
            @endif
            <a href="{{ route('items.show', $batch->item) }}" class="flex-1 flex items-center justify-center px-3 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('View item') }}
            </a>
        </div>
    </div>
</x-app-layout>
